<?php

namespace App\View\Composers;

use App\Models\HelpMessage;
use App\Models\HelpTicket;
use App\Models\OrganizerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotifComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user) {
            $view->with(['notifCount' => 0, 'notifItems' => [], 'helpTerbuka' => 0]);

            return;
        }

        $items = [];

        $req = OrganizerRequest::where('user_id', $user->id)
            ->where('status', '!=', 'diajukan')
            ->whereNull('seen_at')
            ->first();

        if ($req) {
            $items[] = [
                'type' => 'organizer_request',
                'text' => $req->status === 'disetujui'
                    ? 'Pengajuan organizer disetujui!'
                    : 'Pengajuan organizer ditolak.',
                'url' => route('dashboard'),
            ];
        }

        $unread = HelpMessage::whereNull('read_at')
            ->whereHas('ticket', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('user', fn ($q) => $q->where('role', 'admin'))
            ->count();

        if ($unread > 0) {
            $items[] = [
                'type' => 'help_reply',
                'text' => $unread.' balasan bantuan baru.',
                'url' => route('dashboard').'#bantuan',
            ];
        }

        $view->with([
            'notifCount' => count($items),
            'notifItems' => $items,
            'helpTerbuka' => $user->role === 'admin' ? HelpTicket::where('status', 'terbuka')->count() : 0,
        ]);
    }
}
