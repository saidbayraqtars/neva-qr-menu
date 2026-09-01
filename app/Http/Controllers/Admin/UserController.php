<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->value();

        $users = User::query()
            ->where('role', User::ROLE_OWNER)
            ->with(['restaurants:id,user_id,name,slug,subdomain,status', 'subscriptions.plan'])
            ->when($q, fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    /** Admin'in elle açtığı yeni hesap — üyelik talebi olmadan, doğrudan aktif. */
    public function store(Request $request, MembershipService $service): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:40'],
            'plan_id' => ['nullable', Rule::exists('plans', 'id')->where('is_active', true)],
        ], [
            'email.unique' => 'Bu e-posta ile zaten bir hesap var.',
        ]);

        [$user, $temp] = $service->createAccount(
            $validated['business_name'],
            $validated['name'],
            $validated['email'],
            $validated['plan_id'] ?? null,
            $validated['phone'] ?? null,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "“{$validated['business_name']}” hesabı oluşturuldu — {$user->email}. Geçici şifre: {$temp}");
    }

    public function resetPassword(User $user, MembershipService $service): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Admin şifresi buradan sıfırlanamaz.');

        $temp = $service->resetPassword($user);

        return back()->with('success', "{$user->name} için yeni geçici şifre: {$temp} — kullanıcı ilk girişte değiştirmek zorunda.");
    }
}
