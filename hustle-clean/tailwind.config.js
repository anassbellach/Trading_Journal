/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,ts,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
            },
            colors: {
                // Core palette
                surface: {
                    DEFAULT: '#0A0C10',
                    50:  '#F8F9FA',
                    100: '#1A1D25',
                    200: '#141720',
                    300: '#0F1118',
                    400: '#0D0F16',
                    500: '#0A0C10',
                },
                panel: {
                    DEFAULT: 'rgba(255,255,255,0.03)',
                    hover:   'rgba(255,255,255,0.055)',
                    active:  'rgba(255,255,255,0.08)',
                    border:  'rgba(255,255,255,0.07)',
                    strong:  'rgba(255,255,255,0.12)',
                },
                brand: {
                    DEFAULT: '#00C896',
                    50:  '#E6FFF8',
                    100: '#B3FFE8',
                    200: '#66FFCC',
                    300: '#33FFC0',
                    400: '#00F0B0',
                    500: '#00C896',
                    600: '#00A07A',
                    700: '#00785C',
                    800: '#00503D',
                    900: '#00281F',
                    glow: 'rgba(0,200,150,0.2)',
                    muted: 'rgba(0,200,150,0.12)',
                    border: 'rgba(0,200,150,0.2)',
                },
                profit: {
                    DEFAULT: '#00C896',
                    dim: 'rgba(0,200,150,0.15)',
                    text: '#00E0AA',
                },
                loss: {
                    DEFAULT: '#FF4B4B',
                    dim: 'rgba(255,75,75,0.15)',
                    text: '#FF6B6B',
                },
                accent: {
                    blue:   '#7B9FFF',
                    purple: '#9B7BFF',
                    amber:  '#FFB84D',
                    rose:   '#FF6B8A',
                },
                text: {
                    primary:   '#E8EAF0',
                    secondary: 'rgba(255,255,255,0.55)',
                    tertiary:  'rgba(255,255,255,0.35)',
                    muted:     'rgba(255,255,255,0.2)',
                },
            },
            borderRadius: {
                '2xl': '16px',
                '3xl': '20px',
                '4xl': '24px',
            },
            backdropBlur: {
                xs: '2px',
                '2xl': '40px',
            },
            boxShadow: {
                'glass':      '0 4px 24px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.05)',
                'glass-sm':   '0 2px 12px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.04)',
                'glow-brand': '0 0 20px rgba(0,200,150,0.25), 0 0 40px rgba(0,200,150,0.08)',
                'glow-sm':    '0 0 12px rgba(0,200,150,0.15)',
                'card':       '0 1px 3px rgba(0,0,0,0.3), 0 4px 16px rgba(0,0,0,0.2)',
                'dropdown':   '0 8px 32px rgba(0,0,0,0.5), 0 1px 0 rgba(255,255,255,0.05)',
                'modal':      '0 24px 64px rgba(0,0,0,0.7), 0 1px 0 rgba(255,255,255,0.05)',
            },
            animation: {
                'fade-in':       'fadeIn 0.2s ease-out',
                'fade-up':       'fadeUp 0.3s ease-out',
                'slide-in-left': 'slideInLeft 0.25s ease-out',
                'slide-in-right':'slideInRight 0.25s ease-out',
                'scale-in':      'scaleIn 0.2s ease-out',
                'shimmer':       'shimmer 1.8s infinite linear',
                'pulse-brand':   'pulseBrand 2s cubic-bezier(0.4,0,0.6,1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%':   { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeUp: {
                    '0%':   { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInLeft: {
                    '0%':   { opacity: '0', transform: 'translateX(-12px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                slideInRight: {
                    '0%':   { opacity: '0', transform: 'translateX(12px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                scaleIn: {
                    '0%':   { opacity: '0', transform: 'scale(0.95)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                shimmer: {
                    '0%':   { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                pulseBrand: {
                    '0%, 100%': { opacity: '1' },
                    '50%':      { opacity: '0.5' },
                },
            },
            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
            },
            transitionTimingFunction: {
                'spring': 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
        require('@tailwindcss/typography'),
    ],
}
