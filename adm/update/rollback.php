<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 업데이트';
include_once ('../admin.head.php');

$this_version = G5_GNUBOARD_VER;

$backup_list = $g5['update']->getBackupList(G5_DATA_PATH . "/backup");

$latest_version = $g5['update']->getLatestVersion();
if($latest_version == false) $message = "정보조회에 실패했습니다.";

$content = $g5['update']->getVersionModifyContent($latest_version);
preg_match_all('/(?:(?:https?|ftp):)?\/\/[a-z0-9+&@#\/%?=~_|!:,.;]*[a-z0-9+&@#\/%=~_|]/i', $content, $match);
$content_url = $match[0];
foreach($content_url as $key => $var) {
    $content = str_replace($var, "@".$key."@", $content);
}

?>

<?php if($backup_list != false) { ?>
<style>
    .a_style {font-weight:400;padding:0.2em 0.4em;margin: 0;font-size: 12px;background-color: #ddf4ff;border-radius: 6px; border:1px; color: #0969da;}
    .content_title {font-size:16px; font-weight:bold;}
</style>
<ul class="anchor"><li><a href="./">업데이트</a></li><li><a href="./rollback.php">복원</a></li></ul>
<div class="version_box">
    <form method="POST" name="update_box" class="update_box" action="./rollback_step1.php" onsubmit="return update_submit(this);">
        <input type="hidden" name="compare_check" value="0">
        <?php if($this_version != $latest_version) { ?>
        <table style="width:400px; text-align:left;">
            <tbody>
                <tr><th colspan="2"><p>현재 그누보드 버전 : v<?php echo $this_version; ?></p></th></tr>
                <tr></tr>
                <tr>
                    <th>복원시점</th>
                    <td>
                        <select class="target_version" name="rollback_file">
                            <?php foreach($backup_list as $key => $var) { ?>
                                <option value="<?php echo $var->realName; ?>"><?php echo $var->listName; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>포트</th>
                    <td>
                        <label for="ftp">ftp</label>
                        <input id="ftp" type="radio" name="port" value="ftp" checked>
                        <label for="sftp">sftp</label>
                        <input id="sftp" type="radio" name="port" value="sftp">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="username">사용자 이름</label>
                    </th>
                    <td>
                        <input id="username" name="username">
                    </td>
                </tr>
                <tr>
                    <th>
                       <label for="password">사용자 비밀번호</label>
                    </th>
                    <td>
                        <input id="password" name="password">
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn_connect_check">ftp 연결확인</button>
        <?php } ?>
    </form>
</div>
<?php } else { ?>
<div class="version_box">
    <p>복원시점이 존재하지 않습니다. 업데이트를 진행하고 접근해주세요.</p>
</div>
<?php } ?>

<script>
    var inAjax = false;
    $(function() {

        // $(".target_version").change(function() {
        //     var version = $(this).val();
            
        //     if(inAjax == false) {
        //         inAjax = true;
        //     } else {
        //         alert("현재 통신중입니다.");
        //         return false;
        //     }

        //     $.ajax({
        //         url: "./ajax.version_content.php",
        //         type: "POST",
        //         data: {
        //             'version' : version,
        //         },
        //         dataType: "json",
        //         success: function(data) {
        //             inAjax = false;
        //             if(data.error != 0) {
        //                 alert(data.message);
        //                 return false;
        //             }

        //             $(".version_content_box").empty();
        //             $(".version_content_box").append(data['item']);
        //         },
        //         error:function(request,status,error){
        //             inAjax = false;
        //             alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        //         }
        //     });

        //     return false;
        // })

        $(".btn_connect_check").click(function() {
            var version = $(".target_version").val();
            var username = $("#username").val();
            var password = $("#password").val();
            var port = $("input[name=\"port\"]:checked").val();
            
            if(inAjax == false) {
                inAjax = true;
            } else {
                alert("현재 통신중입니다.");
                return false;
            }

            $.ajax({
                url: "./ajax.connect_check.php",
                type: "POST",
                data: {
                    'username' : username,
                    'password' : password,
                    'port' : port
                },
                dataType: "json",
                success: function(data) {
                    inAjax = false;
                    alert(data.message);
                    if(data.error != 0) {
                        return false;
                    }

                    $(".update_box").append("<button type=\"submit\" class=\"btn btn_update\">선택한 시점으로 롤백</button>");
                },
                error:function(request,status,error){
                    inAjax = false;
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

            return false;
        });
    })

    function update_submit(f) {

        if(inAjax == false) {
            inAjax = true;
        } else {
            alert("현재 통신중입니다.");
            return false;
        }

        var admin_password = prompt("관리자 비밀번호를 입력해주세요");
        if(admin_password == "") {
            alert("관리자 비밀번호없이 접근이 불가능합니다.");
            return false;
        } else {
            $.ajax({
                type : 'POST',
                url : './ajax.password_check.php',
                dataType : 'json',
                data : { 'admin_password' : admin_password },
                success: function(data) {
                    inAjax = false;
                    if(data.error != 0) {
                        alert(data.message);
                        return false;
                    }

                    f.submit();
                },
                error:function(request,status,error){
                    inAjax = false;
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

            return false;
        }
    }
</script>

<?php
    include_once ('../admin.tail.php');
?>