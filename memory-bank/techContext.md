# Tech Context: KasirBraga

## Stack Teknologi
- **Backend Framework:** Laravel
- **Frontend:** Laravel Blade + Vite
- **UI Component:** Livewire
- **CSS Framework:** Tailwind CSS v4.1.4 + DaisyUI v5.0.43
- **Database:** MySQL/MariaDB
- **Deployment:** Progressive Web App (PWA)

## Ketergantungan & Setup
- **Tailwind v4 Setup**: Modern `@import "tailwindcss"` approach tanpa PostCSS
- **DaisyUI Integration**: Simplified config dengan themes ["light", "dark"]
- **Custom Theme**: Using `@theme{}` directive untuk brand colors
- **Build System**: Vite dengan Laravel + Livewire plugins
- **Dependencies**: remixicon untuk icon fonts, @tailwindcss/forms untuk form styling

## Konfigurasi Kunci
### CSS Architecture (`app.css`)
```css
@import 'remixicon/fonts/remixicon.css'; 
@import "tailwindcss";
@config "./tailwind.config.js";

@theme {
  --color-primary: oklch(0.431 0.082 158.9);  /* Brand green */
  --color-secondary: oklch(0.794 0.15 95.3);  /* Brand yellow */
  --color-accent: oklch(0.728 0.183 150.5);   /* Brand light green */
}
```

### Tailwind Config (`tailwind.config.js`)
```js
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

export default {
    content: ['./resources/views/**/*.blade.php', './resources/**/*.js'],
    plugins: [forms, daisyui],
    daisyui: {
        themes: ["light", "dark"],
        darkTheme: "dark",
        base: true, styled: true, utils: true,
    }
};
```

### Vite Config (`vite.config.js`)
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import livewire from '@defstudio/vite-livewire-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: false,
        }),
        livewire({ refresh: ['resources/css/app.css'] }),
    ],
});
```

## Build Performance
- **Build Time**: ~429ms (fast compilation)
- **CSS Bundle**: 144.83 kB (includes Tailwind + DaisyUI + fonts)
- **Dev Server**: Hot reload dengan Livewire integration
- **No PostCSS**: Streamlined build tanpa postcss.config.js

## Dependencies Specific
```json
{
  "devDependencies": {
    "@defstudio/vite-livewire-plugin": "^2.0.6",
    "@tailwindcss/forms": "^0.5.2",
    "daisyui": "^5.0.43",
    "laravel-vite-plugin": "^1.3.0",
    "vite": "^6.2.4"
  },
  "dependencies": {
    "remixicon": "^4.6.0",
    "tailwindcss": "^4.1.4"
  }
}
```

## Best Practices Learned
- **Tailwind v4**: Use `@import "tailwindcss"` instead of v3 `@tailwind` directives
- **DaisyUI**: Simplified config works better dengan v4, avoid complex theme forcing
- **No PostCSS**: Cleaner setup tanpa postcss.config.js untuk compatibility
- **Theme Definition**: `@theme{}` approach lebih maintainable dari `data-theme` scripting
- **Layout Simplicity**: Remove JavaScript theme forcing untuk cleaner HTML

## Koneksi & Konfigurasi
- Proyek Laravel sudah diinisialisasi.
- Koneksi ke database MySQL/MariaDB perlu dikonfigurasi di file `.env`.
- Library untuk ekspor XLSX (seperti `maatwebsite/excel`) dan untuk grafik (seperti `chart.js` atau `apexcharts.js`) akan dibutuhkan nanti. 