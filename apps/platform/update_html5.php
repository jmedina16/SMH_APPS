#!/usr/bin/php
<?php
ini_set('error_reporting', 0);
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
        $this->createJsonConf();
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

            $this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99) AND `id` IN 
                (
11798
)");

//            $this->partnerIds = $this->link->prepare("SELECT * FROM partner WHERE `status` IN (1) AND `id` NOT IN (-1,-2,-3,-4,-5,0,99)");
            
            $this->partnerIds->execute();
        } catch (PDOException $e) {
            $date = date('Y-m-d H:i:s');
            print($date . " [smhUpdateHTML5->findPartnerIds] ERROR: Could not execute query (Search PartnerIds): " . $e->getMessage() . "\n");
        }
    }

    public function createJsonConf() {
//        $total = 0;
//        foreach ($this->partnerIds->fetchAll(PDO::FETCH_OBJ) as $row) {
//            echo $row->id;
//            echo "\n\r";
//            $total++;
//        }
//        echo "TOTAL: " . $total;
//        echo "\n\r";


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
                    //if ($data->id == 6709459) {
                    $xml = simplexml_load_string($data->confFileFeatures);
                    $features = $xml->xpath('//feature');
                    $uiVars = $xml->xpath('//var');
                    $snapshot = $xml->xpath('//snapshot');
                    $sources = $xml->xpath('//source');
                    $ad_bumper = $xml->xpath('//bumper');
                    $vast_preroll = $xml->xpath('//preroll');
                    $vast_postroll = $xml->xpath('//postroll');
                    $vast_overlay = $xml->xpath('//overlay');
                    $playerConfig = $xml->xpath('//playerConfig');
                    $skipBtn = $xml->xpath('//skip');
                    $noticeMessage = $xml->xpath('//notice');
                    $theme = $xml->xpath('//theme');
                    $json = new stdClass();
                    $key = new stdClass();
                    $value = new stdClass();
                    $overrideFlashvar = new stdClass();
                    $json->plugins = '';
                    $json->uiVars = array();
                    $watermark = false;
                    $logo = false;
                    $left_counter = false;
                    $right_counter = false;
                    $fullscreen = false;
                    $largePlayBtn = false;
                    $playPauseBtn = false;
                    $volume = false;
                    $scrubber = false;
                    $caption = false;
                    $bumper = false;
                    $vast = false;
                    $autoplay = false;
                    $automute = false;
                    $preroll = false;
                    $postroll = false;
                    $overlay = false;
                    $bumper_pre = false;
                    $bumper_post = false;
                    $includeInLayout = false;
                    $uiconf = '';
                    $i = 0;
                    $width = $data->width;
                    $height = $data->height;
                    $json->layout->skin = 'kdark';
                    foreach ($snapshot as $id) {
                        $uiconf = $id['fullPlayerId'];
                    }
                    foreach ($uiVars as $vars) {
                        if ($vars['key'] == 'mylogo.plugin' && $vars['value'] == 'true') {
                            $json->plugins->logo->plugin = true;
                            $logo = true;
                        }
                        if ($vars['key'] == 'mylogo.plugin' && $vars['value'] == 'false') {
                            $json->plugins->logo->plugin = false;
                        }
                        if ($logo) {
                            if ($vars['key'] == 'mylogo.watermarkPath') {
                                $value = (String) $vars['value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->logo->img = $value;
                                }
                            }
                            if ($vars['key'] == 'mylogo.watermarkClickPath') {
                                $value = (String) $vars['value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->logo->href = $value;
                                }
                            }
                            $json->plugins->logo->title = 'my logo';
                        }

                        if ($vars['key'] == 'autoPlay' && $vars['value'] == 'true') {
                            array_push($json->uiVars, array('key' => 'autoPlay', 'value' => true, 'overrideFlashvar' => false));
                            $autoplay = true;
                        }
                        if ($vars['key'] == 'autoPlay' && $vars['value'] == 'false') {
                            array_push($json->uiVars, array('key' => 'autoPlay', 'value' => false, 'overrideFlashvar' => false));
                            $autoplay = true;
                        }

                        if ($vars['key'] == 'autoMute' && $vars['value'] == 'true') {
                            array_push($json->uiVars, array('key' => 'autoMute', 'value' => true, 'overrideFlashvar' => false));
                            $automute = true;
                        }
                        if ($vars['key'] == 'autoMute' && $vars['value'] == 'false') {
                            array_push($json->uiVars, array('key' => 'autoMute', 'value' => false, 'overrideFlashvar' => false));
                            $automute = true;
                        }

                        if ($uiconf == '6709439' || $uiconf == '6709440' || $uiconf == '6709441') {
                            if ($vars['key'] == 'playlistAPI.autoContinue' && $vars['value'] == 'true') {
                                $json->plugins->playlistAPI['autoContinue'] = true;
                            }
                            if ($vars['key'] == 'playlistAPI.autoContinue' && $vars['value'] == 'false') {
                                $json->plugins->playlistAPI['autoContinue'] = false;
                            }

                            if ($vars['key'] == 'playlistAPI.loop' && $vars['value'] == 'true') {
                                $json->plugins->playlistAPI['loop'] = true;
                            }
                            if ($vars['key'] == 'playlistAPI.loop' && $vars['value'] == 'false') {
                                $json->plugins->playlistAPI['loop'] = false;
                            }

                            if ($vars['key'] == 'playlistHolder.visible' && $vars['value'] == 'true') {
                                $json->plugins->playlistAPI['includeInLayout'] = true;
                                $includeInLayout = true;
                            }
                            if ($vars['key'] == 'playlistHolder.visible' && $vars['value'] == 'false') {
                                $json->plugins->playlistAPI['includeInLayout'] = false;
                                $includeInLayout = true;
                            }
                        }
                        if ($uiconf == '6709441') {
                            if (preg_match('/EntryId/', $vars['key'])) {
                                $json->plugins->playlistAPI['kpl' . $i . 'Id'] = (String) $vars['value'];
                            }
                            if (preg_match('/Name/', $vars['key'])) {
                                $json->plugins->playlistAPI['kpl' . $i . 'Name'] = (String) $vars['value'];
                                $i++;
                            }
                        }
                    }
                    array_push($json->uiVars, array('key' => 'enableTooltips', 'value' => true, 'overrideFlashvar' => false));
                    array_push($json->uiVars, array('key' => 'adsOnReplay', 'value' => true, 'overrideFlashvar' => false));
                    if (!$logo) {
                        $json->plugins->logo->plugin = false;
                    }
                    if (!$autoplay) {
                        array_push($json->uiVars, array('key' => 'autoPlay', 'value' => false, 'overrideFlashvar' => false));
                    }
                    if (!$automute) {
                        array_push($json->uiVars, array('key' => 'autoMute', 'value' => false, 'overrideFlashvar' => false));
                    }
                    if ($uiconf == '6709439' || $uiconf == '6709440' || $uiconf == '6709441') {
                        if (!$includeInLayout) {
                            $json->plugins->playlistAPI['includeInLayout'] = true;
                        }
                    }

                    foreach ($features as $feature) {
                        if ($feature['k_fullName'] == 'TopTitleScreen' && $feature['k_value'] == 'true') {
                            $json->plugins->titleLabel->plugin = true;
                            $json->plugins->titleLabel->text = '{mediaProxy.entry.name}';
                            $json->plugins->titleLabel->align = 'left';
                            $json->plugins->titleLabel->truncateLongTitles = false;
                        }

                        if ($feature['k_fullName'] == 'watermark' && $feature['k_value'] == 'true') {
                            $json->plugins->watermark->plugin = true;
                            $watermark = true;
                        }
                        if ($watermark) {
                            if ($feature['k_fullName'] == 'watermark.watermarkPosition') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->watermark->cssClass = $value;
                                }
                            }
                            if ($feature['k_fullName'] == 'watermark.watermarkPath') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->watermark->img = $value;
                                }
                            }
                            if ($feature['k_fullName'] == 'watermark.watermarkClickPath') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->watermark->href = $value;
                                }
                            }
                            if ($feature['k_fullName'] == 'watermark.padding') {
                                $value = (int) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->watermark->padding = $value;
                                }
                            }
                        }

                        if ($feature['k_fullName'] == 'timerControllerScreen1' && $feature['k_value'] == 'true') {
                            $json->plugins->currentTimeLabel->plugin = true;
                            $left_counter = true;
                        }
                        if ($feature['k_fullName'] == 'timerControllerScreen1' && $feature['k_value'] == 'false') {
                            $json->plugins->currentTimeLabel->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'timerControllerScreen2' && $feature['k_value'] == 'true') {
                            $json->plugins->durationLabel->plugin = true;
                            $right_counter = true;
                        }
                        if ($feature['k_fullName'] == 'timerControllerScreen2' && $feature['k_value'] == 'false') {
                            $json->plugins->durationLabel->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'flavorComboControllerScreen' && $feature['k_value'] == 'true') {
                            $json->plugins->sourceSelector->plugin = true;
                            $json->plugins->sourceSelector->switchOnResize = false;
                            $json->plugins->sourceSelector->simpleFormat = true;
                            array_push($json->uiVars, array('key' => 'mediaProxy.preferedFlavorBR', 'value' => 1600));
                        }

                        if ($feature['k_fullName'] == 'fullScreenBtn' && $feature['k_value'] == 'true') {
                            $json->plugins->fullScreenBtn->plugin = true;
                            $fullscreen = true;
                        }
                        if ($feature['k_fullName'] == 'fullScreenBtn' && $feature['k_value'] == 'false') {
                            $json->plugins->fullScreenBtn->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'onVideoPlayBtn' && $feature['k_value'] == 'true') {
                            $json->plugins->largePlayBtn->plugin = true;
                            $largePlayBtn = true;
                        }
                        if ($feature['k_fullName'] == 'onVideoPlayBtn' && $feature['k_value'] == 'false') {
                            $json->plugins->largePlayBtn->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'playBtn' && $feature['k_value'] == 'true') {
                            $json->plugins->playPauseBtn->plugin = true;
                            $playPauseBtn = true;
                        }
                        if ($feature['k_fullName'] == 'playBtn' && $feature['k_value'] == 'false') {
                            $json->plugins->playPauseBtn->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'volumeBar' && $feature['k_value'] == 'true') {
                            $json->plugins->volumeControl->plugin = true;
                            $json->plugins->volumeControl->showSlider = true;
                            $json->plugins->volumeControl->pinVolumeBar = false;
                            $volume = true;
                        }
                        if ($feature['k_fullName'] == 'volumeBar' && $feature['k_value'] == 'false') {
                            $json->plugins->volumeControl->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'scrubberContainer' && $feature['k_value'] == 'true') {
                            $json->plugins->scrubber->plugin = true;
                            $scrubber = true;
                        }
                        if ($feature['k_fullName'] == 'scrubberContainer' && $feature['k_value'] == 'false') {
                            $json->plugins->scrubber->plugin = false;
                        }

                        if ($feature['k_fullName'] == 'shareBtn' && $feature['k_value'] == 'true') {
                            $json->plugins->share->plugin = true;
                            $json->plugins->share->parent = 'controlsContainer';
                            $json->plugins->share->align = 'right';
                            $json->plugins->share->socialShareURL = 'smart';
                            $json->plugins->share->socialNetworks = 'facebook,twitter,googleplus,linkedin,email';
                            $json->plugins->share->socialShareEnabled = true;
                            $json->plugins->share->embedEnabled = true;
                            $json->plugins->share->allowTimeOffset = true;
                            $json->plugins->share->allowSecuredEmbed = true;
                            $json->plugins->share->emailEnabled = true;
                            $json->plugins->share->shareUiconfID = '';
                            $json->plugins->share->shareConfig->facebook->name = 'Facebook';
                            $json->plugins->share->shareConfig->facebook->icon = '';
                            $json->plugins->share->shareConfig->facebook->cssClass = 'icon-share-facebook';
                            $json->plugins->share->shareConfig->facebook->template = 'https://www.facebook.com/sharer/sharer.php?u={share.shareURL}';
                            $json->plugins->share->shareConfig->facebook->redirectUrl = 'fb://feed/';
                            $json->plugins->share->shareConfig->twitter->name = 'Twitter';
                            $json->plugins->share->shareConfig->twitter->icon = '';
                            $json->plugins->share->shareConfig->twitter->cssClass = 'icon-share-twitter';
                            $json->plugins->share->shareConfig->twitter->template = 'https://twitter.com/share?url={share.shareURL}';
                            $json->plugins->share->shareConfig->twitter->redirectUrl = 'https://twitter.com/intent/tweet/complete?,https://twitter.com/intent/tweet/update';
                            $json->plugins->share->shareConfig->googleplus->name = 'Google+';
                            $json->plugins->share->shareConfig->googleplus->icon = '';
                            $json->plugins->share->shareConfig->googleplus->cssClass = 'icon-share-google';
                            $json->plugins->share->shareConfig->googleplus->template = 'https://plus.google.com/share?url={share.shareURL}';
                            $json->plugins->share->shareConfig->googleplus->redirectUrl = 'https://plus.google.com/app/basic/stream';
                            $json->plugins->share->shareConfig->email->name = 'Email';
                            $json->plugins->share->shareConfig->email->icon = '';
                            $json->plugins->share->shareConfig->email->cssClass = 'icon-share-email';
                            $json->plugins->share->shareConfig->email->template = 'mailto:?subject=Check out {mediaProxy.entry.name}&body=Check out {mediaProxy.entry.name}: {share.shareURL}';
                            $json->plugins->share->shareConfig->email->redirectUrl = '';
                            $json->plugins->share->shareConfig->linkedin->name = 'LinkedIn';
                            $json->plugins->share->shareConfig->linkedin->icon = '';
                            $json->plugins->share->shareConfig->linkedin->cssClass = 'icon-share-linkedin';
                            $json->plugins->share->shareConfig->linkedin->template = 'http://www.linkedin.com/shareArticle?mini=true&url={share.shareURL}';
                            $json->plugins->share->shareConfig->linkedin->redirectUrl = '';
                            $json->plugins->share->shareConfig->sms->name = 'SMS';
                            $json->plugins->share->shareConfig->sms->icon = '';
                            $json->plugins->share->shareConfig->sms->cssClass = 'icon-share-sms';
                            $json->plugins->share->shareConfig->sms->template = 'Check out {mediaProxy.entry.name}: {share.shareURL}';
                            $json->plugins->share->shareConfig->sms->redirectUrl = '';
                            $json->plugins->share->embedOptions->streamerType = 'auto';
                            $json->plugins->share->embedOptions->uiconfID = '';
                            $json->plugins->share->embedOptions->width = (int) $width;
                            $json->plugins->share->embedOptions->height = (int) $height;
                            $json->plugins->share->embedOptions->borderWidth = 0;
                        }

                        if ($feature['k_fullName'] == 'downloadBtn' && $feature['k_value'] == 'true') {
                            $json->plugins->download->plugin = true;
                            $json->plugins->download->parent = 'controlsContainer';
                            $json->plugins->download->align = 'right';
                            $json->plugins->download->flavorID = '';
                            $json->plugins->download->preferredBitrate = '';
                        }

                        if ($feature['k_fullName'] == 'captureThumbBtn' && $feature['k_value'] == 'true') {
                            $json->plugins->captureThumbnail->plugin = true;
                            $json->plugins->captureThumbnail->tooltip = 'Capture Thumbnail';
                        }

                        if ($feature['k_fullName'] == 'ccOverComboBoxWrapper' && $feature['k_value'] == 'true') {
                            $json->plugins->closedCaptions->plugin = true;
                            $json->plugins->closedCaptions->layout = 'ontop';
                            $json->plugins->closedCaptions->displayCaptions = true;
                            $json->plugins->closedCaptions->hideWhenEmpty = false;
                            $json->plugins->closedCaptions->showEmbeddedCaptions = false;
                            $caption = true;
                        }
                        if ($caption) {
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.fontColor') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->fontColor = str_replace("0x", "#", $value);
                                }
                            }
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.ccOverRG') {
                                $value = (Boolean) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->useGlow = $value;
                                }
                            }
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.ccOverGlowColor') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->glowColor = str_replace("0x", "#", $value);
                                }
                            }
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.ccOverGlowBlur') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->glowBlur = str_replace("0x", "#", $value);
                                }
                            }
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.fontFamily') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->fontFamily = $value;
                                }
                            }
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.fontsize') {
                                $value = (int) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->fontsize = $value;
                                }
                            }
                            if ($feature['k_fullName'] == 'ccOverComboBoxWrapper.bgColor') {
                                $value = (String) $feature['k_value'];
                                if (isset($value) && $value !== '') {
                                    $json->plugins->closedCaptions->bg = str_replace("0x", "#", $value);
                                }
                            }
                        }

                        if ($feature['k_fullName'] == 'ccOverComboBoxWrapper' && $feature['k_value'] == 'true') {
                            $json->plugins->closedCaptions->plugin = true;
                            $json->plugins->closedCaptions->layout = 'ontop';
                            $json->plugins->closedCaptions->displayCaptions = true;
                            $json->plugins->closedCaptions->hideWhenEmpty = false;
                            $json->plugins->closedCaptions->showEmbeddedCaptions = false;
                        }
                    }

                    foreach ($sources as $source) {
                        if ($source['id'] == 'bumperOnly' && $source['selected'] == 'true') {
                            $json->plugins->bumper->plugin = true;
                            $json->plugins->bumper->lockUI = true;
                            $preSequence = (int) $source['preSequence'];
                            if (isset($preSequence) && $preSequence !== '') {
                                $json->plugins->bumper->preSequence = $preSequence;
                                $bumper_pre = true;
                            }
                            $postSequence = (int) $source['postSequence'];
                            if (isset($postSequence) && $postSequence !== '') {
                                $json->plugins->bumper->postSequence = $postSequence;
                                $bumper_post = true;
                            }
                            $bumper = true;
                        }
                    }
                    foreach ($ad_bumper as $bumper_attr) {
                        if ($bumper_attr['enabled'] == 'true') {
                            $json->plugins->bumper->plugin = true;
                            $json->plugins->bumper->lockUI = true;
                            if (!$bumper_pre && !$bumper_post) {
                                $json->plugins->bumper->preSequence = 1;
                                $json->plugins->bumper->postSequence = 0;
                            }
                            $bumper = true;
                        }
                    }
                    foreach ($vast_preroll as $vast_preroll_attr) {
                        if ($vast_preroll_attr['enabled'] == 'true') {
                            $preroll = true;
                        }
                    }
                    foreach ($vast_postroll as $vast_postroll_attr) {
                        if ($vast_postroll_attr['enabled'] == 'true') {
                            $postroll = true;
                        }
                    }
                    foreach ($vast_overlay as $vast_overlay_attr) {
                        if ($vast_overlay_attr['enabled'] == 'true') {
                            $overlay = true;
                        }
                    }

                    if ($preroll || $postroll || $overlay) {
                        foreach ($sources as $source) {
                            if ($source['id'] == 'vastAdServer' && $source['selected'] == 'true') {
                                $json->plugins->vast->plugin = true;
                                $json->plugins->vast->pauseAdOnClick = false;
                                $json->plugins->vast->enableCORS = false;
                                $json->plugins->vast->loadAdsOnPlay = false;
                                $preSequence = (int) $source['preSequence'];
                                if (isset($preSequence) && $preSequence !== '') {
                                    $json->plugins->vast->preSequence = $preSequence;
                                }
                                $postSequence = (int) $source['postSequence'];
                                if (isset($postSequence) && $postSequence !== '') {
                                    $json->plugins->vast->postSequence = $postSequence;
                                }
                            }
                        }
                        foreach ($vast_preroll as $vast_preroll_attr) {
                            $numPreroll = (int) $vast_preroll_attr['nads'];
                            if (isset($numPreroll) && $numPreroll !== '') {
                                $json->plugins->vast->numPreroll = $numPreroll;
                            } else {
                                $json->plugins->vast->numPreroll = 0;
                            }
                            $prerollStartWith = (int) $vast_preroll_attr['start'];
                            if (isset($prerollStartWith) && $prerollStartWith !== '') {
                                $json->plugins->vast->prerollStartWith = $prerollStartWith;
                            } else {
                                $json->plugins->vast->prerollStartWith = 0;
                            }
                            $prerollInterval = (int) $vast_preroll_attr['frequency'];
                            if (isset($prerollInterval) && $prerollInterval !== '') {
                                $json->plugins->vast->prerollInterval = $prerollInterval;
                            } else {
                                $json->plugins->vast->prerollInterval = 0;
                            }

                            $json->plugins->vast->prerollUrl = (String) $vast_preroll_attr['url'];
                        }
                        foreach ($vast_postroll as $vast_postroll_attr) {
                            $numPostroll = (int) $vast_postroll_attr['nads'];
                            if (isset($numPostroll) && $numPostroll !== '') {
                                $json->plugins->vast->numPostroll = $numPostroll;
                            } else {
                                $json->plugins->vast->numPostroll = 0;
                            }
                            $postrollStartWith = (int) $vast_postroll_attr['start'];
                            if (isset($postrollStartWith) && $postrollStartWith !== '') {
                                $json->plugins->vast->postrollStartWith = $postrollStartWith;
                            } else {
                                $json->plugins->vast->postrollStartWith = 0;
                            }
                            $postrollInterval = (int) $vast_postroll_attr['frequency'];
                            if (isset($postrollInterval) && $postrollInterval !== '') {
                                $json->plugins->vast->postrollInterval = $postrollInterval;
                            } else {
                                $json->plugins->vast->postrollInterval = 0;
                            }

                            $json->plugins->vast->postrollUrl = (String) $vast_postroll_attr['url'];
                        }
                        foreach ($vast_overlay as $vast_overlay_attr) {
                            $overlayStartAt = (int) $vast_overlay_attr['start'];
                            if (isset($overlayStartAt) && $overlayStartAt !== '') {
                                $json->plugins->vast->overlayStartAt = $overlayStartAt;
                            } else {
                                $json->plugins->vast->overlayStartAt = 0;
                            }
                            $overlayInterval = (int) $vast_overlay_attr['frequency'];
                            if (isset($overlayInterval) && $overlayInterval !== '') {
                                $json->plugins->vast->overlayInterval = $overlayInterval;
                            } else {
                                $json->plugins->vast->overlayInterval = 0;
                            }
                            $json->plugins->vast->trackCuePoints = true;
                            $json->plugins->vast->htmlCompanions = '';

                            $json->plugins->vast->overlayUrl = (String) $vast_overlay_attr['url'];
                        }
                        foreach ($playerConfig as $vast_overlay_timeout_attr) {
                            $timeout = (int) $vast_overlay_timeout_attr['timeout'];
                            if (isset($timeout) && $timeout !== '') {
                                $json->plugins->vast->timeout = $timeout;
                            } else {
                                $json->plugins->vast->timeout = 0;
                            }
                        }
                        foreach ($skipBtn as $vast_skipBtn_attr) {
                            if ($vast_skipBtn_attr['enabled'] == 'true') {
                                $json->plugins->skipBtn->plugin = true;
                                $json->plugins->skipBtn->label = (String) $vast_skipBtn_attr['label'];
                                $json->plugins->skipBtn->skipOffset = 0;
                            }
                        }
                        foreach ($noticeMessage as $vast_noticeMessage_attr) {
                            if ($vast_noticeMessage_attr['enabled'] == 'true') {
                                $json->plugins->noticeMessage->plugin = true;
                                $json->plugins->noticeMessage->text = 'Video will start in {sequenceProxy.timeRemaining} seconds';
                            }
                        }
                    }
                    if ($bumper) {
                        foreach ($ad_bumper as $bumper_attr) {
                            $entryid = (String) $bumper_attr['entryid'];
                            if (isset($entryid) && $entryid !== '') {
                                $json->plugins->bumper->bumperEntryID = $entryid;
                            }
                            $clickurl = (String) $bumper_attr['clickurl'];
                            if (isset($clickurl) && $clickurl !== '') {
                                $json->plugins->bumper->clickurl = $clickurl;
                            }
                        }
                        foreach ($skipBtn as $vast_skipBtn_attr) {
                            if ($vast_skipBtn_attr['enabled'] == 'true') {
                                $json->plugins->skipBtn->plugin = true;
                                $json->plugins->skipBtn->label = (String) $vast_skipBtn_attr['label'];
                                $json->plugins->skipBtn->skipOffset = 0;
                            }
                        }
                    }

                    if (!$left_counter) {
                        $json->plugins->currentTimeLabel->plugin = true;
                    }
                    if (!$right_counter) {
                        $json->plugins->durationLabel->plugin = true;
                    }
                    if (!$fullscreen) {
                        $json->plugins->fullScreenBtn->plugin = true;
                    }
                    if (!$largePlayBtn) {
                        $json->plugins->largePlayBtn->plugin = true;
                    }
                    if (!$playPauseBtn) {
                        $json->plugins->playPauseBtn->plugin = true;
                    }
                    if (!$volume) {
                        $json->plugins->volumeControl->plugin = true;
                        $json->plugins->volumeControl->showSlider = true;
                        $json->plugins->volumeControl->pinVolumeBar = false;
                    }
                    if (!$scrubber) {
                        $json->plugins->scrubber->plugin = true;
                    }

                    $json->plugins->controlBarContainer->plugin = true;
                    if ($uiconf == '6709442') {
                        $json->plugins->controlBarContainer->hover = true;
                    }

                    $color1 = ((int) $theme[0]->color1 == 14540253) ? '#ffffff' : '#' . dechex((String) $theme[0]->color1);
                    //$color3 = ((int) $theme[0]->color3 == 3355443) ? '#000000' : '#' . dechex((String) $theme[0]->color3);
                    if (strlen($color1) != 7) {
                        $color1 = '#ffffff';
                    }

                    $json->plugins->theme->plugin = true;
                    $json->plugins->theme->applyToLargePlayButton = true;
                    $json->plugins->theme->buttonsIconColorDropShadow = true;
                    $json->plugins->theme->buttonsSize = 12;
                    $json->plugins->theme->buttonsColor = '#000000';
                    $json->plugins->theme->buttonsIconColor = $color1;
                    $json->plugins->theme->controlsBkgColor = '#000000';
                    $json->plugins->theme->sliderColor = '#333333';
                    $json->plugins->theme->scrubberColor = $color1;
                    $json->plugins->theme->watchedSliderColor = '#2ec7e1';
                    $json->plugins->theme->bufferedSliderColor = '#afafaf';

                    $json->plugins->loadingSpinner->plugin = true;
                    $json->plugins->loadingSpinner->imageUrl = '';
                    $json->plugins->loadingSpinner->lines = 10;
                    $json->plugins->loadingSpinner->lineLength = 10;
                    $json->plugins->loadingSpinner->width = 6;
                    $json->plugins->loadingSpinner->radius = 12;
                    $json->plugins->loadingSpinner->corners = 1;
                    $json->plugins->loadingSpinner->rotate = 0;
                    $json->plugins->loadingSpinner->direction = 1;
                    $json->plugins->loadingSpinner->color = 'rgb(0,154,218)|rgb(255,221,79)|rgb(0,168,134)|rgb(233,44,46)|rgb(181,211,52)|rgb(252,237,0)|rgb(0,180,209)|rgb(117,192,68)|rgb(232,44,46)|rgb(250,166,26)|rgb(0,154,218)|rgb(232,44,46)|rgb(255,221,79)|rgb(117,192,68)|rgb(232,44,46)';
                    $json->plugins->loadingSpinner->speed = 1.6;
                    $json->plugins->loadingSpinner->trail = 100;
                    $json->plugins->loadingSpinner->shadow = false;
                    $json->plugins->loadingSpinner->className = 'spinner';
                    $json->plugins->loadingSpinner->zIndex = 2000000000;
                    $json->plugins->loadingSpinner->top = 'auto';
                    $json->plugins->loadingSpinner->left = 'auto';

                    $json->plugins->topBarContainer->plugin = true;
                    $json->plugins->playHead->plugin = true;
                    $json->plugins->liveCore->plugin = true;
                    $json->plugins->liveStatus->plugin = true;
                    $json->plugins->liveBackBtn->plugin = true;
                    $json->plugins->moderation->plugin = false;

                    if ($uiconf == '6709439' || $uiconf == '6709440' || $uiconf == '6709441') {
                        $json->plugins->playlistAPI['plugin'] = true;
                        $json->plugins->playlistAPI['showControls'] = true;
                        $json->plugins->playlistAPI['includeHeader'] = true;
                        $json->plugins->playlistAPI['autoPlay'] = false;
                        $json->plugins->playlistAPI['hideClipPoster'] = false;
                    }
                    if ($uiconf == '6709439' || $uiconf == '6709441') {
                        $json->plugins->playlistAPI['containerPosition'] = 'right';
                        $json->plugins->playlistAPI['layout'] = 'vertical';
                    }
                    if ($uiconf == '6709440') {
                        $json->plugins->playlistAPI['containerPosition'] = 'bottom';
                        $json->plugins->playlistAPI['layout'] = 'vertical';
                    }

//                    echo $data->id;
//                    echo "\n\r";
//                    echo json_encode($json, JSON_UNESCAPED_SLASHES);
//                    echo "\n\r";

                    $conf = json_encode($json, JSON_UNESCAPED_SLASHES);
                    $result = $this->updateUiconf($ks, $partnerid, $data->id, $conf);
                    print_r($result);
                    //}
                    $total++;
                }
            }

            echo 'Total: ' . $total;
            echo "\n\r";
        }
    }

    public function updateUiconf($ks, $pid, $id, $conf) {
        $config = new KalturaConfiguration($pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($ks);
        $uiConf = new KalturaUiConf();
        $uiConf->config = $conf;
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

$instance = new smhUpdateHTML5();
$instance->run();
?>
