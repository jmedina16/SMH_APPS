<?php
/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
//echo __DIR__;
//if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
//  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
//}
//
require_once '../google/vendor/autoload.php';
session_start();

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = '625514053094-0rdhl4tub0dn2kd4edk9onfcd38i1uci.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'o9fEzEUdCq_mXLMGDMHboE6m';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

//$test = $client->refreshToken('1/LAbVK-stGh9Sm50j8ICoXVq92mbgkjrOk3XIdQYkVwI');
//print_r($test);
//echo "<br><br>";
// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

    $client->authenticate($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
    try {
//        $channels = $youtube->channels->listChannels("snippet", array(
//            'mine' => 'false'
//        ));
//        echo "<pre>";
//        print_r($channels);
//        echo "</pre>";

//        $test = new Google_Service_YouTube_AccessPolicy();
//        $test = $test->getAllowed();
//
//        echo "<pre>";
//        print_r($test);
//        echo "</pre>";


        // Execute an API request that lists broadcasts owned by the user who
        // authorized the request.
        $broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts('snippet,contentDetails,status', array(
            'mine' => 'true',
            'broadcastType' => 'persistent',
            'maxResults' => 50,
            //'fields' => 'items(contentDetails/boundStreamId,snippet/title),pageInfo'
        ));

        echo "<pre>";
        print_r($broadcastsResponse);
        echo "</pre>";

        $streamIds = array();
        foreach ($broadcastsResponse['items'] as $item) {
            $boundStreamId = $item['contentDetails']['boundStreamId'];
            array_push($streamIds, $boundStreamId);
        }

        $streamIds = implode(",", $streamIds);
        $streamsResponse = $youtube->liveStreams->listLiveStreams('snippet,contentDetails,cdn,status', array(
            'id' => $streamIds,
            'maxResults' => 50,
            //'fields' => 'items(cdn(format,frameRate,ingestionInfo,resolution),id),pageInfo'
        ));
        echo "<pre>";
        print_r($streamsResponse);
        echo "</pre>";
//
//        $htmlBody .= "<h3>Live Broadcasts</h3><ul>";
//        foreach ($broadcastsResponse['items'] as $broadcastItem) {
//            $htmlBody .= sprintf('<li>%s (%s)</li>', $broadcastItem['snippet']['title'], $broadcastItem['id']);
//        }
//        $htmlBody .= '</ul>';
    } catch (Google_Service_Exception $e) {
        $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
        $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    }

    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
    $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
    // If the user hasn't authorized the app, initiate the OAuth flow
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;

    $authUrl = $client->createAuthUrl();
    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
    <head>
        <title>My Live Broadcasts</title>
    </head>
    <body>
        <?= $htmlBody ?>
    </body>
</html>
