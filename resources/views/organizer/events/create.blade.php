<x-public-layout>
    <h1 class="text-2xl font-bold mb-4">Buat Event</h1>
    <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data"
          class="bg-white p-6 rounded-lg shadow">
        @csrf
        @include('organizer.events._form')
    </form>
</x-public-layout>