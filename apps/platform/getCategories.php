<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_POST['ks'];
$modify_perm = $_POST['modify'];

$partnerId = 0;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);

$filter = new KalturaCategoryFilter();
$filter->orderBy = '-createdAt';
$pager = new KalturaFilterPager();

// PAGING
if (isset($_POST['start']) && $_POST['length'] != '-1') {
    $pager->pageSize = intval($_POST['length']);
    $pager->pageIndex = floor(intval($_POST['start']) / $pager->pageSize) + 1;
}

// ORDERING
//$aColumns = array("status", "fullName", "id", "email", "roleIds", "lastLoginTime", "actions");
//if (isset($_POST['iSortCol_0'])) {
//    for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
//        if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
//            $filter->orderBy = ($_POST['sSortDir_' . $i] == 'asc' ? '+' : '-') . $aColumns[intval($_POST['iSortCol_' . $i])];
//            break; //Kaltura can do only order by single field currently
//        }
//    }
//}
//
// FILTERING
if (isset($_POST['search']) && $_POST['search'] != "") {
    $filter->freeText = $_POST['search'];
}

$result = $client->category->listAction($filter, $pager);

$output = array(
    "orderBy" => $filter->orderBy,
    "recordsTotal" => intval($result->totalCount),
    "recordsFiltered" => intval($result->totalCount),
    "data" => array(),
);


foreach ($result->objects as $data) {
    $unixtime_to_date = date('n/j/Y H:i', $data->createdAt);
    $newDatetime = strtotime($unixtime_to_date);
    $newDatetime = date('m/d/Y h:i A', $newDatetime);
    $delete_action = '';
    $move_action = '';
    $delete_arr = $data->id . '\',\'' . addslashes($data->name) . '\',' . $data->directSubCategoriesCount;

    if ($modify_perm) {
        $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhCat.deleteCat(\'' . $delete_arr . ');">Delete</a></li>';
        $move_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhCat.moveCat(\'' . $data->id . '\');">Move Category</a></li>';
    }

    $cat_arr = $data->id . '\',\'' . addslashes($data->name) . '\',\'' . addslashes($data->description) . '\',\'' . $data->tags . '\',\'' . $data->referenceId . '\'';

    $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu">
                            <li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhCat.editCat(\'' . $cat_arr . ');">Metadata</a></li>  
                            ' . $move_action . '                                        
                            ' . $delete_action . '
                        </ul>
                    </div>
                </span>';
    $row = array();
    $row[] = '<input type="checkbox" class="cat-bulk" name="cat_bulk" value="' . $data->id . '" />';
    $row[] = "<div class='data-break'>" . $data->id . "</div>";
    $row[] = "<div class='data-break'>" . addslashes($data->name) . "</div>";
    $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
    $row[] = "<div class='data-break'>" . $data->directSubCategoriesCount . "</div>";
    $row[] = "<div class='data-break'>" . $data->directEntriesCount . "</div>";
    $row[] = $actions;
    $output['data'][] = $row;
}

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);
?>
