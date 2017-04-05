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
$filter = new KalturaUserRoleFilter();
$filter->orderBy = '+createdAt';
$filter->tagsMultiLikeOr = 'kmc';
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
//if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
//    $filter->firstNameOrLastNameStartsWith = $_POST['sSearch'];
//}

$result = $client->userRole->listAction($filter, $pager);

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
    $duplicate_action = '';
    $delete_arr = $data->id . '\',\'' . addslashes($data->name);
    if ($delete_perm)
        $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhRoles.deleteRole(\'' . $delete_arr . '\');">Delete</a></li>';
    if ($modify_perm)
        $duplicate_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhRoles.duplicateRole(\'' . $data->id . '\');">Duplicate</a></li>';

    $role_arr = $data->id . '\',\'' . addslashes($data->name) . '\',\'' . addslashes($data->description) . '\',\'' . $data->permissionNames . '\'';

    $disable = '';
    if ($data->id == 2) {
        $disable = 'disabled';
        $btn_style = 'btn-disabled';
    } else {
        $btn_style = 'btn-default';
    }
    $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn ' . $btn_style . '" ' . $disable . '><span class="text">Edit</span></button>
                        <button class="btn ' . $btn_style . ' dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true" ' . $disable . '><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu">
                        <li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhRoles.editRole(\'' . $role_arr . ');">Role</a></li>  
                        ' . $duplicate_action . '                                        
                        ' . $delete_action . '
                        </ul>
                    </div>
                </span>';
    $row = array();
    $row[] = "<div class='data-break'>" . addslashes($data->name) . "</div>";
    $row[] = "<div class='data-break'>" . addslashes($data->description) . "</div>";
    $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
    $row[] = $actions;
    $output['data'][] = $row;
}

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);
?>
