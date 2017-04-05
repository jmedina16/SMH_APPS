<?php

error_reporting(0);
$tz = $_POST['tz'];
date_default_timezone_set($tz);
require_once('../../app/clients/php5/KalturaClient.php');

class playerStatsTables {

    protected $action;
    protected $ks;
    protected $start;
    protected $length;
    protected $draw;
    protected $offset;
    protected $from;
    protected $to;
    protected $objectId;

    public function __construct() {
        $this->action = $_POST["action"];
        $this->ks = $_POST["ks"];
        $this->start = $_POST["start"];
        $this->length = $_POST["length"];
        $this->draw = $_POST["draw"];
        $this->offset = $_POST["offset"];
        $this->from = $_POST["from"];
        $this->to = $_POST["to"];
        $this->objectId = $_POST["objectId"];
    }

    //run
    public function run() {
        switch ($this->action) {
            case "top_content":
                $this->topContent();
                break;
            case "dropoff_content":
                $this->dfContent();
                break;
            case "geo_map":
                $this->geoContent();
                break;
            case "platforms":
                $this->platformsContent();
                break;
            case "platforms_bar":
                $this->platformsBarContent();
                break;
            case "os_bar":
                $this->osBarContent();
                break;
            case "browser_bar":
                $this->browserBarContent();
                break;
            case "live":
                $this->liveContent();
                break;
            default:
                echo "Action not found!";
        }
    }

    public function decimal_to_time($decimal) {
        $pre_h = floor($decimal * 60);
        $hours = floor($pre_h / 3600);
        $minutes = floor((int) $decimal % 60);
        $seconds = $decimal - (int) $decimal;
        $seconds = round($seconds * 60);

        $final_hours = ($hours == 0) ? '' : str_pad($hours, 2, "0", STR_PAD_LEFT) . ":";

        return $final_hours . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
    }

    public function rectime($secs) {
        $hr = floor($secs / 3600);
        $min = floor(($secs - ($hr * 3600)) / 60);
        $sec = $secs - ($hr * 3600) - ($min * 60);

        if ($hr < 10) {
            $hr = "0" . $hr;
        }
        if ($min < 10) {
            $min = "0" . $min;
        }
        if ($sec < 10) {
            $sec = "0" . $sec;
        }
        $hr_result = ($hr == "00") ? '' : $hr . ':';
        return $hr_result . $min . ':' . $sec;
    }

    public function topContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::TOP_CONTENT;
        $order = 'count_plays';
        $objectIds = null;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        $entryIds = array();
        foreach ($table_data as $entry) {
            array_push($entryIds, $entry['object_id']);
        }

