@extends('layouts.app')

@section('title', '회원가입약관')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="text-center mb-4">회원가입약관</h2>
            
            <form method="POST" action="{{ route('register.agree') }}" id="fregister">
                @csrf
                
                <!-- 회원가입약관 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">회원가입약관</h5>
                    </div>
                    <div class="card-body">
                        <div class="terms-box">
                            {!! nl2br(e($config->cf_stipulation ?? '제1조(목적)
이 약관은 회사(이하 "회사"라 한다)가 운영하는 사이트에서 제공하는 서비스(이하 "서비스"라 한다)를 이용함에 있어 회사와 이용자의 권리·의무 및 책임사항을 규정함을 목적으로 합니다.

제2조(용어의 정의)
① "사이트"란 회사가 재화 또는 용역을 이용자에게 제공하기 위하여 컴퓨터 등 정보통신설비를 이용하여 재화 또는 용역을 거래할 수 있도록 설정한 가상의 영업장을 말합니다.
② "이용자"란 "사이트"에 접속하여 이 약관에 따라 "사이트"가 제공하는 서비스를 받는 회원 및 비회원을 말합니다.
③ "회원"이라 함은 "사이트"에 개인정보를 제공하여 회원등록을 한 자로서, "사이트"의 정보를 지속적으로 제공받으며, "사이트"가 제공하는 서비스를 계속적으로 이용할 수 있는 자를 말합니다.

제3조(약관의 효력과 변경)
① 이 약관은 이용자에게 공시함으로써 효력을 발생합니다.
② 회사는 필요하다고 인정되는 경우 이 약관을 변경할 수 있으며, 회사가 약관을 변경할 경우에는 적용일자 및 변경사유를 명시하여 현행약관과 함께 사이트의 초기화면에 그 적용일자 7일 이전부터 적용일자 전일까지 공지합니다.

제4조(약관 외 준칙)
이 약관에서 정하지 아니한 사항과 이 약관의 해석에 관하여는 전자상거래 등에서의 소비자보호에 관한 법률, 약관의 규제 등에 관한 법률, 공정거래위원회가 정하는 전자상거래 등에서의 소비자 보호지침 및 관계법령 또는 상관례에 따릅니다.

제5조(회원가입)
① 이용자는 회사가 정한 가입 양식에 따라 회원정보를 기입한 후 이 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청합니다.
② 회사는 제1항과 같이 회원으로 가입할 것을 신청한 이용자 중 다음 각 호에 해당하지 않는 한 회원으로 등록합니다.
  1. 등록 내용에 허위, 기재누락, 오기가 있는 경우
  2. 기타 회원으로 등록하는 것이 "사이트"의 기술상 현저히 지장이 있다고 판단되는 경우
③ 회원가입계약의 성립 시기는 회사의 승낙이 회원에게 도달한 시점으로 합니다.')) !!}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form-check">
                            <input class="form-check-input @error('agree') is-invalid @enderror" 
                                   type="checkbox" id="agree" name="agree" value="1">
                            <label class="form-check-label" for="agree">
                                회원가입약관의 내용에 동의합니다.
                            </label>
                            @error('agree')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 개인정보처리방침 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">개인정보처리방침안내</h5>
                    </div>
                    <div class="card-body">
                        <div class="terms-box">
                            {!! nl2br(e($config->cf_privacy ?? '1. 개인정보의 수집 및 이용목적
회사는 수집한 개인정보를 다음의 목적을 위해 활용합니다.
- 서비스 제공에 관한 계약 이행 및 서비스 제공에 따른 요금정산
- 회원 관리: 회원제 서비스 이용에 따른 본인확인, 개인 식별, 불량회원의 부정 이용 방지와 비인가 사용 방지, 가입 의사 확인, 연령확인
- 마케팅 및 광고에 활용: 이벤트 등 광고성 정보 전달, 접속 빈도 파악 또는 회원의 서비스 이용에 대한 통계

2. 수집하는 개인정보 항목
회사는 회원가입, 상담, 서비스 신청 등을 위해 아래와 같은 개인정보를 수집하고 있습니다.
- 필수항목: 이름, 생년월일, 성별, 로그인ID, 비밀번호, 휴대전화번호, 이메일
- 선택항목: 주소
- 자동수집항목: IP 정보, 쿠키, 방문 일시, 서비스 이용 기록

3. 개인정보의 보유 및 이용기간
원칙적으로, 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체 없이 파기합니다. 단, 다음의 정보에 대해서는 아래의 이유로 명시한 기간 동안 보존합니다.
- 보존 이유: 회원 탈퇴 시 부정이용 방지
- 보존 기간: 3개월

4. 개인정보의 파기절차 및 방법
회사는 원칙적으로 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체없이 파기합니다.')) !!}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form-check">
                            <input class="form-check-input @error('agree2') is-invalid @enderror" 
                                   type="checkbox" id="agree2" name="agree2" value="1">
                            <label class="form-check-label" for="agree2">
                                개인정보처리방침안내의 내용에 동의합니다.
                            </label>
                            @error('agree2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">회원가입</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.terms-box {
    height: 300px;
    overflow-y: auto;
    background-color: #f8f9fa;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    font-size: 0.9rem;
    line-height: 1.6;
}
</style>
@endpush