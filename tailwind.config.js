/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: ["./resources/views/**/*.blade.php", "./resources/js/**/*.js"],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', 'ui-sans-serif', 'system-ui'],
      },
      keyframes: {
        "slide-in": {
          "0%": { opacity: 0, transform: "translateY(-10px) scale(0.99)" },
          "100%": { opacity: 1, transform: "translateY(0) scale(1)" },
        },
      },
      animation: {
        "slide-in": "slide-in 0.3s ease-out",
      },
    },
  },
  plugins: [],
};
