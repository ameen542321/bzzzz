/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#3b82f6',
                    dark: '#1e40af',
                },
            },
        },
    },


    plugins: [],
//     theme: {
//     extend: {
//         colors: {
//             darkBg: '#0f0f0f',
//             darkText: '#e5e5e5',
//         },
//     },
// },

}
