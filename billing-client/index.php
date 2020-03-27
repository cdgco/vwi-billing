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
$configlocation = "../../includes/";
if (file_exists( '../../includes/config.php' )) { require( '../../includes/includes.php'); }  else { header( 'Location: ../../install' ); exit();};

if(base64_decode($_SESSION['loggedin']) == 'true') {}
else { header('Location: ../../login.php?to=plugins/vwi-billing'); exit(); }

require("../billing/stripe-php/init.php");

if($configstyle != '2') {
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $billingconfig = array(); $billingresult=mysqli_query($con,"SELECT VARIABLE,VALUE FROM `" . $mysql_table . "billing-config`");
    $billingplans = array(); $billingresult2=mysqli_query($con,"SELECT PACKAGE,ID,DISPLAY FROM `" . $mysql_table . "billing-plans`");
    $billingcustomers = array(); $billingresult3=mysqli_query($con,"SELECT username,ID FROM `" . $mysql_table . "billing-customers`");
    while ($bcrow = mysqli_fetch_assoc($billingresult)) { $billingconfig[$bcrow["VARIABLE"]] = $bcrow["VALUE"]; }
    while ($bprow = mysqli_fetch_assoc($billingresult2)) { $billingplans[$bprow["PACKAGE"]] = ['NAME' => $bprow["PACKAGE"], 'ID' => $bprow["ID"], 'DISPLAY' => $bprow["DISPLAY"]]; }
    while ($burow = mysqli_fetch_assoc($billingresult3)) { $billingcustomers[$burow["username"]] = $burow["ID"]; }
    mysqli_free_result($billingresult);mysqli_free_result($billingresult2);mysqli_free_result($billingresult3);mysqli_close($con);
}
else {
    
    if (!$con) { $billingconfig = json_decode(file_get_contents( $co1 . 'billingconfig.json'), true);
                 $billingplans = json_decode(file_get_contents( $co1 . 'billingplans.json'), true);
               $billingcustomers = json_decode(file_get_contents( $co1 . 'billingcustomers.json'), true); }
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
        
        $billingplans = array(); $billingresult2=mysqli_query($con,"SELECT PACKAGE,ID,DISPLAY FROM `" . $mysql_table . "billing-plans`");
        while ($bprow = mysqli_fetch_assoc($billingresult2)) { $billingplans[$bprow["PACKAGE"]] = ['NAME' => $bprow["PACKAGE"], 'ID' => $bprow["ID"], 'DISPLAY' => $bprow["DISPLAY"]]; }
        mysqli_free_result($billingresult2); mysqli_close($con);
        if (!file_exists( $co1 . 'billingplans.json' )) { 
            file_put_contents( $co1 . "billingplans.json",json_encode($billingplans));
        }  
        elseif ((time()-filemtime( $co1 . "billingplans.json")) > 1800 || $billingplans != json_decode(file_get_contents( $co1 . 'billingplans.json'), true)) { 
            file_put_contents( $co1 . "billingplans.json",json_encode($billingplans)); 
        }
        $billingcustomers = array(); $billingresult3=mysqli_query($con,"SELECT PACKAGE,ID,DISPLAY FROM `" . $mysql_table . "billing-plans`");
        while ($burow = mysqli_fetch_assoc($billingresult3)) { $billingcustomers[$burow["username"]] = $burow["ID"]; }
        mysqli_free_result($billingresult3); mysqli_close($con);
        if (!file_exists( $co1 . 'billingcustomers.json' )) { 
            file_put_contents( $co1 . "billingcustomers.json",json_encode($billingcustomers));
        }  
        elseif ((time()-filemtime( $co1 . "billingcustomers.json")) > 1800 || $billingcustomers != json_decode(file_get_contents( $co1 . 'billingcustomers.json'), true)) { 
            file_put_contents( $co1 . "billingcustomers.json",json_encode($billingcustomers)); 
        }
        
    }
}
\Stripe\Stripe::setApiKey($billingconfig['sec_key']);

