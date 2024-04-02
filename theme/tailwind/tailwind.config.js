/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.html", "./**/*.php"],
  theme: {
    extend: {
      colors: {
        header: '#212020',
        mainborder: '#383838',
        schbg: '#2c2c2c'
      },
      height: {
        140: "140px",
      },
      lineHeight: {
        11: "55px",
      },
      top: {
        13: "54px",
      }
    }
  },
  plugins: [],
}

