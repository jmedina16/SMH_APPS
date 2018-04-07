<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

class playlists {

    protected $action;
    protected $ks;
    protected $start;
    protected $length;
    protected $draw;
    protected $search;

    public function __construct() {
        $this->action = $_POST["action"];
        $this->ks = $_POST["ks"];
        $this->start = $_POST["start"];
        $this->length = $_POST["length"];
        $this->draw = $_POST["draw"];
        $this->search = $_POST["search"];
    }

    //run
    public function run() {
        switch ($this->action) {
            case "get_program_content":
                $this->getPlaylistsTable();
                break;
            default:
                echo "Action not found!";
        }
    }

    public function getPlaylistsTable() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaPlaylistFilter();
        $filter->orderBy = '-createdAt';
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
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
        //access control profiles
        // FILTERING
        if (isset($this->search) && $this->search != "") {
            $filter->freeText = $this->search;
        }

        $result = $client->playlist->listAction($filter, $pager);

        $output = array(
            "orderBy" => $filter->orderBy,
            "data" => array(),
        );

        $totalCount = 0;
        foreach ($result->objects as $entry) {
            $row = array();
            $duration = 0;
            $pthumb = '';
            if ($entry->playlistType == '3') {
                $totalCount++;
                $manual_playlist = explode(',', $entry->playlistContent);
                $video_count = count($manual_playlist);
                $thumbnails = '';
                if (!$video_count) {
                    $thumbnails .= '<div style="background-color: #ccc; width: 100%; height: 100%;"></div>';
                } else {
                    $manual_playlist_final = array_slice($manual_playlist, 0, 5);
                    $manual_playlist_final_count = count($manual_playlist_final);
                    $pthumb = $manual_playlist_final[0];
                    foreach ($manual_playlist_final as $id) {
                        if ($manual_playlist_final_count == 1) {
                            $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $entry->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="100%" height="68">';
                        } else if ($manual_playlist_final_count == 2) {
                            $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $entry->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="50%" height="68">';
                        } else if ($manual_playlist_final_count == 3) {
                            $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $entry->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/3/width/300/height/90" width="33.33%" height="68">';
                        } else if ($manual_playlist_final_count == 4) {
                            $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $entry->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/1/width/300/height/90" width="25%" height="68">';
                        } else if ($manual_playlist_final_count == 5) {
                            $thumbnails .= '<img onerror="smhMain.imgError(this)" src="/p/' . $entry->partnerId . '/thumbnail/entry_id/' . $id . '/quality/100/type/1/width/300/height/90" width="20%" height="68">';
                        }
                    }

                    foreach ($manual_playlist as $eid) {
                        $version = null;
                        $entry_result = $client->baseEntry->get($eid, $version);
                        $duration += (int) $entry_result->duration;
                    }
                }

                $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
                $newDatetime = strtotime($unixtime_to_date);
                $newDatetime = date('m/d/Y h:i A', $newDatetime);

                $entry_name = stripslashes($entry->name);
                if (strlen($entry_name) > 44) {
                    $entry_name = substr($entry_name, 0, 44) . "...";
                }

                $playlist = '<div class="cm-playlist-wrapper">
                    <div class="thumbnail-holder">' . $thumbnails . '</div>
                    <div class="videos-num">' . $video_count . ' Videos</div>
                </div>';

                $entry_container = "<div class='entry-wrapper' data-playlistid=" . $entry->id . " data-duration=" . $duration . ">" .
                        $playlist .
                        "<div class='entry-details'>
                    <div class='entry-name'>
                        <div>" . $entry_name . "</div>
                    </div>
                    <div class='entry-subdetails'>
                        <span style='width: 65px; display: inline-block;'>Playlist ID:</span><span>" . $entry->id . "</span>
                    </div>
                    <div class='entry-subdetails'>
                        <span style='width: 65px; display: inline-block;'>Created on:</span><span>" . $newDatetime . "</span>
                    </div>
                </div>
                <div class='clear'></div>
                </div>";

                $row[] = "<input type='checkbox' class='program-entry' name='program_list' style='width=33px' value='" . $entry->id . ";" . $entry->name . ";" . $duration . ";" . $pthumb . "' />";
                $row[] = $entry_container;
                $output['data'][] = $row;
            }
        }

        $output["recordsTotal"] = intval($totalCount);
        $output["recordsFiltered"] = intval($totalCount);

        if (isset($_POST['draw'])) {
            $output["draw"] = intval($_POST['draw']);
        }
        echo json_encode($output);
    }

}

$tables = new playlists();
$tables->run();
?>