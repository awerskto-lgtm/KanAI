<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tablice Kanban</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-4">Wybierz tablicę przypisaną do Twojej organizacji.</p>
                <ul class="space-y-2">
                    @foreach($boards as $board)
                        <li class="border rounded-md p-3 flex items-center justify-between">
                            <div>
                                <p class="font-semibold">{{ $board->name }}</p>
                                <p class="text-xs text-gray-500">{{ $board->organization->name }} / {{ $board->team->name }}</p>
                            </div>
                            <a href="{{ route('boards.show', $board) }}" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm">Otwórz</a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4">{{ $boards->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
