import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],

    // Kiracı menüleri 2 saat public olarak önbellekleniyor. Yeni dağıtımda varlık
    // adı değişiyor; dizin boşaltılırsa önbellekteki HTML'in işaret ettiği eski
    // dosya 404 döner ve menü CSS'siz kalır. Eski çıktılar dursun, üzerine yazılsın.
    build: {
        emptyOutDir: false,
    },
});
