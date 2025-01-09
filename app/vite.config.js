import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/admin/dashboard.ts",
                "resources/js/public/app.ts",
                "resources/scss/app.scss",
            ],
            refresh: true,
        }),
    ],
});
