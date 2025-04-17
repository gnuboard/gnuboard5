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
    
    function get_bin_dirname()
    {
        global $config;
        
        $bin_path = ((int)$config['cf_cert_use'] === 2 && !$config['cf_cert_kcp_enckey']) ? 'bin_old' : 'bin';
        
        return $bin_path;
    }
    // hash 처리 영역
    function make_hash_data( $home_dir , $key , $str )
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli';
            else
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_x64';
        } else {
            $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_exe.exe';
        }
        
        if ($key) {
            $hash_data = $this -> mf_exec( $bin_exe ,
                                           "lf_CT_CLI__make_hash_data",
                                           $key,
                                           $str
                                         );
        } else {
            $hash_data = $this -> mf_exec( $bin_exe ,
                                           "lf_CT_CLI__make_hash_data",
                                           $str
                                         );
        }

        if ( $hash_data == "" ) { $hash_data = "HS01"; }

        return $hash_data;
    }

    // dn_hash 체크 함수
    function check_valid_hash ($home_dir , $key , $hash_data , $str )
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli';
            else
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_x64';
        } else {
            $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_exe.exe';
        }
        
        if ($key) {
            $ret_val = $this -> mf_exec( $bin_exe ,
                                         "lf_CT_CLI__check_valid_hash" ,
                                         $key,
                                         $hash_data ,
                                         $str
                                        );
        } else {
            $ret_val = $this -> mf_exec( $bin_exe ,
                                         "lf_CT_CLI__check_valid_hash" ,
                                         $hash_data ,
                                         $str
                                        );
        }

        if ( $ret_val == "" ) { $ret_val = "HS02"; }

        return $ret_val;
    }

    // 암호화 인증데이터 복호화
    function decrypt_enc_cert ( $home_dir, $key , $site_cd , $cert_no , $enc_cert_data , $opt)
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli';
            else
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_x64';
            
            if ($key) {
                $dec_data = $this -> mf_exec( $bin_exe ,
                                             "lf_CT_CLI__decrypt_enc_cert" ,
                                              $key,
                                              $site_cd ,
                                              $cert_no ,
                                              $enc_cert_data ,
                                              $opt
                                            );
            } else {
                $dec_data = $this -> mf_exec( $bin_exe ,
                                             "lf_CT_CLI__decrypt_enc_cert" ,
                                              $site_cd ,
                                              $cert_no ,
                                              $enc_cert_data ,
                                              $opt
                                            );
            }

        } else {
            $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_exe.exe';
            
            if ($key) {
                $dec_data = $this -> mf_exec( $bin_exe ,
                                             "lf_CT_CLI__decrypt_enc_cert" ,
                                              $key,
                                              $site_cd ,
                                              $cert_no ,
                                              $enc_cert_data
                                            );
            } else {
                $dec_data = $this -> mf_exec( $bin_exe ,
                                             "lf_CT_CLI__decrypt_enc_cert" ,
                                              $site_cd ,
                                              $cert_no ,
                                              $enc_cert_data
                                            );
            }
        }

        if ( $dec_data == "" ) { $dec_data = "HS03"; }


        parse_str( str_replace( chr( 31 ), "&", $dec_data ), $this->m_dec_data );
    }

    function get_kcp_lib_ver( $home_dir )
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if(PHP_INT_MAX == 2147483647) // 32-bit
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli';
            else
                $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_x64';
        } else {
            $bin_exe = $home_dir . '/'.$this->get_bin_dirname().'/ct_cli_exe.exe';
        }
        
        $ver_data = $this -> mf_exec( $bin_exe , 
                                       "lf_CT_CLI__get_kcp_lib_ver"
                                     );

        if ( $ver_data == "" ) { $ver_data = "HS04"; }
        
        return $ver_data;
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