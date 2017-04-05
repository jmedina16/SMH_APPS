<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_POST['ks'];
$modify_perm = $_POST['modify'];
$delete_perm = $_POST['delete'];

$partnerId = 0;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);

$client->startMultiRequest();

$filter = null;
$pager = null;
$client->flavorParams->listAction($filter, $pager);

$filter = new KalturaAccessControlProfileFilter();
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


$client->accessControlProfile->listAction($filter, $pager);

$result = $client->doMultiRequest();

$output = array(
    "orderBy" => $filter->orderBy,
    "recordsTotal" => intval($result[1]->totalCount),
    "recordsFiltered" => intval($result[1]->totalCount),
    "data" => array()
);


foreach ($result[1]->objects as $data) {
    $domains = 'Allow All';
    $countries_codes = 'Allow All';
    $ips = 'Allow All';
    $flavors = 'Allow All';
    $advsec = 'None';
    $auth_token = -1;
    $preview_time = -1;
    $unixtime_to_date = date('n/j/Y H:i', $data->createdAt);
    $newDatetime = strtotime($unixtime_to_date);
    $newDatetime = date('m/d/Y h:i A', $newDatetime);
    $auth_block = '';
    if ($data->rules) {
        foreach ($data->rules as $item1) {
            if (count($item1->conditions) == 0) {
                foreach ($item1->actions as $action) {
                    $auth_block = ($action->isBlockedList == 0) ? 'Authorized: ' : 'Blocked: ';
                    $flavors_exp = explode(",", $action->flavorParamsIds);
                    $flavors_arr = array();
                    foreach ($result[0]->objects as $flavor) {
                        if (in_array($flavor->id, $flavors_exp)) {
                            array_push($flavors_arr, $flavor->name);
                        }
                    }
                    $flavors = $auth_block . implode(", ", $flavors_arr);
                }
            } else {
                foreach ($item1->conditions as $item2) {
                    if ($item2->type == 4) {
                        $domains_arr = array();
                        $auth_block = ($item2->not == 1) ? 'Authorized: ' : 'Blocked: ';
                        foreach ($item2->values as $item3) {
                            array_push($domains_arr, $item3->value);
                        }
                        $domains = $auth_block . implode(",", $domains_arr);
                    }
                    if ($item2->type == 2) {
                        $countries_code_arr = array();
                        $auth_block = ($item2->not == 1) ? 'Authorized: ' : 'Blocked: ';
                        foreach ($item2->values as $item3) {
                            array_push($countries_code_arr, $item3->value);
                        }
                        $countries_codes = $auth_block . implode(",", $countries_code_arr);
                    }
                    if ($item2->type == 3) {
                        $ips_arr = array();
                        $auth_block = ($item2->not == 1) ? 'Authorized: ' : 'Blocked: ';
                        foreach ($item2->values as $item3) {
                            array_push($ips_arr, $item3->value);
                        }
                        $ips = $auth_block . implode(",", $ips_arr);
                    }
                    if ($item2->type == 1) {
                        foreach ($item1->actions as $action) {
                            if ($action->type == 1) {
                                $auth_token = 1;
                                $advsec = 'Protected by authentication token';
                            } else {
                                $auth_token = 1;
                                if ($action->limit < 60) {
                                    $time = (int) gmdate("s", $action->limit);
                                    $time = ltrim($time, '0');
                                    $preview = $time . ' second';
                                    $preview_time = 's:' . $time;
                                } else {
                                    $time = (explode(":", ltrim(gmdate("i:s", $action->limit), 0)));
                                    if ($time[1] == 00) {
                                        $time_m = ltrim($time[0], '0');
                                        $preview = $time_m . ' minute';
                                        $preview_time = 'm:' . $time_m;
                                    } else {
                                        $time_m = ltrim($time[0], '0');
                                        $time_s = ltrim($time[1], '0');
                                        $preview = $time_m . ' minute, ' . $time_s . ' second';
                                        $preview_time = 'm:' . $time_m . ';' . 's:' . $time_s;
                                    }
                                }
                                $advsec = 'Protected by authentication token<br> with a ' . $preview . ' free preview';
                            }
                        }
                    }
                }
            }
        }
    }

    $acid = ($data->isDefault) ? -1 : $data->id;
    $isDeafult = ($data->isDefault) ? '<i class="fa fa-check-square-o" style="color: #676a6c; width: 100%; text-align: center;"></i>' : '';
    $delete_action = '';
    $$edit_action = '';
    $default_action = '';
    $delete_arr = $data->id . '\',\'' . addslashes($data->name);
    if ($delete_perm && $data->isDefault != 1)
        $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhAC.deleteAC(\'' . $delete_arr . '\');">Delete</a></li>';

    $isDefault = ($data->isDefault == 0) ? 'false' : 'true';
    $role_arr = $data->id . '\',\'' . addslashes($data->name) . '\',\'' . addslashes($data->description) . '\',\'' . $isDefault . '\',\'' . $domains . '\',\'' . $countries_codes . '\',\'' . $ips . '\',\'' . $flavors . '\',' . $auth_token . ',\'' . $preview_time . '\'';

    if ($modify_perm) {
        $edit_action = 'onclick="smhAC.editAC(\'' . $role_arr . ');"';
    }
    if ($modify_perm && $data->isDefault != 1) {
        $default_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhAC.setDefault(\'' . $delete_arr . '\');">Set as Default</a></li>';
    }

    $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu">
                            <li role="presentation"><a role="menuitem" tabindex="-1" ' . $edit_action . '>Profile</a></li>            
                            ' . $default_action . '
                            ' . $delete_action . '
                        </ul>
                    </div>
                </span>';

    $row = array();
    $row[] = '<input type="checkbox" class="ac-delete" name="ac_delete" value="' . $acid . '" />';
    $row[] = "<div class='data-break'>" . $data->id . "</div>";
    $row[] = "<div class='data-break'>" . addslashes($data->name) . "</div>";
    $row[] = "<div class='data-break'>" . addslashes($data->description) . "</div>";
    $row[] = "<div class='data-break'><a onclick='smhAC.viewRules(\"" . addslashes($data->name) . "\",\"" . $domains . "\",\"" . $countries_codes . "\",\"" . $ips . "\",\"" . $flavors . "\",\"" . $advsec . "\");'>View Rules <i class='fa fa-external-link' style='width: 100%; text-align: center; display: inline; font-size: 12px;'></i></a></div>";
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
