<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MembershipRequest;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Üyelik talepleri — havale/EFT akışı.
 *   1) Kullanıcı kayıt olur → talep 'pending', ödeme 'unpaid', referans kodu üretilir
 *   2) Havale gelince admin "Ödeme alındı" işaretler
 *   3) Admin onaylar → hesap açılır, kullanıcıya şifre belirleme linki gider
 */
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

        return view('admin.membership-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'bank' => (array) config('neva.payment.bank'),
        ]);
    }

    /** Havale geldi / gelmedi işaretle. */
    public function togglePayment(Request $request, MembershipRequest $membershipRequest): RedirectResponse
    {
        abort_if(! $membershipRequest->isPending(), 422, 'Yalnızca bekleyen talepler güncellenebilir.');

        $validated = $request->validate([
            'payment_note' => ['nullable', 'string', 'max:255'],
        ]);

        $nowPaid = ! $membershipRequest->isPaid();

        $membershipRequest->update([
            'payment_status' => $nowPaid ? MembershipRequest::PAYMENT_PAID : MembershipRequest::PAYMENT_UNPAID,
            'paid_at' => $nowPaid ? now() : null,
            'payment_note' => $validated['payment_note'] ?? $membershipRequest->payment_note,
        ]);

        AuditLog::record($nowPaid ? 'membership.paid' : 'membership.unpaid', $membershipRequest, [
            'reference' => $membershipRequest->reference_code,
            'amount' => (string) $membershipRequest->amount,
        ]);

        return back()->with('success', $nowPaid ? 'Ödeme alındı olarak işaretlendi.' : 'Ödeme işareti kaldırıldı.');
    }

    public function approve(MembershipRequest $membershipRequest, MembershipService $service): RedirectResponse
    {
        abort_if(! $membershipRequest->isPending(), 422, 'Bu talep zaten sonuçlanmış.');

        if (! $membershipRequest->isPaid()) {
            return back()->with('error', 'Önce havalenin geldiğini "Ödeme alındı" ile işaretleyin.');
        }

        $approved = $service->approve($membershipRequest, (int) auth()->id());

        AuditLog::record('membership.approve', $membershipRequest, [
            'user_id' => $approved->user_id,
            'plan' => $approved->plan?->slug,
        ]);

        return back()->with('success', "\u{201C}{$approved->business_name}\u{201D} hesabı açıldı. Şifre belirleme bağlantısı {$approved->email} adresine gönderildi.");
    }

    public function reject(Request $request, MembershipRequest $membershipRequest, MembershipService $service): RedirectResponse
    {
        abort_if(! $membershipRequest->isPending(), 422, 'Bu talep zaten sonuçlanmış.');

        $validated = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        $service->reject($membershipRequest, (int) auth()->id(), $validated['admin_note'] ?? null);

        AuditLog::record('membership.reject', $membershipRequest, ['note' => $validated['admin_note'] ?? null]);

        return back()->with('success', 'Talep reddedildi.');
    }
}
