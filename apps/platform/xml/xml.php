<?php

$datarate = $_GET['datarate'];
$outputsize = $_GET['outputsize'];
$url = $_GET['url'];
$stream = $_GET['stream'];
$key = $_GET['key'];
$tri = $_GET['tri'];

if (!isset($_GET['record'])) {
    $record = '';
} else {
    $record = '&amp;record=' . $_GET['record'];
}

if (!isset($_GET['ByDuration'])) {
    $duration = '';
} else {
    $duration = '&amp;ByDuration=' . $_GET['ByDuration'];
}

if (!isset($_GET['ByFileSize'])) {
    $size = '';
} else {
    $size = '&amp;ByFileSize=' . $_GET['ByFileSize'];
}

if (!isset($_GET['format'])) {
    $format = '';
} else {
    $format = '&amp;format=' . $_GET['format'];
}

if (!isset($_GET['entryId'])) {
    $entryId = '';
} else {
    $entryId = '&amp;entryId=' . $_GET['entryId'];
}

$pid = $_GET['pid'];
$encode_format = $_GET['encodeFormat'];

$stream_name = "$stream?key=$key$record$duration$size$format$entryId";

$XML = new SimpleXMLElement("<flashmedialiveencoder_profile></flashmedialiveencoder_profile>");
$preset = $XML->addChild('preset');
$preset->addChild('name', 'Custom');
$capture = $XML->addChild('capture');
$video = $capture->addChild('video');

if ($tri == 'true') {
    $video->addChild('device', 'NewTek TriCaster Video');
}

$input_size = str_replace(";", "", $outputsize);
$input_size_arr = explode("x", $input_size);
$width = $input_size_arr[0];
$height = $input_size_arr[1];

$video->addChild('crossbar_input', '0');
$video->addChild('frame_rate', '25');
$size = $video->addChild('size');
$size->addChild('width', $width);
$size->addChild('height', $height);
$audio = $capture->addChild('audio');

if ($tri == 'true') {
    $audio->addChild('device', 'NewTek TriCaster Audio');
}

$audio->addChild('crossbar_input', '0');
$audio->addChild('sample_rate', '44100');
$audio->addChild('channels', '1');
$audio->addChild('input_volume', '75');
$encode = $XML->addChild('encode');
$video = $encode->addChild('video');
$video->addChild('format', $encode_format);
$video->addChild('datarate', $datarate);
$video->addChild('outputsize', $outputsize);
$advanced = $video->addChild('advanced');
$advanced->addChild('profile', 'Baseline');
$advanced->addChild('level', '3.0');
$advanced->addChild('keyframe_frequency', '2 Seconds');
$autoadjust = $video->addChild('autoadjust');
$autoadjust->addChild('enable', 'false');
$autoadjust->addChild('maxbuffersize', '1');
$dropframes = $autoadjust->addChild('dropframes');
$dropframes->addChild('enable', 'false');
$degradequality = $autoadjust->addChild('degradequality');
$degradequality->addChild('enable', 'false');
$degradequality->addChild('preservepfq', 'false');
$audio = $encode->addChild('audio');
$audio->addChild('format', 'AAC');
$audio->addChild('datarate', '128');
$output = $XML->addChild('output');
$rtmp = $output->addChild('rtmp');
$rtmp->addChild('url', $url);
$rtmp->addChild('stream', $stream_name);
$preview = $XML->addChild('preview');
$video = $preview->addChild('video');
$input = $video->addChild('input');
$input->addChild('zoom', '100%');
$output = $video->addChild('output');
$output->addChild('zoom', '100%');
$log = $XML->addChild('log');
$log->addChild('level', '100');
$data = $XML->asXML();

if (strpos($_SERVER['HTTP_USER_AGENT'], "Tricaster") !== false) {
    header('Content-Disposition: attachment; filename="tricaster.xml"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/xml');
    header('Pragma: public');
    header('Content-Length: ' . strlen($data));
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false) {
    header('Content-Disposition: attachment; filename="export.xml"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/xml');
    header('Pragma: public');
    header('Content-Length: ' . strlen($data));
} else {
    header('Content-Disposition: attachment; filename="export.xml"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/xml');
    header('Expires: 0');
    header('Pragma: no-cache');
    header('Content-Length: ' . strlen($data));
}
exit($data);
?>
