import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Identidad ClinicTech — Teal clínico (salud, calma, confianza).
                // Única fuente de verdad del color de marca: usa siempre `brand-*`,
                // nunca un teal/indigo hardcodeado en las vistas.
                brand: {
                    50: '#F0FDFA',
                    100: '#CCFBF1',
                    200: '#99F6E4',
                    300: '#5EEAD4',
                    400: '#2DD4BF',
                    500: '#14B8A6',
                    600: '#0D9488', // principal (botones, enlaces activos)
                    700: '#0F766E', // hover
                    800: '#115E59',
                    900: '#134E4A', // texto/headers de marca
                    950: '#042F2E',
                },
            },
        },
    },

    plugins: [forms],
};
