<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

class livestreams {

    protected $action;
    protected $ks;
    protected $start;
    protected $length;
    protected $draw;
    protected $ac;
    protected $category;
    protected $search;
    protected $pid;
    protected $delete_perm;
    protected $config_perm;
    protected $modify_perm;
    protected $ac_perm;
    protected $thumb_perm;
    protected $stats_perm;
    protected $_link;
    protected $sn;

    public function __construct() {
        $this->ks = $_POST["ks"];
        $this->start = $_POST["start"];
        $this->length = $_POST["length"];
        $this->draw = $_POST["draw"];
        $this->ac = $_POST["ac"];
        $this->category = $_POST["category"];
        $this->search = $_POST["search"];
        $this->pid = $_POST["pid"];
        $this->delete_perm = $_POST['delete'];
        $this->modify_perm = $_POST['modify'];
        $this->config_perm = $_POST['config_perm'];
        $this->ac_perm = $_POST['ac_perm'];
        $this->thumb_perm = $_POST['thumb_perm'];
        $this->stats_perm = $_POST['stats_perm'];
        $this->sn = $_POST['sn'];
        $this->_link = @mysql_connect("10.5.21.50", "root", "smh0nly") or die('Unable to establish a DB connection');
        mysql_set_charset('utf8');
        mysql_select_db("wowza_realtime_stats", $this->_link);
    }

    //run
    public function run() {
        $this->getTable();
    }

    public function getLiveStreams() {
        $streams = array();
        $query = "SELECT * FROM `livestreams` WHERE app_name LIKE '" . $this->pid . "-live'";
        $result = mysql_query($query, $this->_link) or die('Query failed: ' . mysql_error());
        while ($row = mysql_fetch_assoc($result)) {
            $streams[] = $row;
        }
        return $streams;
    }

    public function getEntryId($pid, $streamName) {
        $partner_name = $this->getUserInfo($pid);
        if ($partner_name != '') {
            $mbr_test = substr($streamName, -1);
            $entry = array();
            $smh_akey = substr(md5(strtolower($pid . '-live') . 'aq23df2h'), 0, 8);
            $query = "SELECT * FROM entry WHERE partner_id = '" . $pid . "' AND status = 2 AND custom_data LIKE '%" . $streamName . "?key=" . $smh_akey . "%';";
            $result = mysql_query($query, $this->_link2) or die('Query failed: ' . mysql_error());

            if (mysql_num_rows($result) == 0 && is_numeric($mbr_test)) {
                $streamName = substr($streamName, 0, -1);
                return $this->getEntryId($pid, $streamName);
            } else {
                while ($row = mysql_fetch_assoc($result)) {
                    $entry['entry_id'] = $row['id'];
                }
                return $entry;
            }
        }
    }

