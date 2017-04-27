<?php
session_start();
require_once( '../facebook/vendor/autoload.php' );

$fb = new Facebook\Facebook([
    'app_id' => '1880095552209614',
    'app_secret' => 'e52af82e8fe68106197f87138355436c',
    'default_graph_version' => 'v2.8',
        ]);
//$requestPicture = $fb->get('/me/picture?redirect=false&height=300', 'EAAKtaWZAqBv8BAEbmCH7kvBDxNbWLfvZAAencfV0nbxYDCr2hSUUvhJ8ozfItMlLevgRs1fQ181XlKKBPZBKZC31t6ZA0iTDmsBlk6WH2ZAfZAVp3yQC1tZBdvGFFH6gpPKK24gjb2d4OwidbZAbvBrZAISVm7HCEZBLVCM0bnBUSrXQwZDZD');
//$picture = $requestPicture->getGraphUser();
//echo '<pre>';
//print_r($picture['url']);
//echo '</pre>';
//$helper = $fb->getRedirectLoginHelper();
//
//$permissions = ['publish_actions','manage_pages','publish_pages','user_managed_groups','user_events']; // Optional permissions
//$loginUrl = $helper->getLoginUrl('https://mediaplatform.streamingmediahosting.com/apps/platform/fb-callback.php', $permissions);
//
//echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
//try {
//    // Get the \Facebook\GraphNodes\GraphUser object for the current user.
//    // If you provided a 'default_access_token', the '{access-token}' is optional.
//
//
//    $response = $fb->get('/me', 'EAAKtaWZAqBv8BAIWLhxNIaFzWIZAuPqNZC5rA0e5NwqiTb3mGFR054wRFieaR02MhCeCuhxHoSKwuKZAC4N7vn08CddamVhfxXFsbNwKxCvQWjCTYRxnpjetZBRRgpdTykqSZAapNG2hvBEtgSIbclsIIoWZBgkQiRsASWzNHcWvAZDZD');
//    //$pageList = $response->getGraphNode()->asArray();
//    $user = $response->getGraphUser()->asArray();
//    echo '<pre>';
//    print_r($user);
//    echo '</pre>';
//
//
////    echo '<pre>';
////    print_r($pages);
////    echo '</pre>';
//} catch (\Facebook\Exceptions\FacebookResponseException $e) {
//    // When Graph returns an error
//    echo 'Graph returned an error: ' . $e->getMessage();
//    exit;
//} catch (\Facebook\Exceptions\FacebookSDKException $e) {
//    // When validation fails or other local issues
//    echo 'Facebook SDK returned an error: ' . $e->getMessage();
//    exit;
//}
//try {
//    // Get the \Facebook\GraphNodes\GraphUser object for the current user.
//    // If you provided a 'default_access_token', the '{access-token}' is optional.
//
//
//    $response = $fb->get('/119297148602495/accounts', 'EAAKtaWZAqBv8BAOrpKGZCbyZATyZBbSpwwT4N03qMq9WfGiVwHYiRjZCbavT1aZCOO3JAl2CJPCIGZAxgZCt8HvjtTyEcdpvr7INayVKLmC3F42Yv7EQnZC2GLY9FwaZC2ZBwSOQCjgMhlC6uCVp37pcHxkVa6aCDqKj0LfcgQZBgYgaZAQZDZD');
//    //$pageList = $response->getGraphNode()->asArray();
//    $pageList = $response->getGraphEdge()->asArray();
//    echo '<pre>';
//    print_r($pageList);
//    echo '</pre>';
//    
////    foreach ($pageList as $page) {
////        echo '<pre>';
////        print_r($page);
////        echo '</pre>';
////    }
//
//
////    echo '<pre>';
////    print_r($pages);
////    echo '</pre>';
//} catch (\Facebook\Exceptions\FacebookResponseException $e) {
//    // When Graph returns an error
//    echo 'Graph returned an error: ' . $e->getMessage();
//    exit;
//} catch (\Facebook\Exceptions\FacebookSDKException $e) {
//    // When validation fails or other local issues
//    echo 'Facebook SDK returned an error: ' . $e->getMessage();
//    exit;
//}
//
//$me = $response->getGraphUser();
//echo 'Logged in as ' . $me->getName();
//
// The OAuth 2.0 client handler helps us manage access tokens
//$oAuth2Client = $fb->getOAuth2Client();
//
//// Get the access token metadata from /debug_token
//$tokenMetadata = $oAuth2Client->debugToken('EAAKtaWZAqBv8BAEbmCH7kvBDxNbWLfvZAAencfV0nbxYDCr2hSUUvhJ8ozfItMlLevgRs1fQ181XlKKBPZBKZC31t6ZA0iTDmsBlk6WH2ZAfZAVp3yQC1tZBdvGFFH6gpPKK24gjb2d4OwidbZAbvBrZAISVm7HCEZBLVCM0bnBUSrXQwZDZD');
//echo '<h3>Metadata</h3>';
//echo '<pre>';
//print_r($tokenMetadata);
//echo '</pre>';
try {
//    // to create live video
//    $createLiveVideo = $fb->post('/104139716814983', ['title' => 'Test', 'description' => 'descrip of the video'], 'EAAat79uIgs4BADz9haKAxFH3YEoM3ZCxyz7UcVrQsNQI5jG2qsU52XMhgaFVyUoDLur0qdsZB8PfxMTuyo8MHepyH8TpMqMnRt0K9rKHgGdkJycFdgk9XEX7BmP9veZAy12w22P6lAR2xcZAkr9cZAcvZCLZB0ZBq2TlpxpQ3PlDowZDZD');
//    $createLiveVideo = $createLiveVideo->getGraphNode()->asArray();
//    echo '<pre>';
//    print_r($createLiveVideo);
//    echo '</pre>';
    //$LiveVideo = $fb->get('/131357290729814?fields=created_time,embed_html,live_status,privacy', 'EAAKtaWZAqBv8BAI3m5ZCtqpi75vF003ZAtRqDM2lN2bhCzOr7E1y9kIysPFfkVN1oNpVpujz9w6nHmkPmeeIukmxO77ztXYZCtFXEZArpLy40WfGTXAfy1CDz3zIJ0ZCRNZAqqdhUvtCeczYa3WuzJG3jqbTJlwfG8gTkp4gryBEgZDZD');
//    $LiveVideo = $fb->get('/102317076997247?fields=broadcast_start_time,creation_time,embed_html,is_manual_mode,permalink_url,status,video', 'EAAat79uIgs4BADz9haKAxFH3YEoM3ZCxyz7UcVrQsNQI5jG2qsU52XMhgaFVyUoDLur0qdsZB8PfxMTuyo8MHepyH8TpMqMnRt0K9rKHgGdkJycFdgk9XEX7BmP9veZAy12w22P6lAR2xcZAkr9cZAcvZCLZB0ZBq2TlpxpQ3PlDowZDZD');
//    $LiveVideo = $LiveVideo->getGraphNode()->asArray();
//    echo '<pre>';
//    print_r($LiveVideo);
//    echo '</pre>';

    $accounts = $fb->get('/102317076997247/events', 'EAAat79uIgs4BAOGF4dqEgCoVQZCiDwbYV69ONka74omRQoV8pjkiIXlDWfMjDbGR6Y5rACw0nrOF0DI3SFpweihuXigCiCLzIezUlJDMhxtasihnQYun2hPJ4sfE1qxOcX3z6KAJDN3iKW6mQ0qv9ZCtrM3dFOfsBK6rLC1gZDZD');
    $createLiveVideo = $accounts->getGraphEdge()->asArray();
    echo '<pre>';
    print_r($createLiveVideo);
    echo '</pre>';
} catch (\Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
?>
<!--<div id="fb-root"></div>-->
<!--<button id="liveButton">Create Live Stream To Facebook</button>-->
<!--<script>
//    window.fbAsyncInit = function () {
//        FB.init({
//            appId: '753618154817279',
//            xfbml: true,
//            version: 'v2.8'
//        });
//        FB.AppEvents.logPageView();
//    };
//
//    (function (d, s, id) {
//        var js, fjs = d.getElementsByTagName(s)[0];
//        if (d.getElementById(id)) {
//            return;
//        }
//        js = d.createElement(s);
//        js.id = id;
//        js.src = "//connect.facebook.net/en_US/sdk.js";
//        fjs.parentNode.insertBefore(js, fjs);
//    }(document, 'script', 'facebook-jssdk'));

//    document.getElementById('liveButton').onclick = function () {
//        FB.ui({
//            display: 'popup',
//            method: 'live_broadcast',
//            phase: 'create',
//        }, function (response) {
//            if (!response.id) {
//                alert('dialog canceled');
//                return;
//            }
//            console.log(response);
//            FB.ui({
//                display: 'popup',
//                method: 'live_broadcast',
//                phase: 'publish',
//                broadcast_data: response,
//            }, function (response) {
//                console.log(response);
//            });
//        });
//    };
<!--</script>
<div class="fb-video"
    data-href="https://www.facebook.com/video.php?v=130408790824664"
    data-width="500"
    data-allowfullscreen="true"></div>-->