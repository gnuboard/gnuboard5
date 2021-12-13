<?php
$sub_menu = "200100";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, 'w');

$g5['title'] = "엑셀 회원 데이터 업로드";
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="mlistexcel" id="mlistexcel" method="post" action="./member_excel_upload_update.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">
        <div class="local_desc01 local_desc">
            <p>
                샘플 엑셀 파일을 다운로드 받아 양식에 맞게 수정하신 후, 업로드해 주세요.<br>
                엑셀 파일을 저장하실 때는 <strong>Excel 97 - 2003 통합문서 (*.xls)로 저장</strong>해 주세요.<br>
                계정 추가만 가능하며, 기존 계정 수정은 실행하지 않습니다.
            </p>

            <p>
                <!-- #TODO 다운로드 링크 추가 -->
                <a href="" download="">샘플 다운로드</a>
            </p>
        </div>

        <div id="excelfile_upload">
            <label for="excelfile">파일선택</label>
            <input type="file" name="excelfile" id="excelfile" required>
        </div>

        <div id="excelfile_upload" class="password">
            <input type="password" name="admin_password" id="admin_password" class="frm_input" autocomplete="off" placeholder="최고관리자 패스워드" required>
        </div>

        <div class="win_btn btn_confirm">
            <button type="submit" class="btn_submit btn">액셀 업로드</button>
            <button type="button" onclick="window.close();" class="btn_close btn">창닫기</button>
        </div>
    </form>
</div>

<script>
    $(function() {
        $("#mlistexcel").on("submit", function() {
            if (!$("#admin_password").val()) {
                alert("패스워드를 입력해 주세요.");
                $("#admin_password").focus();
                return false;
            }

            if ($("#excelfile").val()) {
                let nameSplit = $("#excelfile").val().split("."),
                    name = nameSplit[nameSplit.length - 1];

                if(name != "xls") {
                    alert("엑셀 파일을 선택해 주세요.");
                    $("#excelfile").focus();
                    return false;
                }
            } else {
                alert("파일을 선택해 주세요.")
                $("#excelfile").focus();
                return false;
            }
        })
    })
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');