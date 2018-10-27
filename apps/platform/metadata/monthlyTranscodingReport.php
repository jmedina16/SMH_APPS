<?php

error_reporting(1);
date_default_timezone_set('America/Los_Angeles');
require_once('../../../app/clients/php5/KalturaClient.php');
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

class transcodeReport {

    protected $link = null;
    protected $login;
    protected $password;
    protected $database;
    protected $hostname;
    protected $port;

    public function __construct() {
        $this->login = 'kaltura';
        $this->password = 'nUKFRl7bE9hShpV';
        $this->database = 'kaltura';
        $this->hostname = '127.0.0.1';
        $this->port = '3306';
        $this->connect();
    }

    //connect to database
    public function connect() {
        if (!is_null($this->link)) {
            return;
        }

        try {
            $this->link = new PDO("mysql:host=$this->hostname;port=3306;dbname=$this->database", $this->login, $this->password);
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [smhLimitsCheck->connect] ERROR: Cannot connect to database: " . $e->getMessage() . "\n");
        }
    }

    //run ppv api
    public function run() {
        // mark the start time
        $start_time = MICROTIME(TRUE);

        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->run] INFO: Transcoding reports is running \n");

        $date = getopt("d:");
        // Get arguments
        if (count($date > 0)) {
            $date = $date['d'];
        } else {
            $date = null;
        }

        if ($date) {
            $yearmonth = $date;
        } else {
            $yearmonth = $this->getMonthYear();
        }

        $partnerData = $this->getPartnerIds();
        $childData = $this->getChildAccounts();

        $combindAccounts = $this->combindAccounts($partnerData, $childData);

        //$resellerAccounts = $this->getResellerAccounts($partnerData);
        //print_r($partnerData);
        $this->build_report($combindAccounts, $yearmonth);

        $stop_time = MICROTIME(TRUE);

