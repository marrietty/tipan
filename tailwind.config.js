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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },

            // TIPAN brand palette. See docs "Color Palette & Typography".
            colors: {
                // Primary scale keyed off the brand "Primary Blue".
                primary: {
                    DEFAULT: '#4F7CFF', // Primary Blue
                    light: '#7DB7FF',   // Sky Blue
                    dark: '#3D63DB',    // Primary Dark
                    indigo: '#BBD3FF',  // Light Indigo
                },
                cyan: {
                    DEFAULT: '#42D4FF', // Cyan / Accent
                },

                // Neutrals.
                surface: '#FFFFFF',   // Card / Surface
                canvas: '#F6F9FF',    // Background
                line: '#E6EEFF',      // Borders / Line
                heading: '#1A2B6D',   // Heading text
                body: '#475569',      // Body text
                muted: '#94A3B8',     // Light text

                // Status / feedback.
                success: '#22C55E',
                warning: '#F59E0B',
                danger: '#EF4444',
                info: '#3B82F6',
                positive: '#10B981', // Teal / Positive
            },

            backgroundImage: {
                'brand-gradient':
                    'linear-gradient(135deg, #4F7CFF 0%, #6C97FF 50%, #9FD3FF 100%)',
            },

            fontSize: {
                // TIPAN type scale.
                h1: ['32px', { lineHeight: '1.2', fontWeight: '800' }],
                h2: ['24px', { lineHeight: '1.3', fontWeight: '600' }],
                h3: ['20px', { lineHeight: '1.4', fontWeight: '500' }],
                h4: ['16px', { lineHeight: '1.5', fontWeight: '500' }],
                'body-1': ['14px', { lineHeight: '1.6', fontWeight: '400' }],
                'body-2': ['12px', { lineHeight: '1.6', fontWeight: '400' }],
            },
        },
    },

    plugins: [forms],
};
