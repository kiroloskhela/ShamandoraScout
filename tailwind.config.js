import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  safelist: [
    // Dynamic card-stat / action button colors used via Blade interpolation
    {
      pattern: /(bg|text|border)-(blue|emerald|green|red|cyan|slate|gray|indigo|yellow|pink|rose)-(50|100|200|400|500|600|700)/,
      variants: ['hover', 'focus', 'disabled', 'peer-focus', 'dark'],
    },
    'dark:bg-blue-950/50',
    'dark:bg-emerald-950/50',
    'dark:bg-indigo-950/50',
    'dark:bg-yellow-950/50',
    'dark:bg-pink-950/50',
    'dark:bg-rose-950/50',
    'dark:text-blue-400',
    'dark:text-emerald-400',
    'dark:text-indigo-400',
    'dark:text-yellow-400',
    'dark:text-pink-400',
    'dark:text-rose-400',
    'dark:ring-blue-500/20',
    'dark:ring-emerald-500/20',
    'dark:ring-indigo-500/20',
    'dark:ring-yellow-500/20',
    'dark:ring-pink-500/20',
    'dark:ring-rose-500/20',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Cairo', 'Source Sans 3', 'sans-serif'],
        serif: ['Cairo', 'serif'],
        mono: ['ui-monospace', 'SFMono-Regular', 'monospace'],
        display: ['Cairo', 'Source Sans 3', 'sans-serif'],
        body: ['Cairo', 'Source Sans 3', 'sans-serif'],
      },
    },
  },
  plugins: [forms],
};
