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
}
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

 require("../stripe-php/init.php");

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
    array('hash' => $vst_apikey, 'user' => $vst_username,'password' => $vst_password,'cmd' => 'v-list-user-packages','arg1' => 'json'),
    array('hash' => $vst_apikey, 'user' => $vst_username,'password' => $vst_password,'cmd' => 'v-list-sys-info','arg1' => 'json')
);

$curl0 = curl_init();
$curl1 = curl_init();
$curlstart = 0; 


while($curlstart <= 1) {
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
$serverconnection = array_values(json_decode(curl_exec($curl1), true))[0]['OS'];
    

setlocale(LC_CTYPE, $locale);
setlocale(LC_MESSAGES, $locale);
bindtextdomain('messages', 'locale');
textdomain('messages');

$searchpackage = array_search($_POST['plan-x'], $billingname);
if($billingdata[$searchpackage]['NAME'] === $_POST['plan-x'] && $billingdata[$searchpackage]['DISPLAY'] == 'true') {
    if($billingdata[$searchpackage]['ID'] == '') {
          $paidplan = 'false';
    }
    else {
        try { $currentplan = \Stripe\Plan::retrieve('vwi_plan_' . $billingdata[$searchpackage]['ID'])->__toArray(true); } 
        catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
        if(isset($err) || $err != '') { header('Location: register.php?stripeerr=' . $err); exit();}
        else {
            try { $currentproduct = \Stripe\Product::retrieve('vwi_prod_' . $billingdata[$searchpackage]['ID'])->__toArray(true); } 
            catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
            if(isset($err) || $err != '') { header('Location: register.php?stripeerr=' . $err); exit();}
            else { 
                $paidplan = 'true'; 
                $curplanid = $billingdata[$searchpackage]['ID'];
                 }
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

if($answer != '0') {
    header("Location: register.php?error=" . $answer);
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
                "items" => [["plan" => "vwi_plan_" . $curplanid]],
                "trial_from_plan" => true
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
                $invoicelink = json_decode($curinvoice->__toJSON(), true)['hosted_invoice_url'];
            }
        }
    }
}    

?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="16x16" href="../../images/favicon.png">
        <title><?php echo $sitetitle; ?> - <?php echo _('Register'); ?></title>
        <link href="../../components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../components/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
        <link href="../../components/sweetalert2/sweetalert2.min.css" rel="stylesheet">
        <link href="../../components/animate.css/animate.min.css" rel="stylesheet">
        <link href="../../../css/style.css" rel="stylesheet">
        <link href="../../../css/colors/<?php if(isset($_COOKIE['theme']) && $themecolor != 'custom.css') { echo base64_decode($_COOKIE['theme']); } else {echo $themecolor; } ?>" id="theme" rel="stylesheet">
        <?php if($themecolor == "custom.css") { require( '../../../css/colors/custom.php'); } ?>
        <style>
            html {
                overflow-y: scroll;
            }
            input:-webkit-autofill,
            input:-webkit-autofill:hover, 
            input:-webkit-autofill:focus
            input:-webkit-autofill, 
            textarea:-webkit-autofill,
            textarea:-webkit-autofill:hover
            textarea:-webkit-autofill:focus,
            select:-webkit-autofill,
            select:-webkit-autofill:hover,
            select:-webkit-autofill:focus {
                border: 1px solid #e4e7ea;
                -webkit-text-fill-color: #565656 !important;
                -webkit-box-shadow: 0 0 0px 1000px #ffffff inset;
                transition: background-color 5000s ease-in-out 0s;
            }
            .color-button {
                color: #fff !important;
            }
        </style>
        <?php if(GOOGLE_ANALYTICS_ID != ''){ echo "<script async src='https://www.googletagmanager.com/gtag/js?id=" . GOOGLE_ANALYTICS_ID . "'></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '" . GOOGLE_ANALYTICS_ID . "');</script>"; } ?>
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>
        <section id="wrapper" class="new-login-register">
            <div class="lg-info-panel bg-theme">
                <div class="inner-panel">
                    <a href="javascript:void(0)" class="p-20 di"><img src="../../images/<?php echo $cpicon; ?>" class="logo-1"></a>
                    <div class="lg-content">
                        <h2><?php echo $sitetitle; ?> <?php echo _('Control Panel'); ?> <br></h2><p><?php require '../../../includes/versioncheck.php'; ?></p>
                    </div>
                </div>
            </div>

            <div class="new-login-box">
                <div class="white-box">
                    <h3 class="box-title m-b-0"><?php echo _('Sign up for'); ?> <?php echo $sitetitle; ?></h3> <small><?php echo _('Subscription Complete'); ?></small>
                    <form class="form-horizontal new-lg-form" method="get" id="loginform" action="../../../login.php">                         
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                <button class="btn btn-lg btn-block text-uppercase waves-effect waves-light btn-success" style="border:none;" id="payment-submit" type="submit">Login</button>
                            </div>
                        </div>
                        <?php if(isset($invoicelink) && $invoicelink != '') {
                        echo '
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                 <a href="'.$invoicelink.'" target="_blank" style="color: inherit;text-decoration: inherit;">
                                <button class="btn btn-lg btn-block text-uppercase waves-effect waves-light btn-muted" style="border:none;" id="payment-submit" type="button">View Invoice</button></a>
                            </div>
                        </div>';
                        } ?>
                    </form>
                </div>
            </div>
        </section>
        <script src="../../components/jquery/jquery.min.js"></script>
        <script src="../../components/sweetalert2/sweetalert2.min.js"></script>
        <script src="../../components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="../../components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../../components/bootstrap-select/js/bootstrap-select.min.js"></script>
        <script src="../../components/metismenu/dist/metisMenu.min.js"></script>
        <script src="../../components/waves/waves.js"></script>
        <script src="https://js.stripe.com/v3/"></script>
        <script src="../../../js/main.js"></script>
        <script type="text/javascript">
            
            function loadLoader(){
                swal({
                    title: '<?php echo _("Loading"); ?>',
                    text: '',
                    onOpen: function () {
                        swal.showLoading()
                    }
                })};
            Waves.attach('.button', ['waves-effect']);
            Waves.init();
            const toast1 = Swal.mixin({
              toast: true,
              position: "top-end",
              showConfirmButton: false,
              timer: 3500
            });
          const toast2 = Swal.mixin({
              toast: true,
              position: "top-end",
              showConfirmButton: false
            });
            
             <?php 
            if($configstyle == '2'){
                if($warningson == "all"){
                    if(substr(sprintf('%o', fileperms($configlocation)), -4) == '0777') {
                        echo "toast1({ 
                                text: '"._("Includes folder has not been secured")."',
                                type: 'warning'
                            });";

                    } 
                    if(isset($mysqldown) && $mysqldown == 'yes') {
                        echo "toast2({
                                title: '" . _("Database Error") . "',
                                text: '" . _("MySQL Server Failed To Connect") . "',
                                type: 'error'
                            });";
                    } 
                }
            }
            else {
                if(substr(sprintf('%o', fileperms($configlocation)), -4) == '0777') {
                    echo "toast1({ 
                            text: '"._("Includes folder has not been secured")."',
                            type: 'warning'
                        });";

                } 
                if(isset($mysqldown) && $mysqldown == 'yes') {
                    echo "toast2({
                           title: '" . _("Database Error") . "',
                            text: '" . _("MySQL Server Failed To Connect") . "',
                            type: 'error'
                        });";

                }    
            }
            if(!isset($serverconnection)){
            echo "toast2({
                    text: '" . _("Failed to connect to server. Please check config.") . "',
                    type: 'error'
            });"; }
            ?>
        </script>
    </body>
</html>