<?php

//PayPal's script for handling purchases and purchase validation
require_once("common.php");

ini_set('log_errors', true);

ini_set('error_log', dirname(__FILE__) . '/output.log');

class pptransact {

    private $uid = null;
    private $password = null;
    private $signature = null;
    private $success;
    private $userId;
    private $state;
    private $URL;
    private $retries = 0;

    public function __construct() {
        $this->success = true;
        $this->state = "init";
        isset($_GET["userId"]) ? $this->userId = $_GET["userId"] : $this->userId = '';
        isset($_GET["kentry"]) ? $this->kentry = $_GET["kentry"] : $this->kentry = '';
        isset($_GET["sm_ak"]) ? $this->get_paypal_config($_GET["sm_ak"]) : '';
        (isset($_GET["sm_ak"]) && isset($_GET["kentry"]) && isset($_GET["type"])) ? $this->get_kentry_details($_GET["sm_ak"], $_GET["kentry"], $_GET["type"]) : '';
        (isset($_GET["sm_ak"]) && isset($_GET["ticketId"])) ? $this->get_ticket_price($_GET["sm_ak"], $_GET["ticketId"]) : '';
    }

    public function init() {
        $returnObj = array('success' => true,
            'userId' => $_GET["userId"],
            'state' => "init");

        return json_encode($returnObj);
    }