    public function getTable() {
        $smh_akey = substr(md5(strtolower($this->pid . '-live') . 'aq23df2h'), 0, 8);
        $streams = $this->getLiveStreams();
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaLiveStreamEntryFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '-1,-2,0,1,2,7,4';
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
        if (isset($this->ac) && $this->ac != "") {
            $filter->accessControlIdIn = $this->ac;
        }

        // FILTERING
        if (isset($this->search) && $this->search != "") {
            $filter->freeText = $this->search;
        }

        if (isset($this->category) && $this->category != "" && $this->category != undefined) {
            $filter->categoriesIdsMatchOr = $this->category;
        }

        $result = $client->liveStream->listAction($filter, $pager);

        $output = array(
            "orderBy" => $filter->orderBy,
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array(),
        );

        foreach ($result->objects as $entry) {
            $live_status = 'Off Air';
            $delete_action = '';
            $edit_action = '';
            $edit_config_action = '';
            $ac_action = '';
            $thumb_action = '';
            $social_action = '';
            $preview_action = '';
            $stats_action = '';
            $row = array();
            $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
            $newDatetime = strtotime($unixtime_to_date);
            $newDatetime = date('m/d/Y h:i A', $newDatetime);

            foreach ($streams as $stream) {
                $mbr_test = substr($stream['stream_name'], -1);
                $stream_name = $stream['stream_name'] . "?key=" . $smh_akey;

                if (strpos($entry->streamName, $stream_name) !== false) {
                    $live_status = '<i class="fa fa-circle" style="color:#FF0000; font-size: 11px;"></i> LIVE';
                }
                if (is_numeric($mbr_test)) {
                    $streamName = substr($stream['stream_name'], 0, -1);
                    $stream_name = $streamName . "?key=" . $smh_akey;
                    if (strpos($entry->streamName, $stream_name) !== false) {
                        $live_status = '<i class="fa fa-circle" style="color:#FF0000; font-size: 11px;"></i> LIVE';
                    }
                }
            }

            if ($this->delete_perm) {
                $delete_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES);
                $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhLS.deleteLiveStream(\'' . $delete_arr . '\');">Delete</a></li>';
            }

            if ($this->modify_perm) {
                $edit_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->tags), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->referenceId), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->categories), ENT_QUOTES) . '\',' . $entry->accessControlId;
                $edit_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.editMetadata(\'' . $edit_arr . ');">Metadata</a></li>';
            }

            $partnerData = json_decode($entry->partnerData);
            $platforms_status = '';
            $platforms_preview_embed = '';
            $youtube = false;
            $facebook = false;
            if ($this->sn == 1) {
                $platforms_status_arr = array();
                $platforms_preview_embed_arr = array();
                $platforms = $this->getPlatforms($partnerData);
                $platform_logos = array();
                if ($platforms['snConfig']) {
                    foreach ($platforms['platforms'] as $platform) {
                        if ($platform['platform'] == 'smh') {
                            if ($platform['status']) {
                                array_push($platforms_status_arr, "smh:1");
                                array_push($platforms_preview_embed_arr, "smh:1");
                                //$platform_logos .= "<div><img src='/img/smh_logo.png' width='80px'>";
                                array_push($platform_logos, "smh");
                            } else {
                                array_push($platforms_status_arr, "smh:0");
                                array_push($platforms_preview_embed_arr, "smh:0");
                            }
                        }
                        if ($platform['platform'] == 'facebook_live') {
                            if ($platform['status']) {
                                $facebook = true;
                                array_push($platforms_status_arr, "facebook:1");
                                array_push($platforms_preview_embed_arr, "facebook:1:" . $platform['liveId']);
                                array_push($platform_logos, "fb");
                                //$platform_logos .= "<div style='margin-top: 10px;'><img src='/img/facebook_logo.png' width='75px'></div>";
                            } else {
                                array_push($platforms_status_arr, "facebook:0");
                                array_push($platforms_preview_embed_arr, "facebook:0");
                            }
                        }
                        if ($platform['platform'] == 'youtube_live') {
                            if ($platform['status']) {
                                $youtube = true;
                                array_push($platforms_status_arr, "youtube:1");
                                array_push($platforms_preview_embed_arr, "youtube:1:" . $platform['broadcastId']);
                                array_push($platform_logos, "yt");
                                //$platform_logos .= "<div style='margin-top: 10px;'><img src='/img/youtube_logo.png' width='75px'></div>";
                            } else {
                                array_push($platforms_status_arr, "youtube:0");
                                array_push($platforms_preview_embed_arr, "youtube:0");
                            }
                        }
                    }
                    $platforms_status = implode(";", $platforms_status_arr);
                    $platforms_preview_embed = implode(";", $platforms_preview_embed_arr);
                } else {
                    //$platform_logos = "<div><img src='/img/smh_logo.png' width='80px'>";
                    array_push($platform_logos, "smh");
                }

                $social_arr = $entry->id . '\',\'' . $platforms_status;
                $social_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.editPlatformConfig(\'' . $social_arr . '\');">Social Media</a></li>';
            }

            $thumbnail_url = str_replace("http://mediaplatform.streamingmediahosting.com", "", $entry->thumbnailUrl);
            $preview_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . $platforms_preview_embed;
            $preview_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.previewEmbed(\'' . $preview_arr . '\');">Preview & Embed</a></li>';
            $livestream_thumbnail = '<div class="livestream-wrapper">
        <div class="play-wrapper">
            <a onclick="smhLS.previewEmbed(\'' . $preview_arr . '\');">
                <i style="top: 18px;" class="play-button"></i></div>
                <div class="thumbnail-holder"><img onerror="smhMain.imgError(this)" src="' . $thumbnail_url . '/quality/100/type/1/width/300/height/90" width="150" height="110"></div>
                <div class="status">' . $live_status . '</div>
            </a>
        </div>';

            if ($this->config_perm) {
                $bitrate_arr = array();
                foreach ($entry->bitrates as $bitrate) {
                    array_push($bitrate_arr, "$bitrate[1],$bitrate[2],$bitrate[0]");
                }
                $bitrates = implode(";", $bitrate_arr);
                $record_json = json_decode($entry->partnerData);
                $record_stream = $record_json->record[0]->enable;
                $record_duration = $record_json->record[0]->byDuration;
                $record_filesize = $record_json->record[0]->byFileSize;
                $sname = explode("?", $entry->streamName);
                $sname = $sname[0];
                $smh_key = substr(md5(strtolower($entry->partnerId . '-live') . 'aq23df2h'), 0, 8);
                $edit_config_arr = $entry->id . '\',\'' . $sname . '\',\'' . $smh_key . '\',\'' . htmlspecialchars(addslashes($entry->offlineMessage), ENT_QUOTES) . '\',\'' . $record_stream . '\',\'' . $record_duration . '\',\'' . $record_filesize . '\',\'' . $bitrates . '\',\'' . $platforms_status . '\'';
                $edit_config_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.editStreamConfig(\'' . $edit_config_arr . ');">Stream Configuration</a></li>';
            }

            if ($this->ac_perm) {
                $ac_arr = $entry->id . '\',' . $entry->accessControlId;
                $ac_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.editAC(\'' . $ac_arr . ');">Access Control</a></li>';
            }

            if ($this->thumb_perm) {
                $thumb_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.editThumbnail(\'' . $entry->id . '\');">Thumbnail</a></li>';
            }

            if ($this->stats_perm) {
                $stats_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhLS.viewStats(\'' . $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->tags), ENT_QUOTES) . '\',\'' . $newDatetime . '\');">Player Statistics</a></li>';
            }

            $actions = '<span class="dropdown header">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu"> 
                                            ' . $edit_action . '  
                                            ' . $edit_config_action . '
                                            ' . $ac_action . '
                                            ' . $thumb_action . '
                                            ' . $stats_action . '
                                            ' . $social_action . '
                                            ' . $preview_action . '
                                            ' . $delete_action . '
                                        </ul>
                                    </div>
                                </span>';

            $stats = '<div><i class="fa fa-play-circle" style="width: 20px;" data-placement="right" data-toggle="tooltip" data-delay=\'{"show":700, "hide":30}\' data-original-title="Plays"></i> ' . number_format($entry->plays) . '</div>
                <div><i class="fa fa-eye" style="width: 20px;" data-placement="right" data-toggle="tooltip" data-delay=\'{"show":700, "hide":30}\' data-original-title="Views"></i> ' . number_format($entry->views) . '</div>';

            $bitrate_arr = array();
            foreach ($entry->bitrates as $bitrate_data) {
                array_push($bitrate_arr, $bitrate_data[1] . "," . $bitrate_data[2] . "," . $bitrate_data[0]);
            }
            $bitrate = join(";", $bitrate_arr);

            $stream_record = 'false';
            foreach ($partnerData as $key => $value) {
                if ($key == 'record') {
                    foreach ($value as $key2 => $value2) {
                        $stream_record = ($value2->enable == "true") ? 'true' : 'false';
                    }
                }
            }

            $bulk_entries = $entry->id . ';' . str_replace(" ", "", $entry->tags) . ';' . $entry->categoriesIds;

            $sn = '';
            if ($youtube || $facebook) {
                $sn = $entry->streamName . "&entryId=" . $entry->id;
            } else {
                $sn = $entry->streamName;
            }


            $row[] = '<input type="checkbox" class="livestream-bulk" name="livestream_bulk" value="' . $bulk_entries . '" />';
            $row[] = $livestream_thumbnail;
            $row[] = "<div class='data-break'>" . $entry->name . "</div>";
            $row[] = "<div class='data-break'>" . $entry->id . "</div>";
            $row[] = "<div class='data-break'><a onclick='smhLS.viewSettings(\"" . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . "\",\"" . $bitrate . "\",\"" . $sn . "\");'>View Settings <i class='fa fa-external-link' style='width: 100%; text-align: center; display: inline; font-size: 12px;'></i></a></div>";
            $row[] = "<div class='data-break'>" . $newDatetime . "</div>";

            if ($this->sn == 1) {
                $platform_logos = implode(",", $platform_logos);
                $row[] = "<div class='data-break'><a onclick='smhLS.viewPlatforms(\"" . $platform_logos . "\");'>View Platforms <i class='fa fa-external-link' style='width: 100%; text-align: center; display: inline; font-size: 12px;'></i></a></div>";
            }

            $row[] = $stats;
            $row[] = $actions;
            $output['data'][] = $row;
        }

        if (isset($_POST['draw'])) {
            $output["draw"] = intval($_POST['draw']);
        }
        echo json_encode($output);
    }

    public function getPlatforms($json) {
        $result = array();
        $result['platforms'] = array();
        foreach ($json as $key => $value) {
            if ($key == 'snConfig') {
                $result['snConfig'] = true;
                foreach ($value as $platforms) {
                    if ($platforms->platform == "smh") {
                        $platform = array('platform' => 'smh', 'status' => $platforms->status);
                        array_push($result['platforms'], $platform);
                    }
                    if ($platforms->platform == "facebook_live") {
                        if ($platforms->status) {
                            $platform = array('platform' => 'facebook_live', 'status' => $platforms->status, 'liveId' => $platforms->liveId);
                            array_push($result['platforms'], $platform);
                        } else {
                            $platform = array('platform' => 'facebook_live', 'status' => $platforms->status);
                            array_push($result['platforms'], $platform);
                        }
                    }
                    if ($platforms->platform == "youtube_live") {
                        if ($platforms->status) {
                            $platform = array('platform' => 'youtube_live', 'status' => $platforms->status, 'broadcastId' => $platforms->broadcastId);
                            array_push($result['platforms'], $platform);
                        } else {
                            $platform = array('platform' => 'youtube_live', 'status' => $platforms->status);
                            array_push($result['platforms'], $platform);
                        }
                    }
                }
            }
        }
        return $result;
    }

}

$tables = new livestreams();
$tables->run();
?>