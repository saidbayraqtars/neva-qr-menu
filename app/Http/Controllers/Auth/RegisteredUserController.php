<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\MembershipReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kayıt Ol — 2 adımlı sihirbaz. HESAP / ŞİFRE YARATILMAZ.
 *  1) İşletme + iletişim bilgileri
 *  2) Paket seçimi
 * → 'pending' bir MembershipRequest oluşur + havale referans kodu üretilir.
 * Kullanıcıya IBAN + referans kodu gösterilir ve e-posta ile gönderilir.
 * Havale gelince admin işaretler, onaylar; hesap açılır.
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
            'reference_code' => $this->uniqueReference(),
            'status' => MembershipRequest::STATUS_PENDING,
            'payment_status' => MembershipRequest::PAYMENT_UNPAID,
        ]);

        $this->sendInstructions($membershipRequest);

        return redirect()
            ->route('register.received')
            ->with('membership_request_id', $membershipRequest->id);
    }

    public function received(Request $request): View
    {
        $membershipRequest = MembershipRequest::with('plan')
            ->find($request->session()->get('membership_request_id'));

        abort_if($membershipRequest === null, 404);

        return view('auth.register-received', [
            'membershipRequest' => $membershipRequest,
            'bank' => (array) config('neva.payment.bank'),
        ]);
    }

    /** NQR-XXXXXX — havale açıklamasına yazılacak tekil kod. */
    private function uniqueReference(): string
    {
        $prefix = (string) config('neva.payment.reference_prefix', 'NQR');

        do {
            $code = $prefix.'-'.Str::upper(Str::random(6));
        } while (MembershipRequest::where('reference_code', $code)->exists());

        return $code;
    }

    private function sendInstructions(MembershipRequest $membershipRequest): void
    {
        try {
            Notification::route('mail', $membershipRequest->email)
                ->notify(new MembershipReceived($membershipRequest->load('plan')));
        } catch (\Throwable $e) {
            report($e); // e-posta gitmese de bilgiler ekranda gösteriliyor
        }
    }
}
