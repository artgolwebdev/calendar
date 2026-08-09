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
                sans: ['Rubik', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                ink: {
                    50: '#F7F7F8',
                    100: '#EFEFF1',
                    200: '#E5E5E8',
                    300: '#D4D4D8',
                    400: '#A1A1AA',
                    500: '#71717A',
                    600: '#52525B',
                    700: '#3F3F46',
                    800: '#27272A',
                    900: '#18181B',
                    950: '#0C0C0E',
                },

                volt: {
                    50: '#FCFFE7',
                    100: '#F6FFC2',
                    200: '#ECFF8A',
                    300: '#E2FF52',
                    400: '#D6FF1A',
                    500: '#CCFF00',
                    600: '#A3CC00',
                    700: '#7B9900',
                    800: '#536600',
                    900: '#2B3300',
                    DEFAULT: '#CCFF00',
                },

                danger: {
                    light: '#FEF2F2',
                    DEFAULT: '#DC2626',
                    hover: '#B91C1C',
                },
            },
        },
    },

    plugins: [
        forms,
        require('tailwindcss-rtl'),
    ],
};
