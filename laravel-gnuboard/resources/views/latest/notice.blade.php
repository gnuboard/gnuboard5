<ul class="list-unstyled">
    @forelse($posts as $post)
        <li class="mb-2">
            <a href="{{ route('board.show', [$boardTable, $post->wr_id]) }}" class="text-muted small">
                <span class="d-inline-block text-truncate" style="max-width: 250px;">
                    {{ $post->subject_short ?? cut_str($post->wr_subject, 30) }}
                </span>
                <span class="float-end">{{ $post->wr_datetime->format('m-d') }}</span>
            </a>
        </li>
    @empty
        <li class="text-muted small">등록된 공지사항이 없습니다.</li>
    @endforelse
</ul>