    public function get_paypal_config($sm_ak) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_config/w_get_gateways?sm_ak=" . $sm_ak . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output);
        define("UID", $result->api_user_id);  // Replace the string with your API User ID
        define("PASSWORD", $result->api_password);  // Replace the value with your API password
        define("SIG", $result->api_sig);  // Replace the string with your API Signature
        define("CURRENCY", $result->currency);  // Replace the string with your API Signature    
    }

    public function get_kentry_details($sm_ak, $kentry, $type) {
        $url = '';
        if ($type == 'cl' || $type == 'cr' || $type == 'ct' || $type == 'cb') {
            $url = 'http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_entry/get_cat_details';
        } else {
            $url = 'http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_entry/get_entry_details';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?sm_ak=" . $sm_ak . "&kentry=" . $kentry . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output);
        define("ENTRY_NAME", $result->name);
    }

    public function get_ticket_price($sm_ak, $ticket_id) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_ticket/get_ticket_price?sm_ak=" . $sm_ak . "&ticket_id=" . $ticket_id . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output);
        $this->price = (float) $result;
    }

    //Gets the purchase token from PayPal to display the purchase window
    public function getToken($userId, $entryId, $qty, $mobile, $orderId, $subId, $ticket_type, $bill_per, $protocol) {
        $dirRoot = sprintf($protocol . "://%s%s/", $_SERVER['SERVER_NAME'], dirname($_SERVER['PHP_SELF']));

        $price = '';
        $price_round = '';

        if (CURRENCY == 'JPY') {
            $price = round(($this->price));
            $price_round = round(($qty * $this->price));
        } else {
            $price = $this->price;
            $price_round = round(($qty * $this->price), 2);
        }

        $recurr_desc = '';
        if ($ticket_type == 'reg') {
            $postDetails = array('USER' => UID,
                'PWD' => PASSWORD,
                'SIGNATURE' => SIG,
                'METHOD' => "SetExpressCheckout",
                'SOLUTIONTYPE' => "Sole",
                'VERSION' => VER,
                'PAYMENTREQUEST_0_CURRENCYCODE' => CURRENCY,
                'PAYMENTREQUEST_0_AMT' => $qty * $price,
                'PAYMENTREQUEST_0_ITEMAMT' => $qty * $price,
                'PAYMENTREQUEST_0_DESC' => ENTRY_NAME,
                'PAYMENTREQUEST_0_PAYMENTACTION' => "Sale",
                'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
                'L_PAYMENTREQUEST_0_NAME0' => ENTRY_NAME,
                'L_PAYMENTREQUEST_0_NUMBER0' => $this->kentry,
                'L_PAYMENTREQUEST_0_QTY0' => $qty,
                'L_PAYMENTREQUEST_0_AMT0' => $price,
                'PAYMENTREQUEST_0_SHIPPINGAMT' => "0",
                'PAYMENTREQUEST_0_SHIPDISCAMT' => "0",
                'PAYMENTREQUEST_0_INSURANCEAMT' => "0",
                'PAYMENTREQUEST_0_PAYMENTACTION' => "sale",
                'L_PAYMENTTYPE0' => "sale",
                'PAYMENTREQUEST_0_CUSTOM' => sprintf("%s,%s,%s", $orderId, $_GET["sm_ak"], $ticket_type),
                'RETURNURL' => "{$dirRoot}success.php?data=" . $price_round . "|$userId|$entryId|" . $_GET["sm_ak"] . "|$orderId|$subId|$ticket_type|$bill_per|$recurr_desc|" . base64_encode($_GET["url"]) . "|" . $_GET["mobile"] . "|" . $_GET["type"] . "|" . $_GET["kentry"] . "|" . $_GET["pid"] . "|" . $_GET["tz"],
                'CANCELURL' => "{$dirRoot}cancel.php?data=" . $orderId . "|" . $_GET["sm_ak"] . "|" . base64_encode($_GET["url"]) . "|" . $_GET["mobile"]);
        } else {
            if ($bill_per == 'w') {
                $recurr_desc = 'Weekly%20Subscription';
            } else if ($bill_per == 'm') {
                $recurr_desc = 'Monthly%20Subscription';
            } else if ($bill_per == 'y') {
                $recurr_desc = 'Yearly%20Subscription';
            }

            $postDetails = array('USER' => UID,
                'PWD' => PASSWORD,
                'SIGNATURE' => SIG,
                'METHOD' => "SetExpressCheckout",
                'SOLUTIONTYPE' => "Sole",
                'VERSION' => VER,
                'PAYMENTREQUEST_0_CURRENCYCODE' => CURRENCY,
                'PAYMENTREQUEST_0_AMT' => $qty * $price,
                'PAYMENTREQUEST_0_ITEMAMT' => $qty * $price,
                'PAYMENTREQUEST_0_DESC' => ENTRY_NAME,
                'PAYMENTREQUEST_0_PAYMENTACTION' => "Sale",
                'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
                'L_PAYMENTREQUEST_0_NAME0' => ENTRY_NAME,
                'L_PAYMENTREQUEST_0_NUMBER0' => $this->kentry,
                'L_PAYMENTREQUEST_0_QTY0' => $qty,
                'L_PAYMENTREQUEST_0_AMT0' => $price,
                'PAYMENTREQUEST_0_SHIPPINGAMT' => "0",
                'PAYMENTREQUEST_0_SHIPDISCAMT' => "0",
                'PAYMENTREQUEST_0_INSURANCEAMT' => "0",
                'PAYMENTREQUEST_0_PAYMENTACTION' => "sale",
                'L_PAYMENTTYPE0' => "sale",
                'L_BILLINGTYPE0' => "RecurringPayments",
                'L_BILLINGAGREEMENTDESCRIPTION0' => $recurr_desc,
                'PAYMENTREQUEST_0_CUSTOM' => sprintf("%s,%s,%s", $orderId, $_GET["sm_ak"], $ticket_type),
                'RETURNURL' => "{$dirRoot}success.php?data=" . $price_round . "|$userId|$entryId|" . $_GET["sm_ak"] . "|$orderId|$subId|$ticket_type|$bill_per|$recurr_desc|" . base64_encode($_GET["url"]) . "|" . $_GET["mobile"] . "|" . $_GET["type"] . "|" . $_GET["kentry"] . "|" . $_GET["pid"] . "|" . $_GET["tz"],
                'CANCELURL' => "{$dirRoot}cancel.php?data=" . $orderId . "|" . $_GET["sm_ak"] . "|" . base64_encode($_GET["url"]) . "|" . $_GET["mobile"]);
        }

        $arrPostVals = array_map(create_function('$key, $value', 'return $key."=".$value."&";'), array_keys($postDetails), array_values($postDetails));
        $postVals = implode($arrPostVals);
        if ($_GET["sm_ak"] == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            $response = parseString(runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals));
        } else {
            $response = parseString(runCurl(URLBASE, $postVals));
        }

        $r1 = json_encode($postVals);
        $phpStringArray = str_replace(array("{", "}", ":"), array("array(", ")", "=>"), $r1);
        error_log($phpStringArray);

        //forward the user to login and accept transaction
        if ($_GET["sm_ak"] == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            if ($mobile === "true") {
                $URL = 'https://www.sandbox.paypal.com/webscr';
            } else {
                $URL = 'https://www.sandbox.paypal.com/incontext';
            }
        } else {
            if ($mobile === "true") {
                $URL = URLREDIRECT;
            } else {
                $URL = URLREDIRECTINCONTEXT;
            }
        }

        $returnObj = '';
        if ($response["TOKEN"]) {
            $redirect = sprintf("%s?useraction=commit&token=%s", $URL, urldecode($response["TOKEN"]));
            $returnObj = array('success' => true,
                'redirecturl' => $redirect);

            $r2 = json_encode($response);
            $phpStringArray = str_replace(array("{", "}", ":"), array("array(", ")", "=>"), $r2);
            error_log($phpStringArray);
            echo json_encode($returnObj);
        } else {
            if ($this->retries >= 3) {
                $redirect = sprintf("%s?useraction=commit&token=%s", $URL, urldecode($response["TOKEN"]));
                $returnObj = array('success' => false,
                    'redirecturl' => $redirect);
                $this->cancelOrder($orderId, $_GET["sm_ak"]);
                $r2 = json_encode($response);
                $phpStringArray = str_replace(array("{", "}", ":"), array("array(", ")", "=>"), $r2);
                error_log($phpStringArray);
                echo json_encode($returnObj);
            } else {
                sleep(2);
                $this->retries++;
                $this->getToken($userId, $entryId, $qty, $mobile, $orderId, $subId, $ticket_type, $bill_per, $protocol);
            }
        }
        //print_r($response);
    }

    //Confirms the payment
    public function commitPayment($payerId, $token, $amt, $uid, $sm_ak, $orderId, $subId, $ticket_type, $bill_per, $recurr_desc, $mobile) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_config/w_get_gateways?sm_ak=" . $sm_ak . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output);
        $returnObj = array();
        $postDetails = array('USER' => $result->api_user_id,
            'PWD' => $result->api_password,
            'SIGNATURE' => $result->api_sig,
            'METHOD' => "DoExpressCheckoutPayment",
            'VERSION' => VER,
            'AMT' => $amt,
            'TOKEN' => $token,
            'PAYERID' => $payerId,
            'PAYMENTACTION' => "Sale",
            'CURRENCYCODE' => $result->currency);

        $arrPostVals = array_map(create_function('$key, $value', 'return $key."=".$value."&";'), array_keys($postDetails), array_values($postDetails));
        $postVals = rtrim(implode($arrPostVals), "&");

        if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            $response = runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals);
        } else {
            $response = runCurl(URLBASE, $postVals);
        }
        //HACK: On sandbox the first request will fail - we need to wait for 2 seconds and then try again
        if ($response == false) {
            sleep(2);
            if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
                $response = parseString(runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals));
            } else {
                $response = parseString(runCurl(URLBASE, $postVals));
            }
        } else {
            $response = parseString($response);
        }

        $details = $this->verifyPurchase($result->api_user_id, $result->api_password, $result->api_sig, $response["PAYMENTINFO_0_TRANSACTIONID"], $sm_ak);

        $receiverEmail = $details["RECEIVEREMAIL"];
        $receiverId = $details["RECEIVERID"];
        $firstName = $details["FIRSTNAME"];
        $lastName = $details["LASTNAME"];
        $payerEmail = $details["EMAIL"];
        $payerId = $details["PAYERID"];
        $countryCode = $details["COUNTRYCODE"];
        $paymentStatus = $details["PAYMENTSTATUS"];
        $transactionId = $details["TRANSACTIONID"];
        $paymentType = $details["PAYMENTTYPE"];
        $orderTime = $details["ORDERTIME"];
        $itemName = $details["L_NAME0"];
        $this->recordOrder($sm_ak, $orderId, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $paymentStatus, $transactionId, $paymentType, $orderTime, $itemName);

        $returnObj['paymentStatus'] = $details["PAYMENTSTATUS"];
        if ($sm_ak == 'EQdhUO8gFcw1m3Et/ni1J1TYeOCSJm5pMvx9Bj3oC0g=') {
            $returnObj['thank_you'] = true;
        } else {
            $returnObj['thank_you'] = false;
        }

        if ($ticket_type == 'sub') {
            if ($details["PAYMENTSTATUS"] == 'Completed') {
                $this->createRecurringProfile($result->api_user_id, $result->api_password, $result->api_sig, $result->currency, $token, $amt, $uid, $orderId, $subId, $recurr_desc, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $sm_ak);
            } else {
                $this->createRecurringSuspenedProfile($result->api_user_id, $result->api_password, $result->api_sig, $result->currency, $token, $amt, $uid, $orderId, $subId, $recurr_desc, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $sm_ak);
            }
        }

        return json_encode($returnObj);
        //print_r($details);
    }

    public function createRecurringProfile($api_user_id, $api_password, $api_sig, $currency, $token, $amt, $uid, $orderId, $subId, $recurr_desc, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $sm_ak) {
        $recurr_date = '';
        if ($bill_per == 'w') {
            $recurr_date = '+1 week';
            $recurr_period = 'Week';
        } else if ($bill_per == 'm') {
            $recurr_date = '+1 month';
            $recurr_period = 'Month';
        } else if ($bill_per == 'y') {
            $recurr_date = '+1 year';
            $recurr_period = 'Year';
        }

        $DaysTimestamp = strtotime($recurr_date, strtotime('now'));
        $Mo = date('m', $DaysTimestamp);
        $Day = date('d', $DaysTimestamp);
        $Year = date('Y', $DaysTimestamp);
        $StartDateGMT = $Year . '-' . $Mo . '-' . $Day . 'T00:00:00\Z';

        $postDetails = array('USER' => $api_user_id,
            'PWD' => $api_password,
            'SIGNATURE' => $api_sig,
            'METHOD' => "CreateRecurringPaymentsProfile",
            'VERSION' => VER,
            'AMT' => $amt,
            'TOKEN' => $token,
            'PROFILESTARTDATE' => $StartDateGMT,
            'DESC' => $recurr_desc,
            'BILLINGPERIOD' => $recurr_period,
            'BILLINGFREQUENCY' => 1,
            'MAXFAILEDPAYMENTS' => 2,
            'PROFILEREFERENCE' => sprintf("%s,%s", $sm_ak, $subId),
            'CURRENCYCODE' => $currency);

        $arrPostVals = array_map(create_function('$key, $value', 'return $key."=".$value."&";'), array_keys($postDetails), array_values($postDetails));
        $postVals = rtrim(implode($arrPostVals), "&");

        if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            $response = runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals);
        } else {
            $response = runCurl(URLBASE, $postVals);
        }
        //HACK: On sandbox the first request will fail - we need to wait for 2 seconds and then try again
        if ($response == false) {
            sleep(2);
            if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
                $response = parseString(runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals));
            } else {
                $response = parseString(runCurl(URLBASE, $postVals));
            }
        } else {
            $response = parseString($response);
        }

        // print_r($response);
