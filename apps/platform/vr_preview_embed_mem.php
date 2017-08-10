<?php
require_once('../../app/clients/php5/KalturaClient.php');

function verfiy_ks($pid, $ks) {
    $success = false;
    $config = new KalturaConfiguration(0);
    $config->serviceUrl = 'https://mediaplatform.streamingmediahosting.com/';
    $client = new KalturaClient($config);
    $partnerFilter = null;
    $pager = null;
    $client->setKs($ks);
    $results = $client->partner->listpartnersforuser($partnerFilter, $pager);

    $partner_id = '';
    foreach ($results->objects as $partnerInfo) {
        $partner_id = $partnerInfo->id;
    }

    if (isset($partner_id) && $partner_id == $pid) {
        $success = array('success' => true, 'pid' => $partner_id);
    } else {
        $success = array('success' => false);
    }

    return $success;
}

function get_regular_player_details($ks, $pid) {
    $url = 'https://mediaplatform.streamingmediahosting.com/index.php/kmc/getuiconfs';
    $fields = array(
        'ks' => urlencode($ks),
        'partner_id' => urlencode($pid),
        'type' => 'player'
    );

    $fields_string = '';
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    curl_close($ch);

    $resp = json_decode($result);

    return $resp;
}

function get_playlist_player_details($ks, $pid) {
    $url = 'https://mediaplatform.streamingmediahosting.com/index.php/kmc/getuiconfs';
    $fields = array(
        'ks' => urlencode($ks),
        'partner_id' => urlencode($pid),
        'type' => 'playlist'
    );

    $fields_string = '';
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    curl_close($ch);

    $resp = json_decode($result);

    return $resp;
}

$pid = $_GET['pid'];
$ks = $_GET['ks'];
$mode = $_GET['mode'];
$valid = verfiy_ks($pid, $ks);

