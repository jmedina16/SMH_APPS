<?php

require_once('../../app/clients/php5/KalturaClient.php');
error_reporting(0);

class reseller {

    protected $pid;
    protected $action;
    private $ks;
    private $parent_id;
    private $start;
    private $length;
    private $draw;
    protected $adminPartnerId = -2;
    protected $adminAPISecretKey = '68b329da9893e34099c7d8ad5cb9c940';

    public function __construct() {
        $this->parent_id = $_GET['parent_id'];
        $this->ks = $this->impersonate($this->parent_id);
        $this->action = $_GET['action'];
        $this->start = $_GET['start'];
        $this->length = $_GET['length'];
        $this->draw = $_GET['draw'];
    }

    //run reseller api
    public function run() {
        switch ($this->action) {
            case "get_acnts":
                $this->get_acnts();
                break;
            default:
                echo "Action not found!";
        }
    }

    public function get_acnts() {
        $partnerId = 0;
        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $client->setKs($this->ks);
        $client->startMultiRequest();

        $filter = new KalturaPartnerFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '1,2';
        $filter->idNotIn = $this->parent_id;
        $client->partner->listAction($filter, $pager);

        $filter = new KalturaPartnerFilter();
        $filter->orderBy = '-createdAt';
        $filter->statusIn = '1,2';
        $filter->idNotIn = $this->parent_id;
        $pager = new KalturaFilterPager();

        // PAGING
        if (isset($this->start) && $this->length != '-1') {
            $pager->pageSize = intval($this->length);
            $pager->pageIndex = floor(intval($this->start) / $pager->pageSize) + 1;
        }

        $client->partner->listAction($filter, $pager);
        $result = $client->doMultiRequest();

        $child_ids = array();
        foreach ($result[0]->objects as $partner) {
            array_push($child_ids, $partner->id);
        }

        $storage_total = 0;
        $bandwidth_total = 0;
        $child_stats = $this->get_child_stats($child_ids);
        $storage_total += $child_stats['child_storage'];
        $bandwidth_total += $child_stats['child_bandwidth'];
        $user_stats = $this->get_user_stats();
        $storage_total += $user_stats['storage'];
        $bandwidth_total += $user_stats['bandwidth'];
        $user_limits = $this->get_user_limits();
        $user_bandwidth_limit_gb = $user_limits['bandwidth_limit_gb'];
        $user_storage_limit_gb = $user_limits['storage_limit_gb'];

        $bandwidth_used_percentage = 0;
        if ($user_bandwidth_limit_gb > 0) {
            $bandwidth_used_percentage = number_format($this->get_used_percentage($bandwidth_total, $user_bandwidth_limit_gb), 2);
        }
        $storage_used_percentage = 0;
        if ($user_storage_limit_gb > 0) {
            $storage_used_percentage = number_format($this->get_used_percentage($storage_total, $user_storage_limit_gb), 2);
        }

        $user_data_transfer = $this->formatStorage($bandwidth_total);
        $user_storage_used = $this->formatStorage($storage_total);

//        $user_bandwidth_limit_80 = $this->get_percentage(80, $user_limits['bandwidth_limit_gb']);
//        $user_storage_limit_80 = $this->get_percentage(80, $user_limits['storage_limit_gb']);
//
//        $bandwidth_total_formated = floatval(str_replace(',', '', $bandwidth_total));
//        $user_bandwidth_limit_80_formated = floatval(str_replace(',', '', $user_bandwidth_limit_80));
//        $user_bandwidth_limit_gb_formated = floatval(str_replace(',', '', $user_bandwidth_limit_gb));
//        $storage_total_formated = floatval(str_replace(',', '', $storage_total));
//        $user_storage_limit_80_formated = floatval(str_replace(',', '', $user_storage_limit_80));
//        $user_storage_limit_gb_formated = floatval(str_replace(',', '', $user_storage_limit_gb));


        $url = 'http://mediaplatform.streamingmediahosting.com/apps/services/v1.0/index.php?action=get_services&pid=' . $this->parent_id;
        $parent_services = json_decode($this->curl_request($url));
        $available = (int) $parent_services->reseller_users_quota - count($child_ids);

        $user_bandwidth_left = (int) $user_bandwidth_limit_gb - $child_stats['child_bandwidth_limit'];
        $user_bandwidth_percentage = number_format($this->get_used_percentage($user_bandwidth_left, (int) $user_bandwidth_limit_gb), 2);
        $user_storage_left = (int) $user_storage_limit_gb - $child_stats['child_storage_limit'];
        $user_storage_percentage = number_format($this->get_used_percentage($user_storage_left, (int) $user_storage_limit_gb), 2);

        $output = array(
            "recordsTotal" => intval($result[1]->totalCount),
            "recordsFiltered" => intval($result[1]->totalCount),
            "data" => array(),
            "total_storage_used" => $user_storage_used,
            "total_storage_used_percentage" => (float) $storage_used_percentage,
            "total_bandwidth_used" => $user_data_transfer,
            "total_bandwidth_used_percentage" => (float) $bandwidth_used_percentage,
            "user_storage_limit" => (int) $user_storage_limit_gb,
            "user_storage_used" => $child_stats['child_storage_limit'],
            "user_storage_percentage" => (int) $user_storage_percentage,
            "user_bandwidth_limit" => (int) $user_bandwidth_limit_gb,
            "user_bandwidth_used" => $child_stats['child_bandwidth_limit'],
            "user_bandwidth_percentage" => (int) $user_bandwidth_percentage,
            "usersAvail" => $available
        );

        if (isset($this->draw)) {
            $output["draw"] = intval($this->draw);
        }

        foreach ($result[1]->objects as $partner) {
            $row = array();
            $url = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getStats.php?pid=' . $partner->id;
            $partner_stats = json_decode($this->curl_request($url));
            $storage = '';
            $storage_gb = 0;
            $transfer = '';
            $transfer_gb = 0;

            if ($partner_stats->Error) {
                $storage = "0.00MB";
                $transfer = "0.00MB";
            } else {
                if ($partner_stats->result->storage) {
                    $storage = $this->formatStorage($partner_stats->result->storage);
                    $storage_gb = $partner_stats->result->storage;
                } else {
                    $storage = "0.00MB";
                }
                if ($partner_stats->result->bandwidth) {
                    $transfer = $this->formatStorage($partner_stats->result->bandwidth);
                    $transfer_gb = $partner_stats->result->bandwidth;
                } else {
                    $transfer = "0.00MB";
                }
            }

            $url = 'http://10.5.22.10/index.php/api/accounts/limits/' . $partner->id . '.json';
            $partner_limits = json_decode($this->curl_request($url));
            $bandwidth_limit = ($partner_limits[0]->bandwidth_limit == 0) ? 'unlimited' : number_format($partner_limits[0]->bandwidth_limit) . 'GB';
            $bandwidth_limit_gb = $partner_limits[0]->bandwidth_limit;
            $storage_limit = ($partner_limits[0]->storage_limit == 0) ? 'unlimited' : number_format($partner_limits[0]->storage_limit) . 'GB';
            $storage_limit_gb = $partner_limits[0]->storage_limit;

            $bandwidth_limit_80 = $this->get_percentage(80, $bandwidth_limit_gb);
            $storage_limit_80 = $this->get_percentage(80, $storage_limit_gb);

            $bandwidth_used_percentage = 0;
            if ($bandwidth_limit_gb > 0) {
                $bandwidth_used_percentage = number_format($this->get_used_percentage($transfer_gb, $bandwidth_limit_gb), 2);
            }
            $storage_used_percentage = 0;
            if ($storage_limit_gb > 0) {
                $storage_used_percentage = number_format($this->get_used_percentage($storage_gb, $storage_limit_gb), 2);
            }

            $status = '<div class="alert alert-success">Active</div>';
            if ($bandwidth_limit_gb > 0) {
                $data_transfer = $transfer . ' / ' . $bandwidth_limit . ' (' . $bandwidth_used_percentage . '%)';
            } else {
                $data_transfer = $transfer . ' / ' . $bandwidth_limit;
            }
            if ($storage_limit_gb > 0) {
                $storage_used = $storage . ' / ' . $storage_limit . ' (' . $storage_used_percentage . '%)';
            } else {
                $storage_used = $storage . ' / ' . $storage_limit;
            }

            if ($partner->status == 1) {
                $transfer_gb_formated = floatval(str_replace(',', '', $transfer_gb));
                $bandwidth_limit_80_formated = floatval(str_replace(',', '', $bandwidth_limit_80));
                $bandwidth_limit_gb_formated = floatval(str_replace(',', '', $bandwidth_limit_gb));
                $bandwidth_used_percentage_formated = floatval(str_replace(',', '', $bandwidth_used_percentage));
                $storage_gb_formated = floatval(str_replace(',', '', $storage_gb));
                $storage_limit_80_formated = floatval(str_replace(',', '', $storage_limit_80));
                $storage_limit_gb_formated = floatval(str_replace(',', '', $storage_limit_gb));
                $storage_used_percentage_formated = floatval(str_replace(',', '', $storage_used_percentage));
                if ($transfer_gb_formated >= $bandwidth_limit_80_formated && $bandwidth_limit_gb_formated > 0 && $bandwidth_used_percentage_formated < 100) {
                    $status = '<div class="alert alert-warning">Warning</div>';
                    $data_transfer = '<span style="color: #ad1717; font-weight: bold;">' . $transfer . ' / ' . $bandwidth_limit . ' (' . $bandwidth_used_percentage . '%)</span>';
                }
                if ($storage_gb_formated >= $storage_limit_80_formated && $storage_limit_gb_formated > 0 && $storage_used_percentage_formated < 100) {
                    $status = '<div class="alert alert-warning">Warning</div>';
                    $storage_used = '<span style="color: #ad1717; font-weight: bold;">' . $storage . ' / ' . $storage_limit . ' (' . $storage_used_percentage . '%)</span>';
                }
                if ($bandwidth_used_percentage_formated >= 100) {
                    $status = '<div class="alert alert-danger">Limit Reached</div>';
                    $data_transfer = '<span style="color: #ad1717; font-weight: bold;">' . $transfer . ' / ' . $bandwidth_limit . ' (' . $bandwidth_used_percentage . '%)</span>';
                }
                if ($storage_used_percentage_formated >= 100) {
                    $status = '<div class="alert alert-danger">Limit Reached</div>';
                    $storage_used = '<span style="color: #ad1717; font-weight: bold;">' . $storage . ' / ' . $storage_limit . ' (' . $storage_used_percentage . '%)</span>';
                }
            } else {
                $status = '<div class="alert alert-danger">Blocked</div>';
            }

            $block_action = ($partner->status == 1) ? 'Block' : 'Unblock';
            $block_action_text = ($partner->status == 1) ? 'Block' : 'Unblock';

            $url = 'http://mediaplatform.streamingmediahosting.com/apps/services/v1.0/index.php?action=get_services&pid=' . $partner->id;
            $partner_services = json_decode($this->curl_request($url));

            $limits_action = '';
            if ($storage_limit == 'unlimited' && $bandwidth_limit == 'unlimited') {
                $limits_action = '';
            } else {
                $limits_action = '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhRS.editLimits(' . $partner->id . ',' . $storage_limit_gb . ',' . $bandwidth_limit_gb . ');">Limits</a></li>';
            }

            $actions = '<span class="dropdown header">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default"><span class="text">Edit</span></button>
                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                                <ul class="dropdown-menu" id="menu" role="menu" aria-labelledby="dropdownMenu">
                                <li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhRS.editAccount(' . $partner->id . ',\'' . $partner->name . '\',\'' . $partner->description . '\',\'' . $partner->adminName . '\',\'' . $partner->adminEmail . '\');">Account</a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhRS.editServices(' . $partner_services->transcoding_vod . ',' . $partner_services->pay_per_view . ',' . $partner_services->membership . ',' . $partner_services->streaming_live_chat . ',' . $partner_services->white_label . ',' . $partner_services->force_parent_layout . ',' . $partner_services->use_custom_layout . ',' . $partner->id . ',' . 'false' . ');">Services</a></li>' .
                    $limits_action .
                    '<li role="presentation"><a role="menuitem" tabindex="-1" onclick="smhRS.editStatus(' . $partner->id . ',\'' . $partner->adminName . '\',\'' . $block_action . '\');">' . $block_action_text . '</a></li>
                                <li role="presentation" style="border-top: solid 1px #f0f0f0;"><a role="menuitem" tabindex="-1" onclick="smhRS.deleteAccount(' . $partner->id . ',\'' . $partner->adminName . '\');">Delete</a></li>                                        
                                </ul>
                            </div>
                        </span>';

            $row[] = $status;
            $row[] = $partner->id;
            $row[] = '<div class="data-break">' . $partner->name . '</div>';
            $row[] = '<div class="data-break">' . $partner->adminEmail . '</div>';
            $row[] = '<div class="data-break">' . $partner->adminName . '</div>';
            $row[] = '<div class="data-break">' . $data_transfer . '</div>';
            $row[] = '<div class="data-break">' . $storage_used . '</div>';
            $row[] = $actions;
            $output['data'][] = $row;
        }

        echo json_encode($output);
    }

    public function get_user_stats() {
        $stats = array();
        $url = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getStats.php?pid=' . $this->parent_id;
        $user_stats = json_decode($this->curl_request($url));
        if ($user_stats->Error) {
            $storage = 0;
            $transfer = 0;
        } else {
            if ($user_stats->result->storage) {
                $storage = $user_stats->result->storage;
            } else {
                $storage = 0;
            }
            if ($user_stats->result->bandwidth) {
                $transfer = $user_stats->result->bandwidth;
            } else {
                $transfer = 0;
            }
        }
        $stats = array('storage' => $storage, 'bandwidth' => $transfer);
        return $stats;
    }

    public function get_user_limits() {
        $user_limits = array();
        $url = 'http://10.5.22.10/index.php/api/accounts/limits/' . $this->parent_id . '.json';
        $user_limits = json_decode($this->curl_request($url));
        $bandwidth_limit = ($user_limits[0]->bandwidth_limit == 0) ? 'unlimited' : number_format($user_limits[0]->bandwidth_limit) . 'GB';
        $storage_limit = ($user_limits[0]->storage_limit == 0) ? 'unlimited' : number_format($user_limits[0]->storage_limit) . 'GB';
        $bandwidth_limit_gb = $user_limits[0]->bandwidth_limit;
        $storage_limit_gb = $user_limits[0]->storage_limit;
        $user_limits = array('bandwidth_limit' => $bandwidth_limit, 'bandwidth_limit_gb' => $bandwidth_limit_gb, 'storage_limit_gb' => $storage_limit_gb, 'storage_limit' => $storage_limit);
        return $user_limits;
    }

    public function get_child_stats($child_ids) {
        $stats = array();
        $storage_total = 0;
        $storage_limit_total = 0;
        $bandwidth_total = 0;
        $bandwidth_limit_total = 0;
        foreach ($child_ids as $child) {
            $url = 'http://mediaplatform.streamingmediahosting.com/apps/scripts/getStats.php?pid=' . $child;
            $limits_url = 'http://10.5.22.10/index.php/api/accounts/limits/' . $child . '.json';
            $partner_stats = json_decode($this->curl_request($url));
            $partner_limits = json_decode($this->curl_request($limits_url));
            $storage_limit_total += $partner_limits[0]->storage_limit;
            $bandwidth_limit_total += $partner_limits[0]->bandwidth_limit;
            if ($partner_stats->Error) {
                $storage = 0;
                $transfer = 0;
            } else {
                if ($partner_stats->result->storage) {
                    $storage = $partner_stats->result->storage;
                } else {
                    $storage = 0;
                }
                if ($partner_stats->result->bandwidth) {
                    $transfer = $partner_stats->result->bandwidth;
                } else {
                    $transfer = 0;
                }
            }
            $storage_total += $storage;
            $bandwidth_total += $transfer;
        }
        $stats = array('child_storage' => $storage_total, 'child_bandwidth' => $bandwidth_total, 'child_storage_limit' => $storage_limit_total, 'child_bandwidth_limit' => $bandwidth_limit_total);
        return $stats;
    }

    public function formatStorage($size) {
        if ($size >= 1) {
            return number_format($size, 2) . "GB";
        } else {
            $size = $size * 1000;
            return number_format($size, 2) . "MB";
        }
    }

    public function curl_request($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function get_used_percentage($num, $total) {
        $first_num = $total / $num;
        $percentage = 100 / $first_num;
        return $percentage;
    }

    public function get_percentage($percent, $total) {
        $first_num = 100 / $percent;
        $percentage = $total / $first_num;
        return $percentage;
    }

    public function impersonate($pid) {
        $config = new KalturaConfiguration($pid);
        $config->serviceUrl = 'http://mediaplatform.streamingmediahosting.com/';
        $client = new KalturaClient($config);
        $secret = $this->adminAPISecretKey;
        $impersonatedPartnerId = $pid;
        $userId = null;
        $type = KalturaSessionType::ADMIN;
        $partnerId = $this->adminPartnerId;
        $expiry = null;
        $privileges = null;
        $result = $client->session->impersonate($secret, $impersonatedPartnerId, $userId, $type, $partnerId, $expiry, $privileges);
        return $result;
    }

}

header('Content-Type: application/json');
$rs = new reseller();
$rs->run();
?>