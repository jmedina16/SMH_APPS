<?php

if ($configName == 'kmc') {

//    if (!isset($_COOKIE['kmcks']) || empty($_COOKIE['kmcks'])) {
//        die('Error: Missing KS');
//    }

    if (!isset($_GET['partnerId'])) {
        die('Error: Missing Partner ID');
    }

    if (!isset($_GET['kclipUiconf']) || !isset($_GET['kdpUiconf'])) {
        die('Error: Missing Uiconfs for KDP/kClip');
    }

    $config['kmc'] = array(
        'host' => $_SERVER['HTTP_HOST'],
        'partner_id' => intval($_GET['partnerId']),
        'user_id' => $_GET['uid'],
        'ks' => $_GET['ks'],
        'overwrite_entry' => ($_GET['mode'] == "trim") ? true : false,
        'clipper_uiconf_id' => intval($_GET['kclipUiconf']),
        'kdp_uiconf_id' => intval($_GET['kdpUiconf']),
        'show_embed' => false,
        'html5_embed' => false,
        'trim_save_message' => 'The trimmed video is now converting. This might take a few minutes. Please close the window to continue.',
        'clip_save_message' => 'A new clip has been created. This might take a few minutes.<br /><br />You can now close this window and your new clip will appear in your "Entries" table as soon as it has finished processing.',
    );
}