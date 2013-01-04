<?php
include_once("./_common.php");

// prepare an array of wavfiles
$lc ='/home/tmp/g4s/plugin/captcha/wavs/';
$wav = $lc.'captcha.wav';

$fields = join('/',array( 'H8ChunkID', 'VChunkSize', 'H8Format',
                          'H8Subchunk1ID', 'VSubchunk1Size',
                          'vAudioFormat', 'vNumChannels', 'VSampleRate',
                          'VByteRate', 'vBlockAlign', 'vBitsPerSample' ));

$fp     = fopen($wav,'rb');
$header = fread($fp,36);
$info   = unpack($fields,$header);
print_r2($info);
?>