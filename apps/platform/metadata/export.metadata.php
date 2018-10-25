<?php

error_reporting(1);
date_default_timezone_set('America/Los_Angeles');
require_once('../../../app/clients/php5/KalturaClient.php');
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

class export {

    protected $pid;
    protected $ks;
    protected $action;
    protected $cdn;
    protected $page_size;

    public function __construct() {
        $this->pid = $_GET['pid'];
        $this->ks = $_GET['ks'];
        $this->action = $_GET['action'];
        $this->page_size = $_GET['page_size'];
    }

    //run ppv api
    public function run() {
        switch ($this->action) {
            case "export_live_metadata":
                $this->export_live();
                break;
            case "export_cat_metadata":
                $this->export_cat();
                break;
            case "export_plist_metadata":
                $this->export_plist();
                break;
            case "export_players_metadata":
                $this->export_players();
                break;
            case "export_ac_metadata":
                $this->export_ac();
                break;
            case "export_trans_metadata":
                $this->export_trans();
                break;
            case "export_reseller_metadata":
                $this->export_reseller();
                break;
            case "export_users_metadata":
                $this->export_users();
                break;
            case "export_roles_metadata":
                $this->export_roles();
                break;
            case "export_entries_metadata":
                $this->export_entries();
                break;
            case "export_ppv_user_metadata":
                $this->export_ppv_users();
                break;
            case "export_ppv_tickets_metadata":
                $this->export_ppv_tickets();
                break;
            case "export_ppv_content_metadata":
                $this->export_ppv_content();
                break;
            case "export_ppv_orders_metadata":
                $this->export_ppv_orders();
                break;
            case "export_ppv_subs_metadata":
                $this->export_ppv_subs();
                break;
            case "export_ppv_affiliates_metadata":
                $this->export_ppv_affiliates();
                break;
            case "export_ppv_campaigns_metadata":
                $this->export_ppv_campaigns();
                break;
            case "export_ppv_marketing_metadata":
                $this->export_ppv_marketing();
                break;
            case "export_ppv_commissions_metadata":
                $this->export_ppv_commissions();
                break;
            case "export_mem_user_metadata":
                $this->export_mem_users();
                break;
            case "export_mem_content_metadata":
                $this->export_mem_content();
                break;
            default:
                echo "Action not found!";
        }
    }

    public function getCDN($pid) {
        $url = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getCDN.php?action=get_cdn&pid=' . $pid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output);
    }

    public function getEdgeCastURL($baseUrl) {
        return str_replace("cvip.smhcdn.com", "fml.19BC0.taucdn.net/2019BC0", $baseUrl);
    }

    public function getEdgeCastHLS($hlsURL, $pid) {
        if (strpos($hlsURL, 'cvip.smhcdn.com') !== false) {
            $hlsURL = substr($hlsURL, 7);
            $hlsURL = explode('/', $hlsURL);
            $hls_url = 'http://wpc.19BC0.taucdn.net/hls-live/2019BC0/' . $pid . '-live/hls/' . $hlsURL[2] . '.m3u8';
        } else {
            $hls_url = $hlsURL;
        }

        return $hls_url;
    }

    public function getLevel3HLS($hlsURL, $pid) {
        if (strpos($hlsURL, 'cvip.smhcdn.com') !== false) {
            $hlsURL = substr($hlsURL, 7);
            $hlsURL = explode('/', $hlsURL);
            $hls_url = 'http://' . $pid . '-live.hls.adaptive.level3.net/' . $pid . '/live/' . $pid . '-live/' . $hlsURL[2] . '/appleman.m3u8';
        } else {
            $hls_url = $hlsURL;
        }
        return $hls_url;
    }

