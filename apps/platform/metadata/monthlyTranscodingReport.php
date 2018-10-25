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

        $partnerData = $this->getPartnerIds();
        $this->build_report($partnerData);

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

            $partnerIds_query = $this->link->prepare("SELECT * FROM partner WHERE status IN (1,2) AND id NOT IN (0,-1,-2,-3,-4,-5,99,10364)");
            $partnerIds_query->execute();
            $partner_array = array();
            foreach ($partnerIds_query->fetchAll(PDO::FETCH_OBJ) as $row) {
                array_push($partner_array, array('partnerId' => $row->id, 'partnerName' => $row->partner_name));
            }

            return $partner_array;
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [transcodeReport->getPartnerIds] ERROR: Could not execute query (Search Partner Ids): " . $e->getMessage() . "\n");
        }
    }

    public function build_report($partnerData) {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->build_report] INFO: Building report.. \n");
        $transcoding_data = $this->get_transcoding_data($partnerData);
        $months = $this->get_months();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Account')
                ->setCellValue('B1', 'PartnerId')
                ->setCellValue('C1', 'Transcoding Limit');

        $l = 'D';
        foreach ($months as $month) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($l . '1', $month);
            $l++;
        }

        $i = 2;
        $month_count;
        foreach ($transcoding_data as $value) {
            $l = 'D';
            $month_count = 0;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $i, $value['partner_name'])
                    ->setCellValue('B' . $i, $value['partner_id'])
                    ->setCellValue('C' . $i, $value['transcoding_limit']);
            foreach ($value['months'] as $value) {
                if ($month_count == count($months))
                    break;
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($l . $i, $value['value']);
                $l++;
                $month_count++;
            }
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Transcoding_Report');
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'transcoding_report_' . date('m-d-Y_H_i_s') . '.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($filename);
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

    public function get_transcoding_data($partnerData) {
        $date = date('Y-m-d H:i:s');
        print($date . " [transcodeReport->get_transcoding_data] INFO: Building transcoding data.. \n");
        $partner_data = array();
        $transcoding_data = array();
        $current_month = date('n');
        foreach ($partnerData as $partner) {
            $transcoding_data = array();
            //if ($partner['partnerId'] == 13373 || $partner['partnerId'] == 10012) {
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
                array_push($transcoding_data, array('month' => $data->date, 'value' => $data->value . " GB"));
            }
            if (!$transcoding_stats->error) {
                $transcoding_limit = ($transcoding_stats[0]->transcoding_limit == 0) ? 'unlimited' : $transcoding_stats[0]->transcoding_limit . ' GB';
            }

            array_push($partner_data, array('partner_id' => $partner['partnerId'], 'partner_name' => $partner['partnerName'], 'transcoding_limit' => $transcoding_limit, 'months' => $transcoding_data));
            //}
        }
        //print_r($partner_data);
        return $partner_data;
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

$export = new transcodeReport();
$export->run();
?>