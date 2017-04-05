<?php

error_reporting(0);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_GET['ks'];

$partnerId = 0;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);
$filter = new KalturaMediaEntryFilter();
$filter->orderBy = '-createdAt';
$filter->mediaTypeEqual = '2';
$filter->statusEqual = '2';
$pager = new KalturaFilterPager();

// PAGING
if (isset($_GET['start']) && $_GET['length'] != '-1') {
    $pager->pageSize = intval($_GET['length']);
    $pager->pageIndex = floor(intval($_GET['start']) / $pager->pageSize) + 1;
}

// FILTERING
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
    $filter->freeText = $_GET['sSearch'];
}

$filteredListResult = $client->media->listAction($filter, $pager);

$output = array(
    "recordsTotal" => intval($filteredListResult->totalCount),
    "recordsFiltered" => intval($filteredListResult->totalCount),
    "data" => array()
);

if (isset($_GET['draw'])) {
    $output["draw"] = intval($_GET['draw']);
}

foreach ($filteredListResult->objects as $entry) {
    $row = array();
    $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
    $newDatetime = strtotime($unixtime_to_date);
    $newDatetime = date('m/d/Y h:i A', $newDatetime);
    $row[] = "<input type='radio' class='logoimage' name='logoimage' style='width=33px' value='" . $entry->id . "' />";
    $row[] = "<div style='height: 100px;'><span class='helper'></span><img src='https://mediaplatform.streamingmediahosting.com/p/" . $entry->partnerId . "/thumbnail/entry_id/" . $entry->id . "/version/100000/bgcolor/F7F7F7/type/2/' style='width: 130px; max-height: 100px; vertical-align: middle;'/></div>";
    $row[] = '<div id="entry_id">' . $entry->id . '</div>';
    $row[] = '<div class="data-break">' . $entry->name . '</div>';
    $row[] = '<div class="data-break">' . $newDatetime . '</div>';
    $output['data'][] = $row;
}

echo json_encode($output);
?>
