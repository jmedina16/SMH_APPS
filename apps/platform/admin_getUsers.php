<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_POST['ks'];
$delete_perm = $_POST['delete'];
$modify_perm = $_POST['modify'];

$partnerId = 0;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);

$client->startMultiRequest();

$client->partner->getinfo();

$filter = new KalturaUserFilter();
$filter->orderBy = '+createdAt';
$filter->statusIn = '1,0';
$filter->isAdminEqual = KalturaNullableBoolean::TRUE_VALUE;
$filter->loginEnabledEqual = KalturaNullableBoolean::TRUE_VALUE;
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
// FILTERING
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
    $filter->firstNameOrLastNameStartsWith = $_POST['sSearch'];
}

$client->user->listAction($filter, $pager);

$result = $client->doMultiRequest();

$myData = $result[1]->objects;
$you = $_POST['id'];
$account_limit = $result[0]->adminLoginUsersQuota;
$i = 0;

$output = array(
    "orderBy" => $filter->orderBy,
    "recordsTotal" => intval($result[1]->totalCount),
    "recordsFiltered" => intval($result[1]->totalCount),
    "data" => array(),
    "usersInUse" => 0,
    "usersAvail" => 0
);


foreach ($myData as $data) {
    $owner_arr = '';
    $block_arr = '';
    $delete_action = '';
    $block_action = '';
    $oy = '';
    if ($data->isAccountOwner && $you == $data->email) {
        $name = $data->fullName . " (You, Account Owner)";
        $oy = 'true';
    } else if ($data->isAccountOwner && $you != $data->email) {
        $name = $data->fullName . " (Account Owner)";
        $oy = 'true';
    } else if (!$data->isAccountOwner && $you == $data->email) {
        $name = $data->fullName . " (You)";
        $oy = 'true';
    } else {
        $name = $data->fullName;
        $oy = 'false';
        $block_arr = $data->id . '\',' . $data->status . ',\'' . addslashes($data->fullName);
        $delete_arr = $data->id . '\',\'' . addslashes($data->fullName);
        $block_text = ($data->status == 1) ? 'Block' : 'Unblock';
        if ($delete_perm)
            $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhAdmin.deleteUser(\'' . $delete_arr . '\');">Delete</a></li>';
        if ($modify_perm)
            $block_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhAdmin.editStatus(\'' . $block_arr . '\');">' . $block_text . '</a></li>';
    }

    $owner_arr = $data->email . '\',\'' . addslashes($data->firstName) . '\',\'' . addslashes($data->lastName) . '\',' . $data->roleIds . ',' . $oy;

    if ($data->lastLoginTime === null) {
        $unixtime_to_date = date('n/j/Y H:i', 1008276300);
        $newDatetime = strtotime($unixtime_to_date);
        $newDatetime = date('m/d/Y h:i A', $newDatetime);
    } else {
        $unixtime_to_date = date('n/j/Y H:i', $data->lastLoginTime);
        $newDatetime = strtotime($unixtime_to_date);
        $newDatetime = date('m/d/Y h:i A', $newDatetime);
    }

    $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu">
                        <li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhAdmin.editUser(\'' . $owner_arr . ');">User</a></li>
                        ' . $block_action . ' 
                        ' . $delete_action . '
                        </ul>
                    </div>
                 </span>';
    $row = array();
    $row[] = ($data->status == 1) ? '<div class="alert alert-success">Active</div>' : '<div class="alert alert-danger">Blocked</div>';
    $row[] = "<div class='data-break'>" . $name . "</div>";
    $row[] = "<div class='data-break'>" . $data->id . "</div>";
    $row[] = "<div class='data-break'>" . $data->email . "</div>";
    $row[] = "<div class='data-break'>" . $data->roleNames . "</div>";
    $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
    $row[] = $actions;
    $output['data'][] = $row;
    $i++;
}

$output['usersInUse'] = $i;

$available = $account_limit - $i;
$output['usersAvail'] = $available;

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);
?>
