<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $board->name }}</h2>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-3 p-3 bg-blue-100 text-blue-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 overflow-x-auto">
                @foreach($board->columns as $column)
                    <section class="bg-white rounded-lg shadow p-3 min-h-40">
                        <header class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-sm">{{ $column->name }}</h3>
                            @if($column->wip_limit)
                                <span class="text-xs text-gray-500">WIP {{ $column->tasks->count() }}/{{ $column->wip_limit }}</span>
                            @endif
                        </header>
                        <div class="space-y-3">
                            @foreach($column->tasks as $task)
                                <article class="border rounded p-2">
                                    <p class="font-medium text-sm">{{ $task->title }}</p>
                                    <p class="text-xs text-gray-500">Priorytet: {{ $task->priority }}</p>
                                    <form method="POST" action="{{ route('tasks.move', $task) }}" class="mt-2">
                                        @csrf
                                        <label class="text-xs">Przenieś</label>
                                        <select name="to_column_id" class="w-full text-xs border-gray-300 rounded mt-1">
                                            @foreach($board->columns as $option)
                                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="mt-2 w-full text-xs bg-gray-900 text-white rounded px-2 py-1">Zapisz</button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
