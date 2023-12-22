<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!defined('G5_IS_ADMIN') && defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/tail.sub.php')) {
    require_once(G5_THEME_PATH.'/tail.sub.php');
    return;
}
?>

<?php if ($is_admin == 'super') {  ?><!-- <div style='float:left; text-align:center;'>RUN TIME : <?php echo get_microtime()-$begin_time; ?><br></div> --><?php }  ?>


<?php run_event('tail_sub'); ?>

<script>

  // 다크모드 설정
  function handleDarkModeChange() {
    const isUserColorTheme = localStorage.getItem('color-theme');
    const isOsColorTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const getUserTheme = () => (isUserColorTheme ? isUserColorTheme : isOsColorTheme);
    
    const initialTheme = getUserTheme();
    if (initialTheme === 'dark') {
      document.documentElement.setAttribute('color-theme', 'dark');
    } else {
      document.documentElement.setAttribute('color-theme', 'light');
    }
  }; 
 
  document.addEventListener('DOMContentLoaded', handleDarkModeChange)

  // 로컬스토리지 데이터에따라 실시간 변경
  window.addEventListener('storage', function (event) {
      handleDarkModeChange();
  });

</script>

</body>
</html>
<?php echo html_end(); // HTML 마지막 처리 함수 : 반드시 넣어주시기 바랍니다.