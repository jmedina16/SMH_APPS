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
$filter = new KalturaPlaylistFilter();
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
// FILTERING
if (isset($_POST['search']) && $_POST['search'] != "") {
    $filter->freeText = $_POST['search'];
}

$result = $client->playlist->listAction($filter, $pager);

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
    $edit_action = '';
    $preview_action = '';

    if ($modify_perm) {
        $pt = "";
        if ($data->playlistType) {
            $pt = $data->playlistType;
        }
        $edit_arr = $data->id . '\',\'' . addslashes($data->name) . '\',\'' . addslashes($data->description) . '\',' . $pt;
        $edit_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhPlaylists.editPlaylist(\'' . $edit_arr . ');">Playlist</a></li>';
    }
    if ($delete_perm) {
        $delete_arr = $data->id . '\',\'' . addslashes($data->name);
        $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhPlaylists.deletePlaylist(\'' . $delete_arr . '\');">Delete</a></li>';
    }
    $preview_arr = $data->id . '\',\'' . addslashes($data->name);
    $preview_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhPlaylists.previewPlaylist(\'' . $preview_arr . '\');">Preview & Embed</a></li>';

    $playlistType = "";
    $manual_playlist = array();
    $video_count = 0;
    $thumbnails = '';
    if ($data->playlistType == '10') {
        $playlistType = 'Rule Based';
        $detailed = null;
        $playlistContext = null;
        $filter = null;
        $rb_result = $client->playlist->execute($data->id, $detailed, $playlistContext, $filter);
        $rb_plist_ids = array();
        foreach ($rb_result as $entry) {
            array_push($rb_plist_ids, $entry->id);
            $video_count++;
        }

        if (!$video_count) {
            $thumbnails .= '<div style="background-color: #ccc; width: 100%; height: 100%;"></div>';
        } else {
            $rb_plist_ids_final = array_slice($rb_plist_ids, 0, 5);
            $rb_plist_ids_final_count = count($rb_plist_ids_final);
            foreach ($rb_plist_ids_final as $id) {
                if ($rb_plist_ids_final_count == 1) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="100%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($rb_plist_ids_final_count == 2) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="50%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($rb_plist_ids_final_count == 3) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="33.33%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($rb_plist_ids_final_count == 4) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/1/width/300/height/90" width="25%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($rb_plist_ids_final_count == 5) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/1/width/300/height/90" width="20%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                }
            }
        }
    } else if ($data->playlistType == '3') {
        $playlistType = 'Manual';
        $manual_playlist = explode(',', $data->playlistContent);
        $video_count = count($manual_playlist);
        if (!$video_count) {
            $thumbnails .= '<div style="background-color: #ccc; width: 100%; height: 100%;"></div>';
        } else {
            $manual_playlist_final = array_slice($manual_playlist, 0, 5);
            $manual_playlist_final_count = count($manual_playlist_final);
            foreach ($manual_playlist_final as $id) {
                if ($manual_playlist_final_count == 1) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="100%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($manual_playlist_final_count == 2) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="50%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($manual_playlist_final_count == 3) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="33.33%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($manual_playlist_final_count == 4) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/1/width/300/height/90" width="25%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                } else if ($manual_playlist_final_count == 5) {
                    $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $data->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/1/width/300/height/90" width="20%" height="90" onmouseover="smhPlaylists.thumbRotatorStart(this)" onmouseout="smhPlaylists.thumbRotatorEnd(this)">';
                }
            }
        }
    }

    $disable = '';
    if ($data->id == 2) {
        $disable = 'disabled';
        $btn_style = 'btn-disabled';
    } else {
        $btn_style = 'btn-default';
    }
    $actions = '<span class="dropdown header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                        <button class="btn ' . $btn_style . ' dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true" ' . $disable . '><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu"> 
                            ' . $edit_action . '  
                            ' . $preview_action . '
                            ' . $delete_action . '
                        </ul>
                    </div>
                </span>';
    $playlist = '<div class="playlist-wrapper">
        <div class="play-wrapper">
            <a onclick="smhPlaylists.previewPlaylist(\'' . $preview_arr . '\');">
                <i style="top: 18px;" class="play-button"></i></div>
                <div class="thumbnail-holder">' . $thumbnails . '</div>
                <div class="videos-num">' . $video_count . ' Videos</div>
            </a>
        </div>';
    $row = array();
    $row[] = '<input type="checkbox" class="playlist-bulk" name="playlist_bulk" value="' . $data->id . '" />';
    $row[] = $playlist;
    $row[] = "<div class='data-break'>" . addslashes($data->name) . "</div>";
    $row[] = "<div class='data-break'>" . $data->id . "</div>";
    $row[] = "<div class='data-break'>" . $playlistType . "</div>";
    $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
    $row[] = $actions;
    $output['data'][] = $row;
}

if (isset($_POST['draw'])) {
    $output["draw"] = intval($_POST['draw']);
}
echo json_encode($output);
?>
