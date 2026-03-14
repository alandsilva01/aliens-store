export default {
  content: ['./index.html', './src/**/*.{vue,js}'],
  theme: {
    extend: {
      colors: {
        alien: {
          bg:      '#050A0E',
          card:    '#0A1628',
          border:  '#0D2137',
          green:   '#00FF88',
          purple:  '#7B2FFF',
          blue:    '#00D4FF',
          text:    '#E0F0FF',
          muted:   '#4A6FA5',
        }
      },
      fontFamily: {
        orbitron: ['Orbitron', 'sans-serif'],
        rajdhani: ['Rajdhani', 'sans-serif'],
      }
    }
  },
  plugins: []
}
