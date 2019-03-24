<?php

/** 
*
* Vesta Web Interface
*
* Copyright (C) 2019 Carter Roeser <carter@cdgtech.one>
* https://cdgco.github.io/VestaWebInterface
*
* Vesta Web Interface is free software: you can redistribute it and/or modify
* it under the terms of version 3 of the GNU General Public License as published 
* by the Free Software Foundation.
*
* Vesta Web Interface is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with Vesta Web Interface.  If not, see
* <https://github.com/cdgco/VestaWebInterface/blob/master/LICENSE>.
*
*/

session_start();
$configlocation = "../../../includes/";
if (file_exists( '../../../includes/config.php' )) { require( '../../../includes/includes.php'); }  else { header( 'Location: ../../../install' ); exit(); };
if(isset($_SESSION['loggedin'])) {
    if(base64_decode($_SESSION['loggedin']) == 'true') { header('Location: ../../../index.php'); exit();  }
    
if(isset($regenabled) && $regenabled != 'true'){ header("Location: ../../../error-pages/403.html"); }

if(!in_array('vwi-billing', $plugins)) {
    header( 'Location: ../../../register.php' ); exit();
}    
    
    
if ((!isset($_POST['fname-x'])) || ($_POST['fname-x'] == '')) { header('Location: register.php?error=1'); exit();}
elseif ((!isset($_POST['lname-x'])) || ($_POST['lname-x'] == '')) { header('Location: register.php?error=1'); exit();}
elseif ((!isset($_POST['uname-x'])) || ($_POST['uname-x'] == '')) { header('Location: register.php?error=1'); exit();}
elseif ((!isset($_POST['email-x'])) || ($_POST['email-x'] == '')) { header('Location: register.php?error=1'); exit();}
elseif ((!isset($_POST['pw-x'])) || ($_POST['pw-x'] == '')) { header('Location: register.php?error=1'); exit();}
elseif ((!isset($_POST['plan-x'])) || ($_POST['plan-x'] == '')) { header('Location: register.php?error=1'); exit();}

 require("../stripe-lib/init.php");

if($configstyle != '2') {
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $billingconfig = array(); $billingresult=mysqli_query($con,"SELECT VARIABLE,VALUE FROM `" . $mysql_table . "billing-config`");
    while ($bcrow = mysqli_fetch_assoc($billingresult)) { $billingconfig[$bcrow["VARIABLE"]] = $bcrow["VALUE"]; }
    mysqli_free_result($billingresult); mysqli_close($con);
    
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $billingplans = array(); $billingresult2=mysqli_query($con,"SELECT PACKAGE,ID,DISPLAY FROM `" . $mysql_table . "billing-plans`");
    while ($bprow = mysqli_fetch_assoc($billingresult2)) { $billingplans[$bprow["PACKAGE"]] = ['NAME' => $bprow["PACKAGE"], 'ID' => $bprow["ID"], 'DISPLAY' => $bprow["DISPLAY"]]; }
    mysqli_free_result($billingresult2); mysqli_close($con);
}
else {
    
    if (!$con) { $billingconfig = json_decode(file_get_contents( $co1 . 'billingconfig.json'), true);
                 $billingplans = json_decode(file_get_contents( $co1 . 'billingplans.json'), true); }
    else { 
        $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
        $billingconfig = array(); $billingresult=mysqli_query($con,"SELECT VARIABLE,VALUE FROM `" . $mysql_table . "billing-config`");
        while ($bcrow = mysqli_fetch_assoc($billingresult)) { $billingconfig[$bcrow["VARIABLE"]] = $bcrow["VALUE"]; }
        mysqli_free_result($billingresult); mysqli_close($con);
        if (!file_exists( $co1 . 'billingconfig.json' )) { 
            file_put_contents( $co1 . "billingconfig.json",json_encode($billingconfig));
        }  
        elseif ((time()-filemtime( $co1 . "billingconfig.json")) > 1800 || $billingconfig != json_decode(file_get_contents( $co1 . 'billingconfig.json'), true)) { 
            file_put_contents( $co1 . "billingconfig.json",json_encode($billingconfig)); 
        }
        
        $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
        $billingplans = array(); $billingresult2=mysqli_query($con,"SELECT PACKAGE,ID,DISPLAY FROM `" . $mysql_table . "billing-plans`");
        while ($bprow = mysqli_fetch_assoc($billingresult2)) { $billingplans[$bprow["PACKAGE"]] = ['NAME' => $bprow["PACKAGE"], 'ID' => $bprow["ID"], 'DISPLAY' => $bprow["DISPLAY"]]; }
        mysqli_free_result($billingresult2); mysqli_close($con);
        if (!file_exists( $co1 . 'billingplans.json' )) { 
            file_put_contents( $co1 . "billingplans.json",json_encode($billingplans));
        }  
        elseif ((time()-filemtime( $co1 . "billingplans.json")) > 1800 || $billingplans != json_decode(file_get_contents( $co1 . 'billingplans.json'), true)) { 
            file_put_contents( $co1 . "billingplans.json",json_encode($billingplans)); 
        }
        
    }
}
    
\Stripe\Stripe::setApiKey($billingconfig['sec_key']);

$postvars = array(
    array('hash' => $vst_apikey, 'user' => $vst_username,'password' => $vst_password,'cmd' => 'v-list-user-packages','arg1' => 'json')
);

$curl0 = curl_init();
$curlstart = 0; 


while($curlstart <= 0) {
    curl_setopt(${'curl' . $curlstart}, CURLOPT_URL, $vst_url);
    curl_setopt(${'curl' . $curlstart}, CURLOPT_RETURNTRANSFER,true);
    curl_setopt(${'curl' . $curlstart}, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt(${'curl' . $curlstart}, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt(${'curl' . $curlstart}, CURLOPT_POST, true);
    curl_setopt(${'curl' . $curlstart}, CURLOPT_POSTFIELDS, http_build_query($postvars[$curlstart]));
    $curlstart++;
} 

$packname = array_keys(json_decode(curl_exec($curl0), true));
$packdata = array_values(json_decode(curl_exec($curl0), true));
$billingname = array_keys($billingplans);
$billingdata = array_values($billingplans);
    
$searchpackage = array_search($_POST['plan'], $billingname);
if($billingdata[$searchpackage]['NAME'] === $_POST['plan'] && $billingdata[$searchpackage]['DISPLAY'] == 'true') {
    if($billingdata[$searchpackage]['ID'] == '') {
          $paidplan = 'false';
    }
    else {
        try { $currentplan = \Stripe\Plan::retrieve('vwi_plan_' . $billingdata[$searchpackage]['ID'])->__toArray(true); } 
        catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
        if(isset($err) || $err != '') { header('Location: register.php?error=3'); exit(); }
        else {
            try { $currentproduct = \Stripe\Product::retrieve('vwi_prod_' . $billingdata[$searchpackage]['ID'])->__toArray(true); } 
            catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
            if(isset($err) || $err != '') { header('Location: register.php?error=3'); exit(); }
            else { $paidplan = 'true'; }
        }
    }
}    
    
$vst_returncode = 'yes';
$vst_command = 'v-add-user';
$username1 = $_POST['uname-x'];
$password = $_POST['pw-x'];
$email = $_POST['email-x']; 
$package = $_POST['plan-x'];
$firstname = $_POST['fname-x']; 
$name = $_POST['lname-x']; 
$fullname = $firstname . ' ' . $name;
$currenttime = time();

$postvars = array(
    'hash' => $vst_apikey, 'user' => $vst_username,
    'password' => $vst_password,
    'returncode' => $vst_returncode,
    'cmd' => $vst_command,
    'arg1' => $username1,
    'arg2' => $password,
    'arg3' => $email,
    'arg4' => $package,
    'arg5' => $firstname,
    'arg6' => $name
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $vst_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postvars));
$answer = curl_exec($curl);

if (INTERAKT_APP_ID != '' && INTERAKT_API_KEY != ''){

    $postvars1 = array(
        'uname' => $username1,
        'email' => $email,
        'package' => $package,
        'name' => $fullname,
        'created_at' => $currenttime
    );
    $curl0 = curl_init();

    curl_setopt($curl0, CURLOPT_URL, 'https://app.interakt.co/api/v1/members');
    curl_setopt($curl0, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl0, CURLOPT_USERPWD, INTERAKT_APP_ID . ':' . INTERAKT_API_KEY);
    curl_setopt($curl0, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl0, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl0, CURLOPT_POST, true);
    curl_setopt($curl0, CURLOPT_POSTFIELDS, http_build_query($postvars1));
    $r1 = curl_exec($curl0);
}

if($answer == '0' && $paidplan != 'false') {
    try {
        $customer = \Stripe\Customer::create([
            "source" => $_POST['stripeToken'],
            "email" => $email,
            "metadata" => ["name" => $fullname, "username" => $username1]
        ]);
    }
    catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
    if(isset($err) || $err != '') {
        header("Location: register.php?stripeerr=" . $err);
    }
    else { 
        $cus_id = json_decode($customer->__toJSON(), true)['id'];
        try {
            $subscription = \Stripe\Subscription::create([
                "customer" => $cus_id,
                "items" => [["plan" => "vwi_plan_" . $billingdata[$searchpackage]['ID']]]
            ]);
        }
        catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
        if(isset($err) || $err != '') {
            header("Location: register.php?stripeerr=" . $err);
        }
        else {
            try {
                $curinvoice = \Stripe\Invoice::retrieve(json_decode($subscription->__toJSON(), true)['latest_invoice']);
            }
            catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
            if(isset($err) || $err != '') {
                header("Location: register.php?stripeerr=" . $err);
            }
            else {
                echo "URL: " . json_decode($curinvoice->__toJSON(), true)['hosted_invoice_url'];
            }
        }
    }
]);
    
    
}    
    
if(isset($answer)) {
    header("Location: ../../../login.php?code=".$answer); exit();
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="../../../css/style.css" rel="stylesheet">
    </head>
    <body class="fix-header">
        <div class="preloader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> 
            </svg>
        </div>
    </body>
    <script src="../../components/jquery/jquery.min.js"></script>
</html>