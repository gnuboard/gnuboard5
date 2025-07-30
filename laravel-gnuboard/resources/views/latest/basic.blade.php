<div class="lat">
    <h2 class="lat_title">
        <a href="{{ route('board.index', $boardTable) }}">{{ $board->bo_subject }}</a>
    </h2>
    <ul>
        @forelse($posts as $post)
            <li>
                @if($board->isUseCategory() && $post->ca_name)
                    <span class="lt_cate">[{{ $post->ca_name }}]</span>
                @endif
                
                <a href="{{ route('board.show', [$boardTable, $post->wr_id]) }}">
                    {{ $post->subject_short ?? cut_str($post->wr_subject, 24) }}
                    @if($post->wr_comment > 0)
                        <span class="lt_cmt">+{{ $post->wr_comment }}</span>
                    @endif
                </a>
                
                <span class="lt_date">{{ $post->wr_datetime->format('m-d') }}</span>
            </li>
        @empty
            <li class="empty_li">게시물이 없습니다.</li>
        @endforelse
    </ul>
    <div class="lt_more">
        <a href="{{ route('board.index', $boardTable) }}">
            <span class="sound_only">{{ $board->bo_subject }}</span>더보기
        </a>
    </div>
</div>

<style>
.lat { 
    border: 1px solid #e3e3e3; 
    border-radius: 5px; 
    padding: 15px;
    background: #fff;
}
.lat_title { 
    font-size: 1.2em; 
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e3e3e3;
}
.lat_title a { 
    color: #333; 
    text-decoration: none;
    font-weight: bold;
}
.lat ul { 
    list-style: none; 
    padding: 0; 
    margin: 0;
}
.lat li { 
    padding: 8px 0; 
    border-bottom: 1px dotted #e3e3e3;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.lat li:last-child { border-bottom: 0; }
.lat li a { 
    color: #666; 
    text-decoration: none;
    flex: 1;
}
.lat li a:hover { color: #000; }
.lt_cate { 
    color: #3a8afd; 
    font-size: 0.9em; 
    margin-right: 5px;
}
.lt_cmt { 
    color: #ff6b6b; 
    font-size: 0.9em; 
    margin-left: 5px;
}
.lt_date { 
    color: #999; 
    font-size: 0.85em;
    white-space: nowrap;
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
.lt_more a:hover { color: #000; }
.empty_li { 
    text-align: center; 
    color: #999;
    padding: 20px 0 !important;
}
.sound_only { 
    position: absolute; 
    left: -9999px;
}
</style>