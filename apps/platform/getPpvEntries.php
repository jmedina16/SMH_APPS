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
    }

    //run
    public function run() {
        $this->getTable();
    }

    public function getTable() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaMediaEntryFilter();
        $filter->orderBy = '-createdAt';
        $filter->mediaTypeIn = '1,5,100';
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
            $row = array();
            $mediaType = '';

            $time = $this->rectime($entry->duration);

            if ($entry->mediaType == '1') {
                $mediaType = 'Video';
            } else if ($entry->mediaType == '2') {
                $mediaType = 'Image';
            } else if ($entry->mediaType == '5') {
                $mediaType = 'Audio';
            } else if ($entry->mediaType == '100') {
                $mediaType = 'Live Stream';
            }

            $row[] = "<input type='radio' class='ppv-entry' name='ppv_entry' style='width=33px' value='" . $entry->id . ";" . $entry->mediaType . ";" . $entry->name . "' />";
            $row[] = "<div class='data-break'>" . $entry->name . "</div>";
            $row[] = "<div class='data-break'>" . $entry->id . "</div>";
            $row[] = "<div class='data-break'>" . $mediaType . "</div>";
            $row[] = "<div class='data-break'>" . $time . "</div>";
            $output['data'][] = $row;
        }

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
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