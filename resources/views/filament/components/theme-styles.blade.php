<style>
    :root {
        --theme-shell-light: radial-gradient(circle at top, rgba(251, 191, 36, 0.18), transparent 34%), linear-gradient(180deg, #fffdf6 0%, #f3efe4 100%);
        --theme-shell-dark: radial-gradient(circle at top, rgba(251, 191, 36, 0.14), transparent 28%), linear-gradient(180deg, #14110f 0%, #09090b 100%);
        --theme-panel-light: linear-gradient(180deg, rgba(255, 255, 255, 0.94) 0%, rgba(250, 246, 237, 0.96) 100%);
        --theme-panel-dark: linear-gradient(180deg, rgba(24, 24, 27, 0.96) 0%, rgba(10, 10, 12, 0.98) 100%);
    }

    html:not(.dark) body {
        background: var(--theme-shell-light);
    }

    html.dark body {
        background: var(--theme-shell-dark);
    }

    html:not(.dark) .fi-sidebar,
    html:not(.dark) .fi-topbar,
    html:not(.dark) .fi-ta-ctn,
    html:not(.dark) .fi-section,
    html:not(.dark) .fi-wi-stats-overview-stat,
    html:not(.dark) .fi-fo-field-wrp,
    html:not(.dark) .fi-modal-window,
    html:not(.dark) .fi-dropdown-panel,
    html:not(.dark) .fi-theme-panel {
        background: var(--theme-panel-light);
        box-shadow: 0 20px 60px rgba(148, 123, 77, 0.08);
    }

    html.dark .fi-sidebar,
    html.dark .fi-topbar,
    html.dark .fi-ta-ctn,
    html.dark .fi-section,
    html.dark .fi-wi-stats-overview-stat,
    html.dark .fi-fo-field-wrp,
    html.dark .fi-modal-window,
    html.dark .fi-dropdown-panel,
    html.dark .fi-theme-panel {
        background: var(--theme-panel-dark);
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.34);
    }

    html:not(.dark) .fi-sidebar-header,
    html:not(.dark) .fi-topbar nav,
    html:not(.dark) .fi-ta-header-toolbar,
    html:not(.dark) .fi-theme-panel {
        background-color: transparent;
    }

    html:not(.dark) .fi-sidebar {
        border-right: 1px solid rgba(161, 98, 7, 0.14);
    }

    html.dark .fi-sidebar {
        border-right: 1px solid rgba(251, 191, 36, 0.1);
    }

    html:not(.dark) .fi-main {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.45) 0%, rgba(255, 248, 235, 0.18) 100%);
    }

    html.dark .fi-main {
        background: linear-gradient(180deg, rgba(16, 16, 19, 0.28) 0%, rgba(9, 9, 11, 0.08) 100%);
    }

    .fi-theme-panel-select {
        color-scheme: light;
    }

    html.dark .fi-theme-panel-select {
        color-scheme: dark;
    }
</style>
