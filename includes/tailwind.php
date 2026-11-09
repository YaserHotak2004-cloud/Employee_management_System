<?php
// Shared Tailwind CSS v4.3 (browser CDN) setup for this project.
// Docs: https://tailwindcss.com/docs/installation/play-cdn
?>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.3.5"></script>
<style type="text/tailwindcss">
  @theme {
    --color-brand-50: #ecfeff;
    --color-brand-100: #cffafe;
    --color-brand-500: #06b6d4;
    --color-brand-600: #0891b2;
    --color-brand-700: #0e7490;
    --color-brand-900: #164e63;
  }

  @layer base {
    body {
      @apply bg-slate-100 text-slate-800 antialiased;
    }
  }
</style>
