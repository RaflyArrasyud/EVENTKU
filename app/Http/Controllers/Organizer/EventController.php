<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = auth()->user()->events()->latest()->paginate(10);

        return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        return view('organizer.events.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['organizer_id'] = auth()->id();

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        Event::create($data);

        return redirect()->route('organizer.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function edit(Event $event)
    {
        $this->authorizeOwner($event);

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeOwner($event);
        $data = $this->validated($request);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        $event->update($data);

        return redirect()->route('organizer.events.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeOwner($event);
        $event->delete();

        return back()->with('success', 'Event dihapus.');
    }

    private function authorizeOwner(Event $event): void
    {
        abort_if($event->organizer_id !== auth()->id(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kategori' => 'required|string|max:100',
            'tipe' => 'required|in:online,offline',
            'lokasi' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'poster' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,terbit',
        ]);
    }
}