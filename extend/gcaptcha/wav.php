<?php
include_once("./_common.php");

// prepare an array of wavfiles
$wavs_dir = $g4['path'].'/plugin/captcha/wavs/';
$number = (string)$_SESSION['ss_captcha_key'];
$wavs = array();
for($i=0;$i<strlen($number);$i++){
    $file = $wavs_dir.$number[$i].'.wav';
    $wavs[] = $file;
}

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
    $info = array();
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

    return ''
        .pack('a4', 'RIFF')
        .pack('V', strlen($data) + 36)
        .pack('a4', 'WAVE')
        .pack('a4', 'fmt ')
        .pack('V', $info['Subchunk1Size'])  // 16
        .pack('v', $info['AudioFormat'])    // 1
        .pack('v', $info['NumChannels'])    // 1
        .pack('V', $info['SampleRate'])     // 8000
        .pack('V', $info['ByteRate'])       // 8000
        .pack('v', $info['BlockAlign'])     // 1
        .pack('v', $info['BitsPerSample'])  // 8
        .pack('a4', 'data')
        .pack('V', strlen($data))
        .$data;
}
?>
