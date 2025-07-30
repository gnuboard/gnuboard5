<div class="pic_block">
    <h2 class="lat_title">
        <a href="{{ route('board.index', $boardTable) }}">{{ $board->bo_subject }}</a>
    </h2>
    <div class="pic_block_wrap">
        @forelse($posts as $post)
            <div class="pic_block_item">
                <a href="{{ route('board.show', [$boardTable, $post->wr_id]) }}">
                    <div class="pic_img">
                        <img src="https://via.placeholder.com/200x150" alt="">
                    </div>
                    <div class="pic_text">
                        <h3>{{ $post->subject_short ?? cut_str($post->wr_subject, 23) }}</h3>
                        <p>{{ cut_str(strip_tags($post->wr_content), 50) }}</p>
                        <span class="pic_date">{{ $post->wr_datetime->format('Y-m-d') }}</span>
                    </div>
                </a>
            </div>
        @empty
            <div class="empty_block">게시물이 없습니다.</div>
        @endforelse
    </div>
    <div class="lt_more">
        <a href="{{ route('board.index', $boardTable) }}">더보기</a>
    </div>
</div>

<style>
.pic_block {
    border: 1px solid #e3e3e3;
    border-radius: 5px;
    padding: 20px;
    background: #fff;
}
.pic_block .lat_title {
    font-size: 1.3em;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e3e3e3;
}
.pic_block .lat_title a {
    color: #333;
    text-decoration: none;
    font-weight: bold;
}
.pic_block_wrap {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}
.pic_block_item {
    border: 1px solid #e3e3e3;
    border-radius: 5px;
    overflow: hidden;
    transition: all 0.3s;
}
.pic_block_item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.pic_block_item a {
    display: block;
    text-decoration: none;
    color: #666;
}
.pic_img {
    position: relative;
    padding-bottom: 75%;
    overflow: hidden;
    background: #f5f5f5;
}
.pic_img img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pic_text {
    padding: 15px;
}
.pic_text h3 {
    font-size: 1em;
    margin: 0 0 10px;
    color: #333;
    font-weight: 500;
}
.pic_text p {
    font-size: 0.9em;
    margin: 0 0 10px;
    color: #666;
    line-height: 1.5;
}
.pic_date {
    font-size: 0.85em;
    color: #999;
}
.lt_more {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e3e3e3;
}
.lt_more a {
    display: inline-block;
    padding: 8px 20px;
    background: #f5f5f5;
    color: #666;
    text-decoration: none;
    border-radius: 3px;
    transition: all 0.3s;
}
.lt_more a:hover {
    background: #333;
    color: #fff;
}
.empty_block {
    grid-column: 1 / -1;
    text-align: center;
    color: #999;
    padding: 60px 0;
}
</style>