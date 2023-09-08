/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // You will probably also need those lines
        "./resources/**/**/*.blade.php",
        "./resources/**/**/*.js",
        "./app/View/Components/**/**/*.php",
        "./app/Livewire/**/**/*.php",                     

        // Add mary
        "./vendor/robsontenorio/mary/src/View/Components/**/*.php" 
    ],
    theme: {
        extend: {},
    },
    
    // Add daisyUI
    plugins: [require("daisyui")] 
}