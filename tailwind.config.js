import defaultTheme from 'tailwindcss/defaultTheme';
// ELIMINADO: import forms from '@tailwindcss/forms'; 

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // 1. Habilitar modo oscuro por clase
    
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
        },
    },

    corePlugins: {
        preflight: false, // 2. Desactivar el reset global de Tailwind
    },

    plugins: [
        // ELIMINADO: forms
    ],
};