<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/debugbar/style.css">', 0);
?>
<style>
<?php if (defined('G5_IS_ADMIN') && G5_IS_ADMIN){ ?>
.debug_bar_wrap{z-index:991001}
<?php } ?>
</style>
<div class="debug_bar_wrap">
    <div class="debug_bar_text_group">
        <div class="debug_bar_btn_group"><button class="view_debug_bar debug_button">디버그</button></div>
        <div class="debug_bar_text">
            <?php echo 'PHP 실행시간 : '.$php_run_time.' | 메모리 사용량 : '.number_format($memory_usage).' bytes'; ?>
        </div>
    </div>
    <div class="debug_bar_content">
        <div class="content_inner">

            <div class="debugbar_close_btn_el"><button class="debugbar_close_btn btn">닫기</button></div>
            <div id="debugbar">
                <ul class="debugbar_tab">
                    <li class="debug_tab active" data-tab="executed_query"><a href="#debug_executed_query">SQL Query</a></li>
                    <li class="debug_tab" data-tab="hook_info"><a href="#debug_hook_info">HOOK 정보</a></li>
                </ul>
            </div>

            <div id="debug_executed_query" class="inner_debug">
                <h3 class="query_top">
                    총 쿼리수 : <span><?php echo isset($g5_debug['sql']) ? count($g5_debug['sql']) : 0; ?></span>
                </h3>

                <div class="sql_query_list">
                <table class="debug_table">
                    <caption>
                    쿼리 목록
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">실행순서</th>
                            <th scope="col">쿼리문</th>
                            <th scope="col">실행시간</th>
                        </tr>
                    </thead>
                <tbody>
                <?php
                foreach((array) $g5_debug['sql'] as $key=>$query){

                if( empty($query) ) continue;

                $executed_time = $query['end_time'] - $query['start_time'];
                $show_excuted_time = number_format((float)$executed_time * 1000, 2, '.', '');
                ?>
                <tr>
                    <td scope="row" data-label="실행순서"><?php echo $key; ?></td>
                    <td class="left" data-label="쿼리문"><?php echo $query['sql']; ?></td>
                    <td data-label="실행시간"><?php echo $show_excuted_time.' ms'; ?></td>
                </tr>
                <?php } ?>

                </tbody>

                </table>
                </div>
            </div>

            <div id="debug_hook_info" class="inner_debug">
            <?php
            $event_totals = get_hook_datas('event');
            $event_callbacks = get_hook_datas('event', 1);
            $replace_totals = get_hook_datas('replace');
            $replace_callbacks = get_hook_datas('replace', 1);
            ?>
                <div class="hook_list">

                    <div class="debug_table_wrap">
                        <table class="debug_table hook_table">
                        <caption>
                        HOOK 목록
                        </caption>
                        <thead>
                        <tr>
                            <th scope="col" width="20%">event_tag (갯수)</th>
                            <th scope="col" width="60%">event_function</th>
                            <th scope="col" width="10%">인수의 수</th>
                            <th scope="col" width="10%">우선 순위</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        foreach((array) $event_totals as $tag=>$count){
                        
                        if( $tag === 'count' ) continue;
                        if( empty($count) ) continue;
                        
                        $datas = array();
                        if( isset($event_callbacks[$tag]) ){
                            
                            $event_callbacks_tag = $event_callbacks[$tag];
                            ksort($event_callbacks_tag);
                            
                            foreach((array) $event_callbacks_tag as $priority=>$event_args){
                                if( empty($event_args) ) continue;
                                    
                                    foreach($event_args as $index=>$funcs){
                                        $datas[] = array(
                                            'priority' => $priority,
                                            'function' => $funcs['function'],
                                            'arguments' => $funcs['arguments'],
                                            );
                                    }   //end foreach
                            }   //end foreach

                            $rowspan = $datas ? ' rowspan='.count($datas) : '';
                        
                            $is_print = $rowspan;
                            
                            foreach($datas as $data){
                                
                                $print_function = '';

                                if( $data['function'] && is_array($data['function']) ){
                                    foreach( (array) $data['function'] as $key=>$fn_name ){
                                        $str_delimiter = '';
                                        if($key) $str_delimiter = ' :: ';
                                        
                                        if( is_object($fn_name) ){
                                            $fn_name = get_class($fn_name);
                                        }

                                        $print_function .= $str_delimiter.(string) $fn_name;
                                    }
                                } else {
                                    $print_function = $data['function'];
                                }
                        ?>
                        <tr>
                            <?php if ($is_print){ ?>
                            <td scope="row" data-label="event_tag" <?php echo $rowspan; ?>><?php echo $tag.' <span class="hook_count">('.$count.')</span>'; ?></td>
                            <?php } ?>
                            <td data-label="event_function">
                                <?php echo $print_function; ?>
                            </td>
                            <td data-label="인수의 수"><?php echo $data['arguments']; ?></td>
                            <td data-label="우선 순위"><?php echo $data['priority']; ?></td>
                        </tr>
                        <?php
                                $is_print = '';
                                }   //end foreach
                            } else {    // else if
                        ?>
                        <tr>
                            <td scope="row" data-label="event_tag"><?php echo $tag.' <span class="hook_count">('.$count.')</span>'; ?></td>
                            <td data-label="event_function">&nbsp;</td>
                            <td data-label="인수의 수">&nbsp;</td>
                            <td data-label="우선 순위">&nbsp;</td>
                        </tr>
                        <?php
                            }//end if
                        }   //end foreach
                        ?>

                        </tbody>

                        </table>
                    </div>

                    <div class="debug_table_wrap">
                        <table class="debug_table hook_table">
                        <caption>
                        HOOK 목록
                        </caption>
                        <thead>
                        <tr>
                            <th scope="col" width="20%">replace_tag (갯수)</th>
                            <th scope="col" width="60%">replace_function</th>
                            <th scope="col" width="10%">인수의 수</th>
                            <th scope="col" width="10%">우선 순위</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        foreach((array) $replace_totals as $tag=>$count){
                        
                        if( $tag === 'count' ) continue;
                        if( empty($count) ) continue;

                        $datas = array();
                        if( isset($replace_callbacks[$tag]) ){
                            
                            $replace_callbacks_tag = $replace_callbacks[$tag];
                            ksort($replace_callbacks_tag);
                            
                            foreach((array) $replace_callbacks_tag as $priority=>$replace_args){
                                if( empty($replace_args) ) continue;
                                    
                                    foreach($replace_args as $index=>$funcs){
                                        $datas[] = array(
                                            'priority' => $priority,
                                            'function' => $funcs['function'],
                                            'arguments' => $funcs['arguments'],
                                            );
                                    }   //end foreach
                            }   //end foreach

                            $rowspan = $datas ? ' rowspan='.count($datas) : '';
                        
                            $is_print = $rowspan;
                            
                            foreach($datas as $data){
                                
                                $print_function = '';

                                if( $data['function'] && is_array($data['function']) ){
                                    foreach( (array) $data['function'] as $key=>$fn_name ){
                                        $str_delimiter = '';
                                        if($key) $str_delimiter = ' :: ';
                                        
                                        if( is_object($fn_name) ){
                                            $fn_name = get_class($fn_name);
                                        }

                                        $print_function .= $str_delimiter.(string) $fn_name;
                                    }
                                } else {
                                    $print_function = $data['function'];
                                }
                        ?>
                        <tr>
                            <?php if ($is_print){ ?>
                            <td scope="row" data-label="replace_tag" <?php echo $rowspan; ?>><?php echo $tag.' <span class="hook_count">('.$count.')</span>'; ?></td>
                            <?php } ?>
                            <td data-label="replace_function">
                                <?php echo $print_function; ?>
                            </td>
                            <td data-label="인수의 수"><?php echo $data['arguments']; ?></td>
                            <td data-label="우선 순위"><?php echo $data['priority']; ?></td>
                        </tr>
                        <?php
                                $is_print = '';
                                }   //end foreach
                            } else {    // else if
                        ?>
                        <tr>
                            <td scope="row" data-label="replace_tag"><?php echo $tag.' <span class="hook_count">('.$count.')</span>'; ?></td>
                            <td data-label="replace_function">&nbsp;</td>
                            <td data-label="인수의 수">&nbsp;</td>
                            <td data-label="우선 순위">&nbsp;</td>
                        </tr>
                        <?php
                            }//end if
                        }   //end foreach
                        ?>

                        </tbody>

                        </table>
                    </div>

                </div>  <!-- end .hook_list -->
            </div>

        </div>  <!-- end .content_inner -->
    </div>  <!-- end .debug_bar_content -->
</div>  <!-- end .debug_bar_wrap -->
<script>
jQuery(function($){
    $(".debug_tab").on("click", function() {
        $(".inner_debug").hide();
        $(this).addClass("active").siblings().removeClass("active");
        $("#debug_" + $(this).attr('data-tab')).show();
    });

    $(".debug_tab").on("click", "a", function(e) {
        e.preventDefault();
    });
    
    $(".debug_bar_wrap").on("click", ".debugbar_close_btn", function() {
        $(".view_debug_bar").trigger("click");
    })
    .on("click", ".view_debug_bar", function() {
        $(".debug_bar_content").toggle();
    });
});
</script>