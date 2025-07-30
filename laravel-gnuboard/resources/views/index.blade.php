@extends('layouts.app')

@section('title', '메인')

@section('content')
<div class="container">
    <h2 class="visually-hidden">최신글</h2>
    
    <div class="latest_top_wr">
        <div class="row g-3">
            <div class="col-md-4">
                {!! latest('pic_list', 'free', 4, 23) !!}
            </div>
            <div class="col-md-4">
                {!! latest('pic_list', 'qa', 4, 23) !!}
            </div>
            <div class="col-md-4">
                {!! latest('pic_list', 'notice', 4, 23) !!}
            </div>
        </div>
    </div>
    
    <div class="latest_wr mt-5">
        {!! latest('pic_block', 'gallery', 4, 23) !!}
    </div>
    
    <div class="latest_wr mt-5">
        <div class="row g-3">
            @foreach($otherLatestPosts as $boardTable => $data)
                <div class="col-md-4 mb-4">
                    {!! latest('basic', $boardTable, 6, 24) !!}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.latest_top_wr { margin-bottom: 30px; }
.latest_wr { margin-bottom: 30px; }
.lt_wr { margin-bottom: 20px; }
.visually-hidden { position: absolute; left: -9999px; }
</style>
@endpush