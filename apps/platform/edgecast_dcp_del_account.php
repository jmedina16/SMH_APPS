<?php

ini_set('error_reporting', 0);
ini_set('memory_limit', '1024M'); // or you could use 1G
require_once('../../app/clients/php5/KalturaClient.php');

class dcp {

    public function __construct() {
        
    }

    //run garbage collection
    public function run() {
        // mark the start time
        $start_time = MICROTIME(TRUE);

        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: HTML5 Update is running \n");

        $this->deleteHLSInstance('testing5');
        // mark the stop time
        $stop_time = MICROTIME(TRUE);

        // get the difference in seconds
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: Done after [" . $time . "] seconds\n");
    }

    public function deleteHLSInstance($pid) {
        $total = 0;
        echo "<br>";
        $hls_instances = json_decode($this->getAllInstances());
        foreach ($hls_instances as $instance) {
            if ($instance->InstanceName === $pid . '-live') {
                $response = $this->doInstanceDelete($instance->Id);
                if($response === 200){
                    echo 'Deleted!';
                    echo "<br>";
                }
            }
            $total++;
        }
        echo "TOTAL: " . $total;
        echo "<br>";
    }

    public function getAllInstances() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.edgecast.com/v2/mcc/customers/19BC0/httpstreaming/dcp/live");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: TOK:d7e4fb53-0bbf-4e6d-aa03-976ce9294a0f',
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function doInstanceDelete($id) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.edgecast.com/v2/mcc/customers/19BC0/httpstreaming/dcp/live/" . $id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: TOK:d7e4fb53-0bbf-4e6d-aa03-976ce9294a0f',
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode;
    }

}

$instance = new dcp();
$instance->run();
?>
