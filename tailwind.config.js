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
      pattern: /(bg|text|border)-(blue|emerald|green|red)-(50|100|200|300|400|500|600|700)/,
      variants: ['hover', 'focus', 'disabled', 'peer-focus'],
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
