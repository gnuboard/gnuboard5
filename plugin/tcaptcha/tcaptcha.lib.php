<?php
define('_ANSWER_COUNT_', 3);

$text_number = new stdClass;
$text_number->kr = new stdClass;
$text_number->en = new stdClass;

//$text_number->kr = (object)array('a'=>1);

// 기수 cardinal (양을 나타낼때 사용하는 수)
$text_number->kr = (object)array(
    'number'    => array(
        array("영","일","이","삼","사","오","육","칠","팔","구","십"),
        array("영","하나","둘","셋","넷","다섯","여섯","일곱","여덟","아홉","열")
    ),
    // 서수 ordinal (순서를 나타낼때 사용하는 수)
    'ordinal'   => array("영","첫번째","두번째","세번째","네번째","다섯번째","여섯번째","일곱번째","여덟번째","아홉번째","열번째"),
    'high'      => array("다음 중 가장 큰 수는? %s.", "%s 중에서 가장 큰 수는?"),
    'low'       => array("다음 중 가장 작은 수는? %s.", "%s 중에서 가장 작은 수는?"),
    'position0' => array("다음 중 %s 숫자는? %s."), // 인수가 두개 있으며 첫번째에 위치가, 두번째 인수에 질문이 나열된다.
    'position1' => array("%s 중 %s 숫자는?"), // 인수가 두개 있으며 첫번째에 인수가 두반째에 위치에 대한 질문이 나열된다.
    'add'       => array("%s 더하기 %s ?", "%s + %s = ?"),
    'subtract'  => array("%s 빼기 %s ?", "%s - %s = ?"),
    'multiply'  => array("%s 곱하기 %s ?"),
    //'multiply'  => array("%s 곱하기 %s ?",  "%s 의 %s 배는 ?"),
    'and'       => "그리고",
);
/*
// 서수 ordinal (순서를 나타낼때 사용하는 수)
$text_number->kr->ordinal   = array("영","첫번째","두번째","세번째","네번째","다섯번째","여섯번째","일곱번째","여덟번째","아홉번째","열번째");
$text_number->kr->plus      = array("+","＋","더하기");
$text_number->kr->minus     = array("-","－","빼기");
$text_number->kr->multiply  = array("x","×","*","곱하기");
$text_number->kr->high      = array("다음 중 가장 큰 수는? %s.", "%s 중에서 가장 큰 수는?");
$text_number->kr->low       = array("다음 중 가장 작은 수는? %s.", "%s 중에서 가장 작은 수는?");
$text_number->kr->position0 =array("다음 중 %s 숫자는? %s."); // 인수가 두개 있으며 첫번째에 위치가, 두번째 인수에 질문이 나열된다.
$text_number->kr->position1 =array("%s 중 %s 숫자는?"); // 인수가 두개 있으며 첫번째에 인수가 두반째에 위치에 대한 질문이 나열된다.
$text_number->kr->add       = array("%s 더하기 %s ?", "%s + %s = ?");
$text_number->kr->subtract  = array("%s 빼기 %s ?", "%s - %s = ?");
$text_number->kr->and       = "그리고";
*/

$text_number->en = (object)array(
    'number'    => array(
        array("zero","one","two","three","four","five","six","seven","eight","nine","ten"),
        array("zero","first","second","third","fourth","fifth","sixth","seventh","eighth","ninth","tenth")
    ),
    'ordinal'   => array("zero","1st","2nd","3rd","4th","5th","6th","7th","8th","9th","10th"),
    'high'      => array("%s : which of these is the largest?"),
    'low'       => array("%s : which of these is the smallest?"),
    'position0' => array("lists %s postion number ? %s."), // 인수가 두개 있으며 첫번째에 위치가, 두번째 인수에 질문이 나열된다.
    'position1' => array("%s lists %s postion number ?"), // 인수가 두개 있으며 첫번째에 인수가 두반째에 위치에 대한 질문이 나열된다.
    'add'       => array("%s add %s ?", "%s plus %s ?", "%s + %s = ?"),
    'subtract'  => array("%s subtract %s ?", "%s minus %s ?", "%s - %s = ?"),
    'multiply'  => array("%s multiply %s ?"),
    'and'       => "and"
);

