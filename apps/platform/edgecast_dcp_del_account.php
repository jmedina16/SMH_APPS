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

        $this->deleteHLSInstance();
        // mark the stop time
        $stop_time = MICROTIME(TRUE);

        // get the difference in seconds
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: Done after [" . $time . "] seconds\n");
    }
   
    public function deleteHLSInstance() {
        $total = 0;
        echo "<br>";
        $hls_instances = json_decode($this->getAllInstances());
        foreach ($hls_instances as $instance) {
            echo $instance->InstanceName;
            echo "<br>";
            $total++;
        }
        echo "TOTAL: " . $total;
        echo "<br>";
    }

    public function getAllInstances() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.edgecast.com/v2/mcc/customers/52BF3/httpstreaming/dcp/live");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: TOK:f71bfb62-4684-42fa-9e26-72aafe49968e',
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}

$instance = new dcp();
$instance->run();
?>
