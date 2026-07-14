import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  safelist: [
    {
      pattern: /(bg|text|border|hover:bg|hover:text|focus:bg|focus:text|focus:border|disabled:border|disabled:bg|disabled:text|peer-focus:text)-(blue|emerald|green|red)-(50|100|200|300|400|500|600|700)/,
    },
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Cairo', 'sans-serif'],
        serif: ['Cairo', 'serif'],
        mono: ['Cairo', 'monospace'],
        display: ['Cairo', 'sans-serif'],
        body: ['Cairo', 'sans-serif'],
      },
    },
  },
  plugins: [forms],
};
