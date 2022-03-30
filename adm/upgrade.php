<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '그누보드 업데이트';
include_once ('./admin.head.php');

$version_list = $g5_update->getVersionList();
$latest_version = $g5_update->getLatestVersion();
if($latest_version == false) $message = "정보조회에 실패했습니다.";

$this_version = G5_GNUBOARD_VER;
?>

<?php if($latest_version != false) { ?>
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

</div>
<?php } else { ?>
<div class="version_box">
    <p>정보 조회에 실패했습니다. 1시간 후 다시 시도해주세요.</p>
</div>
<?php } ?>

<script>
    $(function() {
        var inAjax = false;
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