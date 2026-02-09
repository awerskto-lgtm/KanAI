<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight">Tablice Kanban</h2>
            <span class="text-xs text-slate-500">Wybierz obszar pracy</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($boards as $board)
                    <article class="glass-card p-4">
                        <p class="font-semibold text-lg">{{ $board->name }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-300">{{ $board->organization->name }} · {{ $board->team->name }}</p>
                        <a href="{{ route('boards.show', $board) }}" class="inline-block mt-4 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">Otwórz tablicę</a>
                    </article>
                @endforeach
            </div>
            <div class="mt-6">{{ $boards->links() }}</div>
        </div>
    </div>
</x-app-layout>
