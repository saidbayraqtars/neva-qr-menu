<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Havale/EFT hesapları yönetimi.
 *
 * Para akışını belirleyen ekran: yanlış IBAN doğrudan gelir kaybı demek.
 * Bu yüzden IBAN mod-97 sağlamasından geçiyor ve her değişiklik denetim
 * kaydına yazılıyor.
 */
class PaymentAccountController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-accounts', [
            'accounts' => PaymentAccount::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $account = PaymentAccount::create($data + [
            'sort_order' => (int) PaymentAccount::max('sort_order') + 1,
        ]);

        AuditLog::record('payment_account.create', $account, ['bank' => $account->bank_name]);

        return back()->with('success', "“{$account->bank_name}” hesabı eklendi.");
    }

    public function update(Request $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        $paymentAccount->update($this->validated($request, $paymentAccount->id));

        AuditLog::record('payment_account.update', $paymentAccount, ['bank' => $paymentAccount->bank_name]);

        return back()->with('success', 'Hesap güncellendi.');
    }

    /** Aktif/pasif — silmeden gizlemek, geçmiş ödemeler için kaydı korur. */
    public function toggle(PaymentAccount $paymentAccount): RedirectResponse
    {
        $paymentAccount->update(['is_active' => ! $paymentAccount->is_active]);

        AuditLog::record('payment_account.toggle', $paymentAccount, ['active' => $paymentAccount->is_active]);

        return back()->with('success', $paymentAccount->is_active ? 'Hesap yayında.' : 'Hesap gizlendi.');
    }

    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        $name = $paymentAccount->bank_name;

        AuditLog::record('payment_account.delete', $paymentAccount, ['bank' => $name, 'iban' => $paymentAccount->iban]);

        $paymentAccount->delete();

        return back()->with('success', "“{$name}” hesabı silindi.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:120'],
            'account_name' => ['required', 'string', 'max:160'],
            'iban' => [
                'required', 'string', 'max:40',
                // Boşluklu girilse de tekillik normalize hâl üzerinden bakılmalı.
                Rule::unique('payment_accounts', 'iban')
                    ->ignore($ignoreId)
                    ->where(fn ($q) => $q->where('iban', PaymentAccount::normalizeIban((string) $request->input('iban')))),
                function (string $attribute, mixed $value, callable $fail) {
                    if (! PaymentAccount::ibanIsValid((string) $value)) {
                        $fail('IBAN geçersiz. TR ile başlayan 26 karakterli bir IBAN girin; sağlama hanesi tutmuyor.');
                    }
                },
            ],
            'note' => ['nullable', 'string', 'max:120'],
            'is_active' => ['boolean'],
        ], [
            'iban.unique' => 'Bu IBAN zaten kayıtlı.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
