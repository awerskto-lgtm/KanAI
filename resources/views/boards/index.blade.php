<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight">Tablice Kanban</h2>
            <span class="text-xs text-slate-500">Wybierz obszar pracy</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if(session('status'))
                <div class="p-3 bg-emerald-100 text-emerald-900 rounded-xl">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('teams.store') }}" class="glass-card p-4 space-y-2">
                    @csrf
                    <h3 class="font-semibold">Utwórz zespół (Team)</h3>
                    <select name="organization_id" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" required>
                        <option value="">Wybierz organizację</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="name" placeholder="Nazwa zespołu" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" required>
                    <button class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm">Dodaj zespół</button>
                </form>

                <form method="POST" action="{{ route('boards.store') }}" class="glass-card p-4 space-y-2">
                    @csrf
                    <h3 class="font-semibold">Utwórz tablicę</h3>
                    <select name="team_id" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" required>
                        <option value="">Wybierz zespół</option>
                        @foreach($manageableTeams as $team)
                            <option value="{{ $team->id }}">{{ $team->organization->name }} / {{ $team->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="name" placeholder="Nazwa tablicy" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" required>
                    <textarea name="columns" rows="3" placeholder="Stany (po 1 linii), np. Backlog\nPrzygotowanie\nMontaż\nTesty\nDone" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900"></textarea>
                    <button class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm">Utwórz tablicę</button>
                </form>
            </div>

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