    public function export_live() {
        $live_data = $this->get_live_data();
        $excel_data = array();
        foreach ($live_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'entryid' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'tags' => $data->tags,
                'referenceid' => $data->referenceId,
                'categories' => $data->categories,
                'categoriesIds' => $data->categoriesIds,
                'accessControlId' => $data->accessControlId,
                'status' => $data->status,
                'rank' => $data->rank,
                'totalRank' => $data->totalRank,
                'votes' => $data->votes,
                'thumbnailUrl' => $data->thumbnailUrl,
                'streamName' => $data->streamName,
                'offlineMessage' => $data->offlineMessage,
                'plays' => $data->plays,
                'views' => $data->views,
                'bitrates' => json_encode($data->bitrates),
                'creatorId' => $data->creatorId,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'EntryId')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'Tags')
                ->setCellValue('E1', 'ReferenceId')
                ->setCellValue('F1', 'Categories')
                ->setCellValue('G1', 'CategoriesIds')
                ->setCellValue('H1', 'AccessControlId')
                ->setCellValue('I1', 'Status')
                ->setCellValue('J1', 'Rank')
                ->setCellValue('K1', 'TotalRank')
                ->setCellValue('L1', 'Votes')
                ->setCellValue('M1', 'ThumbnailUrl')
                ->setCellValue('N1', 'StreamName')
                ->setCellValue('O1', 'OfflineMessage')
                ->setCellValue('P1', 'Plays')
                ->setCellValue('Q1', 'Views')
                ->setCellValue('R1', 'Bitrates')
                ->setCellValue('S1', 'CreatorId')
                ->setCellValue('T1', 'CreatedAt')
                ->setCellValue('U1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['entryid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['description'])
                    ->setCellValue('D' . $i, $value['tags'])
                    ->setCellValue('E' . $i, $value['referenceid'])
                    ->setCellValue('F' . $i, $value['categories'])
                    ->setCellValue('G' . $i, $value['categoriesIds'])
                    ->setCellValue('H' . $i, $value['accessControlId'])
                    ->setCellValue('I' . $i, $value['status'])
                    ->setCellValue('J' . $i, $value['rank'])
                    ->setCellValue('K' . $i, $value['totalRank'])
                    ->setCellValue('L' . $i, $value['votes'])
                    ->setCellValue('M' . $i, $value['thumbnailUrl'])
                    ->setCellValue('N' . $i, $value['streamName'])
                    ->setCellValue('O' . $i, $value['offlineMessage'])
                    ->setCellValue('P' . $i, $value['plays'])
                    ->setCellValue('Q' . $i, $value['views'])
                    ->setCellValue('R' . $i, $value['bitrates'])
                    ->setCellValue('S' . $i, $value['creatorId'])
                    ->setCellValue('T' . $i, $value['createdAt'])
                    ->setCellValue('U' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Live_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'live_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_live_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaLiveStreamEntryFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '2';
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->liveStream->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_cat() {
        $cat_data = $this->get_cat_data();
        $excel_data = array();
        foreach ($cat_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'name' => $data->name,
                'fullname' => $data->fullName,
                'description' => $data->description,
                'tags' => $data->tags,
                'referenceId' => $data->referenceId,
                'parentId' => $data->parentId,
                'depth' => $data->depth,
                'fullIds' => $data->fullIds,
                'entriesCount' => $data->entriesCount,
                'directEntriesCount' => $data->directEntriesCount,
                'directSubCategoriesCount' => $data->directSubCategoriesCount,
                'status' => $data->status,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'FullName')
                ->setCellValue('D1', 'Description')
                ->setCellValue('E1', 'Tags')
                ->setCellValue('F1', 'ReferenceId')
                ->setCellValue('G1', 'ParentId')
                ->setCellValue('H1', 'Depth')
                ->setCellValue('I1', 'FullIds')
                ->setCellValue('J1', 'EntriesCount')
                ->setCellValue('K1', 'DirectEntriesCount')
                ->setCellValue('L1', 'DirectSubCategoriesCount')
                ->setCellValue('M1', 'Status')
                ->setCellValue('N1', 'CreatedAt')
                ->setCellValue('O1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['fullname'])
                    ->setCellValue('D' . $i, $value['description'])
                    ->setCellValue('E' . $i, $value['tags'])
                    ->setCellValue('F' . $i, $value['referenceId'])
                    ->setCellValue('G' . $i, $value['parentId'])
                    ->setCellValue('H' . $i, $value['depth'])
                    ->setCellValue('I' . $i, $value['fullIds'])
                    ->setCellValue('J' . $i, $value['entriesCount'])
                    ->setCellValue('K' . $i, $value['directEntriesCount'])
                    ->setCellValue('L' . $i, $value['directSubCategoriesCount'])
                    ->setCellValue('M' . $i, $value['status'])
                    ->setCellValue('N' . $i, $value['createdAt'])
                    ->setCellValue('O' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Categories_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'categories_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_cat_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaCategoryFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '2';
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->category->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_plist() {
        $plist_data = $this->get_plist_data();
        $excel_data = array();
        foreach ($plist_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'playlistType' => $data->playlistType,
                'playlistContent' => $data->playlistContent,
                'status' => $data->status,
                'creatorId' => $data->creatorId,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'PlaylistType')
                ->setCellValue('E1', 'PlaylistContent')
                ->setCellValue('F1', 'Status')
                ->setCellValue('G1', 'CreatorId')
                ->setCellValue('H1', 'CreatedAt')
                ->setCellValue('I1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['description'])
                    ->setCellValue('D' . $i, $value['playlistType'])
                    ->setCellValue('E' . $i, $value['playlistContent'])
                    ->setCellValue('F' . $i, $value['status'])
                    ->setCellValue('G' . $i, $value['creatorId'])
                    ->setCellValue('H' . $i, $value['createdAt'])
                    ->setCellValue('I' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Playlists_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'playlists_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_plist_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaPlaylistFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '2';
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->playlist->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_players() {
        $players_data = $this->get_players_data();
        $excel_data = array();
        foreach ($players_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'name' => $data->name,
                'width' => $data->width,
                'height' => $data->height,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Width')
                ->setCellValue('D1', 'Height')
                ->setCellValue('E1', 'CreatedAt')
                ->setCellValue('F1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['width'])
                    ->setCellValue('D' . $i, $value['height'])
                    ->setCellValue('E' . $i, $value['createdAt'])
                    ->setCellValue('F' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Players_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'players_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_players_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaUiConfFilter();
        $filter->orderBy = '-createdAt';
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->uiConf->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_ac() {
        $ac_data = $this->get_ac_data();
        $excel_data = array();
        foreach ($ac_data as $data) {
            $arr = array();
            foreach ($data->rules as $rule) {
                array_push($arr, $rule->actions, $rule->conditions);
            }

            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'isDefault' => $data->isDefault,
                'rules' => json_encode($arr),
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'isDefault')
                ->setCellValue('E1', 'Rules')
                ->setCellValue('F1', 'CreatedAt')
                ->setCellValue('G1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['description'])
                    ->setCellValue('D' . $i, $value['isDefault'])
                    ->setCellValue('E' . $i, $value['rules'])
                    ->setCellValue('F' . $i, $value['createdAt'])
                    ->setCellValue('G' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Access_Control_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'access_control_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ac_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaAccessControlProfileFilter();
        $filter->orderBy = '-createdAt';
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->accessControlProfile->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_trans() {
        $trans_data = $this->get_trans_data();
        $excel_data = array();
        foreach ($trans_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'isDefault' => $data->isDefault,
                'flavorParamsIds' => $data->flavorParamsIds,
                'status' => $data->status,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'isDefault')
                ->setCellValue('E1', 'FlavorParamsIds')
                ->setCellValue('F1', 'Status')
                ->setCellValue('G1', 'CreatedAt')
                ->setCellValue('H1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['description'])
                    ->setCellValue('D' . $i, $value['isDefault'])
                    ->setCellValue('E' . $i, $value['flavorParamsIds'])
                    ->setCellValue('F' . $i, $value['status'])
                    ->setCellValue('G' . $i, $value['createdAt'])
                    ->setCellValue('H' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Transcoding_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'transcoding_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_trans_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaConversionProfileFilter();
        $filter->orderBy = '-createdAt';
        $filter->typeEqual = KalturaConversionProfileType::MEDIA;
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->conversionProfile->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_reseller() {
        $reseller_data = $this->get_reseller_data();
        $excel_data = array();
        foreach ($reseller_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);

            $url = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getStats.php?pid=' . $data->id;
            $partner_stats = json_decode($this->curl_request($url));
            $storage = '';
            $storage_gb = 0;
            $transfer = '';
            $transfer_gb = 0;

            if ($partner_stats->Error) {
                $storage = "0.00MB";
                $transfer = "0.00MB";
            } else {
                if ($partner_stats->result->storage) {
                    $storage = $this->formatStorage($partner_stats->result->storage);
                } else {
                    $storage = "0.00MB";
                }
                if ($partner_stats->result->bandwidth) {
                    $transfer = $this->formatStorage($partner_stats->result->bandwidth);
                } else {
                    $transfer = "0.00MB";
                }
            }

            $url = 'http://10.5.25.17/index.php/api/accounts/limits/' . $data->id . '.json';
            $partner_limits = json_decode($this->curl_request($url));
            $bandwidth_limit = ($partner_limits[0]->bandwidth_limit == 0) ? 'unlimited' : number_format($partner_limits[0]->bandwidth_limit) . 'GB';
            $storage_limit = ($partner_limits[0]->storage_limit == 0) ? 'unlimited' : number_format($partner_limits[0]->storage_limit) . 'GB';

            $url = 'http://mediaplatform.streamingmediahosting.com/apps/services/v1.0/?action=get_services&pid=' . $data->id;
            $partner_services = json_decode($this->curl_request($url));

            $services = '{mb:' . $partner_services->streaming_mobile . ',trans_vod:' . $partner_services->transcoding_vod . ',ppv:' . $partner_services->pay_per_view . ',vc:' . $partner_services->streaming_live_chat . ',wl:' . $partner_services->white_label . '}';

            array_push($excel_data, array(
                'id' => $data->id,
                'publisher' => $data->name,
                'email' => $data->adminEmail,
                'name' => $data->adminName,
                'dataTransfered' => $transfer,
                'bandwidthLimit' => $bandwidth_limit,
                'storageUsed' => $storage,
                'storageLimit' => $storage_limit,
                'services' => $services,
                'status' => $data->status,
                'createdAt' => $createdAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Publisher')
                ->setCellValue('C1', 'Name')
                ->setCellValue('D1', 'Email')
                ->setCellValue('E1', 'DataTransfered')
                ->setCellValue('F1', 'BandwidthLimit')
                ->setCellValue('G1', 'StorageUsed')
                ->setCellValue('H1', 'StorageLimit')
                ->setCellValue('I1', 'Services')
                ->setCellValue('J1', 'Status')
                ->setCellValue('K1', 'CreatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['publisher'])
                    ->setCellValue('C' . $i, $value['name'])
                    ->setCellValue('D' . $i, $value['email'])
                    ->setCellValue('E' . $i, $value['dataTransfered'])
                    ->setCellValue('F' . $i, $value['bandwidthLimit'])
                    ->setCellValue('G' . $i, $value['storageUsed'])
                    ->setCellValue('H' . $i, $value['storageLimit'])
                    ->setCellValue('I' . $i, $value['services'])
                    ->setCellValue('J' . $i, $value['status'])
                    ->setCellValue('K' . $i, $value['createdAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Reseller_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'reseller_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_reseller_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaPartnerFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '1,2';
        $filter->idNotIn = $this->pid;
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->partner->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_users() {
        $user_data = $this->get_users_data();
        $excel_data = array();
        foreach ($user_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            $logindate_unixtime = date('n/j/Y H:i', $data->lastLoginTime);
            $loggedAt = strtotime($logindate_unixtime);
            $loggedAt = date('m/d/Y h:i A', $loggedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'firstname' => $data->firstName,
                'lastname' => $data->lastName,
                'email' => $data->email,
                'roleIds' => $data->roleIds,
                'roleNames' => $data->roleNames,
                'isAdmin' => $data->isAdmin,
                'isAccountOwner' => $data->isAccountOwner,
                'lastLoginTime' => $loggedAt,
                'status' => $data->status,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'FirstName')
                ->setCellValue('C1', 'LastName')
                ->setCellValue('D1', 'Email')
                ->setCellValue('E1', 'RoleIds')
                ->setCellValue('F1', 'RoleNames')
                ->setCellValue('G1', 'IsAdmin')
                ->setCellValue('H1', 'IsAccountOwner')
                ->setCellValue('I1', 'LastLoginTime')
                ->setCellValue('J1', 'Status')
                ->setCellValue('K1', 'CreatedAt')
                ->setCellValue('L1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['firstname'])
                    ->setCellValue('C' . $i, $value['lastname'])
                    ->setCellValue('D' . $i, $value['email'])
                    ->setCellValue('E' . $i, $value['roleIds'])
                    ->setCellValue('F' . $i, $value['roleNames'])
                    ->setCellValue('G' . $i, $value['isAdmin'])
                    ->setCellValue('H' . $i, $value['isAccountOwner'])
                    ->setCellValue('I' . $i, $value['lastLoginTime'])
                    ->setCellValue('J' . $i, $value['status'])
                    ->setCellValue('K' . $i, $value['createdAt'])
                    ->setCellValue('L' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Users_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'users_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_users_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaUserFilter();
        $filter->orderBy = '+createdAt';
        $filter->statusIn = '1,0';
        $filter->isAdminEqual = KalturaNullableBoolean::TRUE_VALUE;
        $filter->loginEnabledEqual = KalturaNullableBoolean::TRUE_VALUE;
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->user->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_roles() {
        $user_data = $this->get_roles_data();
        $excel_data = array();
        foreach ($user_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'permissionNames' => $data->permissionNames,
                'status' => $data->status,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'PermissionNames')
                ->setCellValue('E1', 'Status')
                ->setCellValue('F1', 'CreatedAt')
                ->setCellValue('G1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['id'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['description'])
                    ->setCellValue('D' . $i, $value['permissionNames'])
                    ->setCellValue('E' . $i, $value['status'])
                    ->setCellValue('F' . $i, $value['createdAt'])
                    ->setCellValue('G' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Roles_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'roles_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_roles_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaUserRoleFilter();
        $filter->orderBy = '+createdAt';
        $filter->tagsMultiLikeOr = 'kmc';
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->userRole->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_entries() {
        $entries_data = $this->get_entries_data();
        $excel_data = array();
        foreach ($entries_data as $data) {
            $createdate_unixtime = date('n/j/Y H:i', $data->createdAt);
            $createdAt = strtotime($createdate_unixtime);
            $createdAt = date('m/d/Y h:i A', $createdAt);
            $updateddate_unixtime = date('n/j/Y H:i', $data->updatedAt);
            $updatedAt = strtotime($updateddate_unixtime);
            $updatedAt = date('m/d/Y h:i A', $updatedAt);
            array_push($excel_data, array(
                'entryid' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'mediaType' => $data->mediaType,
                'tags' => $data->tags,
                'referenceid' => $data->referenceId,
                'categories' => $data->categories,
                'categoriesIds' => $data->categoriesIds,
                'accessControlId' => $data->accessControlId,
                'flavorParamsIds' => $data->flavorParamsIds,
                'conversionProfileId' => $data->conversionProfileId,
                'status' => $data->status,
                'rank' => $data->rank,
                'totalRank' => $data->totalRank,
                'votes' => $data->votes,
                'thumbnailUrl' => $data->thumbnailUrl,
                'downloadUrl' => $data->downloadUrl,
                'dataUrl' => $data->dataUrl,
                'plays' => $data->plays,
                'views' => $data->views,
                'width' => $data->width,
                'height' => $data->height,
                'duration' => $data->duration,
                'msDuration' => $data->msDuration,
                'creatorId' => $data->creatorId,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt
            ));
        }

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'EntryId')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'MediaType')
                ->setCellValue('E1', 'Tags')
                ->setCellValue('F1', 'ReferenceId')
                ->setCellValue('G1', 'Categories')
                ->setCellValue('H1', 'CategoriesIds')
                ->setCellValue('I1', 'AccessControlId')
                ->setCellValue('J1', 'FlavorParamsIds')
                ->setCellValue('K1', 'ConversionProfileId')
                ->setCellValue('L1', 'Status')
                ->setCellValue('M1', 'Rank')
                ->setCellValue('N1', 'TotalRank')
                ->setCellValue('O1', 'Votes')
                ->setCellValue('P1', 'ThumbnailUrl')
                ->setCellValue('Q1', 'DownloadUrl')
                ->setCellValue('R1', 'DataUrl')
                ->setCellValue('S1', 'Plays')
                ->setCellValue('T1', 'Views')
                ->setCellValue('U1', 'Width')
                ->setCellValue('V1', 'Height')
                ->setCellValue('W1', 'Duration')
                ->setCellValue('X1', 'MsDuration')
                ->setCellValue('Y1', 'CreatorId')
                ->setCellValue('Z1', 'CreatedAt')
                ->setCellValue('AA1', 'UpdatedAt');

        $i = 2;
        foreach ($excel_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['entryid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['description'])
                    ->setCellValue('D' . $i, $value['mediaType'])
                    ->setCellValue('E' . $i, $value['tags'])
                    ->setCellValue('F' . $i, $value['referenceid'])
                    ->setCellValue('G' . $i, $value['categories'])
                    ->setCellValue('H' . $i, $value['categoriesIds'])
                    ->setCellValue('I' . $i, $value['accessControlId'])
                    ->setCellValue('J' . $i, $value['flavorParamsIds'])
                    ->setCellValue('K' . $i, $value['conversionProfileId'])
                    ->setCellValue('L' . $i, $value['status'])
                    ->setCellValue('M' . $i, $value['rank'])
                    ->setCellValue('N' . $i, $value['totalRank'])
                    ->setCellValue('O' . $i, $value['votes'])
                    ->setCellValue('P' . $i, $value['thumbnailUrl'])
                    ->setCellValue('Q' . $i, $value['downloadUrl'])
                    ->setCellValue('R' . $i, $value['dataUrl'])
                    ->setCellValue('S' . $i, $value['plays'])
                    ->setCellValue('T' . $i, $value['views'])
                    ->setCellValue('U' . $i, $value['width'])
                    ->setCellValue('V' . $i, $value['height'])
                    ->setCellValue('W' . $i, $value['duration'])
                    ->setCellValue('X' . $i, $value['msDuration'])
                    ->setCellValue('Y' . $i, $value['creatorId'])
                    ->setCellValue('Z' . $i, $value['createdAt'])
                    ->setCellValue('AA' . $i, $value['updatedAt']);
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Entries_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'entries_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_entries_data() {
        $config = new KalturaConfiguration($this->pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $filter = new KalturaMediaEntryFilter();
        $filter->orderBy = '-createdAt';
        $filter->mediaTypeIn = '1,2,5';
        $filter->statusIn = '-1,-2,0,1,2,7,4';
        $filter->isRoot = KalturaNullableBoolean::NULL_VALUE;
        $pager = new KalturaFilterPager();
        $pager->pageSize = $this->page_size;
        $result = $client->baseEntry->listAction($filter, $pager);
        return $result->objects;
    }

    public function export_mem_users() {
        $users_data = $this->get_mem_user_data();
        $objPHPExcel = new PHPExcel();

        $i = 2;
        foreach ($users_data as $data) {
            $cell = 'A';
            $found = false;
            foreach ($data as $key => $value) {
                $title = '';
                if ($key == 'uid') {
                    $title = 'UserId';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'fname') {
                    $title = 'FirstName';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'lname') {
                    $title = 'LastName';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'email') {
                    $title = 'Email';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'activated') {
                    $title = 'Activated';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'last_active') {
                    $title = 'LastActive';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'logged_in') {
                    $title = 'LoggedIn';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'status') {
                    $title = 'Status';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'created_at') {
                    $title = 'CreatedAt';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'updated_at') {
                    $title = 'UpdatedAt';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'additional_details') {
                    $ud_array = json_decode(stripslashes($value));
                    if ($ud_array) {
                        $found = true;
                        foreach ($ud_array as $attr) {
                            $title = $attr->field_name;
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $attr->value);
                            $cell++;
                        }
                    }
                }
            }
            if ($found) {
                $i++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('MEM_Users_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'mem_users_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_mem_user_data() {
        $user_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `user` WHERE partner_id = " . $this->pid;
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($user_data, array('uid' => $row['user_id'], 'fname' => $row['first_name'], 'lname' => $row['last_name'], 'email' => $row['email'], 'additional_details' => $row['user_details'], 'activated' => $row['activated'], 'last_active' => $row['last_active'], 'logged_in' => $row['logged_in'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $user_data;
    }

    public function export_mem_content() {
        $content_data = $this->get_mem_content_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'AccessControlId')
                ->setCellValue('D1', 'AccessControlName')
                ->setCellValue('E1', 'Type')
                ->setCellValue('F1', 'Status')
                ->setCellValue('G1', 'CreatedAt')
                ->setCellValue('H1', 'UpdatedAt');

        $i = 2;
        foreach ($content_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['eid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['ac_id'])
                    ->setCellValue('D' . $i, $value['ac_name'])
                    ->setCellValue('E' . $i, $value['type'])
                    ->setCellValue('F' . $i, $value['status'])
                    ->setCellValue('G' . $i, $value['created_at'])
                    ->setCellValue('H' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('MEM_Entries_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'mem_entries_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_mem_content_data() {
        $content_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `mem_entry` WHERE partner_id = " . $this->pid . " ORDER BY entry_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($content_data, array('eid' => $row['kentry_id'], 'name' => $row['kentry_name'], 'ac_id' => $row['ac_id'], 'ac_name' => $row['ac_name'], 'type' => $row['media_type'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $content_data;
    }

    public function export_ppv_users() {
        $users_data = $this->get_ppv_user_data();
        $objPHPExcel = new PHPExcel();

        $i = 2;
        foreach ($users_data as $data) {
            $cell = 'A';
            $found = false;
            foreach ($data as $key => $value) {
                $title = '';
                if ($key == 'uid') {
                    $title = 'UserId';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'fname') {
                    $title = 'FirstName';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'lname') {
                    $title = 'LastName';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'email') {
                    $title = 'Email';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'restriction') {
                    $title = 'Restriction';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'activated') {
                    $title = 'Activated';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'last_active') {
                    $title = 'LastActive';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'logged_in') {
                    $title = 'LoggedIn';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'status') {
                    $title = 'Status';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'created_at') {
                    $title = 'CreatedAt';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'updated_at') {
                    $title = 'UpdatedAt';
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $value);
                    $found = true;
                    $cell++;
                } else if ($key == 'additional_details') {
                    $ud_array = json_decode(stripslashes($value));
                    if ($ud_array) {
                        $found = true;
                        foreach ($ud_array as $attr) {
                            $title = $attr->field_name;
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . '1', $title);
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell . $i, $attr->value);
                            $cell++;
                        }
                    }
                }
            }
            if ($found) {
                $i++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('PPV_Users_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_users_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_user_data() {
        $user_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `user` WHERE partner_id = " . $this->pid;
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($user_data, array('uid' => $row['user_id'], 'fname' => $row['first_name'], 'lname' => $row['last_name'], 'email' => $row['email'], 'additional_details' => $row['user_details'], 'restriction' => $row['restriction'], 'activated' => $row['activated'], 'last_active' => $row['last_active'], 'logged_in' => $row['logged_in'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $user_data;
    }

    public function export_ppv_tickets() {
        $tickets_data = $this->get_ppv_tickets_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'TicketId')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'Price')
                ->setCellValue('E1', 'Type')
                ->setCellValue('F1', 'ExpiryConfig')
                ->setCellValue('G1', 'MaxViews')
                ->setCellValue('H1', 'BillingPeriod')
                ->setCellValue('I1', 'Status')
                ->setCellValue('J1', 'CreatedAt')
                ->setCellValue('K1', 'UpdatedAt');

        $i = 2;
        foreach ($tickets_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['tid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['desc'])
                    ->setCellValue('D' . $i, $value['price'])
                    ->setCellValue('E' . $i, $value['type'])
                    ->setCellValue('F' . $i, $value['expiry'])
                    ->setCellValue('G' . $i, $value['max_views'])
                    ->setCellValue('H' . $i, $value['billing_period'])
                    ->setCellValue('I' . $i, $value['status'])
                    ->setCellValue('J' . $i, $value['created_at'])
                    ->setCellValue('K' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Tickets_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_tickets_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_tickets_data() {
        $ticket_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `ticket` WHERE partner_id = " . $this->pid;
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            $type = ($row['ticket_type'] == 'reg') ? 'one-off' : 'subscription';
            array_push($ticket_data, array('tid' => $row['ticket_id'], 'name' => $row['ticket_name'], 'desc' => $row['ticket_desc'], 'price' => $row['ticket_price'], 'type' => $type, 'expiry' => $row['expiry_config'], 'max_views' => $row['max_views'], 'billing_period' => $row['billing_period'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $ticket_data;
    }

    public function export_ppv_content() {
        $content_data = $this->get_ppv_content_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Id')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'AccessControlId')
                ->setCellValue('D1', 'AccessControlName')
                ->setCellValue('E1', 'Type')
                ->setCellValue('F1', 'TicketIds')
                ->setCellValue('G1', 'Status')
                ->setCellValue('H1', 'CreatedAt')
                ->setCellValue('I1', 'UpdatedAt');

        $i = 2;
        foreach ($content_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['eid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['ac_id'])
                    ->setCellValue('D' . $i, $value['ac_name'])
                    ->setCellValue('E' . $i, $value['type'])
                    ->setCellValue('F' . $i, $value['ticket_ids'])
                    ->setCellValue('G' . $i, $value['status'])
                    ->setCellValue('H' . $i, $value['created_at'])
                    ->setCellValue('I' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Entries_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_entries_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_content_data() {
        $content_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `entry` WHERE partner_id = " . $this->pid . " ORDER BY entry_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($content_data, array('eid' => $row['kentry_id'], 'name' => $row['kentry_name'], 'ac_id' => $row['ac_id'], 'ac_name' => $row['ac_name'], 'type' => $row['media_type'], 'ticket_ids' => $row['ticket_ids'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $content_data;
    }

    public function export_ppv_orders() {
        $orders_data = $this->get_ppv_orders_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'OrderId')
                ->setCellValue('B1', 'ExpirationDate')
                ->setCellValue('C1', 'Expiry')
                ->setCellValue('D1', 'Views')
                ->setCellValue('E1', 'MaxViews')
                ->setCellValue('F1', 'UserId')
                ->setCellValue('G1', 'UserEmail')
                ->setCellValue('H1', 'TicketId')
                ->setCellValue('I1', 'TicketName')
                ->setCellValue('J1', 'TicketPrice')
                ->setCellValue('K1', 'TicketType')
                ->setCellValue('L1', 'EntryId')
                ->setCellValue('M1', 'EntryName')
                ->setCellValue('N1', 'PaymentStatus')
                ->setCellValue('O1', 'OrderStatus')
                ->setCellValue('P1', 'CreatedAt')
                ->setCellValue('Q1', 'UpdatedAt');

        $i = 2;
        foreach ($orders_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['oid'])
                    ->setCellValue('B' . $i, $value['expires'])
                    ->setCellValue('C' . $i, $value['expiry'])
                    ->setCellValue('D' . $i, $value['views'])
                    ->setCellValue('E' . $i, $value['max_views'])
                    ->setCellValue('F' . $i, $value['user_id'])
                    ->setCellValue('G' . $i, $value['email'])
                    ->setCellValue('H' . $i, $value['ticket_id'])
                    ->setCellValue('I' . $i, $value['ticket_name'])
                    ->setCellValue('J' . $i, $value['ticket_price'])
                    ->setCellValue('K' . $i, $value['ticket_type'])
                    ->setCellValue('L' . $i, $value['entry_id'])
                    ->setCellValue('M' . $i, $value['entry_name'])
                    ->setCellValue('N' . $i, $value['payment_status'])
                    ->setCellValue('O' . $i, $value['order_status'])
                    ->setCellValue('P' . $i, $value['created_at'])
                    ->setCellValue('Q' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Orders_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_orders_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_orders_data() {
        $orders_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `order` WHERE partner_id = " . $this->pid . " ORDER BY order_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            if (strpos($row['ticket_price'], ';') !== false) {
                $ticket_price = explode(";", $row['ticket_price']);
                $ticket_price = preg_replace('/[^0-9.]/', '', $ticket_price[1]);
            } else {
                $ticket_price = $row['ticket_price'];
                $ticket_price = preg_replace('/[^0-9.]/', '', $ticket_price);
            }
            $type = ($row['ticket_type'] == 'reg') ? 'one-off' : 'subscription';
            array_push($orders_data, array('oid' => $row['order_id'], 'expires' => $row['expires'], 'expiry' => $row['expiry_config'], 'views' => $row['views'], 'max_views' => $row['max_views'], 'user_id' => $row['user_id'], 'email' => $row['email'], 'ticket_id' => $row['ticket_id'], 'ticket_name' => $row['ticket_name'], 'ticket_price' => $ticket_price, 'ticket_type' => $type, 'entry_id' => $row['kentry_id'], 'entry_name' => $row['kentry_name'], 'payment_status' => $row['status'], 'order_status' => $row['order_status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $orders_data;
    }

    public function export_ppv_subs() {
        $subs_data = $this->get_ppv_subs_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'SubId')
                ->setCellValue('B1', 'PaymentCycle')
                ->setCellValue('C1', 'UserId')
                ->setCellValue('D1', 'UserEmail')
                ->setCellValue('E1', 'TicketName')
                ->setCellValue('F1', 'TicketPrice')
                ->setCellValue('G1', 'Status')
                ->setCellValue('H1', 'CreatedAt')
                ->setCellValue('I1', 'UpdatedAt');

        $i = 2;
        foreach ($subs_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['sid'])
                    ->setCellValue('B' . $i, $value['payment_cycle'])
                    ->setCellValue('C' . $i, $value['user_id'])
                    ->setCellValue('D' . $i, $value['email'])
                    ->setCellValue('E' . $i, $value['ticket_name'])
                    ->setCellValue('F' . $i, $value['ticket_price'])
                    ->setCellValue('G' . $i, $value['status'])
                    ->setCellValue('H' . $i, $value['created_at'])
                    ->setCellValue('I' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Subs_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_subs_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_subs_data() {
        $subs_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `subscription` WHERE partner_id = " . $this->pid . " ORDER BY sub_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            if (strpos($row['ticket_price'], ';') !== false) {
                $ticket_price = explode(";", $row['ticket_price']);
                $ticket_price = preg_replace('/[^0-9.]/', '', $ticket_price[1]);
            } else {
                $ticket_price = $row['ticket_price'];
                $ticket_price = preg_replace('/[^0-9.]/', '', $ticket_price);
            }
            array_push($subs_data, array('sid' => $row['sub_id'], 'payment_cycle' => $row['payment_cycle'], 'user_id' => $row['user_id'], 'email' => $row['email'], 'ticket_name' => $row['ticket_name'], 'ticket_price' => $ticket_price, 'status' => $row['profile_status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $subs_data;
    }

    public function export_ppv_affiliates() {
        $aff_data = $this->get_ppv_affiliates_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'AffiliateId')
                ->setCellValue('B1', 'FirstName')
                ->setCellValue('C1', 'LastName')
                ->setCellValue('D1', 'Email')
                ->setCellValue('E1', 'Phone')
                ->setCellValue('F1', 'Fax')
                ->setCellValue('G1', 'Address1')
                ->setCellValue('H1', 'Address2')
                ->setCellValue('I1', 'City')
                ->setCellValue('J1', 'State')
                ->setCellValue('K1', 'ZipCode')
                ->setCellValue('L1', 'Country')
                ->setCellValue('M1', 'CompanyName')
                ->setCellValue('N1', 'Website')
                ->setCellValue('O1', 'PayPalEmail')
                ->setCellValue('P1', 'Status')
                ->setCellValue('Q1', 'CreatedAt')
                ->setCellValue('R1', 'UpdatedAt');

        $i = 2;
        foreach ($aff_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['aid'])
                    ->setCellValue('B' . $i, $value['first_name'])
                    ->setCellValue('C' . $i, $value['last_name'])
                    ->setCellValue('D' . $i, $value['email'])
                    ->setCellValue('E' . $i, $value['phone'])
                    ->setCellValue('F' . $i, $value['fax'])
                    ->setCellValue('G' . $i, $value['address1'])
                    ->setCellValue('H' . $i, $value['address2'])
                    ->setCellValue('I' . $i, $value['city'])
                    ->setCellValue('J' . $i, $value['state'])
                    ->setCellValue('K' . $i, $value['zip_code'])
                    ->setCellValue('L' . $i, $value['country'])
                    ->setCellValue('M' . $i, $value['company_name'])
                    ->setCellValue('N' . $i, $value['website'])
                    ->setCellValue('O' . $i, $value['paypal_email'])
                    ->setCellValue('P' . $i, $value['status'])
                    ->setCellValue('Q' . $i, $value['created_at'])
                    ->setCellValue('R' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Affiliates_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_affiliates_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_affiliates_data() {
        $aff_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `affiliate_user` WHERE partner_id = " . $this->pid . " ORDER BY affiliate_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($aff_data, array('aid' => $row['affiliate_id'], 'first_name' => $row['first_name'], 'last_name' => $row['last_name'], 'email' => $row['email'], 'phone' => $row['phone'], 'fax' => $row['fax'], 'address1' => $row['address_line_1'], 'address2' => $row['address_line_2'], 'city' => $row['city'], 'state' => $row['state'], 'zip_code' => $row['zip_code'], 'country' => $row['country'], 'company_name' => $row['company_name'], 'website' => $row['website'], 'paypal_email' => $row['paypal_email'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $aff_data;
    }

    public function export_ppv_campaigns() {
        $campaigns_data = $this->get_ppv_campaigns_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'CampaignId')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'CookieLife')
                ->setCellValue('E1', 'FlatRate')
                ->setCellValue('F1', 'Percentage')
                ->setCellValue('G1', 'Commission')
                ->setCellValue('H1', 'Status')
                ->setCellValue('I1', 'CreatedAt')
                ->setCellValue('J1', 'UpdatedAt');

        $i = 2;
        foreach ($campaigns_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['cid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['desc'])
                    ->setCellValue('D' . $i, $value['cookie_life'])
                    ->setCellValue('E' . $i, $value['flat_rate'])
                    ->setCellValue('F' . $i, $value['percentage'])
                    ->setCellValue('G' . $i, $value['commission'])
                    ->setCellValue('H' . $i, $value['status'])
                    ->setCellValue('I' . $i, $value['created_at'])
                    ->setCellValue('J' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Campaigns_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_campaigns_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_campaigns_data() {
        $campaigns_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `affiliate_campaign` WHERE partner_id = " . $this->pid . " ORDER BY campaign_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($campaigns_data, array('cid' => $row['campaign_id'], 'name' => $row['name'], 'desc' => $row['desc'], 'cookie_life' => $row['cookie_life'], 'flat_rate' => $row['flat_rate'], 'percentage' => $row['percentage'], 'commission' => $row['commission'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $campaigns_data;
    }

    public function export_ppv_marketing() {
        $marketing_data = $this->get_ppv_marketing_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'LinkId')
                ->setCellValue('B1', 'Name')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'URL')
                ->setCellValue('E1', 'AffiliateId')
                ->setCellValue('F1', 'AffiliateName')
                ->setCellValue('G1', 'CampaignId')
                ->setCellValue('H1', 'CampaignName')
                ->setCellValue('I1', 'UniqueHits')
                ->setCellValue('J1', 'RawHits')
                ->setCellValue('K1', 'Sales')
                ->setCellValue('L1', 'Status')
                ->setCellValue('M1', 'CreatedAt')
                ->setCellValue('N1', 'UpdatedAt');

        $i = 2;
        foreach ($marketing_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['mid'])
                    ->setCellValue('B' . $i, $value['name'])
                    ->setCellValue('C' . $i, $value['desc'])
                    ->setCellValue('D' . $i, $value['url'])
                    ->setCellValue('E' . $i, $value['aid'])
                    ->setCellValue('F' . $i, $value['affiliate_name'])
                    ->setCellValue('G' . $i, $value['cid'])
                    ->setCellValue('H' . $i, $value['campaign_name'])
                    ->setCellValue('I' . $i, $value['unique_hits'])
                    ->setCellValue('J' . $i, $value['raw_hits'])
                    ->setCellValue('K' . $i, $value['sales'])
                    ->setCellValue('L' . $i, $value['status'])
                    ->setCellValue('M' . $i, $value['created_at'])
                    ->setCellValue('N' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Marketing_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_marketing_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_marketing_data() {
        $marketing_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `affiliate_marketing` WHERE partner_id = " . $this->pid . " ORDER BY marketing_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($marketing_data, array('mid' => $row['marketing_id'], 'name' => $row['name'], 'desc' => $row['desc'], 'url' => $row['url'], 'aid' => $row['affiliate_id'], 'affiliate_name' => $row['affiliate_name'], 'cid' => $row['campaign_id'], 'campaign_name' => $row['campaign_name'], 'unique_hits' => $row['unique_hits'], 'raw_hits' => $row['raw_hits'], 'sales' => $row['sales'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $marketing_data;
    }

    public function export_ppv_commissions() {
        $commissions_data = $this->get_ppv_commissions_data();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'SaleId')
                ->setCellValue('B1', 'AffiliateId')
                ->setCellValue('C1', 'AffiliateName')
                ->setCellValue('D1', 'CampaignId')
                ->setCellValue('E1', 'Campaign')
                ->setCellValue('F1', 'MarketingId')
                ->setCellValue('G1', 'OrderId')
                ->setCellValue('H1', 'CustomerName')
                ->setCellValue('I1', 'Commission')
                ->setCellValue('J1', 'OrderDate')
                ->setCellValue('K1', 'TotalSale')
                ->setCellValue('L1', 'IP')
                ->setCellValue('M1', 'Status')
                ->setCellValue('N1', 'CreatedAt')
                ->setCellValue('O1', 'UpdatedAt');

        $i = 2;
        foreach ($commissions_data as $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['sid'])
                    ->setCellValue('B' . $i, $value['aid'])
                    ->setCellValue('C' . $i, $value['aff_name'])
                    ->setCellValue('D' . $i, $value['cid'])
                    ->setCellValue('E' . $i, $value['campaign'])
                    ->setCellValue('F' . $i, $value['mid'])
                    ->setCellValue('G' . $i, $value['oid'])
                    ->setCellValue('H' . $i, $value['customer_name'])
                    ->setCellValue('I' . $i, $value['commission'])
                    ->setCellValue('J' . $i, $value['order_date'])
                    ->setCellValue('K' . $i, $value['total_sale'])
                    ->setCellValue('L' . $i, $value['ip'])
                    ->setCellValue('M' . $i, $value['status'])
                    ->setCellValue('N' . $i, $value['created_at'])
                    ->setCellValue('O' . $i, $value['updated_at']);
            $i++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('PPV_Commissions_Metadata');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'ppv_commissions_metadata_' . date('m-d-Y_H_i_s');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function get_ppv_commissions_data() {
        $commissions_data = array();
        $link = @mysqli_connect("127.0.0.1", "smh_ppv", "*A095E628F563DE69BDE25AB08F6625B3B63654EF", "smh_ppv", 3307) or die('Unable to establish a DB connection');
        $query = "SELECT * FROM `affiliate_sales` WHERE partner_id = " . $this->pid . " ORDER BY sale_id DESC";
        $result = mysqli_query($link, $query) or die('Query failed: ' . mysqli_error());
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($commissions_data, array('sid' => $row['sale_id'], 'aid' => $row['affiliate_id'], 'aff_name' => $row['aff_name'], 'cid' => $row['campaign_id'], 'campaign' => $row['campaign'], 'mid' => $row['marketing_id'], 'oid' => $row['order_id'], 'customer_name' => $row['customer_name'], 'commission' => $row['commission'], 'order_date' => $row['order_date'], 'total_sale' => $row['total_sale'], 'ip' => $row['ip'], 'status' => $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']));
        }
        return $commissions_data;
    }

    public function formatStorage($size) {
        if ($size >= 1) {
            return number_format($size, 2) . "GB";
        } else {
            $size = $size * 1000;
            return number_format($size, 2) . "MB";
        }
    }

    public function curl_request($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}

$export = new export();
$export->run();
?>