//        echo $response['PROFILEID'];

        $this->recordSub($sm_ak, $orderId, $subId, $response['PROFILEID'], $response['PROFILESTATUS'], $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $response['TIMESTAMP']);
        //return json_encode($response);
        //print_r($response);
    }

    public function createRecurringSuspenedProfile($api_user_id, $api_password, $api_sig, $currency, $token, $amt, $uid, $orderId, $subId, $recurr_desc, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $sm_ak) {
        $recurr_date = '';
        if ($bill_per == 'w') {
            $recurr_date = '+1 week';
            $recurr_period = 'Week';
        } else if ($bill_per == 'm') {
            $recurr_date = '+1 month';
            $recurr_period = 'Month';
        } else if ($bill_per == 'y') {
            $recurr_date = '+1 year';
            $recurr_period = 'Year';
        }

        $DaysTimestamp = strtotime($recurr_date, strtotime('now'));
        $Mo = date('m', $DaysTimestamp);
        $Day = date('d', $DaysTimestamp);
        $Year = date('Y', $DaysTimestamp);
        $StartDateGMT = $Year . '-' . $Mo . '-' . $Day . 'T00:00:00\Z';

        $postDetails = array('USER' => $api_user_id,
            'PWD' => $api_password,
            'SIGNATURE' => $api_sig,
            'METHOD' => "CreateRecurringPaymentsProfile",
            'VERSION' => VER,
            'AMT' => $amt,
            'TOKEN' => $token,
            'PROFILESTARTDATE' => $StartDateGMT,
            'DESC' => $recurr_desc,
            'BILLINGPERIOD' => $recurr_period,
            'BILLINGFREQUENCY' => 1,
            'PROFILEREFERENCE' => sprintf("%s,%s", $sm_ak, $subId),
            'CURRENCYCODE' => $currency);

        $arrPostVals = array_map(create_function('$key, $value', 'return $key."=".$value."&";'), array_keys($postDetails), array_values($postDetails));
        $postVals = rtrim(implode($arrPostVals), "&");

        if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            $response = runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals);
        } else {
            $response = runCurl(URLBASE, $postVals);
        }
        //HACK: On sandbox the first request will fail - we need to wait for 2 seconds and then try again
        if ($response == false) {
            sleep(2);
            if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
                $response = parseString(runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals));
            } else {
                $response = parseString(runCurl(URLBASE, $postVals));
            }
        } else {
            $response = parseString($response);
        }

        // print_r($response);
