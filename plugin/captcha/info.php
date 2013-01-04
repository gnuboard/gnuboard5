<?php
include_once("./_common.php");

// prepare an array of wavfiles
$wavs_dir = $g4['path'].'/plugin/captcha/wavs/';
$wav = $wavs_dir.'0.wav';

$fields = join('/',array( 'H8ChunkID', 'VChunkSize', 'H8Format',
                          'H8Subchunk1ID', 'VSubchunk1Size',
                          'vAudioFormat', 'vNumChannels', 'VSampleRate',
                          'VByteRate', 'vBlockAlign', 'vBitsPerSample' ));

$fp     = fopen($wav,'rb');
$header = fread($fp,36);
$info   = unpack($fields,$header);
print_r2($info);
?>