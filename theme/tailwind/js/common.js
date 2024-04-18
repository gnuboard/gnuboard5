// 다크모드 설정
// Dark mode 상태 확인 함수
function isDarkModeEnabled() {
const isUserColorTheme = localStorage.getItem('theme');
if (isUserColorTheme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
document.documentElement.classList.add('dark')
} else {
document.documentElement.classList.remove('dark')
}
}

document.addEventListener('DOMContentLoaded', function () {
isDarkModeEnabled();
const darkmodeBtn = document.querySelector('#darkmode-toggle-switch');
darkmodeBtn?.addEventListener('click', function () {
  const currentTheme = localStorage.theme === 'dark' ? 'light' : 'dark';
  localStorage.setItem('theme', currentTheme);
  if(currentTheme === 'dark') {
    document.documentElement.classList.add('dark')
  } else if(currentTheme === 'light'){
    document.documentElement.classList.remove('dark')
  }
});
});