<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

class entries {

    protected $action;
    protected $ks;
    protected $start;
    protected $length;
    protected $draw;
    protected $mediaType;
    protected $duration;
    protected $clipped;
    protected $flavors;
    protected $ac;
    protected $category;
    protected $search;
    protected $delete_perm;
    protected $config_perm;
    protected $modify_perm;
    protected $ac_perm;
    protected $thumb_perm;
    protected $stats_perm;
    protected $download_perm;
    protected $flavors_perm;
    protected $clip_perm;
    protected $captions_perm;
    private $_link;
    protected $sn;

    public function __construct() {
        $this->ks = $_POST["ks"];
        $this->start = $_POST["start"];
        $this->length = $_POST["length"];
        $this->draw = $_POST["draw"];
        $this->mediaType = $_POST["mediaType"];
        $this->duration = $_POST["duration"];
        $this->clipped = $_POST["clipped"];
        $this->flavors = $_POST["flavors"];
        $this->ac = $_POST["ac"];
        $this->category = $_POST["category"];
        $this->search = $_POST["search"];
        $this->delete_perm = $_POST['delete_perm'];
        $this->modify_perm = $_POST['modify_perm'];
        $this->config_perm = $_POST['config_perm'];
        $this->ac_perm = $_POST['ac_perm'];
        $this->thumb_perm = $_POST['thumb_perm'];
        $this->stats_perm = $_POST['stats_perm'];
        $this->download_perm = $_POST['download_perm'];
        $this->clip_perm = $_POST['clip_perm'];
        $this->captions_perm = $_POST['captions_perm'];
        $this->sn = $_POST['sn'];
        $this->_link = @mysqli_connect("127.0.0.1", "kaltura", "nUKFRl7bE9hShpV", "kaltura", 3307) or die('Unable to establish a DB connection');
    }

    //run
    public function run() {
        $this->getTable();
    }

    public function getImportUrl($pid, $entry_id) {
        $result_arr = array();
        $query1 = "SELECT * FROM `bulk_upload_result` WHERE partner_id = " . $pid . " AND object_id = '" . $entry_id . "'";
        $result = mysqli_query($this->_link, $query1) or die('Query failed: ' . mysqli_error());
        $result_array = mysqli_fetch_assoc($result);
        $xml = new SimpleXMLElement($result_array['row_data']);
        $url = $xml->contentAssets->content->urlContentResource->attributes()->url;
        $result_arr['url'] = $url;
        $query2 = "SELECT * FROM `flavor_asset` WHERE partner_id = " . $pid . " AND entry_id = '" . $entry_id . "'";
        $result = mysqli_query($this->_link, $query2) or die('Query failed: ' . mysqli_error());
        $result_array = mysqli_fetch_assoc($result);
        $result_arr['flavor_id'] = $result_array['id'];
        return $result_arr;
    }

    public function getTable() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaMediaEntryFilter();
        $filter->orderBy = '-createdAt';
        $filter->mediaTypeIn = '1,2,5';
        $filter->statusIn = '-1,-2,0,1,2,7,4';
        $filter->isRoot = KalturaNullableBoolean::NULL_VALUE;
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

        //mediaTypeIn
        if (isset($this->mediaType) && $this->mediaType != "") {
            $filter->mediaTypeIn = $this->mediaType;
        }

        //duration
        if (isset($this->duration) && $this->duration != "") {
            $filter->durationTypeMatchOr = $this->duration;
        }

        //original or clipped
        if (isset($this->clipped) && $this->clipped != "") {
            $filter->isRoot = $this->clipped;
        }

        //flavors
        if (isset($this->flavors) && $this->flavors != "") {
            $filter->flavorParamsIdsMatchOr = $this->flavors;
        }

        $result = $client->baseEntry->listAction($filter, $pager);

        $output = array(
            "orderBy" => $filter->orderBy,
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array(),
        );

