<?php
/* ====================================================================== */
/* =   PAGE : 인증 PHP 라이브러리 1.0.1                                 = */
/* = ------------------------------------------------------------------ = */
/* =   Copyright (c)  2012   KCP Inc.   All Rights Reserverd.           = */
/* ====================================================================== */

/* ====================================================================== */
/* =   인증 연동 CLASS                                                  = */
/* ====================================================================== */
class   C_CT_CLI
{
    // 변수 선언 부분
    var    $m_dec_data;

    // 변수 초기화 영역
    function mf_clear()
    {
        $this->m_dec_data="";
    }

    // hash 처리 영역
    function make_hash_data( $home_dir , $str )
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/bin/ct_cli';
            else
                $bin_exe = $home_dir . '/bin/ct_cli_x64';
        } else {
            $bin_exe = $home_dir . '/bin/ct_cli_exe.exe';
        }
        $hash_data = $this -> mf_exec( $bin_exe ,
                                       "lf_CT_CLI__make_hash_data",
                                       $str
                                     );

        if ( $hash_data == "" ) { $hash_data = "HS01"; }

        return $hash_data;
    }

    // dn_hash 체크 함수
    function check_valid_hash ($home_dir , $hash_data , $str )
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/bin/ct_cli';
            else
                $bin_exe = $home_dir . '/bin/ct_cli_x64';
        } else {
            $bin_exe = $home_dir . '/bin/ct_cli_exe.exe';
        }
        $ret_val = $this -> mf_exec( $bin_exe ,
                                     "lf_CT_CLI__check_valid_hash" ,
                                     $hash_data ,
                                     $str
                                    );

        if ( $ret_val == "" ) { $ret_val = "HS02"; }

        return $ret_val;
    }

    // 암호화 인증데이터 복호화
    function decrypt_enc_cert ( $home_dir, $site_cd , $cert_no , $enc_cert_data , $opt)
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/bin/ct_cli';
            else
                $bin_exe = $home_dir . '/bin/ct_cli_x64';

            $dec_data = $this -> mf_exec( $bin_exe ,
                                         "lf_CT_CLI__decrypt_enc_cert" ,
                                          $site_cd ,
                                          $cert_no ,
                                          $enc_cert_data ,
                                          $opt
                                        );

        } else {
            $bin_exe = $home_dir . '/bin/ct_cli_exe.exe';

            $dec_data = $this -> mf_exec( $bin_exe ,
                                         "lf_CT_CLI__decrypt_enc_cert" ,
                                          $site_cd ,
                                          $cert_no ,
                                          $enc_cert_data
                                        );
        }

        if ( $dec_data == "" ) { $dec_data = "HS03"; }


        parse_str( str_replace( chr( 31 ), "&", $dec_data ), $this->m_dec_data );
    }

    // 인증데이터 get data
    function mf_get_key_value( $name )
    {
        return  $this->m_dec_data[ $name ];
    }

    function  mf_exec()
    {
      $arg = func_get_args();

      if ( is_array( $arg[0] ) )  $arg = $arg[0];

      $exec_cmd = array_shift( $arg );
        
        foreach($arg as $k => $i) {
            // 일부서버의 경우 빈값일때 '' 결과가 넘어오지 않는 버그가 있다. kagla 150820
            //$exec_cmd .= " " . escapeshellarg( $i );
            $exec_cmd .= " " . ( escapeshellarg($i) ? escapeshellarg($i) : "''" );
        }

      $rt = exec( $exec_cmd );

      return  $rt;
    }
}