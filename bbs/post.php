<?php
include_once('./_common.php');

if (!$board['bo_table']) {
   alert('존재하지 않는 게시판입니다.', G5_URL);
}

$g5['board_title'] = ((G5_IS_MOBILE && $board['bo_mobile_subject']) ? $board['bo_mobile_subject'] : $board['bo_subject']);

include_once(G5_BBS_PATH.'/board_head.php');

$list = array();
$sql = " select * from {$g5['post_table']} where bo_table = '{$bo_table}' order by po_group desc, po_order asc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $list[$i] = $row;
}
?>

<button type="button" onclick="location.href='<?php echo G5_BBS_URL."/post_form.php?bo_table={$bo_table}" ?>'">글쓰기</button>

<div id="bo_list" class="tbl_head01">
    <h2><?php echo $board['bo_subject'] ?></h2>
    <p><?php echo $board['bo_description'] ?></p>
    <div class="tbl_head01_top">
        <ul>
            <li class="bo_list_total">Total <?php echo number_format($total_count) ?>건</li>
        </ul>
    </div>
    <table border="1">
    <thead>
    <tr>
        <th scope="col">No</th>
        <th scope="col">Title</th>
        <th scope="col">Author</th>
        <th scope="col">Date</th>
        <th scope="col">Views</th>
        <th scope="col">Reply</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $i<count($list); $i++) { ?>
    <tr>
        <td><?php echo $i+1; ?></td>
        <td>
            <a href="<?php echo get_pretty_url($bo_table, $list[$i]['po_id']) ?>">
                <?php echo $list[$i]['po_subject'] ?>
            </a>
        </td>
        <td><?php echo $list[$i]['po_name'] ?></td>
        <td><?php echo $list[$i]['po_datetime'] ?></td>
        <td><?php echo $list[$i]['po_hit'] ?></td>
        <td><a href="<?php echo "./post_form.php?bo_table={$bo_table}&w=r&po_id={$list[$i]['po_id']}" ?>">답글</a></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>    

<?php
include_once(G5_BBS_PATH.'/board_tail.php');
?>