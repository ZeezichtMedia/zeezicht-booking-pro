/** @type {import('tailwindcss').Config} */
module.exports = {
  // CRUCIALE ENCAPSULATION - Alle CSS wordt ingekapseld in onze plugin wrapper
  important: '#zzbp-booking-app',
  
  content: [
    // ALLEEN scannen waar we daadwerkelijk Tailwind classes gebruiken
    './templates/single-accommodation.php',
  ],
  theme: {
    extend: {
      colors: {
        rose: {
          50: '#fff1f2',
          100: '#ffe4e6',
          200: '#fecdd3',
          300: '#fda4af',
          400: '#fb7185',
          500: '#f43f5e',
          600: '#e11d48',
          700: '#be123c',
          800: '#9f1239',
          900: '#881337',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
      }
    },
  },
  plugins: [],
  // Safelist voor WordPress core classes en dynamische content
  safelist: [
    // WordPress alignment classes
    'alignleft',
    'alignright',
    'aligncenter',
    'alignwide',
    'alignfull',
    
    // Common utility classes die dynamisch kunnen worden toegevoegd
    'text-center',
    'text-left',
    'text-right',
    
    // Button states
    'opacity-50',
    'opacity-100',
    'cursor-not-allowed',
    
    // Common responsive classes
    {
      pattern: /^(sm|md|lg|xl):(block|hidden|flex|grid)/,
    },
    
    // Rose color variations
    {
      pattern: /^(bg|text|border)-rose-(50|100|200|300|400|500|600|700|800|900)$/,
    }
  ]
}
