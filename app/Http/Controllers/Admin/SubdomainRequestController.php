<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubdomainRequest;
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

    public function approve(SubdomainRequest $subdomainRequest, SubdomainService $service): RedirectResponse
    {
        $service->approve($subdomainRequest, auth()->id());

        return back()->with('success', "“{$subdomainRequest->requested_subdomain}” onaylandı ve yayına alındı.");
    }

    public function reject(Request $request, SubdomainRequest $subdomainRequest, SubdomainService $service): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->reject($subdomainRequest, auth()->id(), $validated['admin_note'] ?? null);

        return back()->with('success', 'Talep reddedildi.');
    }
}
