<?php
include_once("_common.php");

// ---------------------------------------------------------------------------

# 이미지가 저장될 디렉토리의 전체 경로를 설정합니다.
# 끝에 슬래쉬(/)는 붙이지 않습니다.
# 주의: 이 경로의 접근 권한은 쓰기, 읽기가 가능하도록 설정해 주십시오.

@mkdir("$g4[path]/data/$g4[cheditor4]/", 0707);
@chmod("$g4[path]/data/$g4[cheditor4]/", 0707);

$ym = date("ym", $g4[server_time]);

define("SAVE_DIR", "$g4[path]/data/$g4[cheditor4]/$ym");

@mkdir(SAVE_DIR, 0707);
@chmod(SAVE_DIR, 0707);

# 위에서 설정한 'SAVE_DIR'의 URL을 설정합니다.
# 끝에 슬래쉬(/)는 붙이지 않습니다.

define("SAVE_URL", "$g4[url]/data/$g4[cheditor4]/$ym");

// ---------------------------------------------------------------------------

?>
