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
    {
      pattern: /(bg|text|border)-(blue|emerald|green|red|cyan|slate|gray)-(50|100|200|300|400|500|600|700)/,
      variants: ['hover', 'focus', 'disabled', 'peer-focus'],
    },
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
