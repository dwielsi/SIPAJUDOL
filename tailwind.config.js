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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#EDF3F9',
                    100: '#D3E6F1',
                    200: '#AECAE8',
                    300: '#AECAE8',
                    400: '#4070AF',
                    500: '#4070AF',
                    600: '#275591',
                    700: '#1A3A65',
                    800: '#1A3A65',
                    900: '#1A3A65',
                },
                success: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    500: '#10B981',
                    600: '#059669',
                    700: '#047857',
                },
                warning: {
                    50: '#fdf9e7',
                    100: '#faf0c2',
                    500: '#F3DA52',
                    600: '#b3901f',
                    700: '#8a6d10',
                },
                danger: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    500: '#EF4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                },
                navy: {
                    500: '#4070AF',
                    600: '#275591',
                    900: '#1A3A65',
                },
                teal: {
                    400: '#3fc9bd',
                    500: '#24B9AD',
                    600: '#1c9a90',
                    700: '#177d75',
                },
                gold: {
                    50: '#fdf9e7',
                    100: '#faf0c2',
                    500: '#F3DA52',
                    600: '#b3901f',
                },
                surface: '#EDF3F9',
                slate: {
                    400: '#8898A7',
                    500: '#8898A7',
                },
            },
            boxShadow: {
                soft: '0 1px 2px 0 rgb(0 0 0 / 0.04), 0 1px 3px 0 rgb(0 0 0 / 0.06)',
                card: '0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px -1px rgb(0 0 0 / 0.06)',
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
