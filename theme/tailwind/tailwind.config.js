/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.html", "./**/*.php"],
  theme: {
    extend: {
      colors: {
        'mainbg': '#212020',
        'mainborder': '#383838',
        'schbg': '#2c2c2c',
        'gnbmenu': '#4158d1'
      },
      width: {
        'container': 'calc(100% - 260px)',
        'ltwr': '32%',
      },
      height: {
        140: "140px",
      },
      minHeight: {
        '500': '500px',
      },
      lineHeight: {
        45: "45px",
        52: "52px",
        55: "55px",
      },
      top: {
        13: "54px",
      },
      zIndex: {
        '1000': '1000',
        '999': '999'
      },
      backgroundImage: {
        'chk-cheked': "url('./img/chk.png')",
      }
    }
  },
  plugins: [],
}