class tcaptcha
{
    var $language;
    var $tnum; // text number 의 약어
    var $min_count = 3; // 최소 문제 갯수
    var $max_count = 4; // 최대 문제 갯수
    var $select; // 결과값 배열
    var $arabia; // 결과값 아라비아 숫자 배열
    var $count; // 결과값 수
    var $high; // 결과값 배열 중 가장 큰 값
    var $low;  // 결과값 배열 중 가장 작은 값
    var $position;  // 몇번째 숫자는 값이 얼마인가?
    var $question;  // 문제
    var $answer;  // 더하기, 빼기 시에 답

    function tcaptcha($language='') {
        if (trim($language) == '')
            $language = 'kr';
        $this->set_language($language);
    }

    function set_language($language) {
        $this->language = $language;
    }

    function set_min_count($min_count) {
        $this->min_count = $min_count;
    }

    function set_max_count($max_count) {
        $this->max_count = $max_count;
    }

    function random_question() {
        $this->count = $count  = rand($this->min_count, $this->max_count); // 숫자를 몇개 뿌려줄것인지?
        $select = array(); // 선택된 값들
        $arabia = array(); // 선택된 값들의 아라비아 숫자
        $high = 0;
        $low  = 9999;
        while ($count != count($select)) {
            $choice = rand(0, count($this->tnum->number)-1); // 여러개의 숫자 형식중 하나를 선택한다.
            $number = $this->tnum->number[$choice];
            $index  = rand(1, count($number)-1); // 영은 빼고
            if (in_array($index, $arabia)) continue;
            if (rand(0, 3) < 3) { // 아라비아 숫자도 들어가도록 한다.
                array_push($select, $number[$index]);
            } else {
                array_push($select, $index);
            }
            array_push($arabia, $index);
            if ($index > $high) {
                $high = $index;
            }
            if ($index < $low) {
                $low = $index;
            }
        }

        $this->select = $select;
        $this->arabia = $arabia;
        $this->high = $high; // 배열중 가장 큰 값
        $this->low  = $low; // 배열중 가장 작은 값

        return $select;
    }

    // 숫자의 중간에 , 나 and 를 넣는다.
    function comma_question($question) {
        $str = "";
        $and = false;
        $comma = "";
        for ($qi=0; $qi<count($question)-1; $qi++) {
            $comma = ", ";
            if ($and == false) {
                if (rand(0,2) == 0) {
                    $comma = " {$this->tnum->and} ";
                    $and = true;
                }
            }

            //$unicode_array = utf8_to_unicode($question[$qi]);
            //array_walk($unicode_array, create_function('&$v,$k', '$v = "&#" . $v . ";";'));
            //print_r($unicode_array);
            //$unicode = implode("", $unicode_array);
            $str = $str . "<strong>" . $question[$qi] . "</strong>" . $comma;
        }
        return $str . "<strong>" . $question[$qi] . "</strong>";
    }

    // 가장 큰수나 가장 작은수의 질문을 만든다.
    function series_question($question, $highlow) {
        $question = $this->comma_question($question);
        $highlow_array = $this->tnum->$highlow;
        return sprintf($highlow_array[rand(0, count($highlow_array)-1)], $question);
    }

    // 몇번째 어떤수가 있는지의 질문을 만든다.
    function position_question($question) {
        $question = $this->comma_question($question);
        $position = rand(0, $this->count-1);
        $ordinal = $this->get_ordinal_value($position+1);
        $this->position = $this->arabia[$position]; // 몇번째 숫자는?의 답
        // 포지션 배열에 따라 인수의 위치가 다르다.
        if (rand(0,1) == 0) {
           $position_array = $this->tnum->position0;
            return sprintf($position_array[rand(0, count($position_array)-1)], $ordinal, $question);
        } else {
           $position_array = $this->tnum->position1;
            return sprintf($position_array[rand(0, count($position_array)-1)], $question, $ordinal);
        }
    }

    // 더하기 계산 문제
    function add_question($question) {
        $add_array = $this->tnum->add;
        $rand = rand(0, count($add_array)-1);
        $first_number  = $this->arabia[0];
        $second_number =  $this->arabia[1];
        $this->answer = $first_number + $second_number;
        return sprintf($add_array[rand(0, count($add_array)-1)], $question[0], $question[1] );
    }

    // a, b 변수값을 바꾼다.
    function swap(&$a, &$b)
    {
        $temp = $a;
        $a    = $b;
        $b    = $temp;
    }

    // 빼기 계산 문제
    function subtract_question($question) {
        $subtract_array = $this->tnum->subtract;
        $rand = rand(0, count($subtract_array)-1);
        $first_number  = $this->arabia[0];
        $second_number =  $this->arabia[1];
        if ($first_number < $second_number) {
            $this->swap($first_number, $second_number);
            $this->swap($question[0], $question[1]);
        }
        $this->answer = $first_number - $second_number;
        return sprintf($subtract_array[$rand], $question[0], $question[1] );
    }

