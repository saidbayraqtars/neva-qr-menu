<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->value() ?: 'pending';

        $requests = MembershipRequest::with(['plan', 'user', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.membership-requests.index', compact('requests', 'status'));
    }

    public function togglePayment(MembershipRequest $membershipRequest): RedirectResponse
    {
        abort_if(! $membershipRequest->isPending(), 422, 'Yalnızca bekleyen talepler güncellenebilir.');

        $membershipRequest->update([
            'payment_status' => $membershipRequest->isPaid()
                ? MembershipRequest::PAYMENT_UNPAID
                : MembershipRequest::PAYMENT_PAID,
        ]);

        return back()->with('success', 'Ödeme durumu güncellendi.');
    }

    public function approve(MembershipRequest $membershipRequest, MembershipService $service): RedirectResponse
    {
        abort_if(! $membershipRequest->isPending(), 422, 'Bu talep zaten sonuçlanmış.');

        $approved = $service->approve($membershipRequest, (int) auth()->id());

        return back()->with('success', "“{$approved->business_name}” hesabı oluşturuldu. Geçici şifre: {$approved->temp_password}");
    }

    public function reject(Request $request, MembershipRequest $membershipRequest, MembershipService $service): RedirectResponse
    {
        abort_if(! $membershipRequest->isPending(), 422, 'Bu talep zaten sonuçlanmış.');

        $validated = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        $service->reject($membershipRequest, (int) auth()->id(), $validated['admin_note'] ?? null);

        return back()->with('success', 'Talep reddedildi.');
    }
}
