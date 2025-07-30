<div class="pic_lt">
    <h2 class="lat_title">
        <a href="{{ route('board.index', $boardTable) }}">{{ $board->bo_subject }}</a>
    </h2>
    <ul>
        @forelse($posts as $post)
            <li>
                <a href="{{ route('board.show', [$boardTable, $post->wr_id]) }}">
                    <img src="https://via.placeholder.com/100x70" alt="">
                    <span class="pic_subject">
                        {{ $post->subject_short ?? cut_str($post->wr_subject, 23) }}
                        @if($post->wr_comment > 0)
                            <span class="cnt_cmt">+{{ $post->wr_comment }}</span>
                        @endif
                    </span>
                    <span class="pic_info">
                        <span class="pic_date">{{ $post->wr_datetime->format('Y-m-d') }}</span>
                        <span class="pic_hit">조회 {{ number_format($post->wr_hit) }}</span>
                    </span>
                </a>
            </li>
        @empty
            <li class="empty_li">게시물이 없습니다.</li>
        @endforelse
    </ul>
    <div class="lt_more">
        <a href="{{ route('board.index', $boardTable) }}">더보기</a>
    </div>
</div>

<style>
.pic_lt {
    border: 1px solid #e3e3e3;
    border-radius: 5px;
    padding: 15px;
    background: #fff;
}
.pic_lt .lat_title {
    font-size: 1.1em;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e3e3e3;
}
.pic_lt .lat_title a {
    color: #333;
    text-decoration: none;
    font-weight: bold;
}
.pic_lt ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.pic_lt li {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px dotted #e3e3e3;
}
.pic_lt li:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: 0;
}
.pic_lt li a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #666;
}
.pic_lt li a:hover {
    color: #000;
}
.pic_lt img {
    width: 60px;
    height: 45px;
    object-fit: cover;
    margin-right: 10px;
    border: 1px solid #e3e3e3;
    border-radius: 3px;
}
.pic_subject {
    flex: 1;
    font-size: 0.95em;
    line-height: 1.4;
}
.cnt_cmt {
    color: #ff6b6b;
    font-size: 0.85em;
    margin-left: 5px;
}
.pic_info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    font-size: 0.8em;
    color: #999;
    white-space: nowrap;
}
.pic_date {
    margin-bottom: 2px;
}
.lt_more {
    text-align: right;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e3e3e3;
}
.lt_more a {
    color: #666;
    text-decoration: none;
    font-size: 0.9em;
}
.lt_more a:hover {
    color: #000;
}
.empty_li {
    text-align: center;
    color: #999;
    padding: 30px 0;
}
</style>