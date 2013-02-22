<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G4_PATH.'/head.sub.php');
include_once(G4_LIB_PATH.'/latest.lib.php');
include_once(G4_LIB_PATH.'/outlogin.lib.php');
include_once(G4_LIB_PATH.'/poll.lib.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once(G4_LIB_PATH.'/connect.lib.php');
include_once(G4_LIB_PATH.'/popular.lib.php');

//print_r2(get_defined_constants());
?>

<header id="hd">
    <div id="to_content"><a href="#container">본문 바로가기</a></div>

    <h1><?=$config['cf_title']?></h1>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?=G4_URL?>"><img src="<?=G4_IMG_URL?>/logo.jpg" alt="처음으로" width="53" height="37"></a>
        </div>

        <fieldset id="schall">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" method="get" action="<?=G4_BBS_URL?>/search.php" onsubmit="return fsearchbox_submit(this);">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <input type="text" id="schall_stx" name="stx" title="검색어" maxlength="20"><input type="image" id="schall_submit" src="<?=G4_IMG_URL?>/btn_search.jpg" width="24" height="24" alt="검색">
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

        <ul id="snb">
            <li>
                <a href="<?=G4_BBS_URL?>/current_connect.php" id="snb_cnt">
                    <img src="<?=G4_IMG_URL?>/snb_cnt.jpg" alt="">
                    현재접속자 <?=connect(); // 현재 접속자수 ?>
                </a>
            </li>
            <li>
                <a href="<?=G4_BBS_URL?>/new.php" id="snb_new">
                    <img src="<?=G4_IMG_URL?>/snb_new.jpg" alt="">
                    새글
                </a>
            </li>
            <? if ($is_member) { ?>
            <? if ($is_admin) { ?>
            <li>
                <a href="<?=G4_ADMIN_URL?>" id="snb_adm">
                    <img src="<?=G4_IMG_URL?>/snb_admin.jpg" alt="">
                    관리자
                </a>
            </li>
            <? } ?>
            <li>
                <a href="<?=G4_BBS_URL?>/member_confirm.php?url=<?=G4_BBS_URL?>/register_form.php" id="snb_modify">
                    <img src="<?=G4_IMG_URL?>/snb_modify.jpg" alt="">
                    내 정보
                </a>
            </li>
            <li>
                <a href="<?=G4_BBS_URL?>/logout.php" id="snb_logout">
                    <img src="<?=G4_IMG_URL?>/snb_logout.jpg" alt="">
                    로그아웃
                </a>
            </li>
            <? } else { ?>
            <li>
                <a href="<?=G4_BBS_URL?>/register.php" id="snb_join">
                    <img src="<?=G4_IMG_URL?>/snb_join.jpg" alt="">
                    회원가입
                </a>
            </li>
            <li>
                <a href="<?=G4_BBS_URL?>/login.php" id="snb_login">
                    <img src="<?=G4_IMG_URL?>/snb_login.jpg" alt="">
                    로그인
                </a>
            </li>
            <? } ?>
            <? // 색상대비 on/off
            $cr_path = g4_path();
            if($contrast_use == 'on') {
                $cr_uri = $cr_path['curr_url'].'?contrast=off';
                $cr = "ON";
            } else {
                $cr_uri = $cr_path['curr_url'].'?contrast=on';
                $cr = "OFF";
            }
            if($_SERVER['QUERY_STRING']) {
                $query_string = preg_replace("/contrast=(on|off)&?/", "", $_SERVER['QUERY_STRING']);
                if($query_string)
                    $cr_uri .= '&amp;'.$query_string;
            }
            unset($cr_path);
            ?>
            <li>
                <a href="<?=$cr_uri;?>">색상대비<?=$cr?></a>
            </li>
        </ul>

    </div>
</header>

<hr>

<nav id="gnb">
    <script>$('#gnb').addClass('gnb_js');</script>
    <h2>홈페이지 메인메뉴</h2>
    <ul>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=sirgle">써글톡</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글톡</a></li>
                <li class="gnb_2depth"><a href="#">써글톡</a></li>
                <li class="gnb_2depth"><a href="#">써글톡</a></li>
                <li class="gnb_2depth"><a href="#">써글톡</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=cry">넋두리</a>
            <ul>
                <li class="gnb_2depth"><a href="#">넋두리</a></li>
                <li class="gnb_2depth"><a href="#">넋두리</a></li>
                <li class="gnb_2depth"><a href="#">넋두리</a></li>
                <li class="gnb_2depth"><a href="#">넋두리</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=humor">써글유머</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글유머</a></li>
                <li class="gnb_2depth"><a href="#">써글유머</a></li>
                <li class="gnb_2depth"><a href="#">써글유머</a></li>
                <li class="gnb_2depth"><a href="#">써글유머</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=debate">써글토론</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글토론</a></li>
                <li class="gnb_2depth"><a href="#">써글토론</a></li>
                <li class="gnb_2depth"><a href="#">써글토론</a></li>
                <li class="gnb_2depth"><a href="#">써글토론</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=sirglenoms">써글놈들</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글놈들</a></li>
                <li class="gnb_2depth"><a href="#">써글놈들</a></li>
                <li class="gnb_2depth"><a href="#">써글놈들</a></li>
                <li class="gnb_2depth"><a href="#">써글놈들</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=writtenby">자작써글</a>
            <ul>
                <li class="gnb_2depth"><a href="#">자작써글</a></li>
                <li class="gnb_2depth"><a href="#">자작써글</a></li>
                <li class="gnb_2depth"><a href="#">자작써글</a></li>
                <li class="gnb_2depth"><a href="#">자작써글</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=liveloca">써글현장</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글현장</a></li>
                <li class="gnb_2depth"><a href="#">써글현장</a></li>
                <li class="gnb_2depth"><a href="#">써글현장</a></li>
                <li class="gnb_2depth"><a href="#">써글현장</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=game">써글게임</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글게임</a></li>
                <li class="gnb_2depth"><a href="#">써글게임</a></li>
                <li class="gnb_2depth"><a href="#">써글게임</a></li>
                <li class="gnb_2depth"><a href="#">써글게임</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=it">써글IT</a>
            <ul>
                <li class="gnb_2depth"><a href="#">써글IT</a></li>
                <li class="gnb_2depth"><a href="#">써글IT</a></li>
                <li class="gnb_2depth"><a href="#">써글IT</a></li>
                <li class="gnb_2depth"><a href="#">써글IT</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=zzalbang">짤방대결</a>
            <ul>
                <li class="gnb_2depth"><a href="#">짤방대결</a></li>
                <li class="gnb_2depth"><a href="#">짤방대결</a></li>
                <li class="gnb_2depth"><a href="#">짤방대결</a></li>
                <li class="gnb_2depth"><a href="#">짤방대결</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=goddess">여신대결</a>
            <ul>
                <li class="gnb_2depth"><a href="#">여신대결</a></li>
                <li class="gnb_2depth"><a href="#">여신대결</a></li>
                <li class="gnb_2depth"><a href="#">여신대결</a></li>
                <li class="gnb_2depth"><a href="#">여신대결</a></li>
            </ul>
        </li>
        <li class="gnb_1depth">
            <a href="<?=$g4['url']?>/bbs/board.php?bo_table=sports">스포츠</a>
            <ul>
                <li class="gnb_2depth"><a href="#">스포츠</a></li>
                <li class="gnb_2depth"><a href="#">스포츠</a></li>
                <li class="gnb_2depth"><a href="#">스포츠</a></li>
                <li class="gnb_2depth"><a href="#">스포츠</a></li>
            </ul>
        </li>
    </ul>
</nav>

<hr>

<div id="wrapper">
    <div id="lnb">
        <?=outlogin('basic'); // 외부 로그인 ?>
        <?=poll('basic'); // 설문조사 ?>
    </div>
    <div id="container">
        <? if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) {?><h1 id="wrapper_title"><?=$g4['title']?></h1><?}?>
