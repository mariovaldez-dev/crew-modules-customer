/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/UI/Livewire/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        green: {
          50: '#f4f7f5',
          100: '#e3ece6',
          200: '#c7d8ce',
          300: '#9ebba9',
          400: '#749983',
          500: '#4A7C59',
          600: '#1f7a2f',
          700: '#2a4430',
          800: '#1d2f21',
          900: '#082312',
        }
      }
    },
  },
  plugins: [],
}
