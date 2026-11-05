<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public function index()
    {
        return view('organizer.checkin');
    }

    public function store(Request $request)
    {
        $request->validate(['kode' => 'required|string']);

        $kode = $this->extractKode(trim($request->kode));

        $ticket = Ticket::with('order.user', 'order.ticketType.event')
            ->where('kode', strtoupper($kode))
            ->first();

        if (! $ticket || ! $ticket->order || ! $ticket->order->ticketType || ! $ticket->order->ticketType->event) {
            return back()->with('error', 'Kode tiket tidak ditemukan.');
        }

        if ($ticket->order->ticketType->event->organizer_id !== auth()->id()) {
            return back()->with('error', 'Kode tiket tidak ditemukan.');
        }

        if ($ticket->order->status !== 'lunas') {
            return back()->with('error', 'Pesanan tiket ini belum lunas.');
        }

        if ($ticket->status !== 'aktif') {
            return back()->with('error', 'Tiket ini sudah dipakai.');
        }

        // Deadline only at event finish; early check-in before start allowed.
        if (now()->gte($ticket->order->ticketType->event->tanggal_selesai)) {
            return back()->with('error', 'Masa berlaku tiket sudah berakhir.');
        }

        $claimed = DB::transaction(function () use ($ticket) {
            Ticket::whereKey($ticket->id)->update(['updated_at' => now()]);
            $fresh = Ticket::with('order.ticketType.event')->whereKey($ticket->id)->first();

            if (! $fresh || $fresh->status !== 'aktif') {
                return 'used';
            }
            if (! $fresh->order?->ticketType?->event
                || $fresh->order->ticketType->event->organizer_id !== auth()->id()) {
                return 'missing';
            }
            if ($fresh->order->status !== 'lunas') {
                return 'unpaid';
            }
            if (now()->gte($fresh->order->ticketType->event->tanggal_selesai)) {
                return 'expired';
            }

            $rows = Ticket::whereKey($fresh->id)->where('status', 'aktif')
                ->update(['status' => 'terpakai', 'waktu_checkin' => now()]);

            return $rows ? 'ok' : 'used';
        }, 3);

        if ($claimed === 'expired') {
            return back()->with('error', 'Masa berlaku tiket sudah berakhir.');
        }
        if ($claimed === 'missing') {
            return back()->with('error', 'Kode tiket tidak ditemukan.');
        }
        if ($claimed === 'unpaid') {
            return back()->with('error', 'Pesanan tiket ini belum lunas.');
        }
        if ($claimed !== 'ok') {
            return back()->with('error', 'Tiket ini sudah dipakai.');
        }

        return back()->with('success', 'Check-in berhasil: '.$ticket->order->user->name
            .' - '.$ticket->order->ticketType->event->judul);
    }

    private function extractKode(string $input): string
    {
        $code = $input;
        if (str_contains($input, '/')) {
            $path = parse_url($input, PHP_URL_PATH) ?: $input;
            $code = basename(rtrim($path, '/'));
        }
        $code = strtok($code, '?# ') ?: $code;

        return trim($code);
    }
}
