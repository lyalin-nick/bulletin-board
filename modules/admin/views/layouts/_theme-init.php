<?php
/** @var yii\web\View $this */
?>
<script>
    (() => {
        'use strict';
        const root = document.documentElement;
        const STORAGE_KEY = 'lte-theme';

        let stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch {
            // localStorage может быть недоступен: приватный режим, sandboxed iframe
        }

        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
            resolved = stored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
            resolved = 'dark';
        }

        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
    })();
</script>
