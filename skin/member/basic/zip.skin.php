<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<!-- 우편번호 찾기 시작 { -->
<link rel="stylesheet" href="<?php echo $member_skin_url ?>/style.css">

<div id="post_code" class="new_win mbskin">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <form name="fzip" method="get" autocomplete="off">
    <input type="hidden" name="frm_name" value="<?php echo $frm_name ?>">
    <input type="hidden" name="frm_zip1" value="<?php echo $frm_zip1 ?>">
    <input type="hidden" name="frm_zip2" value="<?php echo $frm_zip2 ?>">
    <input type="hidden" name="frm_addr1" value="<?php echo $frm_addr1 ?>">
    <input type="hidden" name="frm_addr2" value="<?php echo $frm_addr2 ?>">

    <!-- 검색어 입력 시작 { -->
    <fieldset>
        <label for="addr1">동/읍/면/리 검색</label>
        <input type="text" name="addr1" value="<?php echo $addr1 ?>" id="addr1" required  class="required frm_input" minlength="2">
        <input type="submit" value="검색" class="btn_submit">
    </fieldset>
    <!-- } 검색어 입력 끝 -->

    <!-- 검색결과 시작 { -->

    <?php if ($search_count > 0) { ?>
    <dl>
        <dt>총 <?php echo $search_count ?>건 가나다순 정렬</dt>
        <dd>
            <?php
            for ($i=0; $i<count($list); $i++) {
                if ($i == 0) echo '<ul>';
            ?>
                <li><a href='javascript:;' onclick="find_zip('<?php echo $list[$i][zip1] ?>', '<?php echo $list[$i][zip2] ?>', '<?php echo $list[$i][addr] ?>');"><span class="post_code"><?php echo $list[$i][zip1] ?>-<?php echo $list[$i][zip2] ?></span> <?php echo $list[$i][addr] ?> <?php echo $list[$i][bunji] ?></a></li>
            <?php }
            if ($i > 0) echo '</ul>';
            ?>
        </dd>
    </dl>

    <p>검색결과가 끝났습니다.</p>
    <!-- } 검색결과 끝 -->
    <?php
    }
    ?>

    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

    <?php if ($search_count > 0) { ?>
    <script>
    function find_zip(zip1, zip2, addr1)
    {
        var of = opener.document.<?php echo $frm_name ?>;

        of.<?php echo $frm_zip1 ?>.value  = zip1;
        of.<?php echo $frm_zip2 ?>.value  = zip2;

        of.<?php echo $frm_addr1 ?>.value = addr1;

        of.<?php echo $frm_addr2 ?>.focus();
        window.close();
        return false;
    }
    </script>
    <?php }  ?>
</div>
<!-- } 우편번호 찾기 끝 -->