        // get the difference in seconds
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->run] INFO: Done after [" . $time . "] seconds\n");
    }

    // Search for partner ids 
    public function getPartnerIds() {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->getPartnerIds] INFO: Searching for partnerIds.. \n");

        try {
            $stmt = $this->link->prepare("SET SESSION wait_timeout = 600");
            $stmt->execute();

            $partnerIds_query = $this->link->prepare("SELECT * FROM partner WHERE status IN (1,2) AND id NOT IN (0,-1,-2,-3,-4,-5,99,10364) AND (partner_parent_id IS NULL OR partner_parent_id = 0)");
            $partnerIds_query->execute();
            $partner_array = array();
            foreach ($partnerIds_query->fetchAll(PDO::FETCH_OBJ) as $row) {
                array_push($partner_array, array('partnerId' => $row->id, 'partnerName' => $row->partner_name, 'childAccounts' => array()));
            }

            return $partner_array;
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [transcodeReport->getPartnerIds] ERROR: Could not execute query (Search Partner Ids): " . $e->getMessage() . "\n");
        }
    }

    public function getResellerAccounts($partnerData) {
        $reseller_accounts = array();
        $url1 = 'http://10.5.25.17/index.php/api/reseller/list.json';
        foreach ($partnerData as $partner) {
            $url2 = 'http://10.5.25.17/index.php/api/accounts/pid/' . $partner['partnerId'] . '.json';
            $services = json_decode($this->curl_request($url2));
            if (property_exists($services, 'portal_reseller') && $services->portal_reseller == 1) {
                array_push($reseller_accounts, $partner['partnerId']);
            }
        }

        return $reseller_accounts;
    }

    public function getChildAccounts() {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->getChildAccounts] INFO: Searching for partnerIds.. \n");

        try {
            $stmt = $this->link->prepare("SET SESSION wait_timeout = 600");
            $stmt->execute();

            $partnerIds_query = $this->link->prepare("SELECT * FROM partner WHERE status IN (1,2) AND id NOT IN (0,-1,-2,-3,-4,-5,99,10364) AND partner_parent_id IS NOT NULL AND partner_parent_id != 0");
            $partnerIds_query->execute();
            $partner_array = array();
            foreach ($partnerIds_query->fetchAll(PDO::FETCH_OBJ) as $row) {
                array_push($partner_array, array('partnerId' => $row->id, 'partnerName' => $row->partner_name, 'partnerParentId' => $row->partner_parent_id));
            }

            return $partner_array;
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [transcodeReport->getChildAccounts] ERROR: Could not execute query (Search Partner Ids): " . $e->getMessage() . "\n");
        }
    }

    public function combindAccounts($partnerData, $childData) {
        foreach ($childData as $child) {
            foreach ($partnerData as &$partner) {
                if ($child['partnerParentId'] == $partner['partnerId']) {
                    array_push($partner['childAccounts'], array('childId' => $child['partnerId'], 'childName' => $child['partnerName']));
                }
            }
        }
        //print_r($partnerData);
        return $partnerData;
    }

    public function build_report($partnerData, $yearmonth) {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->build_report] INFO: Building report.. \n");
        $yearmonthClean = str_replace('-', '', $yearmonth);
        $transcoding_data = $this->get_transcoding_data($partnerData, $yearmonthClean);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Account ID')
                ->setCellValue('B1', 'Account Name')
                ->setCellValue('C1', 'Child Account ID')
                ->setCellValue('D1', 'Child Account Name')
                ->setCellValue('E1', 'SD Minutes Used')
                ->setCellValue('F1', 'HD Minutes Used')
                ->setCellValue('G1', 'UHD Minutes Used')
                ->setCellValue('H1', 'Audio Only Minutes Used')
                ->setCellValue('I1', 'Total Minutes Used')
                ->setCellValue('J1', 'Transcoding Limit')
                ->setCellValue('K1', 'Transcoding Overage');

        $i = 2;
        $overage = 0;
        $transcoding_total = 0;
        $url = 'http://10.5.25.17/index.php/api/reseller/list.json';
        foreach ($transcoding_data as $value) {
            if ($value['is_child']) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('C' . $i, $value['partner_id'])
                        ->setCellValue('D' . $i, $value['partner_name'])
                        ->setCellValue('I' . $i, $value['transcoding_total'])
                        ->setCellValue('J' . $i, $value['transcoding_limit']);
            } else {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $i, $value['partner_id'])
                        ->setCellValue('B' . $i, $value['partner_name'])
                        ->setCellValue('I' . $i, $value['transcoding_total'])
                        ->setCellValue('J' . $i, $value['transcoding_limit']);
            }
            foreach ($value['months'] as $data) {
                if ($data['month'] == $yearmonth) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('E' . $i, $data['sd_duration'])
                            ->setCellValue('F' . $i, $data['hd_duration'])
                            ->setCellValue('G' . $i, $data['uhd_duration'])
                            ->setCellValue('H' . $i, $data['audio_duration']);
                    if ($value['transcoding_limit'] !== 'unlimited') {
                        if ($value['transcoding_total'] > (float) $value['transcoding_limit']) {
                            $overage = $value['transcoding_total'] - (float) $value['transcoding_limit'];
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('K' . $i, $overage);
                        }
                    }
                }
            }
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('transcodingOverage-' . $yearmonth);
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'monthlyTranscodingOverage-' . $yearmonth . '.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($filename);
    }

    public function getMonthYear() {
        $now = new \DateTime('last day of last month');
        $month = $now->format('m');
        $year = $now->format('Y');

        return "$year-$month";
    }

    public function get_months() {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->get_months] INFO: Building months.. \n");
        $year = date('Y');
        $start_year = $year . '-01-01';
        $datetime = new DateTime($start_year);
        $startn = (int) date_format($datetime, "n");
        $current_month = date('n');
        $date = date_format($datetime, "F");

        $months = array();
        array_push($months, $date);
        $datetime = new DateTime($start_year);
        if ($current_month == 1) {
            for ($x = 1; $x < 12; $x++) {
                $datetime->add(new DateInterval('P1M'));
                $newMonth = date_format($datetime, "F");
                array_push($months, $newMonth);
            }
        } else {
            for ($startn; $startn <= $current_month - 2; $startn++) {
                $datetime->add(new DateInterval('P1M'));
                $newMonth = date_format($datetime, "F");
                array_push($months, $newMonth);
            }
        }
        return $months;
    }

    public function get_transcoding_data($partnerData, $yearmonthClean) {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->get_transcoding_data] INFO: Building transcoding data.. \n");
        $partner_data = array();
        $transcoding_data = array();
        $current_month = date('n');
        foreach ($partnerData as $partner) {
            $transcoding_data = array();
            //if ($partner['partnerId'] == 13373 || $partner['partnerId'] == 10012 || $partner['partnerId'] == 12923) {
            if ($current_month == 1) {
                $previous_year = date("Y", strtotime("-1 years"));
                $url1 = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getMonthlyStats.php?pid=' . $partner['partnerId'] . '&year=' . $previous_year;
            } else {
                $url1 = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getMonthlyStats.php?pid=' . $partner['partnerId'];
            }
            $url2 = 'http://10.5.25.17/index.php/api/accounts/limits/' . $partner['partnerId'] . '.json';
            $partner_stats = json_decode($this->curl_request($url1));
            $transcoding_stats = json_decode($this->curl_request($url2));
            $transcoding = $partner_stats->result->transcoding;
            foreach ($transcoding as $data) {
                array_push($transcoding_data, array('month' => $data->date, 'sd_duration' => $data->sd_duration, 'hd_duration' => $data->hd_duration, 'uhd_duration' => $data->uhd_duration, 'audio_duration' => $data->audio_duration));
            }
            if (!$transcoding_stats->error) {
                $transcoding_limit = ($transcoding_stats[0]->transcoding_limit == 0) ? 'unlimited' : $transcoding_stats[0]->transcoding_limit . ' Minutes';
            }
            $transcoding_total = (float) $this->get_transcoding_total($partner['partnerId'], $yearmonthClean);
            array_push($partner_data, array('partner_id' => $partner['partnerId'], 'partner_name' => $partner['partnerName'], 'transcoding_limit' => $transcoding_limit, 'transcoding_total' => $transcoding_total, 'is_child' => 0, 'months' => $transcoding_data));
            if (count($partner['childAccounts']) > 0) {
                end($partner_data);
                $last_id = key($partner_data);
                $grand_total_arr = array();
                array_push($grand_total_arr, $partner_data[$last_id]['transcoding_total']);
                $childData = $this->get_child_transcoding_data($partner['childAccounts']);
                foreach ($childData as $child) {
                    $transcoding_total = (float) $this->get_transcoding_total($child['partner_id'], $yearmonthClean);
                    array_push($grand_total_arr, $transcoding_total);
                    array_push($partner_data, array('partner_id' => $child['partner_id'], 'partner_name' => $child['partner_name'], 'transcoding_limit' => $child['transcoding_limit'], 'transcoding_total' => $transcoding_total, 'is_child' => 1, 'months' => $child['months']));
                }

                $partner_data[$last_id]['transcoding_total'] = array_sum($grand_total_arr);
            }
            //}
        }
        //print_r($partner_data);
        return $partner_data;
    }

    public function get_child_transcoding_data($partnerData) {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->get_child_transcoding_data] INFO: Building transcoding data.. \n");
        $partner_data = array();
        $transcoding_data = array();
        $current_month = date('n');
        foreach ($partnerData as $partner) {
            $transcoding_data = array();
            if ($current_month == 1) {
                $previous_year = date("Y", strtotime("-1 years"));
                $url1 = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getMonthlyStats.php?pid=' . $partner['childId'] . '&year=' . $previous_year;
            } else {
                $url1 = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getMonthlyStats.php?pid=' . $partner['childId'];
            }
            $url2 = 'http://10.5.25.17/index.php/api/accounts/limits/' . $partner['childId'] . '.json';
            $partner_stats = json_decode($this->curl_request($url1));
            $transcoding_stats = json_decode($this->curl_request($url2));
            $transcoding = $partner_stats->result->transcoding;
            foreach ($transcoding as $data) {
                array_push($transcoding_data, array('month' => $data->date, 'sd_duration' => $data->sd_duration, 'hd_duration' => $data->hd_duration, 'uhd_duration' => $data->uhd_duration, 'audio_duration' => $data->audio_duration));
            }
            if (!$transcoding_stats->error) {
                $transcoding_limit = ($transcoding_stats[0]->transcoding_limit == 0) ? 'unlimited' : $transcoding_stats[0]->transcoding_limit . ' Minutes';
            }
            array_push($partner_data, array('partner_id' => $partner['childId'], 'partner_name' => $partner['childName'], 'transcoding_limit' => $transcoding_limit, 'months' => $transcoding_data));
        }
        return $partner_data;
    }

    public function get_transcoding_total($pid, $yearmonth) {
        $url = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getStats.php?pid=' . $pid . '&date=' . $yearmonth;
        $results = json_decode($this->curl_request($url));
        return $results->result->transcoding_duration;
    }

    public function curl_request($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function curl_post_request($url, $post) {
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            shutdown('Sorry cURL is not installed!');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if (!is_null($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}

$export = new transcodeReport();
$export->run();
?>