<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');

$g5['title'] = '그누보드4 DB 데이터 이전';
include_once(G5_PATH.'/'.G5_THEME_DIR.'/basic/head.sub.php');

if(get_session('tables_copied') == 'done')
    alert('DB 데이터 변환을 이미 실행하였습니다. 중복 실행시 오류가 발생할 수 있습니다.', G5_URL);

if($is_admin != 'super')
    alert('최고관리자로 로그인 후 실행해 주십시오.', G5_URL);
?>

<style>
#g4_import p {padding:0 0 10px;line-height:1.8em}
#g4_import_frm {margin:20px 0 30px;padding:30px 0;border:1px solid #e9e9e9;background:#f5f8f9;text-align:center}
#g4_import_frm .frm_input {background-color:#fff !important}
#g4_import_frm .btn_submit {padding:0 10px;height:24px}
</style>

<!-- 상단 시작 { -->
<div id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/logo.jpg" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>

        <fieldset id="hd_sch">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <label for="sch_stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" id="sch_stx" maxlength="20">
            <input type="submit" id="sch_submit" value="검색">
            </form>

            <script>
            function fsearchbox_submit(f)
            {
                if (f.stx.value.length < 2) {
                    alert("검색어는 두글자 이상 입력하십시오.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
                var cnt = 0;
                for (var i=0; i<f.stx.value.length; i++) {
                    if (f.stx.value.charAt(i) == ' ')
                        cnt++;
                }

                if (cnt > 1) {
                    alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                return true;
            }
            </script>
        </fieldset>

        <ul id="tnb">
            <?php if ($is_member) {  ?>
            <?php if ($is_admin) {  ?>
            <li><a href="<?php echo G5_ADMIN_URL ?>"><b>관리자</b></a></li>
            <?php }  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php } else {  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php"><b>로그인</b></a></li>
            <?php }  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/qalist.php">1:1문의</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/current_connect.php">접속자 <?php echo connect(); // 현재 접속자수  ?></a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/new.php">새글</a></li>
        </ul>

        <div id="text_size">
            <!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
            <button id="size_down" onclick="font_resize('container', 'ts_up ts_up2', '');"><img src="<?php echo G5_URL; ?>/img/ts01.gif" alt="기본"></button>
            <button id="size_def" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up');"><img src="<?php echo G5_URL; ?>/img/ts02.gif" alt="크게"></button>
            <button id="size_up" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2');"><img src="<?php echo G5_URL; ?>/img/ts03.gif" alt="더크게"></button>
        </div>
    </div>

    <hr>

    <nav id="gnb">
        <h2>메인메뉴</h2>
        <ul id="gnb_1dul">
            <li class="gnb_empty">메뉴는 표시하지 않습니다.</li>
        </ul>
    </nav>
</div>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
    <div id="aside">
        <?php echo outlogin('basic'); // 외부 로그인  ?>
    </div>
    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) { ?><div id="container_title"><?php echo $g5['title'] ?></div><?php } ?>

        <div id="g4_import">
            <p>
                이 프로그램은 그누보드5 설치 후 바로 실행하셔야만 합니다.<br>
                만약 그누보드5 사이트를 운영 중에 이 프로그램을 실행하시면 DB 데이터가 망실되거나 데이터의 오류가 발생할 수 있습니다.<br>
                또한 중복해서 실행하실 경우에도 DB 데이터의 오류가 발생할 수 있으니 반드시 한번만 실행해 주십시오.
            </p>
            <p>프로그램을 실행하시려면 그누보드4의 config.php 파일 경로를 입력하신 후 확인을 클릭해 주십시오.</p>

            <form name="fimport" method="post" action="./g4_import_run.php" onsubmit="return fimport_submit(this);">
            <div id="g4_import_frm">
                <label for="file_path">config.php 파일 경로</label>
                <input type="text" name="file_path" id="file_path" required class="frm_input required">
                <input type="submit" value="확인" class="btn_submit">
            </div>
            </form>

            <p>
                경로는 그누보드5 설치 루트를 기준으로 그누보드4의 config.php 파일의 상대경로입니다.<br>
                예를 들어 그누보드4를 웹루트에 설치하셨고 그누보드5를 g5라는 하위 폴더에 설치하셨다면 입력하실 경로는 ../config.php 입니다.
            </p>

        </div>

        <script>
        function fimport_submit(f)
        {
            return confirm('그누보드4의 DB 데이터를 이전하시겠습니까?');
        }
        </script>

    </div>
</div>

<!-- } 콘텐츠 끝 -->

<hr>

<!-- 하단 시작 { -->
<div id="ft">
    <div id="ft_catch"><img src="<?php echo G5_IMG_URL; ?>/ft.png" alt="<?php echo G5_VERSION ?>"></div>
    <div id="ft_copy">
        <p>
            Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
            <a href="#">상단으로</a>
        </p>
    </div>
</div>

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<?php
include_once(G5_PATH.'/'.G5_THEME_DIR.'/basic/tail.sub.php');
?>