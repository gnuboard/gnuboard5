/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.html", "./**/*.php"],
  theme: {
    extend: {
      colors: {
        header: '#212020',
        mainborder: '#383838',
        schbg: '#2c2c2c',
        gnbmenu: '#4158d1'
      },
      height: {
        140: "140px",
      },
      lineHeight: {
        11: "55px",
      },
      top: {
        13: "54px",
      },
      zIndex: {
        '1000': '1000',
        '999': '999'
      }
    }
  },
  plugins: [],
}