        foreach ($result->objects as $entry) {
            $delete_action = '';
            $edit_action = '';
            $ac_action = '';
            $thumb_action = '';
            $preview_action = '';
            $stats_action = '';
            $download_action = '';
            $flavors_action = '';
            $clip_action = '';
            $trim_action = '';
            $captions_action = '';
            $chapters_action = '';
            $survey_action = '';
            $applications_action = '';
            $social_action = '';
            $re_import = '';
            $row = array();
            $status = '';
            $mediaType = '';
            $prevMedia = false;
            $image = false;
            $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
            $newDatetime = strtotime($unixtime_to_date);
            $newDatetime = date('m/d/Y h:i A', $newDatetime);

            if ($this->delete_perm) {
                $delete_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES);
                $delete_action = '<li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhContent.deleteEntry(\'' . $delete_arr . '\');">Delete</a></li>';
            }

            $thumbnail_url = str_replace("http://mediaplatform.streamingmediahosting.com", "", $entry->thumbnailUrl);

            $time = $this->rectime($entry->duration);
            $duration = '';
            $duration_data = 0;
            if ($entry->mediaType == '1' || $entry->mediaType == '5') {
                $duration_data = $entry->duration;
                if (strlen($time) == 5) {
                    $duration = "<div class='videos-num'>" . $time . "</div>";
                } else if (strlen($time) == 8) {
                    $duration = "<div class='videos-num-long'>" . $time . "</div>";
                }
            }

            //$entry->partnerData = '{"snConfig":[{"platform":"youtube","status":true,"upload_status":"uploading","videoId":"k7D-lQfihQo"},{"platform":"facebook","status":false}]}';
            $entry->partnerData = '{"snConfig":[{"platform":"facebook","status":true,"upload_status":"completed","videoId":"118359495445893"},{"platform":"youtube","status":true,"upload_status":"completed","videoId":"2quQuA8mDg8"}]}';
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
                $upload_status = '';
                if ($platforms['snConfig']) {
                    foreach ($platforms['platforms'] as $platform) {
                        if ($platform['platform'] == 'facebook') {
                            if (isset($platform['upload_status'])) {
                                if ($platform['upload_status'] == 'uploading') {
                                    $upload_status = 1;
                                } else if ($platform['upload_status'] == 'completed') {
                                    $upload_status = 2;
                                } else {
                                    $upload_status = 3;
                                }
                            } else {
                                $upload_status = 0;
                            }

                            if ($platform['status']) {
                                $facebook = true;
                                array_push($platforms_status_arr, "facebook:1:" . $upload_status);
                                array_push($platforms_preview_embed_arr, "facebook:1:" . $platform['videoId']);
                                array_push($platform_logos, "fb");
                            } else {
                                array_push($platforms_status_arr, "facebook:0:" . $upload_status);
                                array_push($platforms_preview_embed_arr, "facebook:0");
                            }
                        }
                        if ($platform['platform'] == 'youtube') {
                            if (isset($platform['upload_status'])) {
                                if ($platform['upload_status'] === 'uploading') {
                                    $upload_status = 1;
                                } else if ($platform['upload_status'] === 'completed') {
                                    $upload_status = 2;
                                } else {
                                    $upload_status = 3;
                                }
                            } else {
                                $upload_status = 0;
                            }
                            if ($platform['status']) {
                                $youtube = true;
                                array_push($platforms_status_arr, "youtube:1:" . $upload_status);
                                array_push($platforms_preview_embed_arr, "youtube:1:" . $platform['videoId']);
                                array_push($platform_logos, "yt");
                            } else {
                                array_push($platforms_status_arr, "youtube:0:" . $upload_status);
                                array_push($platforms_preview_embed_arr, "youtube:0");
                            }
                        }
                    }
                    $platforms_status = implode(";", $platforms_status_arr);
                    $platforms_preview_embed = implode(";", $platforms_preview_embed_arr);
                }

                if ($entry->mediaType == '1') {
                    $social_arr = $entry->id . '\',\'' . $platforms_status;
                    $social_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.editPlatformConfig(\'' . $social_arr . '\');">Social Media</a></li>';
                }
            }

            $preview_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . $platforms_preview_embed;

