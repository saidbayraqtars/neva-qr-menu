<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kayıt Ol — 2 adımlı sihirbaz. HESAP / ŞİFRE YARATILMAZ.
 *  1) İşletme + iletişim bilgileri
 *  2) Paket seçimi
 * → 'pending' bir MembershipRequest oluşur, kullanıcıya "Talebiniz alındı" ekranı gösterilir.
 * Admin ödemeyi onayladığında hesap + geçici şifre üretilir.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class),
                Rule::unique(MembershipRequest::class)->where('status', MembershipRequest::STATUS_PENDING),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'table_count' => ['nullable', 'integer', 'min:1', 'max:2000'],
        ], [
            'email.unique' => 'Bu e-posta ile zaten bir hesap veya bekleyen bir başvuru var.',
        ]);

        $plan = Plan::find($validated['plan_id']);
        $tableCount = $plan->hasTablePricing() ? ($validated['table_count'] ?? $plan->setup_table_limit) : null;

        $membershipRequest = MembershipRequest::create([
            'business_name' => $validated['business_name'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'plan_id' => $plan->id,
            'table_count' => $tableCount,
            'amount' => MembershipRequest::computeAmount($plan, $tableCount),
            'status' => MembershipRequest::STATUS_PENDING,
            'payment_status' => MembershipRequest::PAYMENT_UNPAID,
        ]);

        return redirect()->route('register.received')->with('membership_request_id', $membershipRequest->id);
    }

    public function received(Request $request): View
    {
        $membershipRequest = MembershipRequest::find($request->session()->get('membership_request_id'));

        abort_if($membershipRequest === null, 404);

        return view('auth.register-received', ['membershipRequest' => $membershipRequest]);
    }
}
