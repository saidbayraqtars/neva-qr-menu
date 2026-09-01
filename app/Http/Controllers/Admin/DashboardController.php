<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Conversation;
use App\Models\MembershipRequest;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'pending' => SubdomainRequest::pending()->count(),
                'live' => Restaurant::live()->count(),
                'owners' => User::where('role', User::ROLE_OWNER)->count(),
                'drafts' => Restaurant::where('status', Restaurant::STATUS_DRAFT)->count(),
            ],
            // Aksiyon bekleyenler — admin'in bugün yapması gerekenler
            'todo' => [
                'memberships' => MembershipRequest::pending()->count(),
                'paid_waiting' => MembershipRequest::pending()->where('payment_status', MembershipRequest::PAYMENT_PAID)->count(),
                'unread_messages' => (int) Conversation::sum('unread_for_admin'),
                'new_contacts' => ContactMessage::where('status', ContactMessage::STATUS_NEW)->count(),
            ],
            // Yayına alınamayan / doğrulaması başarısız adresler
            'broken' => Restaurant::live()
                ->where('publish_status', Restaurant::PUBLISH_FAILED)
                ->get(['id', 'name', 'subdomain', 'publish_error', 'last_health_check_at']),
            'recent' => SubdomainRequest::with('restaurant.owner')->latest()->limit(8)->get(),
        ]);
    }
}
