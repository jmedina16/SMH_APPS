<?php //

error_reporting(1);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

$ks = $_POST['ks'];
$delete_perm = $_POST['delete'];
$modify_perm = $_POST['modify'];

$partnerId = $_POST['pid'];
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
$client = new KalturaClient($config);
$client->setKs($ks);
$filter = new KalturaUiConfFilter();
$filter->tagsMultiLikeOr = 'kdp3,player,playlist';
if ($partnerId !== '10585') {
    $filter->orderBy = '-createdAt';
}
$pager = new KalturaFilterPager();

// PAGING
if (isset($_POST['start']) && $_POST['length'] != '-1') {
    if ($_POST['length'] == '10' && $partnerId == '10561') {
        $pager->pageSize = 9;
    } else {
        $pager->pageSize = intval($_POST['length']);
    }
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
if (isset($_POST['search']) && $_POST['search'] != "") {
    $filter->nameLike = $_POST['search'];
}

$result = $client->uiConf->listAction($filter, $pager);

$output = array(
    "orderBy" => $filter->orderBy,
    "recordsTotal" => intval($result->totalCount),
    "recordsFiltered" => intval($result->totalCount),
    "data" => array(),
);


foreach ($result->objects as $entry) {
    $delete_action = '';
    $edit_action = '';
    $preview_action = '';
    $duplicate_action = '';

    if ($entry->confFile != null && $entry->confFile != '') {
        $xml_string = $entry->confFile;
        $name = $entry->name;
        $uiconf_id = $entry->id;
        $width = $entry->width;
        $height = $entry->height;
        $dimensions = $width . " x " . $height;
        $p_id = $entry->partnerId;

        $xml = simplexml_load_string($xml_string);
        if ($xml->xpath(sprintf('/layout[@isPlaylist="%s"]', 'true'))) {
            $player_type = 'Playlist';
        } else if ($xml->xpath(sprintf('/layout[@isPlaylist="%s"]', 'multi'))) {
            $player_type = 'Channel Playlist';
            $preview_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhPlayers.previewPlayer(' . $uiconf_id . ',\'' . addslashes($name) . '\');">Preview & Embed</a></li>';
        } else {
            $player_type = 'Player';
        }

        $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
        $newDatetime = strtotime($unixtime_to_date);
        $newDatetime = date('m/d/Y h:i A', $newDatetime);

//        $actions = "<a data-placement='top' data-original-title='Edit' rel='tooltip' onclick='smhPlayer.editPlayer(\"" . $uiconf_id . "\")'><img width='15px' src='/img/ppv_edit.png'></a> &nbsp;&nbsp;<a data-placement='top' data-original-title='Duplicate' rel='tooltip' onclick='smhPlayer.createDuplicate(\"" . $uiconf_id . "\")'><img width='15px' src='/img/duplicate.png'></a>";
//        if ($player_type == 'Channel Playlist') {
//            $actions .= " &nbsp;&nbsp;<a data-placement='top' data-original-title='Preview & Embed' rel='tooltip' onclick='smhPlayer.playlistPrev(\"" . $uiconf_id . "," . $p_id . "," . str_replace(",", "", addslashes($name)) . "\")'><img width='15px' src='/img/Embed-Icon.png'></a>";
//        }

        if ($modify_perm) {
            $edit_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhPlayers.editPlayer(' . $uiconf_id . ');">Player</a></li>';
            $duplicate_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhPlayers.duplicatePlayer(' . $entry->id . ');">Duplicate</a></li>';
        }
        if ($delete_perm) {
            $delete_arr = $entry->id . ',\'' . addslashes($entry->name) . '\'';
            $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhPlayers.deletePlayer(' . $delete_arr . ');">Delete</a></li>';
        }

        $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu"> 
                            ' . $edit_action . '  
                            ' . $preview_action . '
                            ' . $duplicate_action . '
                            ' . $delete_action . '
                        </ul>
                    </div>
                </span>';

        $row = array();
        $row[] = '<input type="checkbox" class="players-bulk" name="players_bulk" value="' . $entry->id . '" />';
        $row[] = $uiconf_id;
        $row[] = "<div class='data-break'>" . $name . "</div>";
        $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
        $row[] = $dimensions;
        $row[] = $player_type;
        $row[] = $actions;
        $output['data'][] = $row;
    }
}

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);
?>
