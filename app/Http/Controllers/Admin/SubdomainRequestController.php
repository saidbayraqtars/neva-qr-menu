<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SubdomainRequest;
use App\Notifications\SubdomainDecision;
use App\Services\SubdomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubdomainRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->value() ?: 'pending';

        $requests = SubdomainRequest::with(['restaurant.owner', 'restaurant.categories', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.requests.index', compact('requests', 'status'));
    }

    /**
     * Onay → alt domain yazılır, PublishSubdomain işi DNS + otomatik doğrulamayı yapar.
     */
    public function approve(SubdomainRequest $subdomainRequest, SubdomainService $service): RedirectResponse
    {
        $service->approve($subdomainRequest, (int) auth()->id());

        $restaurant = $subdomainRequest->restaurant->fresh();

        AuditLog::record('subdomain.approve', $subdomainRequest, [
            'label' => $subdomainRequest->requested_subdomain,
            'restaurant_id' => $restaurant->id,
        ]);

        $this->notifyOwner($restaurant, true, null);

        return back()->with('success', "\u{201C}{$subdomainRequest->requested_subdomain}\u{201D} onaylandı. Yayına alma ve otomatik doğrulama arka planda başlatıldı.");
    }

    public function reject(Request $request, SubdomainRequest $subdomainRequest, SubdomainService $service): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->reject($subdomainRequest, (int) auth()->id(), $validated['admin_note'] ?? null);

        AuditLog::record('subdomain.reject', $subdomainRequest, [
            'label' => $subdomainRequest->requested_subdomain,
            'note' => $validated['admin_note'] ?? null,
        ]);

        $this->notifyOwner($subdomainRequest->restaurant->fresh(), false, $validated['admin_note'] ?? null);

        return back()->with('success', 'Talep reddedildi ve işletmeye bildirildi.');
    }

    private function notifyOwner($restaurant, bool $approved, ?string $note): void
    {
        try {
            $restaurant?->owner?->notify(new SubdomainDecision($restaurant, $approved, $note));
        } catch (\Throwable $e) {
            report($e); // bildirim gitmemesi onayı bozmasın
        }
    }
}
