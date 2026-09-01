<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function contact(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:180'],
                'message' => ['required', 'string', 'max:2000'],
            ]);

            // MVP: mesajı log'a düşür (Faz-2'de e-posta / CRM).
            logger()->channel('stack')->info('İletişim formu', $request->only('name', 'email', 'message'));

            return back()->with('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
        }

        return view('marketing.contact');
    }

    private function plans()
    {
        return Plan::where('is_active', true)->orderBy('sort_order')->get();
    }
}
