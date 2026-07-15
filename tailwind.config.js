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
      pattern: /(bg|text|border|hover:bg)-(blue|emerald|green|red|cyan|slate|gray|amber|rose)-(50|100|200|300|400|500|600|700|800)/,
      variants: ['hover', 'focus', 'disabled', 'peer-focus', 'dark'],
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
