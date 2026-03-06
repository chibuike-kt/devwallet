import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["DM Sans", ...defaultTheme.fontFamily.sans],
                mono: ["DM Mono", ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50: "#f0f7ff",
                    100: "#e0effe",
                    200: "#bae0fd",
                    300: "#7dc8fb",
                    400: "#38aaf5",
                    500: "#0e8de6",
                    600: "#026fc4",
                    700: "#03589f",
                    800: "#074b83",
                    900: "#0c3f6d",
                    950: "#082849",
                },
                surface: {
                    DEFAULT: "#ffffff",
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                },
            },
            borderRadius: {
                xl: "0.875rem",
                "2xl": "1.25rem",
            },
            boxShadow: {
                card: "0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)",
                "card-hover":
                    "0 4px 12px 0 rgb(0 0 0 / 0.08), 0 2px 4px -1px rgb(0 0 0 / 0.04)",
                modal: "0 20px 60px -10px rgb(0 0 0 / 0.15)",
            },
        },
    },
    plugins: [forms],
};
