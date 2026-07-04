<style>
    :root {
        --theme-shell-light: radial-gradient(circle at top, rgba(251, 191, 36, 0.18), transparent 34%), linear-gradient(180deg, #fffdf6 0%, #f3efe4 100%);
        --theme-shell-dark: radial-gradient(circle at top, rgba(251, 191, 36, 0.14), transparent 28%), linear-gradient(180deg, #14110f 0%, #09090b 100%);
        --theme-panel-light: linear-gradient(180deg, rgba(255, 255, 255, 0.94) 0%, rgba(250, 246, 237, 0.96) 100%);
        --theme-panel-dark: linear-gradient(180deg, rgba(24, 24, 27, 0.96) 0%, rgba(10, 10, 12, 0.98) 100%);
        --theme-border-light: rgba(161, 98, 7, 0.14);
        --theme-border-dark: rgba(251, 191, 36, 0.1);
        --theme-accent-light: rgba(245, 158, 11, 0.18);
        --theme-accent-dark: rgba(251, 191, 36, 0.16);
        --theme-card-light: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(255, 251, 235, 0.94) 100%);
        --theme-card-dark: linear-gradient(180deg, rgba(39, 39, 42, 0.92) 0%, rgba(17, 17, 19, 0.94) 100%);
    }

    html:not(.dark) body {
        background: var(--theme-shell-light);
    }

    html.dark body {
        background: var(--theme-shell-dark);
    }

    body::before,
    body::after {
        content: '';
        position: fixed;
        pointer-events: none;
        z-index: 0;
        filter: blur(16px);
        opacity: 0.8;
    }

    body::before {
        top: 6rem;
        right: -5rem;
        width: 22rem;
        height: 22rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, rgba(251, 191, 36, 0) 72%);
        animation: filamentFloat 16s ease-in-out infinite;
    }

    body::after {
        left: -7rem;
        bottom: 4rem;
        width: 24rem;
        height: 24rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(234, 88, 12, 0.12) 0%, rgba(234, 88, 12, 0) 72%);
        animation: filamentFloat 20s ease-in-out infinite reverse;
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

    .fi-sidebar,
    .fi-topbar,
    .fi-ta-ctn,
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-fo-field-wrp,
    .fi-modal-window,
    .fi-dropdown-panel,
    .fi-theme-panel {
        position: relative;
        z-index: 1;
        backdrop-filter: blur(18px);
        border-radius: 1.5rem;
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease, background-color 180ms ease;
    }

    html:not(.dark) .fi-sidebar-header,
    html:not(.dark) .fi-topbar nav,
    html:not(.dark) .fi-ta-header-toolbar,
    html:not(.dark) .fi-theme-panel {
        background-color: transparent;
    }

    html:not(.dark) .fi-sidebar {
        border-right: 1px solid var(--theme-border-light);
    }

    html.dark .fi-sidebar {
        border-right: 1px solid var(--theme-border-dark);
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

    .fi-page {
        position: relative;
        z-index: 1;
    }

    .fi-btn,
    .fi-icon-btn,
    .fi-badge,
    .fi-input,
    .fi-select-input,
    .fi-ta-record,
    .fi-dropdown-list-item {
        transition: transform 160ms ease, box-shadow 160ms ease, background-color 160ms ease, border-color 160ms ease, color 160ms ease;
    }

    .fi-btn:hover,
    .fi-icon-btn:hover,
    .fi-dropdown-list-item:hover {
        transform: translateY(-1px);
    }

    .fi-section:hover,
    .fi-ta-ctn:hover,
    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
    }

    .fi-theme-surface {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 1.75rem;
        isolation: isolate;
    }

    html:not(.dark) .fi-theme-surface {
        background: var(--theme-card-light);
        box-shadow: 0 18px 45px rgba(148, 123, 77, 0.12);
    }

    html.dark .fi-theme-surface {
        background: var(--theme-card-dark);
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.32);
    }

    .fi-theme-surface::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent 45%, rgba(251, 191, 36, 0.1));
        opacity: 0.7;
        pointer-events: none;
    }

    .fi-theme-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
        background-size: 22px 22px;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 88%);
        opacity: 0.3;
        pointer-events: none;
    }

    .fi-theme-hero {
        position: relative;
        overflow: hidden;
        padding: 1.5rem;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        animation: filamentFadeUp 520ms ease both;
    }

    html:not(.dark) .fi-theme-hero {
        background:
            radial-gradient(circle at top right, rgba(251, 191, 36, 0.2), transparent 35%),
            linear-gradient(135deg, rgba(255, 251, 235, 0.96), rgba(255, 255, 255, 0.94));
        box-shadow: 0 22px 48px rgba(148, 123, 77, 0.12);
    }

    html.dark .fi-theme-hero {
        background:
            radial-gradient(circle at top right, rgba(251, 191, 36, 0.16), transparent 35%),
            linear-gradient(135deg, rgba(39, 39, 42, 0.95), rgba(17, 17, 19, 0.94));
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.32);
    }

    .fi-theme-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
    }

    html:not(.dark) .fi-theme-pill {
        background: rgba(255, 255, 255, 0.7);
        color: rgb(120 53 15);
    }

    html.dark .fi-theme-pill {
        background: rgba(255, 255, 255, 0.06);
        color: rgb(253 230 138);
    }

    .fi-theme-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.35rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        animation: filamentFadeUp 460ms ease both;
    }

    html:not(.dark) .fi-theme-card {
        background: rgba(255, 255, 255, 0.72);
        box-shadow: 0 14px 34px rgba(148, 123, 77, 0.1);
    }

    html.dark .fi-theme-card {
        background: rgba(255, 255, 255, 0.04);
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.24);
    }

    .fi-theme-card:hover {
        transform: translateY(-3px);
    }

    html:not(.dark) .fi-theme-card:hover {
        box-shadow: 0 20px 45px rgba(148, 123, 77, 0.16);
        border-color: var(--theme-accent-light);
    }

    html.dark .fi-theme-card:hover {
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.32);
        border-color: var(--theme-accent-dark);
    }

    .fi-theme-card::after {
        content: '';
        position: absolute;
        inset: -30% auto auto -10%;
        width: 9rem;
        height: 9rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.18) 0%, rgba(251, 191, 36, 0) 75%);
        opacity: 0;
        transition: opacity 180ms ease;
        pointer-events: none;
    }

    .fi-theme-card:hover::after {
        opacity: 1;
    }

    .fi-theme-stat {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        padding: 1rem 1.1rem;
        border-radius: 1.15rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    html:not(.dark) .fi-theme-stat {
        background: rgba(255, 255, 255, 0.58);
    }

    html.dark .fi-theme-stat {
        background: rgba(255, 255, 255, 0.05);
    }

    .fi-theme-delay-1 { animation-delay: 60ms; }
    .fi-theme-delay-2 { animation-delay: 120ms; }
    .fi-theme-delay-3 { animation-delay: 180ms; }
    .fi-theme-delay-4 { animation-delay: 240ms; }

    @keyframes filamentFadeUp {
        from {
            opacity: 0;
            transform: translateY(18px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes filamentFloat {
        0%,
        100% {
            transform: translate3d(0, 0, 0);
        }

        50% {
            transform: translate3d(0, -12px, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        body::before,
        body::after,
        .fi-theme-hero,
        .fi-theme-card,
        .fi-section,
        .fi-ta-ctn,
        .fi-wi-stats-overview-stat,
        .fi-btn,
        .fi-icon-btn,
        .fi-dropdown-list-item {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>
