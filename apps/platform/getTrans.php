<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_POST['ks'];
$delete_perm = $_POST['delete'];
$edit_perm = $_POST['modify'];

$partnerId = 0;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);

$client->startMultiRequest();

$filter = null;
$pager = null;
$client->flavorParams->listAction($filter, $pager);

$filter = new KalturaConversionProfileFilter();
$filter->typeEqual = KalturaConversionProfileType::MEDIA;
$filter->orderBy = '+createdAt';
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


$client->conversionProfile->listAction($filter, $pager);

$result = $client->doMultiRequest();

$output = array(
    "orderBy" => $filter->orderBy,
    "recordsTotal" => intval($result[1]->totalCount),
    "recordsFiltered" => intval($result[1]->totalCount),
    "data" => array()
);


foreach ($result[1]->objects as $data) {
    $unixtime_to_date = date('n/j/Y H:i', $data->createdAt);
    $newDatetime = strtotime($unixtime_to_date);
    $newDatetime = date('m/d/Y h:i A', $newDatetime);

    $transid = ($data->isDefault) ? -1 : $data->id;
    $isDeafult = ($data->isDefault) ? '<i class="fa fa-check-square-o" style="color: #676a6c; width: 100%; text-align: center;"></i>' : '';
    $delete_action = '';
    $default_action = '';
    $delete_arr = $data->id . '\',\'' . addslashes($data->name);
    if ($delete_perm && $data->isDefault != 1)
        $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhTrans.deleteTrans(\'' . $delete_arr . '\');">Delete</a></li>';
    if ($edit_perm && $data->isDefault != 1)
        $default_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhTrans.setDefault(\'' . $delete_arr . '\');">Set as Default</a></li>';

    $isDefault = ($data->isDefault == 0) ? 'false' : 'true';
    $trans_arr = $data->id . '\',\'' . addslashes($data->name) . '\',\'' . addslashes($data->description) . '\',\'' . $data->flavorParamsIds . '\'' . ',' . $isDefault;

    $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu">
                            <li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhTrans.editTrans(\'' . $trans_arr . ');">Profile</a></li>                                        
                            ' . $default_action . '                                        
                            ' . $delete_action . '
                        </ul>
                    </div>
                </span>';

    $flavors = '';
    if ($data->flavorParamsIds) {
        $flavors_exp = explode(",", $data->flavorParamsIds);
        $flavors_arr = array();
        foreach ($result[0]->objects as $flavor) {
            if (in_array($flavor->id, $flavors_exp)) {
                array_push($flavors_arr, $flavor->name);
            }
        }
        $flavors = implode(", ", $flavors_arr);
    }

    $row = array();
    $row[] = '<input type="checkbox" class="trans-delete" name="trans_delete" style="width=33px" value="' . $transid . '" />';
    $row[] = "<div class='data-break'>" . $data->id . "</div>";
    $row[] = "<div class='data-break'>" . addslashes($data->name) . "</div>";
    $row[] = "<div class='data-break'>" . addslashes($data->description) . "</div>";
    $row[] = "<div class='data-break'>" . $flavors . "</div>";
    $row[] = "<div class='data-break'>" . $isDeafult . "</div>";
    $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
    $row[] = $actions;
    $output['data'][] = $row;
}

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);
?>
