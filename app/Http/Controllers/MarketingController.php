<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', ['plans' => $this->plans()]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', ['plans' => $this->plans()]);
    }

    public function about(): View
    {
        return view('marketing.about');
    }

    /**
     * İletişim.
     *  - Giriş YAPMAMIŞ ziyaretçi: klasik form → contact_messages + destek e-postası.
     *  - Giriş YAPMIŞ kullanıcı: ad/e-posta tekrar sorulmaz, panel içi mesajlaşmaya yönlendirilir.
     */
    public function contact(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            // Bot tuzağı — insanlar bu alanı görmez, botlar doldurur.
            if (filled($request->input('website_url'))) {
                return back()->with('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:180'],
                'phone' => ['nullable', 'string', 'max:40'],
                'subject' => ['nullable', 'string', 'max:150'],
                'message' => ['required', 'string', 'min:10', 'max:2000'],
            ]);

            $contactMessage = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'body' => $validated['message'],
                'status' => ContactMessage::STATUS_NEW,
                'ip' => $request->ip(),
            ]);

            $this->notifySupport($contactMessage);

            return back()->with('success', 'Mesajınız bize ulaştı. En kısa sürede dönüş yapacağız.');
        }

        return view('marketing.contact', [
            'user' => $request->user(),
        ]);
    }

    private function notifySupport(ContactMessage $message): void
    {
        try {
            $admins = User::where('role', User::ROLE_ADMIN)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new ContactMessageReceived($message));

                return;
            }

            Notification::route('mail', config('neva.brand.support_email'))
                ->notify(new ContactMessageReceived($message));
        } catch (\Throwable $e) {
            report($e); // e-posta gitmese de form kaydı DB'de duruyor
        }
    }

    private function plans()
    {
        return Plan::where('is_active', true)->orderBy('sort_order')->get();
    }
}
