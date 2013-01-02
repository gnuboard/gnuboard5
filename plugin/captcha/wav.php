<?php
include_once("./_common.php");

// prepare an array of wavfiles
$lc ='/home/tmp/g4s/plugin/captcha/wavs/';
$number = (string)$_SESSION['ss_captcha_key'];
$wavs = array();
for($i=0;$i<strlen($number);$i++){
    $file = $lc.$number[$i].'.wav';
    //echo $file;
    if(!@file_exists($file)) {
        $file = $en.$code{$i}.'.wav';
    }
    $wavs[] = $file;
    for ($d=0;$d<rand(0,5);$d++) {
        $wavs[] = $lc.'delay'.rand(0,1).'.wav';
    }
}

//print_r($wavs); exit;

header('Content-type: audio/x-wav');
header('Content-Disposition: attachment;filename=captcha.wav');

echo joinwavs($wavs);

/**
 * Join multiple wav files
 *
 * All wave files need to have the same format and need to be uncompressed.
 * The headers of the last file will be used (with recalculated datasize
 * of course)
 *
 * @link http://ccrma.stanford.edu/CCRMA/Courses/422/projects/WaveFormat/
 * @link http://www.thescripts.com/forum/thread3770.html
 */
function joinwavs($wavs)
{
    $fields = join('/',array( 'H8ChunkID', 'VChunkSize', 'H8Format',
                              'H8Subchunk1ID', 'VSubchunk1Size',
                              'vAudioFormat', 'vNumChannels', 'VSampleRate',
                              'VByteRate', 'vBlockAlign', 'vBitsPerSample' ));

    $data = '';
    foreach($wavs as $wav){
        $fp     = fopen($wav,'rb');
        $header = fread($fp,36);
        $info   = unpack($fields,$header);

        // read optional extra stuff
        if($info['Subchunk1Size'] > 16){
            $header .= fread($fp,($info['Subchunk1Size']-16));
        }

        // read SubChunk2ID
        $header .= fread($fp,4);

        // read Subchunk2Size
        $size  = unpack('vsize',fread($fp, 4));
        $size  = $size['size'];

        // read data
        $data .= fread($fp,$size);
    }

    return $header.pack('V',strlen($data)).$data;
}

exit;
?>
