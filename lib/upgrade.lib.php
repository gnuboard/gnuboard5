<?php
if (!defined('_GNUBOARD_')) exit;

class G5Update {
    private $g5_update;

    public $path = null;
    public $latest_version = null;
    public $target_version = null;
    public $now_version = null;
    
    // token값 입력 필요
    // token값이 없는 경우, 1시간에 60번의 데이터조회가 가능함
    private $token = null;
    
    private $url = "https://api.github.com";
    private $version_list = array();
    private $compare_list = array();
    public $patch = array();

    private $conn;
    private $port;
    private $connPath;

    public function __construct() {  }

    public function connect($hostname, $port, $username, $userPassword) {
        $this->port = $port;

        if($port == "ftp") {
            if(function_exists("ftp_connect")) {
                $this->conn = @ftp_connect($hostname, 21);
                if($this->conn == false) return false;

                $login = ftp_login($this->conn, $username, $userPassword);
                if($login == false) return false;

                ftp_pasv($this->conn, true);

                return true;
            }
        } else if($port == "sftp"){
            if(function_exists("ssh2_connect")) {
                if($this->conn != false) return true;
                $this->conn = @ssh2_connect($hostname, 22);

                if($this->conn == false) return false;
                if(!ssh2_auth_password($this->conn, $username, $userPassword)) return false;
    
                $this->connPath = @ssh2_sftp($this->conn);
                if(!$this->connPath) {
                    $this->conn = false;
                    $this->conPath = false;
                    
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    public function disconnect() {
        $this->port = $port;

        if($this->port == 'ftp') {
            ftp_close($this->conn);
            $this->connPath = null;
        } else if($this->port == 'sftp') {
            ssh2_disconnect($this->conn);
            $this->connPath = null;
        } else {
            return false;
        }

        return true;
    }

    public function getConn() {
        return $this->conn;
    }

    public function clearUpdatedir() {
        rm_rf(G5_DATA_PATH.'/update');
        mkdir(G5_DATA_PATH.'/update', G5_DIR_PERMISSION, true);
        @chmod(G5_DATA_PATH.'/update', G5_DIR_PERMISSION);
    }

    public function setNowVersion($now_version = null) {
        $this->now_version = $now_version;
    }

    public function setTargetVersion($target_version = null) {
        $this->target_version = $target_version;
    }

    public function getToken() {
        return $this->token;
    }

    public function getVersionList() {
        if(empty($this->version_list)) {
            $result = $this->getApiCurlResult('version');
            if($result == false) return false;

            foreach($result as $key => $var) {
                if(!isset($var->tag_name)) continue;
        
                $this->version_list[] = $var->tag_name;
            }
        }

        return $this->version_list;
    }

    public function getVersionModifyContentList() {
        $list = $this->getVersionList();
        if($list == false) return false;

        $version_content = array();
        foreach($list as $key => $var) {
            $result = $this->getVersionModifyContent($var);
            if($result == false) return false;

            $version_content[$var] = $result->body;
        }

        return $version_content;
    }

    public function getVersionModifyContent($tag = null) {
        if($tag == null) return false;
        $result = $this->getApiCurlResult('modify', $tag);
        if($result == false) return false;

        return $result;
    }

    public function writeUpdateFile($originPath, $changePath) {
        if($this->conn == false) return false;
        
        $exist = true;
        if(!file_exists($changePath)) {
            $exist = false;
            $content = "";
        } else {
            $fp = fopen($changePath, 'r');
            $content = @fread($fp, filesize($changePath));
            if($content == false) return false;
        }        

        if($this->port == 'ftp') {
            if(ftp_nlist($this->conn, dirname($originPath)) == false) {
                ftp_mkdir($this->conn, dirname($originPath));
            }
            
            $result = ftp_put($this->conn, $originPath, $changePath, FTP_BINARY);
            if($result == false) return false;
        } else if($this->port == 'sftp') {
            if($exist == false) {
                if(file_exists("ssh2.sftp://".intval($this->connPath).$originPath)) {
                    ssh2_sftp_unlink($this->connPath, $originPath);
                }
            } else {
                if(!file_exists("ssh2.sftp://".intval($this->connPath).$originPath)) {
                    if(!is_dir(dirname($originPath))) mkdir("ssh2.sftp://".intval($this->connPath).dirname($originPath));
                    $result = ssh2_exec($this->conn, "scp -rp ".$changePath.' '.$originPath);
                } else {
                    $result = file_put_contents("ssh2.sftp://".intval($this->connPath).$originPath, $content);
                }
                if($result == false) return false;
            }
        }

        return true;
    }

    public function downloadVersion($version = null) {
        if($version == null) return false;
        if($this->conn == false) return false;

        $this->clearUpdatedir();

        $save = G5_DATA_PATH."/update/gnuboard.zip";

        $zip = fopen($save, 'w+');
        if($zip == false) return false;

        $result = $this->getApiCurlResult('zip', $version);
        if($result == false) return false;

        $file_result = @fwrite($zip, $result);
        if($file_result == false) return false;

        exec('unzip '.$save.' -d '.G5_DATA_PATH.'/update/'.$version);
        exec('mv '.G5_DATA_PATH.'/update/'.$version.'/gnuboard-*/* '.G5_DATA_PATH.'/update/'.$version);
        exec('rm -rf '.G5_DATA_PATH.'/update/'.$version.'/gnuboard-*/');
        exec('rm -rf '.$save);
        
        return true;
    }

    public function checkSameVersionComparison($list = null) {
        if($this->now_version == null) return false;
        if($list == null) return false;

        $result = $this->downloadVersion($this->now_version);
        if($result == false) return false;

        $check = array();
        $check['type'] = 'Y';
        foreach($list as $key => $var) {
            $now_file_path = G5_PATH.'/'.$var;
            $release_file_path = G5_DATA_PATH.'/update/'.$this->now_version.'/'.$var;

            if(!file_exists($now_file_path)) continue;
            if(!file_exists($release_file_path)) continue;

            $now_content = file_get_contents($now_file_path, true);
            $release_content = file_get_contents($release_file_path, true);

            if($now_content !== $release_content) {
                $check['type'] = 'N';
                $check['item'][] = $var;
            }
        }

        return $check;
    }

    public function getLatestVersion() {
        if($this->latest_version == null) {
            $result = $this->getVersionList();
            
            if($result == false) return false;

            $this->latest_version = $result[0];
        }

        return $this->latest_version;
    }

    public function getVersionCompareList() {
        try {
            if($this->now_version == null || $this->target_version == null) throw new Exception("현재버전 및 목표버전이 설정되지 않았습니다.");
            if($this->now_version == $this->target_version) throw new Exception("동일버전으로는 업데이트가 불가능합니다.");

            $version_list = $this->getVersionList();
            if($version_list == false) throw new Exception("버전리스트를 가져오는데 실패했습니다.");

            // 숫자가 작을수록 상위버전
            $now_version_num = array_search($this->now_version, $version_list);
            $target_version_num = array_search($this->target_version, $version_list);

            if($now_version_num > $target_version_num) {
                $result = $this->getApiCurlResult("compare", $this->now_version, $this->target_version);
            } else {
                $result = $this->getApiCurlResult("compare", $this->target_version, $this->now_version);
            }
            
            if($result == false) throw new Exception("비교리스트확인 통신에 실패했습니다.");

            foreach($result->files as $key => $var) {
                $this->compare_list[] = $var->filename;
            }

            return $this->compare_list;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getApiCurlResult($option, $param1 = null, $param2 = null) {
        // if($this->token == null) return false;
        $url = "https://api.github.com";
        switch($option) {
            case "version": 
                $url .= "/repos/gnuboard/gnuboard5/releases";
                break;
            case "compare":
                if($param1 == null || $param2 == null) return false;
                $url .= "/repos/gnuboard/gnuboard5/compare/".$param1."...".$param2;
                break;
            case "zip":
                if($param1 == null) return false;
                $url .= "/repos/gnuboard/gnuboard5/zipball/".$param1;
                break;
            case "modify":
                if($param1 == null) return false;
                $url .= "/repos/gnuboard/gnuboard5/releases/tags/".$param1;
                break;
            default:
                $url = false;
                break;
        }

        if($url == false) return false;
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_USERAGENT => 'gnuboard',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 3600,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => array(
                // 'Authorization: token  ' . $this->token
            ),
        ));
    
        $cinfo = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($option == 'zip') {
            $response = curl_exec($curl);
        } else {
            $response = json_decode(curl_exec($curl));
        }

        if(curl_errno($curl)) {
            return false;
        }
    
        return $response;
    }
}