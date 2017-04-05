#!/usr/bin/php
<?php
ini_set('error_reporting', 0);
ini_set('memory_limit', '1024M'); // or you could use 1G
require_once('../../app/clients/php5/KalturaClient.php');

class smhDowngradeHTML5 {

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
        print($date . " [smhDowngradeHTML5->__construct] INFO: conn took [" . $time . "] seconds to mysql:host=" . $this->hostname . " port=" . $this->port . ";\n");
    }

    //run garbage collection
    public function run() {
        // mark the start time
        $start_time = MICROTIME(TRUE);

        $date = date('Y-m-d H:i:s');
        print($date . " [smhDowngradeHTML5->run] INFO: HTML5 Update is running \n");

        $this->findPartnerIds();
        $this->getPlayers();
        // mark the stop time
        $stop_time = MICROTIME(TRUE);

        // get the difference in seconds
        $time = $stop_time - $start_time;
        $date = date('Y-m-d H:i:s');
        print($date . " [smhDowngradeHTML5->run] INFO: Done after [" . $time . "] seconds\n");
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
            print($date . " [smhDowngradeHTML5->connect] ERROR: Cannot connect to database: " . $e->getMessage() . "\n");
        }
    }

    public function findPartnerIds() {
        $date = date('Y-m-d H:i:s');
        print($date . " [smhDowngradeHTML5->findPartnerIds] INFO: Searching for partnerIds.. \n");

        try {
            $stmt = $this->link->prepare("SET SESSION wait_timeout = 600");
            $stmt->execute();

            $this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1,2) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99) AND `id` = 10012");
            //$this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1,2) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99)");
            $this->partnerIds->execute();
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [smhDowngradeHTML5->findPartnerIds] ERROR: Could not execute query (Search PartnerIds): " . $e->getMessage() . "\n");
        }
    }

    public function getPlayers() {
        foreach ($this->partnerIds->fetchAll(PDO::FETCH_OBJ) as $row) {
            $ks = $this->impersonate($row->id);
            $config = new KalturaConfiguration($row->id);
            $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
            $client = new KalturaClient($config);
            $client->setKs($ks);
            $filter = new KalturaUiConfFilter();
            $filter->orderBy = '-createdAt';
            $filter->tagsMultiLikeOr = 'kdp3,player,playlist';
            $pager = new KalturaFilterPager();
            $pager->pageSize = 200;
            $result = $client->uiConf->listAction($filter, $pager);
            $total = 0;
            foreach ($result->objects as $data) {
                if ($data->confFileFeatures !== null) {
                    if ($data->id == 6716030) {
                        $result = $this->updateUiconf($ks, $partnerid, $data->id);
                        print_r($result);
                    }
                    $total++;
                }
            }

            echo 'Total: ' . $total;
            echo "\n\r";
        }
    }

    public function updateUiconf($ks, $pid, $id) {
        $config = new KalturaConfiguration($pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($ks);
        $uiConf = new KalturaUiConf();
        $uiConf->config = ' ';
        $uiConf->html5Url = '';
        $result = $client->uiConf->update($id, $uiConf);
        return $result;
    }

    public function impersonate($pid) {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $secret = '68b329da9893e34099c7d8ad5cb9c940';
        $impersonatedPartnerId = $pid;
        $userId = null;
        $type = KalturaSessionType::ADMIN;
        $partnerId = -2;
        $expiry = 60;
        $privileges = null;
        $result = $client->session->impersonate($secret, $impersonatedPartnerId, $userId, $type, $partnerId, $expiry, $privileges);
        return $result;
    }

    public function closeConnection() {
        // Closing connection
        $this->link = null;
    }

}

$instance = new smhDowngradeHTML5();
$instance->run();
?>