            $entry_thumbnail = '<div class="entries-wrapper">
        <div class="play-wrapper">
            <a onclick="smhContent.previewEmbed(\'' . $preview_arr . '\');">
                <i style="top: 18px;" class="play-button"></i></div>
                <div class="thumbnail-holder"><img onerror="smhMain.imgError(this)" src="' . $thumbnail_url . '/quality/100/type/1/width/300/height/90" width="150" height="110" onmouseover="smhContent.thumbRotatorStart(this)" onmouseout="smhContent.thumbRotatorEnd(this)"></div>
                ' . $duration . '
            </a>
        </div>';

            if ($entry->status == '6') {
                $status = "Blocked";
            } else if ($entry->status == '3') {
                $status = "Deleted";
            } else if ($entry->status == '-1') {
                $status = 'Error';
            } else if ($entry->status == '-2') {
                $status = 'Error Importing';
                $import_url = $this->getImportUrl($entry->partnerId, $entry->id);
                $re_import = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.reImport(\'' . $import_url['url'] . '\',\'' . $import_url['flavor_id'] . '\');">Retry Import</a></li>';
            } else if ($entry->status == '0') {
                $status = "Uploading";
            } else if ($entry->status == '5') {
                $status = "Moderate";
            } else if ($entry->status == '7') {
                $status = "No Media";
            } else if ($entry->status == '4') {
                $status = "Pending";
            } else if ($entry->status == '1') {
                $status = "Converting";
            } else if ($entry->status == '2') {
                $status = "Ready";
                $prevMedia = true;
            }

            if ($entry->mediaType == '1') {
                $mediaType = 'Video';
            } else if ($entry->mediaType == '2') {
                $mediaType = 'Image';
                $image = true;
            } else if ($entry->mediaType == '5') {
                $mediaType = 'Audio';
            }

            if ($prevMedia) {
                $preview_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . $platforms_preview_embed;
                $preview_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.previewEmbed(\'' . $preview_arr . '\');">Preview & Embed</a></li>';
            }

            if ($this->modify_perm) {
                $edit_arr = $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->tags), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->referenceId), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->categories), ENT_QUOTES) . '\',' . $entry->accessControlId;
                $edit_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.editMetadata(\'' . $edit_arr . ');">Metadata</a></li>';
                $chapters_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.chaptersPlugin(\'' . $entry->id . '\');">Chapters</a></li>';
                $survey_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.surveyPlugin(\'' . $entry->id . '\');">Survey</a></li>';
            }

            if ($this->ac_perm) {
                $ac_arr = $entry->id . '\',' . $entry->accessControlId;
                $ac_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.editAC(\'' . $ac_arr . ');">Access Control</a></li>';
            }

            if ($this->thumb_perm && !$image) {
                $thumb_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.editThumbnail(\'' . $entry->id . '\',\'' . $mediaType . '\');">Thumbnail</a></li>';
            }

            if ($this->stats_perm) {
                $time = $this->rectime($entry->duration);
                $stats_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.viewStats(\'' . $entry->id . '\',\'' . htmlspecialchars(addslashes($entry->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entry->tags), ENT_QUOTES) . '\',\'' . $time . '\',\'' . $newDatetime . '\');">Player Statistics</a></li>';
            }

            if ($this->download_perm) {
                $download_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="parent.location=&quot;/p/' . $entry->partnerId . '/sp/' . $entry->partnerId . '00/raw/entry_id/' . $entry->id . '/version/0&quot;">Download</a></li>';
            }

            if (!$image) {
                $flavors_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.editFlavors(\'' . $entry->id . '\');">Flavors</a></li>';
            }

            if ($this->clip_perm && !$image) {
                $clip_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.clipEntry(\'' . $entry->id . '\');">Clip</a></li>';
                $trim_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.trimEntry(\'' . $entry->id . '\');">Trim</a></li>';
            }

            if ($this->captions_perm && !$image) {
                $captions_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhContent.editCaptions(\'' . $entry->id . '\');">Captions</a></li>';
            }

            if (($this->clip_perm || $this->captions_perm || $this->modify_perm) && !$image) {
                $applications_action = '<li role="presentation" class="dropdown dropdown-submenu">
                                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-caret-left"></i> Applications</a>
                                                <ul class="dropdown-menu">
                                                    ' . $clip_action . '
                                                    ' . $trim_action . '                                                        
                                                    ' . $captions_action . '  
                                                    ' . $chapters_action . '  
                                                    ' . $survey_action . '    
                                                </ul>
                                            </li> ';
            }

            $actions = '<span class="dropdown header">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                                        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                                        <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu"> 
                                            ' . $re_import . '
                                            ' . $edit_action . '  
                                            ' . $ac_action . '
                                            ' . $thumb_action . '
                                            ' . $flavors_action . '      
                                            ' . $applications_action . '                                                 
                                            ' . $stats_action . '    
                                            ' . $social_action . ' 
                                            ' . $preview_action . '                                                   
                                            ' . $download_action . '
                                            ' . $delete_action . '
                                        </ul>
                                    </div>
                                </span>';

            $stats = '<div><i class="fa fa-play-circle" style="width: 20px;" data-placement="right" data-toggle="tooltip" data-delay=\'{"show":700, "hide":30}\' data-original-title="Plays"></i> ' . number_format($entry->plays) . '</div>
                <div><i class="fa fa-eye" style="width: 20px;" data-placement="right" data-toggle="tooltip" data-delay=\'{"show":700, "hide":30}\' data-original-title="Views"></i> ' . number_format($entry->views) . '</div>
                <div><i class="fa fa-star" style="width: 20px;" data-placement="right" data-toggle="tooltip" data-delay=\'{"show":700, "hide":30}\' data-original-title="Rank"></i> ' . $entry->rank . ' / 5</div>';

            $bulk_entries = $entry->id . ';' . str_replace(" ", "", $entry->tags) . ';' . $entry->categoriesIds;

            $row[] = '<input type="checkbox" class="entries-bulk" name="entries_bulk" value="' . $bulk_entries . '" />';
            $row[] = $entry_thumbnail;
            $row[] = "<div class='data-break'>" . $entry->name . "</div>";
            $row[] = "<div class='data-break'>" . $entry->id . "</div>";
            $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
            $row[] = "<div class='data-break'>" . $mediaType . "</div>";
            $row[] = $stats;
            $row[] = "<div class='data-break'>" . $status . "</div>";
            $row[] = $actions;
            $output['data'][] = $row;
        }

        if (isset($_POST['draw'])) {
            $output["draw"] = intval($_POST['draw']);
        }
        echo json_encode($output);
    }

    public function rectime($secs) {
        $hr = floor($secs / 3600);
        $min = floor(($secs - ($hr * 3600)) / 60);
        $sec = $secs - ($hr * 3600) - ($min * 60);

        if ($hr < 10) {
            $hr = "0" . $hr;
        }
        if ($min < 10) {
            $min = "0" . $min;
        }
        if ($sec < 10) {
            $sec = "0" . $sec;
        }
        $hr_result = ($hr == "00") ? '' : $hr . ':';
        return $hr_result . $min . ':' . $sec;
    }

    public function getPlatforms($json) {
        $result = array();
        $result['platforms'] = array();
        foreach ($json as $key => $value) {
            if ($key == 'snConfig') {
                $result['snConfig'] = true;
                foreach ($value as $platforms) {
                    if ($platforms->platform == "facebook") {
                        if ($platforms->status) {
                            $platform = array('platform' => 'facebook', 'status' => $platforms->status, 'upload_status' => $platforms->upload_status, 'videoId' => $platforms->videoId);
                            array_push($result['platforms'], $platform);
                        } else {
                            $platform = array('platform' => 'facebook', 'status' => $platforms->status);
                            array_push($result['platforms'], $platform);
                        }
                    }
                    if ($platforms->platform == "youtube") {
                        if ($platforms->status) {
                            $platform = array('platform' => 'youtube', 'status' => $platforms->status, 'upload_status' => $platforms->upload_status, 'videoId' => $platforms->videoId);
                            array_push($result['platforms'], $platform);
                        } else {
                            $platform = array('platform' => 'youtube', 'status' => $platforms->status);
                            array_push($result['platforms'], $platform);
                        }
                    }
                }
            }
        }
        return $result;
    }

}

$tables = new entries();
$tables->run();
?>