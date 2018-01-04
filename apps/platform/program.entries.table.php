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
    private $_link;
    protected $sn;

    public function __construct() {
        $this->action = $_POST["action"];
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
        $this->sn = $_POST['sn'];
        $this->_link = @mysqli_connect("127.0.0.1", "kaltura", "nUKFRl7bE9hShpV", "kaltura", 3307) or die('Unable to establish a DB connection');
    }

    //run
    public function run() {
        switch ($this->action) {
            case "get_program_content":
                $this->getEntriesTable();
                break;
            default:
                echo "Action not found!";
        }
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

    public function getEntriesTable() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaMediaEntryFilter();
        $filter->orderBy = '-createdAt';
        $filter->mediaTypeIn = '1,2,5,201,100,101';
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
            $st = "";
            $mediaType = "";
            $prevMedia = 'false';
            $live_stream = 'false';
            $image = 'false';
            $row = array();
            $time = $this->rectime($entry->duration);

            if ($entry->status == '6') {
                $st = "Blocked";
            } else if ($entry->status == '3') {
                $st = "Deleted";
                $prevMedia = 'true';
            } else if ($entry->status == '-1') {
                $st = 'Error';
                $prevMedia = 'true';
            } else if ($entry->status == '-2') {
                $st = 'Error Uploading';
                $prevMedia = 'true';
            } else if ($entry->status == '0') {
                $st = "Uploading";
                $prevMedia = 'true';
            } else if ($entry->status == '5') {
                $st = "Moderate";
            } else if ($entry->status == '7') {
                $st = "No Media";
            } else if ($entry->status == '4') {
                $st = "Pending";
            } else if ($entry->status == '1') {
                $st = "Converting";
                $prevMedia = 'true';
            } else if ($entry->status == '2') {
                $st = "Ready";
            }

            $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
            $newDatetime = strtotime($unixtime_to_date);
            $newDatetime = date('m/d/Y h:i A', $newDatetime);

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

            if ($entry->mediaType == '1') {
                $mediaType = 'Video';
            } else if ($entry->mediaType == '2') {
                $mediaType = 'Image';
                $image = 'true';
            } else if ($entry->mediaType == '201' || $entry->mediaType == '202' || $entry->mediaType == '203' || $entry->mediaType == '204' || $entry->mediaType == '100' || $entry->mediaType == '101') {
                $mediaType = 'Live Stream';
                $live_stream = 'true';
            } else if ($entry->mediaType == '5') {
                $mediaType = 'Audio';
            }

            $entry_name = stripslashes($entry->name);
            if (strlen($entry_name) > 44) {
                $entry_name = substr($entry_name, 0, 44) . "...";
            }

            $entry_container = "<div class='entry-wrapper' data-entryid=" . $entry->id . " data-duration=" . $duration_data . ">
        <div class='entry-thumbnail'>
        <img src='/p/" . $entry->partnerId . "/thumbnail/entry_id/" . $entry->id . "/quality/100/type/1/width/300/height/90' width='100' height='68'>
        </div>
         <div class='entry-details'>
            <div class='entry-name'>
                <div>" . $entry_name . "</div>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Entry ID:</span><span>" . $entry->id . "</span>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Created on:</span><span>" . $newDatetime . "</span>
            </div>
            <div class='entry-subdetails'>
                <span style='width: 85px; display: inline-block;'>Type:</span><span>" . $mediaType . " $duration</span>
            </div>
        </div>
        <div class='clear'></div>
        </div>";

            $row[] = "<input type='radio' class='program-entry' name='program_list' style='width=33px' value='" . $entry->id . ";" . $entry->name . "' />";
            $row[] = $entry_container;
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

}

$tables = new entries();
$tables->run();
?>