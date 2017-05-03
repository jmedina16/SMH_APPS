<?php

ini_set('error_reporting', 0);
ini_set('memory_limit', '1024M'); // or you could use 1G
require_once('../../app/clients/php5/KalturaClient.php');

class dcp {

    protected $link = null;
    protected $login;
    protected $password;
    protected $database;
    protected $hostname;
    protected $port;
    protected $partnerIds;

    public function __construct() {
        $this->login = 'kaltura';
        $this->password = 'nUKFRl7bE9hShpV';
        $this->database = 'kaltura';
        $this->hostname = '127.0.0.1';
        $this->port = '3306';

        // mark the start time
        $start_time = MICROTIME(TRUE);
        $this->connect();
        // mark the stop time
        $stop_time = MICROTIME(TRUE);
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->__construct] INFO: conn took [" . $time . "] seconds to mysql:host=" . $this->hostname . " port=" . $this->port . ";\n");
    }

    //run garbage collection
    public function run() {
        // mark the start time
        $start_time = MICROTIME(TRUE);

        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: HTML5 Update is running \n");

        $this->findPartnerIds();
        $this->createHLSInstances();
        // mark the stop time
        $stop_time = MICROTIME(TRUE);

        // get the difference in seconds
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->run] INFO: Done after [" . $time . "] seconds\n");
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
            print($date . " [smhUpdateHTML5->connect] ERROR: Cannot connect to database: " . $e->getMessage() . "\n");
        }
    }

    public function findPartnerIds() {
        $date = date('Y-m-d H:i:s');
        print($date . " [smhUpdateHTML5->findPartnerIds] INFO: Searching for partnerIds.. \n");

        try {
            $stmt = $this->link->prepare("SET SESSION wait_timeout = 600");
            $stmt->execute();

            $this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1,2) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99)");

            $this->partnerIds->execute();
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [smhUpdateHTML5->findPartnerIds] ERROR: Could not execute query (Search PartnerIds): " . $e->getMessage() . "\n");
        }
    }

    public function createHLSInstances() {
        $total = 0;
        echo "<br>";
        foreach ($this->partnerIds->fetchAll(PDO::FETCH_OBJ) as $row) {
            $create_hls_instances = json_decode($this->createHLSInstance($row->id));
            echo $create_hls_instances->Id . " : " . $create_hls_instances->InstanceName;
            echo "<br>";
            $total++;
        }
        echo "TOTAL: " . $total;
        echo "<br>";
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

    public function closeConnection() {
        // Closing connection
        $this->link = null;
    }

}

$instance = new dcp();
$instance->run();
?>
