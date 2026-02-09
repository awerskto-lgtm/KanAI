<nav x-data="{ open: false }" class="sticky top-0 z-30 backdrop-blur border-b border-slate-200/60 dark:border-slate-700/60 bg-white/80 dark:bg-slate-900/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="font-bold text-lg text-indigo-600 dark:text-indigo-300">KanAI</a>
                <div class="hidden sm:flex space-x-5">
                    <x-nav-link :href="route('boards.index')" :active="request()->routeIs('boards.*')">Tablice</x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-3">
                <div class="flex items-center gap-1 rounded-xl border border-slate-200 dark:border-slate-700 p-1 text-xs">
                    <button type="button" onclick="setTheme('light')" class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800">Dzień</button>
                    <button type="button" onclick="setTheme('dark')" class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800">Noc</button>
                    <button type="button" onclick="setTheme('auto')" class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800">Auto</button>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700">
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Wyloguj</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-md border border-slate-300 dark:border-slate-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-200 dark:border-slate-700 p-3 space-y-2">
        <x-responsive-nav-link :href="route('boards.index')" :active="request()->routeIs('boards.*')">Tablice</x-responsive-nav-link>
        <div class="flex gap-2 text-xs">
            <button type="button" onclick="setTheme('light')" class="px-2 py-1 rounded border border-slate-300 dark:border-slate-700">Dzień</button>
            <button type="button" onclick="setTheme('dark')" class="px-2 py-1 rounded border border-slate-300 dark:border-slate-700">Noc</button>
            <button type="button" onclick="setTheme('auto')" class="px-2 py-1 rounded border border-slate-300 dark:border-slate-700">Auto</button>
        </div>
        <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Wyloguj</x-responsive-nav-link>
        </form>
    </div>
</nav>
