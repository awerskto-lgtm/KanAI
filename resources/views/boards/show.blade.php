<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl leading-tight">{{ $board->name }}</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}')" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm">Kopiuj link widoku</button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 space-y-4">
            @if(session('status'))
                <div class="p-3 bg-emerald-100 text-emerald-900 rounded-xl">{{ session('status') }}</div>
            @endif

            <form method="GET" class="glass-card p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Szukaj (także po archiwum)" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                <select name="column_id" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                    <option value="">Wszystkie kolumny</option>
                    @foreach($board->columns as $column)
                        <option value="{{ $column->id }}" @selected($filters['column_id'] === $column->id)>{{ $column->name }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="include_archived" value="1" @checked($filters['include_archived'])>
                    Pokaż archiwalne
                </label>
                <button class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm">Filtruj</button>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($board->columns as $column)
                    @php($columnTasks = $tasksByColumn->get($column->id, collect()))
                    <section class="glass-card p-3 min-h-44">
                        <header class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-sm">{{ $column->name }}</h3>
                            @if($column->wip_limit)
                                <span class="text-xs text-slate-500">WIP {{ $columnTasks->whereNull('archived_at')->count() }}/{{ $column->wip_limit }}</span>
                            @endif
                        </header>
                        <div class="space-y-3">
                            @foreach($columnTasks as $task)
                                <article class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 bg-white/80 dark:bg-slate-900/70">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-medium text-sm">{{ $task->title }}</p>
                                        @if($task->archived_at)
                                            <span class="text-[10px] px-2 py-1 rounded bg-amber-100 text-amber-900">Archiwum</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">Priorytet: {{ $task->priority }}</p>

                                    <form method="POST" action="{{ route('tasks.move', $task) }}" class="mt-2">
                                        @csrf
                                        <select name="to_column_id" class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                                            @foreach($board->columns as $option)
                                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="mt-2 w-full text-xs bg-slate-900 text-white rounded px-2 py-1">Przenieś</button>
                                    </form>

                                    <form method="POST" action="{{ $task->archived_at ? route('tasks.unarchive', $task) : route('tasks.archive', $task) }}" class="mt-2">
                                        @csrf
                                        @unless($task->archived_at)
                                            <input type="text" name="archive_reason" placeholder="Powód archiwizacji" class="w-full text-xs rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 mb-2">
                                        @endunless
                                        <button class="w-full text-xs rounded px-2 py-1 {{ $task->archived_at ? 'bg-emerald-700 text-white' : 'bg-amber-600 text-white' }}">{{ $task->archived_at ? 'Przywróć' : 'Archiwizuj' }}</button>
                                    </form>

                                    <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <input type="file" name="attachment" class="w-full text-xs">
                                        <button class="mt-1 w-full text-xs rounded px-2 py-1 bg-indigo-600 text-white">Dodaj plik</button>
                                    </form>

                                    @if($task->attachments->isNotEmpty())
                                        <ul class="mt-2 space-y-1">
                                            @foreach($task->attachments as $attachment)
                                                <li>
                                                    <a href="{{ route('attachments.download', $attachment) }}" class="text-xs text-indigo-600 dark:text-indigo-300 underline">{{ $attachment->file_name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
