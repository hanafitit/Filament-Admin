<div
    x-data="{ theme: 'dark' }"
    x-init="
        theme = localStorage.getItem('theme') || @js(filament()->getDefaultThemeMode()->value)

        $watch('theme', (value) => {
            $dispatch('theme-changed', value)
        })
    "
    class="fi-theme-panel border-t border-gray-200/80 bg-white/80 px-4 py-4 backdrop-blur-sm dark:border-white/10 dark:bg-gray-900/85"
>
    <label
        for="sidebar-theme-select"
        class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-500 dark:text-gray-400"
    >
        Тема интерфейса
    </label>

    <div class="relative">
        <select
            id="sidebar-theme-select"
            x-model="theme"
            class="fi-theme-panel-select w-full appearance-none rounded-2xl border border-gray-200 bg-white px-4 py-3 pr-11 text-sm font-medium text-gray-700 shadow-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 dark:focus:border-amber-300 dark:focus:ring-amber-400/20"
        >
            <option value="dark">Текущий</option>
            <option value="light">Светлый</option>
            <option value="system">Системный</option>
        </select>

        <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400 dark:text-gray-500">
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                    clip-rule="evenodd"
                />
            </svg>
        </span>
    </div>

    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
        Системный режим подстроится под тему устройства.
    </p>
</div>
