<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Ziyaretçi (giriş yapmamış) iletişim formu kayıtları. */
class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->value() ?: 'new';

        $messages = ContactMessage::with('handler')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact.index', compact('messages', 'status'));
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,archived'],
        ]);

        $contactMessage->update([
            'status' => $validated['status'],
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('success', 'Kayıt güncellendi.');
    }
}
