<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

class plist {

    protected $ks;
    protected $start;
    protected $length;
    protected $draw;
    protected $search;

    public function __construct() {
        $this->ks = $_POST["ks"];
        $this->start = $_POST["start"];
        $this->length = $_POST["length"];
        $this->draw = $_POST["draw"];
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
        $filter = new KalturaPlaylistFilter();
        $filter->orderBy = '-createdAt';
        $pager = new KalturaFilterPager();

//        // PAGING
//        if (isset($this->start) && $this->length != '-1') {
//            $pager->pageSize = intval($this->length);
//            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
//        }

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
        if (isset($this->search) && $this->search != "") {
            $filter->freeText = $this->search;
        }

        $filteredListResult = $client->playlist->listAction($filter, $pager);

        $output = array(
            "orderBy" => $filter->orderBy,
            "data" => array(),
        );

        $totalCount = 0;
        foreach ($filteredListResult->objects as $entry) {
            $unixtime_to_date = date('n/j/Y H:i', $entry->createdAt);
            $newDatetime = strtotime($unixtime_to_date);
            $newDatetime = date('m/d/Y h:i A', $newDatetime);
            $row = array();
            if ($entry->playlistType == '3') {
                $totalCount++;
                $row[] = "<input type='radio' class='ppv-plist' name='ppv_plist' style='width=33px' value='" . $entry->id . ";" . $entry->playlistType . ";" . $entry->name . "' />";
                $row[] = "<div class='data-break'>" . $entry->name . "</div>";
                $row[] = "<div class='data-break'>" . $entry->id . "</div>";
                $row[] = "<div class='data-break'>" . $newDatetime . "</div>";
                $output['data'][] = $row;
            }
        }
        $output["recordsTotal"] = intval($totalCount);
        $output["recordsFiltered"] = intval($totalCount);

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }
        echo json_encode($output);
    }

}

$tables = new plist();
$tables->run();
?>