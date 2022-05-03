<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 업데이트';
include_once ('./admin.head.php');

$this_version = G5_GNUBOARD_VER;

$version_list = $g5['update']->getVersionList();
$latest_version = $g5['update']->getLatestVersion();
if($latest_version == false) $message = "정보조회에 실패했습니다.";

$content = $g5['update']->getVersionModifyContent($latest_version);
preg_match_all('/(?:(?:https?|ftp):)?\/\/[a-z0-9+&@#\/%?=~_|!:,.;]*[a-z0-9+&@#\/%=~_|]/i', $content, $match);
$content_url = $match[0];
foreach($content_url as $key => $var) {
    $content = str_replace($var, "@".$key."@", $content);
}

?>

<?php if($latest_version != false) { ?>
<style>
    .a_style {font-weight:400;padding:0.2em 0.4em;margin: 0;font-size: 12px;background-color: #ddf4ff;border-radius: 6px; border:1px; color: #0969da;}
    .content_title {font-size:16px; font-weight:bold;}
</style>
<div class="version_box">
    <form method="POST" name="update_box" class="update_box" action="./upgrade_step1.php" onsubmit="return update_submit(this);">
        <input type="hidden" name="compare_check" value="0">
        <?php if($this_version != $latest_version) { ?>
        <table style="width:400px; text-align:left;">
            <tbody>
                <tr><th colspan="2"><p>현재 그누보드 버전 : v<?php echo $this_version; ?></p></th></tr>
                <tr><th colspan="2"><p>최신 그누보드 버전 : <?php echo $latest_version; ?></p></th></tr>
                <tr></tr>
                <tr>
                    <th>목표버전</th>
                    <td>
                        <select class="version_list" name="version_list">
                            <?php foreach($version_list as $key => $var) { ?>
                                <option value="<?php echo $var; ?>"><?php echo $var; ?></option>
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
    <div class="version_content_box" style="margin-top:30px;">
        <?php if(!empty($content)) {
                echo "<p class=\"content_title\">".$latest_version." 버전 수정</p>";
                echo "<p style=\"white-space:pre-line; line-height:2;\">";
                foreach($content_url as $key => $var) {
                    $content = str_replace('@'.$key.'@', '<a class="a_style" href="'.$var.'" target="_blank">변경코드확인</a>', $content);
                }
                echo htmlspecialchars_decode($content, ENT_HTML5);
                echo "</p><br>";
        } ?>
    </div>
</div>
<?php } else { ?>
<div class="version_box">
    <p>정보 조회에 실패했습니다. 1시간 후 다시 시도해주세요.</p>
</div>
<?php } ?>

<script>
    $(function() {
        var inAjax = false;

        $(".version_list").change(function() {
            var version = $(this).val();
            
            if(inAjax == false) {
                inAjax = true;
            } else {
                alert("현재 통신중입니다.");
                return false;
            }

            $.ajax({
                url: "./ajax.version_content.php",
                type: "POST",
                data: {
                    'version' : version,
                },
                dataType: "json",
                success: function(data) {
                    inAjax = false;
                    if(data.error != 0) {
                        alert(data.message);
                        return false;
                    }

                    $(".version_content_box").empty();
                    $(".version_content_box").append(data['item']);
                },
                error:function(request,status,error){
                    inAjax = false;
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

            return false;
        })

        $(".btn_connect_check").click(function() {
            var version = $(".version_list").val();
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

                    $(".update_box").append("<button type=\"submit\" class=\"btn btn_update\">지금 업데이트</button>");
                },
                error:function(request,status,error){
                    inAjax = false;
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

            return false;
        });
    })
</script>

<?php
    include_once ('./admin.tail.php');
?>