//        echo $response['PROFILEID'];

        $this->suspenedSubProfile($api_user_id, $api_password, $api_sig, $sm_ak, $orderId, $subId, $response['PROFILEID'], $response['PROFILESTATUS'], $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $response['TIMESTAMP']);
        //return json_encode($response);
        //print_r($response);
    }

    public function suspenedSubProfile($api_user_id, $api_password, $api_sig, $sm_ak, $orderId, $sub_id, $profile_id, $sub_status, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $date_created) {
        $postDetails = array(
            'USER' => $api_user_id,
            'PWD' => $api_password,
            'SIGNATURE' => $api_sig,
            'METHOD' => "ManageRecurringPaymentsProfileStatus",
            'VERSION' => VER,
            'PROFILEID' => $profile_id,
            'ACTION' => 'Suspend'
        );

        $arrPostVals = array_map(create_function('$key, $value', 'return $key."=".$value."&";'), array_keys($postDetails), array_values($postDetails));
        $postVals = rtrim(implode($arrPostVals), "&");
        if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            $response = runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals);
        } else {
            $response = runCurl(URLBASE, $postVals);
        }
        //HACK: On sandbox the first request will fail - we need to wait for 2 seconds and then try again
        if ($response == false) {
            sleep(2);
            if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
                $response = parseString(runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals));
            } else {
                $response = parseString(runCurl(URLBASE, $postVals));
            }
        } else {
            $response = parseString($response);
        }

        $sub_status = 'SuspendedProfile';

        $this->recordSub($sm_ak, $orderId, $sub_id, $profile_id, $sub_status, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $date_created);
    }

    public function recordOrder($sm_ak, $order_id, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $paymentStatus, $transactionId, $paymentType, $orderTime, $itemName) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_orders/insert_pp_details?sm_ak=" . $sm_ak . "&order_id=" . $order_id . "&receiverEmail=" . $receiverEmail . "&receiverId=" . $receiverId . "&firstName=" . $firstName . "&lastName=" . $lastName . "&payerEmail=" . $payerEmail . "&payerId=" . $payerId . "&currencyCode=" . $countryCode . "&paymentStatus=" . $paymentStatus . "&transactionId=" . $transactionId . "&paymentType=" . $paymentType . "&orderTime=" . $orderTime . "&itemName=" . $itemName . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }

    public function recordSub($sm_ak, $orderId, $sub_id, $profile_id, $sub_status, $receiverEmail, $receiverId, $firstName, $lastName, $payerEmail, $payerId, $countryCode, $bill_per, $date_created) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_orders/insert_pp_sub_details?sm_ak=" . $sm_ak . "&order_id=" . $orderId . "&sub_id=" . $sub_id . "&profile_id=" . $profile_id . "&sub_status=" . $sub_status . "&receiverEmail=" . $receiverEmail . "&receiverId=" . $receiverId . "&firstName=" . $firstName . "&lastName=" . $lastName . "&payerEmail=" . $payerEmail . "&payerId=" . $payerId . "&countryCode=" . $countryCode . "&bill_per=" . $bill_per . "&date_created=" . $date_created . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }

    public function cancelOrder($order_id, $sm_ak) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ppv.streamingmediahosting.com/index.php/api_dev/ppv_orders/w_delete_order?sm_ak=" . $sm_ak . "&order_id=" . $order_id . "&format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }

    //Verifies whether a video or channel was purchased
    public function verifyPurchase($user, $pass, $sig, $transactionId, $sm_ak) {
        $postDetails = array('USER' => $user,
            'PWD' => $pass,
            'SIGNATURE' => $sig,
            'METHOD' => "GetTransactionDetails",
            'VERSION' => VER,
            'TRANSACTIONID' => $transactionId);
        $arrPostVals = array_map(create_function('$key, $value', 'return $key."=".$value."&";'), array_keys($postDetails), array_values($postDetails));
        $postVals = rtrim(implode($arrPostVals), "&");
        if ($sm_ak == 'E78WQinB9EQnuaM4ipvizBZqHbPoRpM/ptdfYhZhAgk=') {
            $response = parseString(runCurl('https://api-3t.sandbox.paypal.com/nvp', $postVals));
        } else {
            $response = parseString(runCurl(URLBASE, $postVals));
        }

        return $response;
    }

}

$transact = new pptransact();

if (array_key_exists("method", $_GET))
    switch ($_GET["method"]) {
        case "init": $connect->init();
            break;
        case "getToken": $transact->getToken($_GET["userId"], $_GET["entryId"], $_GET["qty"], $_GET["mobile"], $_GET["orderId"], $_GET["subId"], $_GET["ticket_type"], $_GET["bill_per"], $_GET["protocol"]);
            break;
        case "commitPayment": $transact->commitPayment($_GET["payerId"], $_GET["token"], $_GET["amt"], $_GET["itemId"]);
            break;
        case "verifyPayment": $transact->verifyPurchase($_GET["userId"], $_GET["itemId"], $_GET["transactions"]);
            break;
    }