$postvars = array(
    array('hash' => $vst_apikey, 'user' => $vst_username,'password' => $vst_password,'cmd' => 'v-list-user','arg1' => $username,'arg2' => 'json'),
    array('hash' => $vst_apikey, 'user' => $vst_username,'password' => $vst_password,'cmd' => 'v-list-user-packages','arg1' => 'json')
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

$admindata = json_decode(curl_exec($curl0), true)[$username];
$packname = array_keys(json_decode(curl_exec($curl1), true));
$packdata = array_values(json_decode(curl_exec($curl1), true));
$billingname = array_keys($billingplans);
$billingdata = array_values($billingplans);
$customeruname = array_keys($billingcustomers);
$customerid = array_values($billingcustomers);
$useremail = $admindata['CONTACT'];
if(isset($admindata['LANGUAGE'])){ $locale = $ulang[$admindata['LANGUAGE']]; }
_setlocale("LC_CTYPE", $locale); 
_setlocale("LC_MESSAGES", $locale);
_bindtextdomain('messages', '../../locale');
_textdomain('messages');

$searchcustomer = array_search($username, $customeruname);
if($customeruname[$searchcustomer] == $username && $customerid[$searchcustomer] != '') {
    try { $currentcustomer = \Stripe\Customer::retrieve($customerid[$searchcustomer])->__toArray(true); } 
    catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
    if(isset($err) || $err != '') { $stripeerr = $err; }
    else {
        try { $customersubs = \Stripe\Subscription::all(["customer" => $customerid[$searchcustomer], "limit" => 100, "status" => 'all'])->__toArray(true); } 
        catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
        if(isset($err) || $err != '') { $stripeerr = $err; }
        else {
            try { $customercharges = \Stripe\Charge::all(["customer" => $customerid[$searchcustomer], "limit" => 100])->__toArray(true); } 
            catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
            if(isset($err) || $err != '') { $stripeerr = $err; }
            else {
                try { $customerinvoices = \Stripe\Invoice::all(["customer" => $customerid[$searchcustomer], "limit" => 100])->__toArray(true); } 
                catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['message']; }
                if(isset($err) || $err != '') { $stripeerr = $err; }
            }
        }
    }
}
else {
    header("Location: disabled.php"); exit();
}

    
foreach ($plugins as $result) {
    if (file_exists('../' . $result)) {
        if (file_exists('../' . $result . '/manifest.xml')) {
            $get = file_get_contents('../' . $result . '/manifest.xml');
            $xml   = simplexml_load_string($get, 'SimpleXMLElement', LIBXML_NOCDATA);
            $arr = json_decode(json_encode((array)$xml), TRUE);
            if (isset($arr['name']) && !empty($arr['name']) && isset($arr['fa-icon']) && !empty($arr['fa-icon']) && isset($arr['section']) && !empty($arr['section']) && isset($arr['admin-only']) && !empty($arr['admin-only']) && isset($arr['new-tab']) && !empty($arr['new-tab']) && isset($arr['hide']) && !empty($arr['hide'])){
                array_push($pluginlinks,$result);
                array_push($pluginnames,$arr['name']);
                array_push($pluginicons,$arr['fa-icon']);
                array_push($pluginsections,$arr['section']);
                array_push($pluginadminonly,$arr['admin-only']);
                array_push($pluginnewtab,$arr['new-tab']);
                array_push($pluginhide,$arr['hide']);
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
        <link rel="icon" type="image/ico" href="../images/<?php echo $cpfavicon; ?>">
        <title><?php echo $sitetitle; ?> - <?php echo __("Billing"); ?></title>
        <link href="../components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../components/metismenu/dist/metisMenu.min.css" rel="stylesheet">
        <link href="../components/select2/select2.min.css" rel="stylesheet">
        <link href="../components/animate.css/animate.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../components/sweetalert2/sweetalert2.min.css" />
        <link href="../components/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet"/>
        <link href="../components/select2/select2.min.css" rel="stylesheet"/>
        <link href="../components/footable/footable.bootstrap.min.css" rel="stylesheet"/>
        <link href="../../css/style.css" rel="stylesheet">
        <link href="../../css/colors/<?php if(isset($_COOKIE['theme']) && $themecolor != 'custom.css') { echo base64_decode($_COOKIE['theme']); } else {echo $themecolor; } ?>" id="theme" rel="stylesheet">
        <?php if($themecolor == "custom.css") { require( '../../css/colors/custom.php'); } ?>   
        <style>
            #cc-table span.fooicon-plus,span.fooicon-minus {
                position: relative;
                top: -5px;
            }
            @media screen and (max-width: 1199px) {
                .resone { display:none !important;}
            }  
            @media screen and (max-width: 991px) {
                .restwo { display:none !important;}
            }    
            @media screen and (max-width: 767px) {
                .resthree { display:none !important;}
            } 
        </style>
        <?php if(GOOGLE_ANALYTICS_ID != ''){ echo "<script async src='https://www.googletagmanager.com/gtag/js?id=" . GOOGLE_ANALYTICS_ID . "'></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '" . GOOGLE_ANALYTICS_ID . "');</script>"; } ?> 
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="fix-header">
        <div class="preloader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> 
            </svg>
        </div>
        <div id="wrapper">
            <nav class="navbar navbar-default navbar-static-top m-b-0">
                <div class="navbar-header">
                    <div class="top-left-part">
                        <a class="logo" href="../../index.php">
                            <img src="../images/<?php echo $cpicon; ?>" alt="home" class="logo-1 dark-logo" />
                            <img src="../images/<?php echo $cplogo; ?>" alt="home" class="hidden-xs dark-logo" />
                        </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-left">
                        <li><a href="javascript:void(0)" class="open-close waves-effect waves-light visible-xs"><i class="ti-close ti-menu"></i></a></li>
                        <?php notifications(); ?>
                    </ul>
                    <ul class="nav navbar-top-links navbar-right pull-right">
                        <li>
                            <form class="app-search m-r-10" id="searchform" action="../../process/search.php" method="get">
                                <input type="text" placeholder="<?php echo __("Search..."); ?>" class="form-control" name="q"> <a href="javascript:void(0);" onclick="document.getElementById('searchform').submit();"><i class="fa fa-search"></i></a> </form>
                        </li>
                        <li class="dropdown">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"><b class="hidden-xs"><?php print_r($displayname); ?></b><span class="caret"></span> </a>
                            <ul class="dropdown-menu dropdown-user animated flipInY">
                                <li>
                                    <div class="dw-user-box">
                                        <div class="u-text">
                                            <h4><?php print_r($displayname); ?></h4>
                                            <p class="text-muted"><?php print_r($useremail); ?></p></div>
                                    </div>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li><a href="../../profile.php"><i class="ti-home"></i> <?php echo __("My Account"); ?></a></li>
                                <li><a href="../../profile.php?settings=open"><i class="ti-settings"></i> <?php echo __("Account Settings"); ?></a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="../../process/logout.php"><i class="fa fa-power-off"></i> <?php echo __("Logout"); ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav slimscrollsidebar">
                    <div class="sidebar-head">
                        <h3>
                            <span class="fa-fw open-close">
                                <i class="ti-menu hidden-xs"></i>
                                <i class="ti-close visible-xs"></i>
                            </span> 
                            <span class="hide-menu"><?php echo __("Navigation"); ?></span>
                        </h3>  
                    </div>
                    <ul class="nav" id="side-menu">
                        <?php indexMenu("../../"); 
                              adminMenu("../../admin/list/", "");
                              profileMenu("../../");
                              primaryMenu("../../list/", "../../process/", "");
                        ?>
                    </ul>
                </div>
            </div>
            <div id="page-wrapper">
                <div class="container-fluid">
                    <div class="row bg-title">
                        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                            <h4 class="page-title"><?php echo __("Billing"); ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <h3 class="box-title m-b-0"><?php echo __("Subscriptions"); ?></h3><br>
                                <div class="table-responsive">
                                 <table class="table footable m-b-0" id="cc-table" data-paging="true" data-paging-size="5" data-page-size="5" data-sorting="true">
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Pricing</th>
                                            <th>Status</th>
                                            <th>Billing</th>
                                            <th>Next Charge</th>
                                            <th>Started</th>
                                            <th></th> <!-- Actions -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $cursub = $customersubs['data'];
                                        if($cursub[0] != '') { 
                                            $x1 = 0; 

                                            do {
                                                echo '<tr>
                                                    <td>'.$cursub[$x1]['plan']['metadata']['package'].'</td>
                                                    <td>';
                                                
                                                    if($cursub[$x1]['plan']['currency'] == "aed" || $cursub[$x1]['plan']['currency'] == "afn" || $cursub[$x1]['plan']['currency'] == "dkk" || $cursub[$x1]['plan']['currency'] == "dzd" || $cursub[$x1]['plan']['currency'] == "egp" || $cursub[$x1]['plan']['currency'] == "lbp" || $cursub[$x1]['plan']['currency'] == "mad" || $cursub[$x1]['plan']['currency'] == "nok" || $cursub[$x1]['plan']['currency'] == "qar" || $cursub[$x1]['plan']['currency'] == "sar" || $cursub[$x1]['plan']['currency'] == "sek" || $cursub[$x1]['plan']['currency'] == "yer"){
                                                        echo number_format(($cursub[$x1]['plan']['amount']/100), 2, '.', ' ') . ' ' .  $currencies[$cursub[$x1]['plan']['currency']];
                                                    }
                                                    elseif($cursub[$x1]['plan']['currency'] == "bif" || $cursub[$x1]['plan']['currency'] == "clp" || $cursub[$x1]['plan']['currency'] == "djf" || $cursub[$x1]['plan']['currency'] == "gnf" || $cursub[$x1]['plan']['currency'] == "jpy" || $cursub[$x1]['plan']['currency'] == "kmf" || $cursub[$x1]['plan']['currency'] == "krw" || $cursub[$x1]['plan']['currency'] == "mga" || $cursub[$x1]['plan']['currency'] == "pyg" || $cursub[$x1]['plan']['currency'] == "rwf" || $cursub[$x1]['plan']['currency'] == "vnd" || $cursub[$x1]['plan']['currency'] == "vuv" || $cursub[$x1]['plan']['currency'] == "xaf" || $cursub[$x1]['plan']['currency'] == "xof" || $cursub[$x1]['plan']['currency'] == "xpf"){
                                                        echo $currencies[$cursub[$x1]['plan']['currency']] . ' ' . $cursub[$x1]['plan']['amount'];
                                                    }
                                                    elseif($cursub[$x1]['plan']['currency'] == "mro"){
                                                        echo $currencies[$cursub[$x1]['plan']['currency']] . ' ' .  number_format(($cursub[$x1]['plan']['amount']/10), 1, '.', ' ');
                                                    }
                                                    else {
                                                       echo $currencies[$cursub[$x1]['plan']['currency']] . ' ' . number_format($cursub[$x1]['plan']['amount']/100, 2) . ' ' .  strtoupper($cursub[$x1]['plan']['currency']);
                                                    }
                                                    echo ' / ';
                                                    if ($cursub[$x1]['plan']['interval_count'] > 1) {
                                                        echo $cursub[$x1]['plan']['interval_count'] . ' ';
                                                    }
                                                    echo $cursub[$x1]['plan']['interval'];

                                                    if ($cursub[$x1]['plan']['interval_count'] > 1) {
                                                        echo 's';
                                                    }
                                                
                                                    echo '</td>
                                                    <td>';
                                                    if($cursub[$x1]['cancel_at_period_end'] == 'true'){ 
                                                        echo '<span class="label label-table label-warning">' . __("Canceled") . '</span>';
                                                    } else {
                                                
                                                        if($cursub[$x1]['status'] == 'incomplete'){ 
                                                            echo '<span class="label label-table label-warning">' . __("Incomplete") . '</span>';
                                                        } 
                                                        elseif($cursub[$x1]['status'] == 'incomplete_expired') { 
                                                                echo '<span class="label label-table label-danger">' . __("Expired") . '</span>';
                                                            } 
                                                        elseif($cursub[$x1]['status'] == 'trialing') { 
                                                                echo '<span class="label label-table label-info">' . __("Trial") . '</span>';
                                                            }
                                                        elseif($cursub[$x1]['status'] == 'active') { 
                                                                echo '<span class="label label-table label-success">' . __("Active") . '</span>';
                                                            }
                                                        elseif($cursub[$x1]['status'] == 'past_due') { 
                                                                echo '<span class="label label-table label-warning">' . __("Past Due") . '</span>';
                                                            }
                                                        elseif($cursub[$x1]['status'] == 'canceled') { 
                                                                echo '<span class="label label-table label-danger">' . __("Canceled") . '</span>';
                                                            }
                                                        elseif($cursub[$x1]['status'] == 'unpaid') { 
                                                                echo '<span class="label label-table label-danger">' . __("Unpaid") . '</span>';
                                                            }
                                                        else { 
                                                            echo '<span class="label label-table label-danger">' . __("Error") . '</span>';
                                                        } 
                                                    }
                                                    echo '</td>
                                                    <td>';
                                                    if($cursub[$x1]['billing'] == 'charge_automatically'){ 
                                                        echo '<span class="label label-table label-info">' . __("Autopay") . '</span>';
                                                    } 
                                                    elseif($cursub[$x1]['billing'] == 'send_invoice') { 
                                                            echo '<span class="label label-table label-info">' . __("Manual Payment") . '</span>';
                                                        } 
                                                
                                                    echo '</td>
                                                    <td>'; 
                                                if(($cursub[$x1]['status'] == 'active' || $cursub[$x1]['status'] == 'trialing' || $cursub[$x1]['status'] == 'incomplete' || $cursub[$x1]['status'] == 'past_due') && $cursub[$x1]['cancel_at_period_end'] != 'true') {
                                                    if($cursub[$x1]['plan']['currency'] == "aed" || $cursub[$x1]['plan']['currency'] == "afn" || $cursub[$x1]['plan']['currency'] == "dkk" || $cursub[$x1]['plan']['currency'] == "dzd" || $cursub[$x1]['plan']['currency'] == "egp" || $cursub[$x1]['plan']['currency'] == "lbp" || $cursub[$x1]['plan']['currency'] == "mad" || $cursub[$x1]['plan']['currency'] == "nok" || $cursub[$x1]['plan']['currency'] == "qar" || $cursub[$x1]['plan']['currency'] == "sar" || $cursub[$x1]['plan']['currency'] == "sek" || $cursub[$x1]['plan']['currency'] == "yer"){
                                                        echo number_format(($cursub[$x1]['plan']['amount']/100), 2, '.', ' ') . ' ' .  $currencies[$cursub[$x1]['plan']['currency']];
                                                    }
                                                    elseif($cursub[$x1]['plan']['currency'] == "bif" || $cursub[$x1]['plan']['currency'] == "clp" || $cursub[$x1]['plan']['currency'] == "djf" || $cursub[$x1]['plan']['currency'] == "gnf" || $cursub[$x1]['plan']['currency'] == "jpy" || $cursub[$x1]['plan']['currency'] == "kmf" || $cursub[$x1]['plan']['currency'] == "krw" || $cursub[$x1]['plan']['currency'] == "mga" || $cursub[$x1]['plan']['currency'] == "pyg" || $cursub[$x1]['plan']['currency'] == "rwf" || $cursub[$x1]['plan']['currency'] == "vnd" || $cursub[$x1]['plan']['currency'] == "vuv" || $cursub[$x1]['plan']['currency'] == "xaf" || $cursub[$x1]['plan']['currency'] == "xof" || $cursub[$x1]['plan']['currency'] == "xpf"){
                                                        echo $currencies[$cursub[$x1]['plan']['currency']] . ' ' . $cursub[$x1]['plan']['amount'];
                                                    }
                                                    elseif($cursub[$x1]['plan']['currency'] == "mro"){
                                                        echo $currencies[$cursub[$x1]['plan']['currency']] . ' ' .  number_format(($cursub[$x1]['plan']['amount']/10), 1, '.', ' ');
                                                    }
                                                    else {
                                                       echo $currencies[$cursub[$x1]['plan']['currency']] . ' ' . number_format($cursub[$x1]['plan']['amount']/100, 2) . ' ' .  strtoupper($cursub[$x1]['plan']['currency']);
                                                    } 
                                                echo ' on ' . date("F j, Y", ($cursub[$x1]['current_period_end']));
                                                    
                                                }
                                                
                                                else { echo '&mdash;'; }
                                                    
                                                    echo '</td>
                                                    <td data-sort-value="' . date("Y-m-j", $cursub[$x1]['created']). '">' . date("F j, Y, g:i a", $cursub[$x1]['created']) . '</td>';
                                                
                                                    if($cursub[$x1]['cancel_at_period_end'] != 'true' && $cursub[$x1]['status'] != 'canceled') {
                                                        echo '<td><button onclick="cancelPlan(\'' . $cursub[$x1]['id'] . '\')"type="button" data-toggle="tooltip" data-original-title="' . __("Cancel Subscription") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-times"></i></button></td>';
                                                    }
                                                    echo '</tr>';
                                                
                                                $x1++;
                                                    
                                            } while (isset($cursub[$x1])); }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <div class="white-box">
                                <h3 class="box-title m-b-0"><?php echo __("Payments"); ?></h3><br>
                                <div class="table-responsive">
                                <table class="table footable m-b-0" data-paging="true" data-paging-size="5" data-page-size="5" data-sorting="true">
                                    <thead>
                                        <tr>
                                            <th>Amount</th> <!-- Price (Cost + Currency) -->
                                            <th>Status</th> <!-- Payment Status (Default) -->
                                            <th>Description</th> <!-- Description -->
                                            <th>Payment Method</th> <!-- Payment Method -->
                                            <th data-type="date" data-format-string="YYYY-MM-DD" data-sorted="true" data-direction="DESC">Date</th> <!-- Date -->
                                            <th></th> <!-- Action -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $customercharge = $customercharges['data'];
                                        if($customercharge[0] != '') { 
                                            $x1 = 0; 

                                            do {
                                                echo '<tr><td>';
                                                    
                                                    if($customercharge[$x1]['currency'] == "aed" || $customercharge[$x1]['currency'] == "afn" || $customercharge[$x1]['currency'] == "dkk" || $customercharge[$x1]['currency'] == "dzd" || $customercharge[$x1]['currency'] == "egp" || $customercharge[$x1]['currency'] == "lbp" || $customercharge[$x1]['currency'] == "mad" || $customercharge[$x1]['currency'] == "nok" || $customercharge[$x1]['currency'] == "qar" || $customercharge[$x1]['currency'] == "sar" || $customercharge[$x1]['currency'] == "sek" || $customercharge[$x1]['currency'] == "yer"){
                                                        echo number_format(($customercharge[$x1]['amount']/100), 2, '.', ' ') . ' ' .  $currencies[$customercharge[$x1]['currency']];
                                                    }
                                                    elseif($customercharge[$x1]['currency'] == "bif" || $customercharge[$x1]['currency'] == "clp" || $customercharge[$x1]['currency'] == "djf" || $customercharge[$x1]['currency'] == "gnf" || $customercharge[$x1]['currency'] == "jpy" || $customercharge[$x1]['currency'] == "kmf" || $customercharge[$x1]['currency'] == "krw" || $customercharge[$x1]['currency'] == "mga" || $customercharge[$x1]['currency'] == "pyg" || $customercharge[$x1]['currency'] == "rwf" || $customercharge[$x1]['currency'] == "vnd" || $customercharge[$x1]['currency'] == "vuv" || $customercharge[$x1]['currency'] == "xaf" || $customercharge[$x1]['currency'] == "xof" || $customercharge[$x1]['currency'] == "xpf"){
                                                        echo $currencies[$customercharge[$x1]['currency']] . ' ' . $customercharge[$x1]['amount'];
                                                    }
                                                    elseif($customercharge[$x1]['currency'] == "mro"){
                                                        echo $currencies[$customercharge[$x1]['currency']] . ' ' .  number_format(($customercharge[$x1]['amount']/10), 1, '.', ' ');
                                                    }
                                                    else {
                                                       echo $currencies[$customercharge[$x1]['currency']] . ' ' . number_format($customercharge[$x1]['amount']/100, 2) . ' ' .  strtoupper($customercharge[$x1]['currency']);
                                                    }
                                                    
                                                    echo '</td>
                                                    <td>';
                                                    if($customercharge[$x1]['status'] == 'succeeded'){ 
                                                        echo '<span class="label label-table label-success">' . __("Succeeded") . '</span>';
                                                    } 
                                                    elseif($customercharge[$x1]['status'] == 'pending') { 
                                                            echo '<span class="label label-table label-warning">' . __("Pending") . '</span>';
                                                        } 
                                                    else { 
                                                        echo '<span class="label label-table label-danger">' . __("Failed") . '</span>';
                                                    } 
                                                    echo '</td>
                                                    <td>'.$customercharge[$x1]['description'].'</td>
                                                    <td>';
                                                    switch ($customercharge[$x1]['source']['brand']) {
                                                        case "Visa":
                                                            echo "<i class='fa fa-cc-visa'></i>";
                                                            break;
                                                        case "MasterCard":
                                                            echo "<i class='fa fa-cc-mastercard'></i>";
                                                            break;
                                                        case "American Express":
                                                            echo "<i class='fa fa-cc-amex'></i>";
                                                            break;
                                                        case "Discover":
                                                            echo "<i class='fa fa-cc-discover'></i>";
                                                            break;
                                                        case "Diners Club":
                                                            echo "<i class='fa fa-cc-diners-club'></i>";
                                                            break;
                                                        case "JCB":
                                                            echo "<i class='fa fa-cc-jcb'></i>";
                                                            break;
                                                        default:
                                                           echo "<i class='fa fa-credit-card-alt'></i>";
                                                    }
                                                    
                                                    echo ' **** ' . $customercharge[$x1]['source']['last4'] . '</td>
                                                    <td data-sort-value="' . date("Y-m-j", $customercharge[$x1]['created']). '">' . date("F j, Y, g:i a", $customercharge[$x1]['created']) . '</td>
                                                    <td>
                                                        <a target="_blank" href="' . $customercharge[$x1]['receipt_url'] . '"><button type="button" data-toggle="tooltip" data-original-title="' . __("Open Receipt") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-external-link"></i></button></a>
                                                    </td>
                                                    <td>';
                                                    
                                                    if($customercharge[$x1]['currency'] == "aed" || $customercharge[$x1]['currency'] == "afn" || $customercharge[$x1]['currency'] == "dkk" || $customercharge[$x1]['currency'] == "dzd" || $customercharge[$x1]['currency'] == "egp" || $customercharge[$x1]['currency'] == "lbp" || $customercharge[$x1]['currency'] == "mad" || $customercharge[$x1]['currency'] == "nok" || $customercharge[$x1]['currency'] == "qar" || $customercharge[$x1]['currency'] == "sar" || $customercharge[$x1]['currency'] == "sek" || $customercharge[$x1]['currency'] == "yer"){
                                                        echo number_format(($customercharge[$x1]['amount']/100), 2, '.', ' ') . ' ' .  $currencies[$customercharge[$x1]['currency']];
                                                    }
                                                    elseif($customercharge[$x1]['currency'] == "bif" || $customercharge[$x1]['currency'] == "clp" || $customercharge[$x1]['currency'] == "djf" || $customercharge[$x1]['currency'] == "gnf" || $customercharge[$x1]['currency'] == "jpy" || $customercharge[$x1]['currency'] == "kmf" || $customercharge[$x1]['currency'] == "krw" || $customercharge[$x1]['currency'] == "mga" || $customercharge[$x1]['currency'] == "pyg" || $customercharge[$x1]['currency'] == "rwf" || $customercharge[$x1]['currency'] == "vnd" || $customercharge[$x1]['currency'] == "vuv" || $customercharge[$x1]['currency'] == "xaf" || $customercharge[$x1]['currency'] == "xof" || $customercharge[$x1]['currency'] == "xpf"){
                                                        echo $currencies[$customercharge[$x1]['currency']] . ' ' . $customercharge[$x1]['amount'];
                                                    }
                                                    elseif($customercharge[$x1]['currency'] == "mro"){
                                                        echo $currencies[$customercharge[$x1]['currency']] . ' ' .  number_format(($customercharge[$x1]['amount']/10), 1, '.', ' ');
                                                    }
                                                    else {
                                                       echo $currencies[$customercharge[$x1]['currency']] . ' ' . number_format($customercharge[$x1]['amount']/100, 2) . ' ' .  strtoupper($customercharge[$x1]['currency']);
                                                    }
                                                    
                                                    echo '</td>
                                                    <td>**** '.$customercard[$x1]['last4'].'</td>
                                                </tr>';
                                                
                                                $x1++;
                                                    
                                            } while (isset($customercharge['data'][$x1])); }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <div class="white-box">
                                <h3 class="box-title m-b-0"><?php echo __("Invoices"); ?></h3><br>
                                <div class="table-responsive">
                                <table class="table footable m-b-0" id="cc-table" data-paging="true" data-paging-size="5" data-page-size="5" data-sorting="true">
                                    <thead>
                                        <tr>
                                            <th>Amount</th>
                                            <th>Invoice Number</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Payment Due</th>
                                            <th></th> <!-- Actions -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $curinv = $customerinvoices['data'];
                                        if($curinv[0] != '') { 
                                            $x1 = 0; 

                                            do {
                                                echo '<tr>
                                                    <td>';
                                                
                                                    if($curinv[$x1]['currency'] == "aed" || $curinv[$x1]['currency'] == "afn" || $curinv[$x1]['currency'] == "dkk" || $curinv[$x1]['currency'] == "dzd" || $curinv[$x1]['currency'] == "egp" || $curinv[$x1]['currency'] == "lbp" || $curinv[$x1]['currency'] == "mad" || $curinv[$x1]['currency'] == "nok" || $curinv[$x1]['currency'] == "qar" || $curinv[$x1]['currency'] == "sar" || $curinv[$x1]['currency'] == "sek" || $curinv[$x1]['currency'] == "yer"){
                                                        echo number_format(($curinv[$x1]['total']/100), 2, '.', ' ') . ' ' .  $currencies[$curinv[$x1]['currency']];
                                                    }
                                                    elseif($curinv[$x1]['currency'] == "bif" || $curinv[$x1]['currency'] == "clp" || $curinv[$x1]['currency'] == "djf" || $curinv[$x1]['currency'] == "gnf" || $curinv[$x1]['currency'] == "jpy" || $curinv[$x1]['currency'] == "kmf" || $curinv[$x1]['currency'] == "krw" || $curinv[$x1]['currency'] == "mga" || $curinv[$x1]['currency'] == "pyg" || $curinv[$x1]['currency'] == "rwf" || $curinv[$x1]['currency'] == "vnd" || $curinv[$x1]['currency'] == "vuv" || $curinv[$x1]['currency'] == "xaf" || $curinv[$x1]['currency'] == "xof" || $curinv[$x1]['currency'] == "xpf"){
                                                        echo $currencies[$curinv[$x1]['currency']] . ' ' . $curinv[$x1]['total'];
                                                    }
                                                    elseif($curinv[$x1]['currency'] == "mro"){
                                                        echo $currencies[$curinv[$x1]['currency']] . ' ' .  number_format(($curinv[$x1]['total']/10), 1, '.', ' ');
                                                    }
                                                    else {
                                                       echo $currencies[$curinv[$x1]['currency']] . ' ' . number_format($curinv[$x1]['total']/100, 2) . ' ' .  strtoupper($curinv[$x1]['currency']);
                                                    }

                                                    echo '</td>
                                                    <td>'.$curinv[$x1]['number'].'</td>
                                                    <td>';
                                                    if($curinv[$x1]['status'] == 'draft'){ 
                                                        echo '<span class="label label-table label-info">' . __("Draft") . '</span>';
                                                    } 
                                                    elseif($curinv[$x1]['status'] == 'open') { 
                                                            echo '<span class="label label-table label-info">' . __("Open") . '</span>';
                                                        } 
                                                    elseif($curinv[$x1]['status'] == 'paid') { 
                                                            echo '<span class="label label-table label-success">' . __("Paid") . '</span>';
                                                        }
                                                    elseif($curinv[$x1]['status'] == 'uncollectable') { 
                                                            echo '<span class="label label-table label-danger">' . __("Uncollectable") . '</span>';
                                                        }
                                                    elseif($curinv[$x1]['status'] == 'void') { 
                                                            echo '<span class="label label-table label-danger">' . __("Void") . '</span>';
                                                        }
                                                
                                                    echo '</td>
                                                    <td data-sort-value="' . date("Y-m-j", $curinv[$x1]['created']). '">' . date("F j, Y, g:i a", $curinv[$x1]['created']) . '</td>
                                                    <td>';
                                                    if($curinv[$x1]['amount_remaining'] > '0'){ 
                                                        if($curinv[$x1]['currency'] == "aed" || $curinv[$x1]['currency'] == "afn" || $curinv[$x1]['currency'] == "dkk" || $curinv[$x1]['currency'] == "dzd" || $curinv[$x1]['currency'] == "egp" || $curinv[$x1]['currency'] == "lbp" || $curinv[$x1]['currency'] == "mad" || $curinv[$x1]['currency'] == "nok" || $curinv[$x1]['currency'] == "qar" || $curinv[$x1]['currency'] == "sar" || $curinv[$x1]['currency'] == "sek" || $curinv[$x1]['currency'] == "yer"){
                                                            echo number_format(($curinv[$x1]['amount_remaining']/100), 2, '.', ' ') . ' ' .  $currencies[$curinv[$x1]['currency']];
                                                        }
                                                        elseif($curinv[$x1]['currency'] == "bif" || $curinv[$x1]['currency'] == "clp" || $curinv[$x1]['currency'] == "djf" || $curinv[$x1]['currency'] == "gnf" || $curinv[$x1]['currency'] == "jpy" || $curinv[$x1]['currency'] == "kmf" || $curinv[$x1]['currency'] == "krw" || $curinv[$x1]['currency'] == "mga" || $curinv[$x1]['currency'] == "pyg" || $curinv[$x1]['currency'] == "rwf" || $curinv[$x1]['currency'] == "vnd" || $curinv[$x1]['currency'] == "vuv" || $curinv[$x1]['currency'] == "xaf" || $curinv[$x1]['currency'] == "xof" || $curinv[$x1]['currency'] == "xpf"){
                                                            echo $currencies[$curinv[$x1]['currency']] . ' ' . $curinv[$x1]['amount_remaining'];
                                                        }
                                                        elseif($curinv[$x1]['currency'] == "mro"){
                                                            echo $currencies[$curinv[$x1]['currency']] . ' ' .  number_format(($curinv[$x1]['amount_remaining']/10), 1, '.', ' ');
                                                        }
                                                        else {
                                                           echo $currencies[$curinv[$x1]['currency']] . ' ' . number_format($curinv[$x1]['amount_remaining']/100, 2) . ' ' .  strtoupper($curinv[$x1]['currency']);
                                                        }
                                                    } 
                                                    else {
                                                        echo '&mdash;';
                                                    }
                                                    echo '</td>
                                                    <td>
                                                        <a target="_blank" href="'.$curinv[$x1]['hosted_invoice_url'].'"><button type="button" data-toggle="tooltip" data-original-title="' . __("Open / Pay Invoice") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-external-link"></i></button></a>
                                                        <a href="'.$curinv[$x1]['invoice_pdf'].'"><button type="button" data-toggle="tooltip" data-original-title="' . __("Download PDF") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-download"></i></button></a>
 
                                                    </td>
                                                    
                                                </tr>';
                                                
                                                $x1++;
                                                    
                                            } while (isset($curinv[$x1])); }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <div class="white-box">
                                <ul class="side-icon-text pull-right">
                                    <li><a href="add/card.php"><span class="circle circle-sm bg-success di" style="padding-top: 11px;"><i class="fa fa-plus"></i></span><span class="resthree"><wrapper class="restwo"><?php echo __("Add "); ?></wrapper><?php echo __("Card"); ?></span></a></li>
                                </ul>
                                <h3 class="box-title m-b-0"><?php echo __("Cards"); ?></h3><br>
                                <div class="table-responsive">
                                <table class="table footable m-b-0" id="cc-table" data-paging="true" data-paging-size="5" data-page-size="5" data-sorting="true">
                                    <thead>
                                        <tr>
                                            <th>Card Type</th> <!-- Card Icon -->
                                            <th>Number</th> <!-- Card Number (Last 4) -->
                                            <th>Expiration</th> <!-- Card Expiration -->
                                            <th data-sorted="true" data-direction="DESC"></th> <!-- Card Status (Default) -->
                                            <th></th> <!-- Actions -->
                                            <th data-breakpoints="all">Type</th>
                                            <th data-breakpoints="all">Name</th>
                                            <th data-breakpoints="all">Billing Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $customercard = $currentcustomer['sources']['data'];
                                        if($customercard[0] != '') { 
                                            $x1 = 0; 

                                            do {
                                                if($customercard[$x1]['object'] == 'card') {
                                                echo '<tr>
                                                    <td>';
                                                    switch ($customercard[$x1]['brand']) {
                                                        case "Visa":
                                                            echo "<i style='font-size:30px' class='fa fa-cc-visa'></i>";
                                                            break;
                                                        case "MasterCard":
                                                            echo "<i style='font-size:30px' class='fa fa-cc-mastercard'></i>";
                                                            break;
                                                        case "American Express":
                                                            echo "<i style='font-size:30px' class='fa fa-cc-amex'></i>";
                                                            break;
                                                        case "Discover":
                                                            echo "<i style='font-size:30px' class='fa fa-cc-discover'></i>";
                                                            break;
                                                        case "Diners Club":
                                                            echo "<i style='font-size:30px' class='fa fa-cc-diners-club'></i>";
                                                            break;
                                                        case "JCB":
                                                            echo "<i style='font-size:30px' class='fa fa-cc-jcb'></i>";
                                                            break;
                                                        default:
                                                           echo "<i style='font-size:30px' class='fa fa-credit-card-alt'></i>";
                                                    }
                                                    
                                                    echo '</td>
                                                    <td>**** '.$customercard[$x1]['last4'].'</td>
                                                    <td>'.$customercard[$x1]['exp_month'].' / '.$customercard[$x1]['exp_year'].'</td>
                                                    <td>';
                                                    if($currentcustomer['default_source'] == $customercard[$x1]['id']){ 
                                                            echo '<span class="label label-table label-info">' . __("Default") . '</span>';
                                                    } 
                                                    echo '</td>
                                                    <td>
                                                        <a href="edit/card.php?card-id=' . $customercard[$x1]['id'] . '"><button type="button" data-toggle="tooltip" data-original-title="' . __("Edit") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="ti-pencil-alt"></i></button></a>
                                                        <button onclick="deleteCard(\'' . $customercard[$x1]['id'] . '\')" type="button" data-toggle="tooltip" data-original-title="' . __("Delete") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-times"></i></button>';
                                                    if($currentcustomer['default_source'] != $customercard[$x1]['id']){ 
                                                            echo '<button onclick="makeDefault(\'' . $customercard[$x1]['id'] . '\')" type="button" data-toggle="tooltip" data-original-title="' . __("Make Default") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-star"></i></button>';
                                                    } 
                                                        
                                                    echo '</td>
                                                    <td>' . $customercard[$x1]['brand'] . ' ';
                                                    if($customercard[$x1]['funding'] != 'unknown'){
                                                        echo $customercard[$x1]['funding'] . ' card';
                                                    }
                                                    echo '</td>
                                                    <td>'; 
                                                    if($customercard[$x1]['name'] == 'undefined undefined' || is_null($customercard[$x1]['name'])){ 
                                                            echo 'No Name Provided';
                                                    } 
                                                    else {
                                                        echo $customercard[$x1]['name'];
                                                    }
                                                    echo '</td>
                                                    <td>';
                                                    if(!empty($customercard[$x1]['address_line1']) || !empty($customercard[$x1]['address_line2'])) {
                                                        if(!empty($customercard[$x1]['address_line1'])) {
                                                            echo $customercard[$x1]['address_line1'];
                                                        }
                                                        if(!empty($customercard[$x1]['address_line2']) && empty($customercard[$x1]['address_line1'])) {
                                                            echo $customercard[$x1]['address_line2'];
                                                        }
                                                        elseif(!empty($customercard[$x1]['address_line2']) && !empty($customercard[$x1]['address_line1'])) {
                                                            echo "<br>" . $customercard[$x1]['address_line2'];
                                                        }
                                                        if(!empty($customercard[$x1]['address_city']) && !empty($customercard[$x1]['address_zip']) && !empty($customercard[$x1]['address_country']) && !empty($customercard[$x1]['address_state'])) {
                                                            echo "<br>" . $customercard[$x1]['address_city']. ', ' . $customercard[$x1]['address_state']. ', ' . $customercard[$x1]['address_zip']. ', ' . $customercard[$x1]['address_country'];
                                                        }
                                                        elseif(!empty($customercard[$x1]['address_zip']) && (!empty($customercard[$x1]['address_city']) || !empty($customercard[$x1]['address_country']) || !empty($customercard[$x1]['address_state']))) {
                                                            echo "<br>" . $customercard[$x1]['address_zip'];
                                                        }
                                                    }
                                                    else {
                                                        echo 'No Address';
                                                    }
                                                    echo '</td>
                                                </tr>';
                                                }
                                                $x1++;
                                                    
                                            } while (isset($currentcustomer['sources']['data'][$x1])); }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php hotkeys($configlocation); ?>
                <footer class="footer text-center"><?php footer(); ?></footer>
            </div>
        </div>
        
        <script src="../components/jquery/jquery.min.js"></script>
        <script src="../components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="../components/jquery-overlaps/jquery.overlaps.js"></script>
        <script src="../components/sweetalert2/sweetalert2.min.js"></script>
        <script src="../components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../components/metismenu/dist/metisMenu.min.js"></script>
        <script src="../components/select2/select2.min.js"></script>
        <script src="../components/waves/waves.js"></script>
        <script src="../components/footable/footable.min.js"></script>
        <script src="../components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="../components/select2/select2.min.js"></script>
        <script src="../components/bootstrap-select/js/bootstrap-select.min.js"></script>
        <script src="../../js/notifications.js"></script>
        <script src="../../js/main.js"></script>
        <script type="text/javascript">
            var processLocation = "../../process/";
            jQuery(function($){
                $('.footable').footable();
            });
            function makeDefault(e){
                e1 = String(e);
                swal.fire({
                    title: '<?php echo __("Processing"); ?>',
                    text: '',
                    onOpen: function () {
                        swal.showLoading()
                    }
                }).then(

                    $.ajax({  
                        type: "POST",  
                        url: "process/default-card.php",  
                        data: { 'verified':'yes', 'card': e1, 'customer': '<?php echo $customerid[$searchcustomer]; ?>' },      
                        success: function(data){
                            swal.close();
                            if(data == '0'){
                                swal.fire({title:'<?php echo __("Successfully Updated!"); ?>', type:'success', allowOutsideClick:false, allowEscapeKey:false, allowEnterKey:false, onOpen: function () {swal.showLoading()}});
                                window.location="index.php";
                            }
                            else {
                                swal.fire({title:'<?php echo __("Error Updating Card"); ?>', html:'<?php echo __("Please try again or contact support."); ?> <br><br><span onclick="$(\'.errortoggle\').toggle();" class="swal-error-title">View Error Code <i class="errortoggle fa fa-angle-double-right"></i><i style="display:none;" class="errortoggle fa fa-angle-double-down"></i></span><span class="errortoggle" style="display:none;"><br><br>(Error: ' + data + ')</span>', type:'error'});
                            }

                        },
                        error: function(){
                            swal.close();
                            swal.fire({title:'<?php echo __("Please try again later or contact support."); ?>', type:'error'});
                        }  
                    }),
                    function () {},
                    function (dismiss) {
                        if (dismiss === 'timer') {
                        }
                    }
                )}
            function deleteCard(e){
                e1 = String(e);
                swal.fire({
                  title: '<?php echo __("Delete Card?"); ?>',
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: '<?php echo __("Confirm"); ?>'
                }).then((result) => {
                  if (result.value) {
                    swal.fire({
                        title: '<?php echo __("Processing"); ?>',
                        text: '',
                        onOpen: function () {
                            swal.showLoading()
                        }
                    }).then(

                        $.ajax({  
                            type: "POST",  
                            url: "delete/card.php",  
                            data: { 'verified':'yes', 'card': e1, 'customer': '<?php echo $customerid[$searchcustomer]; ?>' },      
                            success: function(data){
                                swal.close();
                                if(data == '0'){
                                    swal.fire({title:'<?php echo __("Successfully Deleted!"); ?>', type:'success', allowOutsideClick:false, allowEscapeKey:false, allowEnterKey:false, onOpen: function () {swal.showLoading()}});
                                    window.location="index.php";
                                }
                                else {
                                    swal.fire({title:'<?php echo __("Error Updating Card"); ?>', html:'<?php echo __("Please try again or contact support."); ?> <br><br><span onclick="$(\'.errortoggle\').toggle();" class="swal-error-title">View Error Code <i class="errortoggle fa fa-angle-double-right"></i><i style="display:none;" class="errortoggle fa fa-angle-double-down"></i></span><span class="errortoggle" style="display:none;"><br><br>(Error: ' + data + ')</span>', type:'error'});
                                }

                            },
                            error: function(){
                                swal.close();
                                swal.fire({title:'<?php echo __("Please try again later or contact support."); ?>', type:'error'});
                            }  
                        }),
                        function () {},
                        function (dismiss) {
                            if (dismiss === 'timer') {
                            }
                        }
                    )}
                })
            }
            function cancelPlan(e){
                e1 = String(e);
                swal.fire({
                  title: '<?php echo __("Cancel Subscription?"); ?>',
                  text: '<?php echo __("Your account will be closed at the end of the current billing period unless you subscribe to a new plan before the current billing period ends."); ?>',
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: '<?php echo __("Confirm"); ?>'
                }).then((result) => {
                  if (result.value) {
                    swal.fire({
                        title: '<?php echo __("Processing"); ?>',
                        text: '',
                        onOpen: function () {
                            swal.showLoading()
                        }
                    }).then(

                        $.ajax({  
                            type: "POST",  
                            url: "delete/subscription.php",  
                            data: { 'verified':'yes', 'subscription': e1 },      
                            success: function(data){
                                swal.close();
                                if(data == '0'){
                                    swal.fire({title:'<?php echo __("Successfully Canceled!"); ?>', type:'success', allowOutsideClick:false, allowEscapeKey:false, allowEnterKey:false, onOpen: function () {swal.showLoading()}});
                                    window.location="index.php";
                                }
                                else {
                                    swal.fire({title:'<?php echo __("Error Canceling Subscription"); ?>', html:'<?php echo __("Please try again or contact support."); ?> <br><br><span onclick="$(\'.errortoggle\').toggle();" class="swal-error-title">View Error Code <i class="errortoggle fa fa-angle-double-right"></i><i style="display:none;" class="errortoggle fa fa-angle-double-down"></i></span><span class="errortoggle" style="display:none;"><br><br>(Error: ' + data + ')</span>', type:'error'});
                                }

                            },
                            error: function(){
                                swal.close();
                                swal.fire({title:'<?php echo __("Please try again later or contact support."); ?>', type:'error'});
                            }  
                        }),
                        function () {},
                        function (dismiss) {
                            if (dismiss === 'timer') {
                            }
                        }
                    )}
                })
            }
            <?php 
            processPlugins();
            includeScript();

            if(isset($_GET['a1']) && $_GET['a1'] == "0") {
                echo "swal.fire({title:'" . __("Successfully Created!") . "', type:'success'});";
            } 
            
            if(isset($_GET['a1']) && $_GET['a1'] != "0") {
                echo "swal.fire({title:'" . __("Stripe Processing Error") . "', html:'" . __("Please try again or contact support.") . "<br><br><span onclick=\"$(\'.errortoggle\').toggle();\" class=\"swal-error-title\">View Error <i class=\"errortoggle fa fa-angle-double-right\"></i><i style=\"display:none;\" class=\"errortoggle fa fa-angle-double-down\"></i></span><span class=\"errortoggle\" style=\"display:none;\"><br><br>Stripe Error: " . $_GET['a1'] . "</span>', type:'error'});";
            }
            ?>
        </script>
    </body>
</html>