        $entryIds = implode(",", $entryIds);
        $filter = new KalturaBaseEntryFilter();
        $filter->idIn = $entryIds;
        $filter->statusIn = '2,3,8';
        $pager = null;
        $entryids_result = $client->baseEntry->listAction($filter, $pager);

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }

            if ($entry['count_loads'] == '' || $entry['count_loads'] == null) {
                $count_loads = 0;
            } else {
                $count_loads = number_format($entry['count_loads']);
            }

            $entry_name = '';
            foreach ($entryids_result->objects as $entryid) {
                if ($entry['object_id'] == $entryid->id) {
                    $time = $this->rectime($entryid->duration);
                    $unixtime_to_date = date('n/j/Y H:i', $entryid->createdAt);
                    $newDatetime = strtotime($unixtime_to_date);
                    $newDatetime = date('m/d/Y h:i A', $newDatetime);
                    $entry_name = '<a onclick="smhStats.videoDetailTopContent(\'' . $entryid->id . '\',\'' . htmlspecialchars(addslashes($entryid->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entryid->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entryid->tags), ENT_QUOTES) . '\',\'' . $time . '\',\'' . $newDatetime . '\')">' . $entry['entry_name'] . "</a>";
                }
            }

            $row[] = "<div class='data-break'>" . $entry_name . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['sum_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['avg_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_loads . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['load_play_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['avg_view_drop_off'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . $entry['object_id'] . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function dfContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::CONTENT_DROPOFF;
        $order = 'count_plays';
        $objectIds = null;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        $entryIds = array();
        foreach ($table_data as $entry) {
            array_push($entryIds, $entry['object_id']);
        }

        $entryIds = implode(",", $entryIds);
        $filter = new KalturaBaseEntryFilter();
        $filter->idIn = $entryIds;
        $filter->statusIn = '2,3';
        $pager = null;
        $entryids_result = $client->baseEntry->listAction($filter, $pager);

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }
            if ($entry['count_plays_25'] == '' || $entry['count_plays_25'] == null) {
                $count_plays_25 = 0;
            } else {
                $count_plays_25 = number_format($entry['count_plays_25']);
            }
            if ($entry['count_plays_50'] == '' || $entry['count_plays_50'] == null) {
                $count_plays_50 = 0;
            } else {
                $count_plays_50 = number_format($entry['count_plays_50']);
            }
            if ($entry['count_plays_75'] == '' || $entry['count_plays_75'] == null) {
                $count_plays_75 = 0;
            } else {
                $count_plays_75 = number_format($entry['count_plays_75']);
            }
            if ($entry['count_plays_100'] == '' || $entry['count_plays_100'] == null) {
                $count_plays_100 = 0;
            } else {
                $count_plays_100 = number_format($entry['count_plays_100']);
            }

            $entry_name = '';
            foreach ($entryids_result->objects as $entryid) {
                if ($entry['object_id'] == $entryid->id) {
                    $time = $this->rectime($entryid->duration);
                    $unixtime_to_date = date('n/j/Y H:i', $entryid->createdAt);
                    $newDatetime = strtotime($unixtime_to_date);
                    $newDatetime = date('m/d/Y h:i A', $newDatetime);
                    $entry_name = '<a onclick="smhStats.videoDetailDropOff(\'' . $entryid->id . '\',\'' . htmlspecialchars(addslashes($entryid->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entryid->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entryid->tags), ENT_QUOTES) . '\',\'' . $time . '\',\'' . $newDatetime . '\')">' . $entry['entry_name'] . "</a>";
                }
            }

            $row[] = "<div class='data-break'>" . $entry_name . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_25 . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_50 . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_75 . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_100 . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['play_through_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . $entry['object_id'] . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function geoContent() {
        $countries_text = array(
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Island',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo (DRC)',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CI' => 'Cate dlvoire',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Eqypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'EU' => 'EU',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Islas Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji Islands',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern and Antarctic Lands',
            'GA' => 'Gabon',
            'GM' => 'Gambia, The',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao SAR',
            'MK' => 'Macedonia, Former Yugoslav Republic of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger>',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'MF' => 'Saint Martin',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'CS' => 'Serbia and Montenegro',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'KN' => 'St. Kitts and Nevis',
            'LC' => 'St. Lucia',
            'PM' => 'St. Pierre and Miquelon',
            'VC' => 'St. Vincent and the Grenadines',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'ST' => 'So Tom and Principe',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'UK' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'VI' => 'Virgin Islands',
            'VG' => 'Virgin Islands, British',
            'WF' => 'Walls and Futuna',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'ZZ' => 'Unknown'
        );
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::MAP_OVERLAY;
        $order = 'count_plays';
        $objectIds = $this->country_id;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }
            if ($entry['count_plays_25'] == '' || $entry['count_plays_25'] == null) {
                $count_plays_25 = 0;
            } else {
                $count_plays_25 = number_format($entry['count_plays_25']);
            }
            if ($entry['count_plays_50'] == '' || $entry['count_plays_50'] == null) {
                $count_plays_50 = 0;
            } else {
                $count_plays_50 = number_format($entry['count_plays_50']);
            }
            if ($entry['count_plays_75'] == '' || $entry['count_plays_75'] == null) {
                $count_plays_75 = 0;
            } else {
                $count_plays_75 = number_format($entry['count_plays_75']);
            }
            if ($entry['count_plays_100'] == '' || $entry['count_plays_100'] == null) {
                $count_plays_100 = 0;
            } else {
                $count_plays_100 = number_format($entry['count_plays_100']);
            }

            $row[] = "<div class='data-break'>" . $countries_text[$entry['country']] . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_25 . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_50 . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_75 . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays_100 . '%' . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['play_through_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . $entry['object_id'] . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function platformsContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::PLATFORMS;
        $order = 'count_plays';
        $objectIds = null;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }

            if ($entry['count_loads'] == '' || $entry['count_loads'] == null) {
                $count_loads = 0;
            } else {
                $count_loads = number_format($entry['count_loads']);
            }

            $device_name = '<a onclick="smhStats.deviceDetailPlatform(\'' . $entry['device'] . '\')">' . str_replace("_", " ", $entry['device']) . "</a>";

            $row[] = "<div class='data-break'>" . $device_name . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['sum_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['avg_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_loads . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['load_play_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['avg_view_drop_off'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . $entry['object_id'] . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function platformsBarContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::PLATFORMS;
        $order = 'count_plays';
        $objectIds = $this->objectId;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }

            if ($entry['count_loads'] == '' || $entry['count_loads'] == null) {
                $count_loads = 0;
            } else {
                $count_loads = number_format($entry['count_loads']);
            }

            $row[] = "<div class='data-break'>" . str_replace("_", " ", $entry['os']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['sum_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['avg_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_loads . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['load_play_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['avg_view_drop_off'] * 100, 2) . '%' . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function osBarContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::OPERATION_SYSTEM;
        $order = 'count_plays';
        $objectIds = $this->objectId;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }

            if ($entry['count_loads'] == '' || $entry['count_loads'] == null) {
                $count_loads = 0;
            } else {
                $count_loads = number_format($entry['count_loads']);
            }

            $row[] = "<div class='data-break'>" . str_replace("_", " ", $entry['os']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['sum_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['avg_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_loads . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['load_play_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['avg_view_drop_off'] * 100, 2) . '%' . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function browserBarContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::BROWSERS;
        $order = 'count_plays';
        $objectIds = $this->objectId;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }

            if ($entry['count_loads'] == '' || $entry['count_loads'] == null) {
                $count_loads = 0;
            } else {
                $count_loads = number_format($entry['count_loads']);
            }

            $row[] = "<div class='data-break'>" . str_replace("_", " ", $entry['browser']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['sum_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $this->decimal_to_time($entry['avg_time_viewed']) . "</div>";
            $row[] = "<div class='data-break'>" . $count_loads . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['load_play_ratio'] * 100, 2) . '%' . "</div>";
            $row[] = "<div class='data-break'>" . number_format($entry['avg_view_drop_off'] * 100, 2) . '%' . "</div>";

            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function liveContent() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);

        $reportType = KalturaReportType::LIVE;
        $order = 'count_plays';
        $objectIds = $this->objectId;
        $filter = new KalturaReportInputFilter();
        $filter->fromDay = $this->from;
        $filter->toDay = $this->to;
        $filter->timeZoneOffset = $this->offset;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $result = $client->report->gettable($reportType, $filter, $pager, $order, $objectIds);

        $output = array(
            "recordsTotal" => intval($result->totalCount),
            "recordsFiltered" => intval($result->totalCount),
            "data" => array()
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        $headers = explode(",", $result->header);
        $data = explode(";", $result->data);
        $table = array();
        $table_data = array();

        for ($i = 0; $i < count($data) - 1; $i++) {
            $sub_data = explode(",", $data[$i]);
            $table = array();
            for ($x = 0; $x < count($sub_data); $x++) {
                $table[$headers[$x]] = $sub_data[$x];
            }
            $table_data[$i] = $table;
        }

        $entryIds = array();
        foreach ($table_data as $entry) {
            array_push($entryIds, $entry['object_id']);
        }

        $entryIds = implode(",", $entryIds);
        $filter = new KalturaBaseEntryFilter();
        $filter->idIn = $entryIds;
        $filter->statusIn = '2,3';
        $pager = null;
        $entryids_result = $client->baseEntry->listAction($filter, $pager);

        foreach ($table_data as $entry) {
            $row = array();
            if ($entry['count_plays'] == '' || $entry['count_plays'] == null) {
                $count_plays = 0;
            } else {
                $count_plays = number_format($entry['count_plays']);
            }

            $entry_name = '';
            foreach ($entryids_result->objects as $entryid) {
                if ($entry['object_id'] == $entryid->id) {
                    $unixtime_to_date = date('n/j/Y H:i', $entryid->createdAt);
                    $newDatetime = strtotime($unixtime_to_date);
                    $newDatetime = date('m/d/Y h:i A', $newDatetime);
                    $entry_name = '<a onclick="smhStats.liveDetail(\'' . $entryid->id . '\',\'' . htmlspecialchars(addslashes($entryid->name), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entryid->description), ENT_QUOTES) . '\',\'' . htmlspecialchars(addslashes($entryid->tags), ENT_QUOTES) . '\',\'' . $newDatetime . '\')">' . $entry['entry_name'] . "</a>";
                }
            }
            $row[] = "<div class='data-break'>" . $entry_name . "</div>";
            $row[] = "<div class='data-break'>" . $count_plays . "</div>";
            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

}

$tables = new playerStatsTables();
$tables->run();
?>