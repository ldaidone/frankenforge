<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
/**
 * Shared Tailwind CDN theme configuration.
 *
 * Included in both layouts — theme applies site-wide.
 *
 * Semantic tokens:
 *   Page:    --app-bg / --app-text
 *   Cards:   --app-section / --app-section-border / --app-section-hover
 *   Text:    --app-text (primary) / --app-muted (secondary)
 *   Brand:   orange scale (accents, icons, CTAs)
 *   Font:    JetBrains Mono (mono), Inter (sans)
 */
?>
<style>
    /* HTMX indicator — hidden by default, visible during requests */
    .htmx-indicator {
        opacity: 0;
        transition: opacity 200ms ease-in-out;
    }
    .htmx-request .htmx-indicator {
        opacity: 1;
    }

    /* OOB swap fade transition */
    .htmx-swapping {
        opacity: 0;
        transition: opacity 200ms ease-out;
    }

    :root {
        --app-bg: #ffffff;
        --app-section: #ffffff;
        --app-section-border: #e2e8f0;
        --app-section-hover: #f8fafc;
        --app-text: #0f172a;
        --app-text-muted: #475569;
        --app-text-on-section: #0f172a;
        --app-text-muted-on-section: #64748b;
        --app-quick-link: #f1f5f9;
        --app-quick-link-hover: #e2e8f0;
        --app-border: #e2e8f0;
    }

    .dark {
        --app-bg: #020617;
        --app-section: #0f172a;
        --app-section-border: #1e293b;
        --app-section-hover: #1e293b;
        --app-text: #f8fafc;
        --app-text-muted: #94a3b8;
        --app-text-on-section: #f8fafc;
        --app-text-muted-on-section: #94a3b8;
        --app-quick-link: #1e293b;
        --app-quick-link-hover: #334155;
        --app-border: #1e293b;
    }
</style>
<script>
window.tailwindConfig = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                app: {
                    bg: 'var(--app-bg)',
                    section: 'var(--app-section)',
                    'section-border': 'var(--app-section-border)',
                    'section-hover': 'var(--app-section-hover)',
                    text: 'var(--app-text)',
                    'text-muted': 'var(--app-text-muted)',
                    'text-on-section': 'var(--app-text-on-section)',
                    'text-muted-on-section': 'var(--app-text-muted-on-section)',
                    'quick-link': 'var(--app-quick-link)',
                    'quick-link-hover': 'var(--app-quick-link-hover)',
                    border: 'var(--app-border)',
                },
                brand: {
                    50: '#fff7ed', 100: '#ffedd5', 200: '#fed7aa', 300: '#fdba74',
                    400: '#fb923c', 500: '#f97316', 600: '#ea580c', 700: '#c2410c',
                    800: '#9a3412', 900: '#7c2d12', 950: '#431407',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
            },
            boxShadow: {
                card: '0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06)',
            },
        },
    },
};

// Theme management module
window.FFTheme = {
    get() { return localStorage.getItem('frankenforge-theme') || 'dark'; },
    set(v) { localStorage.setItem('frankenforge-theme', v); },
    apply() {
        const html = document.documentElement;
        const theme = this.get();
        if (theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        this.syncToggle();
    },
    toggle() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        if (isDark) {
            html.classList.remove('dark');
            this.set('light');
        } else {
            html.classList.add('dark');
            this.set('dark');
        }
        this.syncToggle();
    },
    syncToggle() {
        const knob = document.getElementById('theme-knob');
        const icon = document.getElementById('theme-icon');
        if (!knob || !icon) return;
        const isDark = document.documentElement.classList.contains('dark');
        knob.style.transform = isDark ? 'translateX(1.25rem)' : 'translateX(0)';
        icon.className = isDark ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
    }
};

// Apply theme immediately (before body renders to prevent flash)
window.FFTheme.apply();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
if (window.tailwindConfig) {
    tailwind.config = window.tailwindConfig;
}
window.local_tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
</script>
