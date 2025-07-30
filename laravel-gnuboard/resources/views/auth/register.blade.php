@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">회원가입</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="mb_id" class="form-label">아이디 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mb_id') is-invalid @enderror" 
                                   id="mb_id" name="mb_id" value="{{ old('mb_id') }}" required autofocus>
                            <small class="text-muted">영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</small>
                            @error('mb_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mb_password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('mb_password') is-invalid @enderror" 
                                   id="mb_password" name="mb_password" required>
                            @error('mb_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mb_password_confirmation" class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="mb_password_confirmation" name="mb_password_confirmation" required>
                        </div>

                        <div class="mb-3">
                            <label for="mb_name" class="form-label">이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mb_name') is-invalid @enderror" 
                                   id="mb_name" name="mb_name" value="{{ old('mb_name') }}" required>
                            @error('mb_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mb_nick" class="form-label">닉네임 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mb_nick') is-invalid @enderror" 
                                   id="mb_nick" name="mb_nick" value="{{ old('mb_nick') }}" required>
                            <small class="text-muted">공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)</small>
                            @error('mb_nick')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mb_email" class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('mb_email') is-invalid @enderror" 
                                   id="mb_email" name="mb_email" value="{{ old('mb_email') }}" required>
                            @error('mb_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mb_hp" class="form-label">휴대폰번호</label>
                            <input type="text" class="form-control @error('mb_hp') is-invalid @enderror" 
                                   id="mb_hp" name="mb_hp" value="{{ old('mb_hp') }}" placeholder="010-0000-0000">
                            @error('mb_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h5 class="mb-3">주소</h5>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="mb_zip1" name="mb_zip1" 
                                       value="{{ old('mb_zip1') }}" placeholder="우편번호" readonly>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="mb_zip2" name="mb_zip2" 
                                       value="{{ old('mb_zip2') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary" onclick="alert('우편번호 검색 기능은 추후 구현 예정입니다.')">우편번호 검색</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="mb_addr1" name="mb_addr1" 
                                   value="{{ old('mb_addr1') }}" placeholder="기본주소">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="mb_addr2" name="mb_addr2" 
                                   value="{{ old('mb_addr2') }}" placeholder="상세주소">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="mb_addr3" name="mb_addr3" 
                                   value="{{ old('mb_addr3') }}" placeholder="참고항목">
                        </div>

                        <hr>

                        <h5 class="mb-3">기타 개인설정</h5>
                        <div class="mb-3">
                            <label for="mb_signature" class="form-label">서명</label>
                            <textarea class="form-control" id="mb_signature" name="mb_signature" rows="3">{{ old('mb_signature') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="mb_profile" class="form-label">자기소개</label>
                            <textarea class="form-control" id="mb_profile" name="mb_profile" rows="3">{{ old('mb_profile') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mb_mailling" name="mb_mailling" value="1" 
                                       {{ old('mb_mailling') ? 'checked' : '' }}>
                                <label class="form-check-label" for="mb_mailling">
                                    메일링서비스를 받겠습니다.
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mb_sms" name="mb_sms" value="1" 
                                       {{ old('mb_sms') ? 'checked' : '' }}>
                                <label class="form-check-label" for="mb_sms">
                                    SMS를 받겠습니다.
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mb_open" name="mb_open" value="1" 
                                       {{ old('mb_open') ? 'checked' : '' }}>
                                <label class="form-check-label" for="mb_open">
                                    정보공개를 합니다.
                                </label>
                            </div>
                            <small class="text-muted">정보공개를 바꾸시면 앞으로 0일 이내에는 변경이 안됩니다.</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">회원가입</button>
                            <a href="{{ route('home') }}" class="btn btn-secondary">취소</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 아이디 중복 체크
document.getElementById('mb_id').addEventListener('blur', function() {
    var mbId = this.value;
    if (mbId.length < 3) return;
    
    fetch('{{ route("register.check.mb_id") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mb_id: mbId })
    })
    .then(response => response.json())
    .then(data => {
        var input = document.getElementById('mb_id');
        var feedback = input.nextElementSibling.nextElementSibling;
        
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.insertBefore(feedback, input.nextElementSibling.nextElementSibling);
        }
        
        if (data.available) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.textContent = '이미 사용중인 아이디입니다.';
        }
    });
});

// 닉네임 중복 체크
document.getElementById('mb_nick').addEventListener('blur', function() {
    var mbNick = this.value;
    if (mbNick.length < 2) return;
    
    fetch('{{ route("register.check.mb_nick") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mb_nick: mbNick })
    })
    .then(response => response.json())
    .then(data => {
        var input = document.getElementById('mb_nick');
        var feedback = input.nextElementSibling.nextElementSibling;
        
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.insertBefore(feedback, input.nextElementSibling.nextElementSibling);
        }
        
        if (data.available) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.textContent = '이미 사용중인 닉네임입니다.';
        }
    });
});

// 이메일 중복 체크
document.getElementById('mb_email').addEventListener('blur', function() {
    var mbEmail = this.value;
    if (!mbEmail || !mbEmail.includes('@')) return;
    
    fetch('{{ route("register.check.mb_email") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mb_email: mbEmail })
    })
    .then(response => response.json())
    .then(data => {
        var input = document.getElementById('mb_email');
        var feedback = input.nextElementSibling;
        
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.appendChild(feedback);
        }
        
        if (data.available) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.textContent = '이미 사용중인 이메일입니다.';
        }
    });
});
</script>
@endpush