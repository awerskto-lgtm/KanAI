<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl leading-tight">{{ $board->name }}</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="copyBoardLink('{{ $shareUrl }}')" class="ms-btn">Kopiuj link widoku</button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 space-y-4">
            @if(session('status'))
                <div class="p-3 bg-emerald-100 text-emerald-900 rounded-xl">{{ session('status') }}</div>
            @endif

            <form method="GET" class="ms-card p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Szukaj (także po archiwum)" class="ms-input">
                <select name="column_id" class="ms-input">
                    <option value="">Wszystkie kolumny</option>
                    @foreach($board->columns as $column)
                        <option value="{{ $column->id }}" @selected($filters['column_id'] === $column->id)>{{ $column->name }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="include_archived" value="1" @checked($filters['include_archived'])> Pokaż archiwalne</label>
                <button class="ms-btn-primary">Filtruj</button>

                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="mine" value="1" @checked($filters['mine'])> Tylko moje</label>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="blocked" value="1" @checked($filters['blocked'])> Zablokowane</label>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="due_soon" value="1" @checked($filters['due_soon'])> Termin ≤ 3 dni</label>
            </form>

            <form method="POST" action="{{ route('boards.tasks.store', $board) }}" class="ms-card p-4 grid grid-cols-1 md:grid-cols-6 gap-3">
                @csrf
                <input type="text" name="title" placeholder="Szybkie dodanie zadania..." class="ms-input md:col-span-2" required>
                <select name="column_id" class="ms-input" required>
                    @foreach($board->columns as $column)
                        <option value="{{ $column->id }}">{{ $column->name }}</option>
                    @endforeach
                </select>
                <select name="type" class="ms-input">
                    <option value="general">General</option><option value="maintenance">Maintenance</option><option value="qa">QA</option><option value="it">IT</option><option value="incident">Incident</option>
                </select>
                <select name="priority" class="ms-input">
                    <option value="medium">Medium</option><option value="low">Low</option><option value="high">High</option><option value="critical">Critical</option>
                </select>
                <button class="ms-btn-primary">Dodaj zadanie</button>
                <select name="assignee_id" class="ms-input">
                    <option value="">Bez przypisania</option>
                    @foreach($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="due_at" class="ms-input">
                <input type="text" name="description" placeholder="Krótki opis (opcjonalnie)" class="ms-input md:col-span-3">
            </form>

            @if($canManageBoard)
                <form method="POST" action="{{ route('boards.columns.store', $board) }}" class="ms-card p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                    @csrf
                    <input type="text" name="name" placeholder="Nowy stan/kolumna" class="ms-input" required>
                    <select name="type" class="ms-input"><option value="custom">Pośredni</option><option value="backlog">Backlog</option><option value="doing">W toku</option><option value="review">Review</option><option value="blocked">Blocked</option><option value="done">Done</option></select>
                    <input type="number" min="1" max="100" name="wip_limit" placeholder="WIP limit" class="ms-input">
                    <button class="ms-btn">Dodaj stan</button>
                </form>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
                <div class="xl:col-span-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4" id="board-columns">
                    @foreach($board->columns as $column)
                        @php($columnTasks = $tasksByColumn->get($column->id, collect()))
                        <section class="ms-card p-3 min-h-44 drop-column" data-column-id="{{ $column->id }}">
                            <header class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-sm">{{ $column->name }}</h3>
                                @if($column->wip_limit)
                                    <span class="text-xs text-slate-500">WIP {{ $columnTasks->whereNull('archived_at')->count() }}/{{ $column->wip_limit }}</span>
                                @endif
                            </header>
                            <div class="space-y-3">
                                @foreach($columnTasks as $task)
                                    <article class="rounded-xl border border-slate-200 p-3 bg-white task-card" draggable="{{ $task->archived_at ? 'false' : 'true' }}" data-task-id="{{ $task->id }}" data-move-url="{{ route('tasks.move', $task) }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="font-medium text-sm">{{ $task->title }}</p>
                                            @if($task->archived_at)<span class="text-[10px] px-2 py-1 rounded bg-amber-100 text-amber-900">Archiwum</span>@endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">{{ ucfirst($task->priority) }} · {{ strtoupper($task->type) }}</p>
                                        <p class="text-xs text-slate-500">{{ $task->assignee?->name ?? 'Bez przypisania' }}</p>

                                        <form method="POST" action="{{ route('tasks.move', $task) }}" class="mt-2">@csrf
                                            <select name="to_column_id" class="w-full text-xs rounded border-slate-300">@foreach($board->columns as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select>
                                            <button class="mt-2 w-full text-xs bg-slate-900 text-white rounded px-2 py-1">Przenieś</button>
                                        </form>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
                <aside class="ms-card p-3">
                    <h3 class="font-semibold mb-2">Ostatnia aktywność</h3>
                    <ul class="space-y-2 text-xs">
                        @forelse($recentActivity as $event)
                            <li class="border-b border-slate-100 pb-2">
                                <p class="font-medium">{{ $event->action }}</p>
                                <p class="text-slate-500">{{ $event->created_at->diffForHumans() }}</p>
                            </li>
                        @empty
                            <li class="text-slate-500">Brak aktywności.</li>
                        @endforelse
                    </ul>
                </aside>
            </div>
        </div>
    </div>

    <script>
        async function copyBoardLink(url) {
            const ok = await window.copyToClipboard(url);
            if (!ok) { window.prompt('Skopiuj link ręcznie:', url); return; }
            alert('Link skopiowany do schowka.');
        }

        (() => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let draggedCard = null;
            document.querySelectorAll('.task-card[draggable="true"]').forEach((card) => {
                card.addEventListener('dragstart', () => { draggedCard = card; card.classList.add('opacity-50'); });
                card.addEventListener('dragend', () => { card.classList.remove('opacity-50'); });
            });
            document.querySelectorAll('.drop-column').forEach((column) => {
                column.addEventListener('dragover', (e) => { e.preventDefault(); column.classList.add('ring-2', 'ring-blue-400'); });
                column.addEventListener('dragleave', () => { column.classList.remove('ring-2', 'ring-blue-400'); });
                column.addEventListener('drop', async (e) => {
                    e.preventDefault(); column.classList.remove('ring-2', 'ring-blue-400'); if (!draggedCard) return;
                    const response = await fetch(draggedCard.dataset.moveUrl, { method:'POST', headers:{'X-CSRF-TOKEN':token,'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({ to_column_id: column.dataset.columnId })});
                    if (!response.ok) { const payload = await response.json().catch(() => ({})); alert(payload.message || 'Nie udało się przenieść zadania.'); return; }
                    window.location.reload();
                });
            });
        })();
    </script>
</x-app-layout>
