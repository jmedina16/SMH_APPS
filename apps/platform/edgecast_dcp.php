#!/usr/bin/php
<?php
ini_set('error_reporting', 1);
ini_set('memory_limit', '1024M'); // or you could use 1G
require_once('../../app/clients/php5/KalturaClient.php');

class smhUpdateHTML5 {

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
        $this->makeAPIcall();
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

            $this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1,2) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99) AND `id` IN 
                (
10012
)");

//            $this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99)");

            $this->partnerIds->execute();
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [smhUpdateHTML5->findPartnerIds] ERROR: Could not execute query (Search PartnerIds): " . $e->getMessage() . "\n");
        }
    }

    public function createHLSInstances() {
        $total = 0;
        foreach ($this->partnerIds->fetchAll(PDO::FETCH_OBJ) as $row) {
            echo $row->id;
            echo "\n\r";
            $total++;
        }
        echo "TOTAL: " . $total;
        echo "\n\r";
    }

    public function makeAPIcall() {
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
        error_log(print_r($output, true));
        return $output;
    }

    public function closeConnection() {
        // Closing connection
        $this->link = null;
    }

}

$instance = new smhUpdateHTML5();
$instance->run();
?>
