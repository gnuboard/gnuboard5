<?php
$sub_menu = '600400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

// Variables
$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';
if (!(isset($page) && $page) || (isset($page) && $page == 0)) $page = 1;

$rows_per_page = 5; // Number of items per page

// Calculate offset
$from_record = ($page - 1) * $rows_per_page;

// Get total count for pagination
$total_sql = "SELECT COUNT(*) as cnt FROM `{$g5['g5_subscription_order_history_table']}` WHERE od_id = '{$od_id}'";
$total_result = sql_fetch($total_sql);
$total_count = $total_result['cnt'];
$total_pages = ceil($total_count / $rows_per_page);

// Fetch paginated data
$sql = "SELECT * FROM `{$g5['g5_subscription_order_history_table']}` 
        WHERE od_id = '{$od_id}' 
        ORDER BY hs_id DESC 
        LIMIT {$from_record}, {$rows_per_page}";
$result = sql_query($sql);
$hss = sql_result_array($result);
?>

<ul class="order-historys">
    <?php if ($hss) {
        foreach ($hss as $h) { ?>
            <li rel="<?php echo $h['hs_id']; ?>" class="history-item <?php echo $h['hs_category']; ?>">
                <div class="history-content">
                    <?php echo conv_content($h['hs_content'], 0); ?>
                </div>
                <p class="history-btns">
                    <span class="history-date"><?php echo $h['hs_time']; ?></span>
                    <a href="#" class="delete-history" data-id="<?php echo $h['hs_id']; ?>" role="button">삭제하기</a>
                </p>
            </li>
        <?php }
    } else { ?>
        <li class="no-data">히스토리가 없습니다.</li>
    <?php } ?>
</ul>

<!-- Pagination -->
<div id="pagination" class="pagination">
    <?php
    $pagination = get_paging($rows_per_page, $page, $total_pages, "ajax.subscription_history.php?od_id={$od_id}");
    echo $pagination;
    ?>
</div>
