<?php
if (!defined('_GNUBOARD_')) exit;

$group = array();
$qry = sql_query("select * from {$g5['sms5_form_group_table']} order by fg_name");
while ($res = sql_fetch_array($qry)) array_push($group, $res);

$res = sql_fetch("select count(*) as cnt from `{$g5['sms5_form_table']}` where fg_no=0");
$no_count = $res['cnt'];
?>

<form name="emo_frm">
    <label for="emo_sel" class="sound_only">이모티콘 그룹</label>
    <select name="fg_no" id="emo_sel">
        <option value="" <?php echo $fg_no?'':'selected'?>> 전체 </option>
        <option value="0" <?php echo $fg_no=='0'?'selected':''?>> 미분류 (<?php echo number_format($no_count)?>) </option>
        <?php for($i=0; $i<count($group); $i++) {?>
        <option value="<?php echo $group[$i]['fg_no']?>" <?php echo ($fg_no==$group[$i]['fg_no'])?'selected':''?>> <?php echo $group[$i]['fg_name']?> (<?php echo number_format($group[$i]['fg_count'])?>) </option>
        <?php } ?>
    </select>
</form>

<ul class="emo_list">
</ul>

<form name="emo_sch" id="emo_sch" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
<input type="hidden" name="fg_no" value="<?php echo $fg_no?>">
<input type="hidden" name="page" id="hidden_page" >
<input type="hidden" name="fg_no" id="hidden_fg_no" >
<label for="st" class="sound_only">검색대상</label>
<select name="st" id="st">
    <option value="all" <?php echo $st=='all'?'selected':''?>>제목 + 이모티콘</option>
    <option value="name" <?php echo $st=='name'?'selected':''?>>제목</option>
    <option value="content" <?php echo $st=='content'?'selected':''?>>이모티콘</option>
</select>
<label for="sv" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="sv" value="<?php echo $sv?>" id="sv" required class="frm_input required" size="15">
<input type="submit" value="검색" class="btn_submit">
</form>

<nav class="pg_wrap">
    <span class="pg" id="emo_pg"></span>
</nav>

<script src="<?php echo G5_JS_URL?>/jquery.sms_paging.js"></script>
<script>
var emoticon_list = {
    go : function(fo_no){
        var wr_message = document.getElementById('wr_message');

        //wr_message.focus();
        wr_message.value = document.getElementById('fo_contents_' + fo_no).value;

        byte_check('wr_message', 'sms_bytes');
    }
};
(function($){
    var $search_form = $("form#emo_sch");
    emoticon_list.fn_paging = function( hash_val,total_page ){
        $('#emo_pg').paging({
            current:hash_val ? hash_val : 1,
            max:total_page == 0 || total_page ? total_page : 45,
            length : 5,
            liitem : 'span',
            format:'{0}',
            next:'다음',
            prev:'이전',
            sideClass:'pg_page pg_next',
            prevClass:'pg_page pg_prev',
            first:'&lt;&lt;',last:'&gt;&gt;',
            href:'#',
            itemCurrent:'pg_current',
            itemClass:'pg_page',
            appendhtml:'<span class="sound_only">페이지</span>',
            onclick:function(e,page){
                e.preventDefault();
                $("#hidden_page").val( page );
                var params = $($search_form).serialize();
                emoticon_list.select_page( params, "json" );
            }
        });
    }
    emoticon_list.loading = function( el, src ){
        if( !el || !src) return;
        $(el).append("<span class='tmp_loading'><img src='"+src+"' title='loading...' ></span>");
    }
    emoticon_list.loadingEnd = function( el ){
        $(".tmp_loading", $(el)).remove();
    }
    emoticon_list.select_page = function( params, type ){
        if( !type ){
            type = "json";
        }
        emoticon_list.loading(".emo_list", "./img/ajax-loader.gif" ); //로딩 이미지 보여줌
        $.ajax({
            url: "./ajax.sms_write_form.php",
            cache:false,
            timeout : 30000,
            dataType:type,
            data:params,
            success: function(HttpRequest) {
                if( type == "json" ){
                    if (HttpRequest.error) {
                        alert(HttpRequest.error);
                        return false;
                    } else {
                        var $emoticon_box = $(".emo_list");
                        $emoticon_box.html( HttpRequest.list_text );
                        emoticon_list.fn_paging( HttpRequest.page, HttpRequest.total_page );
                        $("#hidden_page").val( HttpRequest.page );
                    }
                }
                emoticon_list.loadingEnd(".emo_list"); //로딩 이미지 지움
            }
        });
    }

    $("#emo_sel").bind("change", function(e){
        var params = { fg_no : $(this).val() };
        $search_form[0].reset();
        $("#hidden_fg_no").val( $(this).val() );
        emoticon_list.select_page( params, "json" );
    });
    $search_form.submit(function(e){
        e.preventDefault();
        var $form = $(this),
            params = $(this).serialize();
        emoticon_list.select_page( params, "json" );
    });
    $("#emo_sel").trigger("change");
})(jQuery);
</script>