if ($mode == 's' || $mode == 'cr' || $mode == 'cl' || $mode == 'ct' || $mode == 'cb') {
    $players = get_regular_player_details($ks, $pid);

    $player_select = "<select id='players' class='form-control' style='width: 213px;'>";
    $i = 0;
    foreach ($players as $player) {
        if ($player->id == 6709584 || $player->id == 6709796 || $player->id == 6710348) {
            
        } else {
            if ($player->id == 6710347) {
                $player_select .= "<option value='" . $player->id . "," . $player->width . "," . $player->height . "' selected>" . $player->name . "</option>";
            } else {
                $player_select .= "<option value='" . $player->id . "," . $player->width . "," . $player->height . "'>" . $player->name . "</option>";
            }
            $i++;
        }
    }
    $player_select .= "</select>";
} else if ($mode == 'p') {
    $players = get_playlist_player_details($ks, $pid);

    $player_select = "<select id='players' class='form-control' style='width: 213px;'>";
    $player_select .= "<option value='6709427,680,333' selected>Horizontal Dark skin Playlist</option>";
    $player_select .= "<option value='6709426,400,680'>Vertical Dark skin Playlist</option>";
    $player_select .= "<option value='6709425,680,333'>Horizontal Light skin Playlist</option>";
    $player_select .= "<option value='6709424,400,680'>Vertical Light skin Playlist</option>";
    $i = 0;
    foreach ($players as $player) {
        if ($player->id == 6709584 || $player->id == 6709796 || $player->id == 6709427 || $player->id == 6709426 || $player->id == 6709425 || $player->id == 6709424) {
            
        } else {
            $player_select .= "<option value='" . $player->id . "," . $player->width . "," . $player->height . "'>" . $player->name . "</option>";
            $i++;
        }
    }
    $player_select .= "</select>";
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Streaming Media Hosting Pay Per View</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style type="text/css">
            #main-wrapper {
                background: -moz-linear-gradient(center top , #FFFCFF 5%, #EDEAED 100%) repeat scroll 0 0 #FFFCFF;
                border: 1px solid #949494;
                border-radius: 6px;
                box-shadow: 0 0 0 0 #FFFFFF inset;
                color: #404040;
                padding: 10px 12px 70px;
                text-decoration: none;
                width: 70%;
                margin-left: auto; 
                margin-right: auto;
                margin-top: 55px;
            }

            #clear {
                clear: both;
            }

            #options{
                width: 500px;
                height: 300px;
                margin-left: auto; 
                margin-right: auto;
                margin-top: 20px;
                margin-bottom: 50px;
            }

            #embed_code {
                resize: none;
            }

            #select-bttn:hover, #select-bttn:focus {
                background-position: 0 -15px !important;
                color: #333333 !important;
                text-decoration: none !important;
                transition: background-position 0.1s linear 0s !important;
            }

            #select-bttn:hover, #select-bttn:focus, #select-bttn:active, #select-bttn.active, #select-bttn.disabled, #select-bttn[disabled] {
                background-color: #E6E6E6 !important;
                color: #333333 !important;
            }

            #select-bttn.active, #select-bttn:active {
                background-image: none !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
                outline: 0 none !important;
            }

            .label-info, .badge-info {
                background-color: #3A87AD !important;
            }

            .label {
                border-radius: 3px !important;
            }

            .label, .badge {
                color: #FFFFFF !important;
                display: inline-block !important;
                font-size: 11.844px !important;
                font-weight: bold !important;
                line-height: 14px !important;
                padding: 2px 4px !important;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25) !important;
                vertical-align: baseline !important;
                white-space: nowrap !important;
            }

            #smh_purchase_window *, #smh_purchase_window *::before, #smh_purchase_window *::after {
                box-sizing: content-box !important;
            }
            #smh_purchase_window #smh-login-wrapper{
                margin-left: 11px !important;
            }
            .form-control {
                border-color: #d2d6de !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            .btn-default {
                background-color: #e0e0e0 !important;
                border-color: #c4c4c4 !important;
                color: #484848 !important;
                font-weight: bold !important;
            }
            .content {
                margin-left: auto;
                margin-right: auto;
                min-height: 250px;
            }
            .options {
                color: #333;
                font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
                font-size: 16px;
            }
            .options select {
                display: inline-block;
            }
            .options {
                background-color: #f6f6f6;
                border-right: 1px solid #d4d4d4;
                box-sizing: border-box;
                color: #333;
                display: block;
                float: left;
                font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
                font-size: 16px;
                height: 700px;
                max-width: 370px;
                overflow-y: auto;
                padding: 5px 5px 5px 26px;
                width: 30%;
            }
            .player_preview {
                border: 0 none;
                box-sizing: border-box;
                color: #333;
                display: block;
                float: left;
                font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
                font-size: 16px;
                height: 100%;
                margin-left: 6%;
                margin-right: 0;
                min-height: 687px;
                padding: 30px 30px 30px 26px;
                width: 60%;
            }
            #embed_code {
                margin-bottom: 30px;
                width: 96% !important;
            }
            .right-ar {
                margin-top: 10px;
            }
            .options .mCSB_inside > .mCSB_container {
                margin-right: 26px;
            }            
        </style>
        <link type="text/css" rel="stylesheet" media="screen" href="/css/jquery.mCustomScrollbar.css">
        <link type="text/css" rel="stylesheet" media="screen" href="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/smh_mem_style.css?1385197029"></link>
        <link type="text/css" rel="stylesheet" media="screen" href="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/bootstrap.min.css?1385197029"></link>
        <link type="text/css" rel="stylesheet" media="screen" href="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/categoryOnPage.css?1385197029"></link>
        <link type="text/css" rel="stylesheet" media="screen" href="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/font-awesome.min.css?1385197029"></link>
        <link type="text/css" rel="stylesheet" media="screen" href="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/tooltipster.css?1385197029"></link>
        <script src="/js/jQuery-2.1.4.min.js?v=1" type="text/javascript"></script>
        <script src="/js/jquery.mCustomScrollbar.min.js?v=1" type="text/javascript"></script>        
    </head>
    <body>
        <?php
        if ($valid) {
            ?>
            <script>
                var smh2;
                $(document).ready(function () {
                    smh2 = jQuery.noConflict();
                    var headTag = document.getElementsByTagName("head")[0];
                    var jqTag = document.createElement('script');
                    jqTag.setAttribute("type", "text/javascript")
                    jqTag.setAttribute("src", 'https://devplatform.streamingmediahosting.com/html5/html5lib/v2.55/kWidget/onPagePlugins/mem/mem_init.js');
                    headTag.appendChild(jqTag);
                });

                mem_protocol = 'https';
                mem_protocol_prev = "http";
                mem_type = '<?php echo $_GET['mode'] ?>';
                var err_comp = true;
                var fb_eid = '';
                function load_smh_mem() {
                    format = smh2('select.format option:selected').val();
                    delivery = smh2('select.delivery option:selected').val();
                    mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);

                    smh2('.options').on('change', 'select#players', function (event) {
                        smh2('#memWindow').css('display', 'none');
                        var p = '';
                        if (smh2("#ssl-embed").is(':checked')) {
                            p = 'https';
                        } else {
                            p = 'http';
                        }

    <?php if ($mode == 'p' || $mode == 's') { ?>
                            var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                            var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                        var data = smh2('select#players option:selected').val();
                        var temp = data.split(',');
                        var uiconf_id = temp[0];
                        var width = temp[1];
                        var height = temp[2];
                        smh2('#dim_width').val(width);
                        smh2('#dim_height').val(height);
                        mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", uiconf_id, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                        var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", uiconf_id, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                        smh2('#embed_code').val(player);
                        smh2('#prev-result').css("display", "none");
                    });
                    mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>,<?php echo $_GET['width'] ?>,<?php echo $_GET['height'] ?>, "<?php echo $_GET['entry_id'] ?>", '<?php echo $_GET['mode'] ?>');
                    var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>,<?php echo $_GET['width'] ?>,<?php echo $_GET['height'] ?>, "<?php echo $_GET['entry_id'] ?>", "<?php echo $_GET['mode'] ?>", mem_protocol_prev, format, delivery, fb_eid, err_comp);
                    smh2('#embed_code').val(player);

                    smh2('#select-bttn').click(function (event) {
                        smh2('#embed_code').select();
                        smh2('#prev-result').css({
                            "display": "block",
                            "margin-left": "auto",
                            "margin-right": "auto",
                            "width": "326px",
                            "margin-top": "5px",
                            "margin-bottom": "10px"
                        });
                        smh2('#prev-result').html('<span class="label label-info">Press Ctrl+C to copy embed code (Command+C on Mac)</span>');
                    });

                    smh2('select.format').change(function (event) {
                        format = smh2('select.format option:selected').val();
                        delivery = smh2('select.delivery option:selected').val();
                        mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                        smh2('#memWindow').css('display', 'none');
                        var p = '';
                        if (smh2("#ssl-embed").is(':checked')) {
                            p = 'https';
                        } else {
                            p = 'http';
                        }

    <?php if ($mode == 'p' || $mode == 's') { ?>
                            var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                            var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                        var width = smh2('#dim_width').val();
                        var height = smh2('#dim_height').val();
                        mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                        var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                        smh2('#embed_code').val(player);
                        smh2('#prev-result').css("display", "none");
                    });

                    smh2('select.delivery').change(function (event) {
                        format = smh2('select.format option:selected').val();
                        delivery = smh2('select.delivery option:selected').val();
                        mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                        smh2('#memWindow').css('display', 'none');
                        var p = '';
                        if (smh2("#ssl-embed").is(':checked')) {
                            p = 'https';
                        } else {
                            p = 'http';
                        }

    <?php if ($mode == 'p' || $mode == 's') { ?>
                            var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                            var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                        var width = smh2('#dim_width').val();
                        var height = smh2('#dim_height').val();
                        mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                        var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                        smh2('#embed_code').val(player);
                        smh2('#prev-result').css("display", "none");
                    });

                    smh2('select#layoutmode').change(function (event) {
                        format = smh2('select.format option:selected').val();
                        delivery = smh2('select.delivery option:selected').val();
                        mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                        smh2('#memWindow').css('display', 'none');
                        var p = '';
                        if (smh2("#ssl-embed").is(':checked')) {
                            p = 'https';
                        } else {
                            p = 'http';
                        }

    <?php if ($mode == 'p' || $mode == 's') { ?>
                            var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                            var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                        var width = smh2('#dim_width').val();
                        var height = smh2('#dim_height').val();
                        mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                        var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                        smh2('#embed_code').val(player);
                        smh2('#prev-result').css("display", "none");

                    });

                    smh2('#ssl-embed').click(function () {
                        format = smh2('select.format option:selected').val();
                        delivery = smh2('select.delivery option:selected').val();
                        mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                        if (smh2("#ssl-embed").is(':checked')) {
    <?php if ($mode == 'p' || $mode == 's') { ?>
                                var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                                var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                            var width = smh2('#dim_width').val();
                            var height = smh2('#dim_height').val();
                            var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, 'https', format, delivery, fb_eid, err_comp);
                            smh2('#embed_code').val(player);
                            smh2('#prev-result').css("display", "none");
                            smh2('#ssl-notice').html('Your web server must be configured for SSL in order to support https connections');
                        } else {
    <?php if ($mode == 'p' || $mode == 's') { ?>
                                var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                                var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                            var width = smh2('#dim_width').val();
                            var height = smh2('#dim_height').val();
                            var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, 'http', format, delivery, fb_eid, err_comp);
                            smh2('#embed_code').val(player);
                            smh2('#prev-result').css("display", "none");
                            smh2('#ssl-notice').empty();
                        }
                    });

                    smh2('.options').on('click', '#update-dim', function () {
                        format = smh2('select.format option:selected').val();
                        delivery = smh2('select.delivery option:selected').val();
                        mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                        smh2('#memWindow').css('display', 'none');
                        var p = '';
                        if (smh2("#ssl-embed").is(':checked')) {
                            p = 'https';
                        } else {
                            p = 'http';
                        }

    <?php if ($mode == 'p' || $mode == 's') { ?>
                            var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                            var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                        var width = smh2('#dim_width').val();
                        var height = smh2('#dim_height').val();
                        mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                        var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                        smh2('#embed_code').val(player);
                        smh2('#prev-result').css("display", "none");
                    });

                    smh2('.options').on('keyup', '#dim_width', function () {
                        var ratio = smh2('#aspect_ratio').val();
                        if (ratio !== 'dim_custom') {
                            var aspect = ratio == 'dim_16_9' ? 9 / 16 : 3 / 4;
                            width = smh2('#dim_width').val();
                            height = parseInt(width * aspect);
                            smh2('#dim_height').val(height);
                        }
                    });

                    smh2('.options').on('change', '#aspect_ratio', function () {
                        var ratio = smh2('#aspect_ratio').val();
                        var width, height;
                        if (ratio !== 'dim_custom') {
                            smh2('#dim_height').attr('disabled', '');
                            var aspect = ratio == 'dim_16_9' ? 9 / 16 : 3 / 4;
                            width = smh2('#dim_width').val();
                            height = parseInt(width * aspect);
                            smh2('#dim_height').val(height);
                        } else {
                            smh2('#dim_height').removeAttr('disabled');
                        }
                    });

                    smh2('.options').on('change', '#error-comp', function () {
                        if (smh2("#error-comp").is(':checked')) {
                            err_comp = true;
                            format = smh2('select.format option:selected').val();
                            delivery = smh2('select.delivery option:selected').val();
                            mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                            smh2('#memWindow').css('display', 'none');
                            var p = '';
                            if (smh2("#ssl-embed").is(':checked')) {
                                p = 'https';
                            } else {
                                p = 'http';
                            }
    <?php if ($mode == 'p' || $mode == 's') { ?>
                                var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                                var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                            var width = smh2('#dim_width').val();
                            var height = smh2('#dim_height').val();
                            mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                            var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                            smh2('#embed_code').val(player);
                            smh2('#prev-result').css("display", "none");
                        } else {
                            err_comp = false;
                            format = smh2('select.format option:selected').val();
                            delivery = smh2('select.delivery option:selected').val();
                            mem.init(mem_protocol, true, format, delivery, fb_eid, err_comp);
                            smh2('#memWindow').css('display', 'none');
                            var p = '';
                            if (smh2("#ssl-embed").is(':checked')) {
                                p = 'https';
                            } else {
                                p = 'http';
                            }
    <?php if ($mode == 'p' || $mode == 's') { ?>
                                var mode = "<?php echo $mode ?>";
    <?php } else { ?>
                                var mode = smh2('select#layoutmode option:selected').val();
    <?php } ?>
                            var width = smh2('#dim_width').val();
                            var height = smh2('#dim_height').val();
                            mem.checkAccess(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>",<?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode);
                            var player = getPlayerEmbed(<?php echo $_GET['pid'] ?>, "<?php echo $_GET['sm_ak'] ?>", <?php echo $_GET['uiconf_id'] ?>, width, height, "<?php echo $_GET['entry_id'] ?>", mode, p, format, delivery, fb_eid, err_comp);
                            smh2('#embed_code').val(player);
                            smh2('#prev-result').css("display", "none");
                        }
                    });
                }

                function getPlayerEmbed(pid, sm_ak, uiconf_id, width, height, entry_id, mode, protocol, format, delivery, fb_eid, err_comp) {
                    var player = '<script>mem_protocol=\'' + protocol + '\';mem_type=\'' + mode + '\';function load_smh_mem(){mem.init(\'' + protocol + '\',true,\'' + format + '\', \'' + delivery + '\', \'' + fb_eid + '\', ' + err_comp + ');mem.checkAccess(' + pid + ',"' + sm_ak + '",' + uiconf_id + ',' + width + ',' + height + ',"' + entry_id + '",mem_type);}<\/script><script src="' + protocol + '://mediaplatform.streamingmediahosting.com/p/<?php echo $_GET['pid'] ?>/html5/html5lib/v2.55/kWidget/onPagePlugins/mem/mem_init.js" type="text/javascript"><\/script><div id="myVideoContainer"></div><div id="memWindow" style="display: none;"></div>';
                    return player;
                }
            </script>
            <script src="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/mem_init.js" type="text/javascript"></script>
            <div class="content">
                <div class="options">
                    <div style="font-size: 14px; font-weight: bold; margin-left: auto; margin-right: auto; margin-top: 10px;">
                        <span style="margin-right: 11px; color: #444; font-size: 12px;">Select Player:</span><span>
                            <?php echo $player_select ?>
                        </span>
                    </div>
                    <div style="margin-top: 5px;"><span style="font-size: 12px; color: #999;">Player includes both layout and functionality (advertising, subtitles, etc)</span></div>
                    <hr>                    
                    <div style="margin-top: 10px; font-weight: bold;"><span style="color: #444; font-size: 12px;margin-right: 11px;">VR Video Format:</span><span><select class="form-control format" style="width: 183px;"><option value="2d">2D</option><option value="sbs">Side By Side</option><option value="tb">Top/Bottom</option></select></span></div>
                    <div style="margin-top: 5px;"><span style="font-size: 12px; color: #999;">Supported VR video formats include 2D, 3D side-by-side and 3D top/bottom.</span></div>
                    <hr>
                    <div style="margin-top: 10px; font-weight: bold;"><span style="color: #444; font-size: 12px;margin-right: 30px;">Delivery Type:</span><span><select class="form-control delivery" style="width: 183px;"><option value="hls">HLS Streaming</option><option value="http">HTTP Progressive Download</option></select></span></div>
                    <div style="margin-top: 5px;"><span style="font-size: 12px; color: #999;">Adaptive Streaming automatically adjusts to the viewer's bandwidth, while Progressive Download allows buffering of the content.</span></div>
                    <hr>
                    <?php if ($mode == 'cr' || $mode == 'cl' || $mode == 'ct' || $mode == 'cb') { ?>
                        <div style="margin-top: 10px; font-weight: bold;">
                            <span style="color: #444; font-size: 12px;margin-right: 1px;">Category Location:</span>
                            <span>
                                <select style="width: 213px;" class="form-control" id="layoutmode"><option value='cr'>Right of Player</option><option value='cl'>Left of Player</option><option value='ct'>Top of Player</option><option value='cb'>Bottom of Player</option></select>
                            </span>
                        </div>
                        <hr>
                    <?php } ?>
                    <?php if ($mode == 's' || $mode == 'p') { ?>
                        <div style="margin-top: 10px; font-weight: bold;">
                            <span><input id="error-comp" name="error_comp" checked="" type="checkbox"></span>&nbsp;<span style="color: #444; font-size: 12px; font-weight: bold; margin-left: 5px; position: relative; top: -2px;">Display Browser Compatibility Warning</span>
                        </div>
                        <div style="margin-top: 5px;">
                            <span id="embedType-text" style="font-size: 12px; color: #999;">If the browser does not support VR, display a browser compatibility warning message.</span>
                        </div>
                        <hr>
                    <?php } ?>                        
                    <div style="margin-top: 10px; margin-bottom: 10px; font-weight: bold;">
                        <span style="color: #444; font-size: 12px;margin-right: 30px;">Player Dimensions</span>
                    </div>
                    <span style="color: #444; font-size: 12px; font-weight: bold;">Aspect Ratio:</span>
                    <select id="aspect_ratio" style="margin-bottom: 15px; display: inline-block; margin-left: 8px; width: 135px; font-weight: bold;" class="form-control"><option value="dim_4_3">4/3</option><option value="dim_16_9">16/9</option><option value="dim_custom" selected>custom</option></select>
                    <div class="clear"></div>
                    <span style="color: #444; font-size: 12px; font-weight: bold;">Width</span><input type="text" value="<?php echo $_GET['width'] ?>" id="dim_width" name="dim_width" style="width: 70px; margin-left: 10px; display: inline;" class="form-control"><span> px</span>
                    <div class="right-ar">
                        <span style="color: #444; font-size: 12px; font-weight: bold;">Height</span><input type="text" value="<?php echo $_GET['height'] ?>" id="dim_height" name="dim_height" style="width: 70px; margin-left: 5px; display: inline;" class="form-control"><span> px</span>
                    </div>
                    <button class="btn btn-default" style="margin-top: 20px" id="update-dim"><i class="fa fa-refresh">&nbsp;</i>Update Dimensions</button>
                    <div class="clear"></div>
                    <hr>
                    <div style="margin-top: 10px; font-weight: bold;">
                        <span><input type="checkbox" id="ssl-embed"></span>&nbsp;<span style="color: #444; font-size: 12px; font-weight: bold; margin-left: 5px; position: relative; top: -2px;">Support for HTTPS embed code</span></div>
                    <hr>
                    <div style="margin-top: 10px; font-weight: bold;">
                        <div style="color: #444; font-size: 12px; padding-top: 15px; float: left;">Embed Code:</div><div style="float: right; margin-right: 13px;"><button style="margin: 10px 0 10px 0;" class="btn btn-default" id="select-bttn">Select Code</button></div>
                    </div>
                    <textarea id="embed_code" class="form-control" rows="5" cols="51"></textarea>
                </div>
                <div class="player_preview">Preview Player<hr>
                    <div style="overflow-y: auto; height: 555px;">
                        <div id="myVideoContainer"></div><div id="memWindow" style="display: none;"></div>
                    </div>
                </div>
            </div>

        <?php } else { ?>
            <div>Error</div>
        <?php } ?>
        <script>
                $('.options').mCustomScrollbar({
                    theme: "inset-dark",
                    scrollButtons: {
                        enable: true
                    }
                });
        </script>
    </body>
</html>