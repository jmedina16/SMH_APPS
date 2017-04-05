<?php

//error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_POST['ks'];
$totalResults = $_POST['limit'];
$partnerId = 0;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);
$filter = array();
$filter[0] = new KalturaMediaEntryFilterForPlaylist();
$filter[0]->orderBy = $_POST['order'];
$filter[0]->mediaTypeIn = '1,2,5,201,100,101';
$filter[0]->statusIn = '-1,-2,0,1,2,7,4';
$filter[0]->isRoot = KalturaNullableBoolean::NULL_VALUE;

//mediaTypeIn
if (isset($_POST['mediaType']) && $_POST['mediaType'] != "") {
    $filter[0]->mediaTypeIn = $_POST['mediaType'];
}

//duration
if (isset($_POST['duration']) && $_POST['duration'] != "") {
    $filter[0]->durationTypeMatchOr = $_POST['duration'];
}

//original or clipped
if (isset($_POST['clipped']) && $_POST['clipped'] != "") {
    $filter[0]->isRoot = $_POST['clipped'];
}

//access control profiles
if (isset($_POST['ac']) && $_POST['ac'] != "") {
    $filter[0]->accessControlIdIn = $_POST['ac'];
}

//flavors
if (isset($_POST['flavors']) && $_POST['flavors'] != "") {
    $filter[0]->flavorParamsIdsMatchOr = $_POST['flavors'];
}

// FILTERING
if (isset($_POST['search']) && $_POST['search'] != "") {
    $filter[0]->freeText = $_POST['search'];
}

if (isset($_POST['category']) && $_POST['category'] != "" && $_POST['category'] != undefined) {
    $filter[0]->categoriesIdsMatchOr = $_POST['category'];
}

$detailed = null;
$result = $client->playlist->executeFromFilters($filter, $totalResults, $detailed);

$output = array(
    "orderBy" => $filter[0]->orderBy,
    "recordsTotal" => count($result),
    "recordsFiltered" => intval($result->totalCount),
    "data" => array(),
);

$total_duration = 0;
foreach ($result as $entry) {
    $st = "";
    $mediaType = "";
    $prevMedia = 'false';
    $live_stream = 'false';
    $image = 'false';
    $row = array();

//    if ($entry->mediaType == '1') {
//        $mediaType = '<img src="/img/video.jpg" width="14px" height="15px" alt="Video" />';
//    } else if ($entry->mediaType == '2') {
//        $mediaType = '<img src="/img/image.jpg" width="16px" height="16px" alt="Image" />';
//        $image = 'true';
//    } else if ($entry->mediaType == '201' || $entry->mediaType == '202' || $entry->mediaType == '203' || $entry->mediaType == '204' || $entry->mediaType == '100' || $entry->mediaType == '101') {
//        $mediaType = '<img src="/img/live_flash.jpg" width="16px" height="16px" alt="Live Flash" />';
//        $live_stream = 'true';
//    } else if ($entry->mediaType == '5') {
//        $mediaType = '<img src="/img/audio.png" width="16px" height="16px" alt="Audio" />';
//    } else {
//        $mediaType = $entry->mediaType;
//    }

    $time = rectime($entry->duration);
//    $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
//    $newDatetime = strtotime($unixtime_to_date);
//    $newDatetime = date('m/d/Y h:i A', $newDatetime);


    if ($entry->status == '6') {
        $st = "Blocked";
    } else if ($entry->status == '3') {
        $st = "Deleted";
        $prevMedia = 'true';
    } else if ($entry->status == '-1') {
        $st = 'Error';
        $prevMedia = 'true';
    } else if ($entry->status == '-2') {
        $st = 'Error Uploading';
        $prevMedia = 'true';
    } else if ($entry->status == '0') {
        $st = "Uploading";
        $prevMedia = 'true';
    } else if ($entry->status == '5') {
        $st = "Moderate";
    } else if ($entry->status == '7') {
        $st = "No Media";
    } else if ($entry->status == '4') {
        $st = "Pending";
    } else if ($entry->status == '1') {
        $st = "Converting";
        $prevMedia = 'true';
    } else if ($entry->status == '2') {
        $st = "Ready";
    }

    $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
    $newDatetime = strtotime($unixtime_to_date);
    $newDatetime = date('m/d/Y h:i A', $newDatetime);

    $duration = '';
    $duration_data = 0;
    if ($entry->mediaType == '1' || $entry->mediaType == '5') {
        $total_duration += $entry->duration;
        $duration_data = $entry->duration;
        if (strlen($time) == 5) {
            $duration = "<div class='videos-num'>" . $time . "</div>";
        } else if (strlen($time) == 8) {
            $duration = "<div class='videos-num-long'>" . $time . "</div>";
        }
    }

    if ($entry->mediaType == '1') {
        $mediaType = 'Video';
    } else if ($entry->mediaType == '2') {
        $mediaType = 'Image';
        $image = 'true';
    } else if ($entry->mediaType == '201' || $entry->mediaType == '202' || $entry->mediaType == '203' || $entry->mediaType == '204' || $entry->mediaType == '100' || $entry->mediaType == '101') {
        $mediaType = 'Live Stream';
        $live_stream = 'true';
    } else if ($entry->mediaType == '5') {
        $mediaType = 'Audio';
    }

    $entry_name = stripslashes($entry->name);
    if (strlen($entry_name) > 68) {
        $entry_name = substr($entry_name, 0, 68) . "...";
    }

    $entry_container = "<div class='entry-wrapper' data-entryid=" . $entry->id . " data-duration=" . $duration_data . ">
        <div class='entry-thumbnail'>
        <img src='/p/" . $entry->partnerId . "/thumbnail/entry_id/" . $entry->id . "/quality/100/type/1/width/300/height/90' width='115' height='85'>
        </div>
         <div class='entry-details'>
            <div class='entry-name'>
                <div>" . $entry_name . "</div>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Created on:</span><span>" . $newDatetime . "</span>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Entry ID:</span><span>" . $entry->id . "</span>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Plays:</span><span>" . number_format($entry->plays) . "</span>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Type:</span><span>" . $mediaType . " $duration</span>
            </div>
        </div>
        <div class='tools' onclick='smhPlaylists.removeDND(this);'>
            <i class='fa fa-trash-o'></i>
        </div>
        <div class='clear'></div>
        </div>";

//    $row[] = "<div id='data-name'><a id='" . $entry->id . "' href='#'>" . $entry->name . "</a></div>";
//    $row[] = $mediaType;
//    $row[] = $time;
//    $row[] = $st;
//    $row[] = $entry->duration;
    $row[] = $entry_container;
    $output['data'][] = $row;
}
$output['total_duration'] = rectime($total_duration);
$output['total_duration_raw'] = $total_duration;

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);

function rectime($secs) {
    $hr = floor($secs / 3600);
    $min = floor(($secs - ($hr * 3600)) / 60);
    $sec = $secs - ($hr * 3600) - ($min * 60);

    if ($hr < 10) {
        $hr = "0" . $hr;
    }
    if ($min < 10) {
        $min = "0" . $min;
    }
    if ($sec < 10) {
        $sec = "0" . $sec;
    }
    $hr_result = ($hr == "00") ? '' : $hr . ':';
    return $hr_result . $min . ':' . $sec;
}

?>
