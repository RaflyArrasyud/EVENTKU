<x-public-layout>
    <h1 class="text-2xl font-bold mb-4">Edit Event</h1>
    <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data"
          class="bg-white p-6 rounded-lg shadow">
        @csrf @method('PUT')
        @include('organizer.events._form')
    </form>
</x-public-layout>