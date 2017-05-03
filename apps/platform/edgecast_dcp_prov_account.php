<?php

ini_set('error_reporting', 0);
ini_set('memory_limit', '1024M'); // or you could use 1G
require_once('../../app/clients/php5/KalturaClient.php');

class dcp {

    public function __construct() {}

    //run garbage collection
    public function run() {
        // mark the start time
        $start_time = MICROTIME(TRUE);

        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: HTML5 Update is running \n");

        $this->createHLSInstances();
        // mark the stop time
        $stop_time = MICROTIME(TRUE);

        // get the difference in seconds
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: Done after [" . $time . "] seconds\n");
    }

    public function createHLSInstances() {
        $create_hls_instances = json_decode($this->createHLSInstance('testing5'));
        if (isset($create_hls_instances->Id)) {
            echo $create_hls_instances->Id . " : " . $create_hls_instances->InstanceName;
            echo "<br>";
        }
    }

    public function createHLSInstance($pid) {
        $fields = array(
            'InstanceName' => $pid . '-live',
            'SegmentSize' => 10
        );
        $field_string = json_encode($fields);

        //open connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.edgecast.com/v2/mcc/customers/52BF3/httpstreaming/dcp/live");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: TOK:f71bfb62-4684-42fa-9e26-72aafe49968e',
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}

$instance = new dcp();
$instance->run();
?>