    // 곱하기 계산 문제
    function multiply_question($question) {
        $multiply_array = $this->tnum->multiply;
        $rand = rand(0, count($multiply_array)-1);
        $first_number  = $this->arabia[0];
        $second_number =  $this->arabia[1];
        $this->answer = $first_number * $second_number;
        return sprintf($multiply_array[$rand], $question[0], $question[1] );
    }

    // 서수값을 반환
    function get_ordinal_value($index) {
        return $this->tnum->ordinal[$index];
    }

    // ajax 비교를 위한 코드 : 답을 저장해 놓는다.
    function set_session($answer) {
        $this->token = _token();
        set_session("ss_tcaptcha_token", $this->token);
        set_session("ss_tcaptcha_answer", $answer);
        set_session("ss_tcaptcha_error_count", 0);
    }

    function run() {
        global $text_number;
        $this->tnum = $text_number->{$this->language};
        $random_question = $this->random_question();
        switch (rand(0,5)) {
            case 0 :
                $question = $this->series_question( $random_question, 'high' );
                $this->set_session($this->high);
                break;
            case 1 :
                $question = $this->series_question( $random_question, 'low' );
                $this->set_session($this->low);
                break;
            case 2 :
                $question = $this->add_question( $random_question );
                $this->set_session($this->answer);
                break;
            case 3 :
                $question = $this->subtract_question( $random_question );
                $this->set_session($this->answer);
                break;
            case 4 :
                $question = $this->multiply_question( $random_question );
                $this->set_session($this->answer);
                break;
            default :
                $question = $this->position_question( $random_question );
                $this->set_session($this->position);
                break;
        }
        $this->question = $question;
        return $question;
    }
}


function html_unicode($unicode)
{
    return "&#".$unicode.";";
}


function utf8_to_unicode( $str )
{
    $unicode = array();
    $values = array();
    $lookingFor = 1;

    for ($i = 0; $i < strlen( $str ); $i++ ) {

        $thisValue = ord( $str[ $i ] );

        if ( $thisValue < 128 ) $unicode[] = $thisValue;
        else {

            if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;

            $values[] = $thisValue;

            if ( count( $values ) == $lookingFor ) {

                $number = ( $lookingFor == 3 ) ?
                    ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
                    ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );

                $unicode[] = $number;
                $values = array();
                $lookingFor = 1;

            } // if

        } // if

    } // for

    return $unicode;

}

function unicode_to_utf8($dec)
{
    $unicode_hex = dechex($dec);
    $unicode = hexdec($unicode_hex);

    $utf8 = "";

    if ($unicode < 128) {
        $utf8 = chr($unicode);
    } elseif ( $unicode < 2048 ) {
        $utf8 .= chr( 192 + ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
        $utf8 .= chr( 128 + ( $unicode % 64 ) );
    } else {
        $utf8 .= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
        $utf8 .= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
        $utf8 .= chr( 128 + ( $unicode % 64 ) );
    }
    return $utf8;
}


function chk_captcha()
{
    $token = get_session("ss_tcaptcha_token");
    if ($token && $token == $_POST['user_token']) {
        $answer = get_session("ss_tcaptcha_answer");
        if ($answer && $answer == $_POST['user_answer']) {
            return true;
        }
    }
    set_session("ss_tcaptcha_token", "");
    return false;
}


function chk_js_captcha() 
{
    return "if (!chk_tcaptcha(f.user_answer, f.user_token)) { return false; }\n";

}


function run_captcha($encoding='kr')
{
    $captcha = new tcaptcha($encoding);

    $str  = "<fieldset id=\"captcha\">\n";
    $str .= "<legend>자동등록방지</legend>\n";
    $str .= "<div><a href=\"javascript:;\" id=\"tcaptcha\">".$captcha->run()."</a></div>\n";
    $str .= "<span>답은 반드시 숫자로 입력하세요.</span>\n";
    $str .= "<input type=\"text\" id=\"user_answer\" name=\"user_answer\" title=\"자동등록방지 숫자\" size=\"10\" required=\"required\" />\n";
    $str .= "<input type=\"hidden\" id=\"user_token\" name=\"user_token\" value=\"{$captcha->token}\" />";
    $str .=  "</fieldset>\n";
    return $str;
}
?>