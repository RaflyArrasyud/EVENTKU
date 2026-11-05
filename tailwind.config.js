import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                ink: '#111111',
                cream: '#f8f8f5',
                deep: '#080C0A',
                surface: '#0A1012',
                card: '#0B1114',
                line: '#202B30',
                neon: '#7FB069', // leaf muted, bukan neon — jangan kembalikan ke terang
                paper: '#F5F7F8',
                muted: '#A7B0B6',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
                hand: ['ui-cursive', 'Segoe Script', 'Apple Chancery', 'Comic Sans MS', 'cursive'],
            },
            maxWidth: {
                'shell': '1120px',
            },
            animation: {
                'blob': 'blob 7s infinite',
                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
            },
            keyframes: {
                blob: {
                    '0%': { transform: 'translate(0px, 0px) scale(1)' },
                    '33%': { transform: 'translate(30px, -20px) scale(1.1)' },
                    '66%': { transform: 'translate(-20px, 20px) scale(0.95)' },
                    '100%': { transform: 'translate(0px, 0px) scale(1)' },
                },
                heroFloat: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                fadeInUp: {
                    '0%': { opacity: 0, transform: 'translateY(20px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [forms],
};
