<?php
if (!defined('_GNUBOARD_')) exit;

if( ! $config['cf_social_login_use']) {     //소셜 로그인을 사용하지 않으면
    return;
}

$socials = social_get_provider_service_name('', 'all');

$session_id = session_id();

add_stylesheet('<link rel="stylesheet" href="'.get_social_skin_url().'/style.css?ver='.G5_CSS_VER.'">', 10);
?>

<li>
    <label class="frm_label">SNS 로그인 관리</label>
    <div class="reg-form sns-wrap-reg">
        <div class="sns-wrap">

        <?php foreach( $socials as $social=>$provider_name ){
            
            if( !option_array_checked($social, $config['cf_social_servicelist'])) {
                continue;
            }

            $social_nonce = social_nonce_create($social, $session_id);
            $add_class='';
            $title='';
            if( in_array($social, $my_provides) ){
                
                $link_href = G5_SOCIAL_LOGIN_URL.'/unlink.php?provider='.$social.'&amp;social_nonce='.$social_nonce;

                $title = $provider_name.' 연결해제하기';
            } else {
                $add_class = ' sns-icon-not';

                $link_href = $self_url.'?provider='.$social.'&amp;mylink=1&amp;url='.$urlencode;

                $title = $provider_name.' 연결하기';

            }
        ?>

        <a href="<?php echo $link_href; ?>" id="sns-<?php echo $social; ?>" class="sns-icon social_link sns-<?php echo $social; ?><?php echo $add_class; ?>" title="<?php echo $title; ?>" data-provider="<?php echo $social; ?>" ><span class="ico"></span><span class="txt"><?php echo $provider_name; ?> 로그인</span></a>

        <?php }     //end foreach ?>

        </div>
    </div>
</li>

<script>

function social_get_nonce(provider){
    var socials = [];

    <?php foreach( $socials as $social=>$v ){ ?>
        socials["<?php echo $social; ?>"] = "<?php echo social_nonce_create($social, $session_id); ?>";
    <?php } ?>

    return (typeof socials[provider] != 'undefined') ? socials[provider] : '';
}

function social_link_fn(provider){

    provider = provider.toLowerCase();

    var $icon = jQuery("#sns-"+provider);

    if( $icon.length ){

        var social_url = "<?php echo G5_SOCIAL_LOGIN_URL; ?>",
            link_href = social_url+"/unlink.php?provider="+provider+"&social_nonce="+social_get_nonce(provider),
            atitle = provider+" 연결해제하기";

        $icon.attr({"href":link_href, "title":atitle}).removeClass("sns-icon-not");

        //$icon.children("img").attr({"src" : social_url+"/img/32x32/"+provider+".png", "title":atitle, "alt":atitle}).removeClass("link").addClass("unlink");
        
        alert('연결 되었습니다');

        return true;
    }

    return false;
}

jQuery(function($){

    var social_img_path = "<?php echo G5_SOCIAL_LOGIN_URL; ?>",
        self_url = "<?php echo $self_url; ?>",
        urlencode = "<?php echo $urlencode; ?>";
        
    $(".sns-wrap").on("click", ".social_link", function(e){
        e.preventDefault();

        var othis = $(this);

        if( ! othis.hasClass('sns-icon-not') ){     //소셜계정 해제하기

            if (!confirm('정말 이 계정 연결을 해제하시겠습니까?')) {
                return false;
            }

            var ajax_url = "<?php echo G5_SOCIAL_LOGIN_URL.'/unlink.php' ?>",
                mb_id = '',
                provider = $(this).attr("data-provider");

            if( ! provider ){
                alert("잘못된 요청! provider 값이 없습니다.");
                return false;
            }

            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {
                    'provider': provider,
                    'mb_id': mb_id,
                    'nonce' : social_get_nonce(provider)
                },
                dataType: 'json',
                cache : false,
                async: false,
                success: function(data, textStatus) {
                    if (data.error) {
                        alert(data.error);
                        return false;
                    } else {
                        var atitle = provider+" 연결하기",
                            link_href = self_url+"?provider="+provider+"&mylink=1&url="+urlencode;
                        
                        othis.attr({"href":link_href, "title":atitle}).addClass("sns-icon-not");

                        //othis.children("img").attr({"src" : social_img_path+"/img/32x32/"+provider+"_off.png", "title":atitle, "alt":atitle}).removeClass("unlink").addClass("link");

                    }
                },
                error: function(data) {
                    try { console.log(data) } catch (e) { alert(data.error) };
                }
            });

        } else {        //소셜계정 연결하기

            var pop_url = $(this).attr("href");
            var is_popup = "<?php echo G5_SOCIAL_USE_POPUP; ?>";
            
            if( is_popup ){
                var newWin = window.open(
                    pop_url, 
                    "social_sing_on", 
                    "location=0,status=0,scrollbars=1,width=600,height=500"
                );

                if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
                     alert('브라우저에서 팝업이 차단되어 있습니다. 팝업 활성화 후 다시 시도해 주세요.');

            } else {
                location.replace(pop_url);
            }

        }
        return false;
    });
});
</script>