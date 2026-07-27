import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

/**
 * Module-local build.
 *
 * Output goes to public/build-saas so it never collides with the core
 * application's existing public/build manifest. Per the implementation plan,
 * do not mix a root-level and module-level Vite strategy for the same assets.
 */
export default defineConfig({
    build: {
        outDir: '../../public/build-saas',
        emptyOutDir: true,
        // Explicit filename, not `true`. With `manifest: true` Vite 5+ writes
        // to <outDir>/.vite/manifest.json, but Laravel's @vite() helper looks
        // for <buildDirectory>/manifest.json — so the app throws
        // ViteManifestNotFoundException even though the build succeeded.
        manifest: 'manifest.json',
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-saas',
            input: [
                'resources/assets/css/marketing.css',
                'resources/assets/js/marketing/app.js',
            ],
            refresh: [
                'Modules/Saas/resources/views/**',
                'Modules/Saas/routes/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@saas': fileURLToPath(new URL('./resources/assets/js', import.meta.url)),
        },
    },
    test: {
        environment: 'jsdom',
        include: ['tests/Js/**/*.spec.js'],
    },
});
