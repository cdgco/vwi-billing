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
else { header('Location: ../../login.php?to=plugins/vwi-billing/add.php'); }

if(!isset($_GET['package']) || $_GET['package'] == '') { header("Location: index.php"); exit(); }
 $postvars = array(
    array('hash' => $vst_apikey, 'user' => $vst_username,'password' => $vst_password,'cmd' => 'v-list-user','arg1' => $username,'arg2' => 'json'));

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

$admindata = json_decode(curl_exec($curl0), true)[$username];
$useremail = $admindata['CONTACT'];
if(isset($admindata['LANGUAGE'])){ $locale = $ulang[$admindata['LANGUAGE']]; }
_setlocale("LC_CTYPE", $locale); 
_setlocale("LC_MESSAGES", $locale);
_bindtextdomain('messages', '../locale');
_textdomain('messages');

function randomPassword() { $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'; $pass = array(); $alphaLength = strlen($alphabet) - 1; for ($i = 0; $i < 19; $i++) { $n = rand(0, $alphaLength); 
$pass[] = $alphabet[$n]; } return implode($pass); }
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
        <link href="../components/bootstrapvalidator/bootstrapValidator.css" rel="stylesheet"/>
        <link href="../../css/style.css" rel="stylesheet">
        <link href="../../css/colors/<?php if(isset($_COOKIE['theme']) && $themecolor != 'custom.css') { echo base64_decode($_COOKIE['theme']); } else {echo $themecolor; } ?>" id="theme" rel="stylesheet">
        <?php if($themecolor == "custom.css") { require( '../../css/colors/custom.php'); } ?>
        <?php if(GOOGLE_ANALYTICS_ID != ''){ echo "<script async src='https://www.googletagmanager.com/gtag/js?id=" . GOOGLE_ANALYTICS_ID . "'></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '" . GOOGLE_ANALYTICS_ID . "');</script>"; } ?> 
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body class="fix-header" onload="checkCurrency();">
        <div class="preloader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> 
            </svg>
        </div>
        <div id="wrapper">
            <nav class="navbar navbar-default navbar-static-top m-b-0">
                <div class="navbar-header">
                    <div class="top-left-part">
                        <a class="logo" href="../index.php">
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
                            <h4 class="page-title"><?php echo __("Add Plan"); ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <form class="form-horizontal form-material" autocomplete="off" action="create.php" id="form" method="post">
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Package"); ?></label>
                                        <div class="col-md-12">
                                                <input type="text" disabled class="form-control" value="<?php echo $_GET['package']; ?>">
                                            <input type="hidden" name="package" value="<?php echo $_GET['package']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Product Name"); ?></label>
                                        <div class="col-md-12">
                                                <input type="text" class="form-control" name="name" required>
                                                <small class="form-text text-muted"><?php echo __("This will appear on customers' receipts and invoices."); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Internal ID"); ?></label>
                                        <div class="col-md-12">
                                            <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                <div class="input-group-addon">vwi_prod_</div>
                                                <input type="text" class="form-control" style="padding-left: 0.5%;" pattern="[0-9A-Za-z]{14,}" name="id" title="<?php echo __("14 Character Minimum. Letters & Numbers."); ?>" value="<?php echo randomPassword(); ?>" required>
                                            </div>
                                            <small class="form-text text-muted"><?php echo __("Unique Product ID used in Stripe and VWI Backend"); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Statement Descriptor (Optional)"); ?></label>
                                        <div class="col-md-12">
                                                <input type="text" maxlength="22" class="form-control" name="statement" title="<?php echo __("22 Character Maximum. Letters & Numbers."); ?>">
                                                <small class="form-text text-muted"><?php echo __("This will appear on customers' bank statements, so make sure it's clearly recognizable."); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Currency"); ?></label>
                                        <div class="col-md-12">
                                            <select class="form-control select2" name="currency" id="selectcurrency">
                                                <option value="usd"><?php echo __("USD - US Dollars"); ?></option>
                                                <option value="aed"><?php echo __("AED - United Areb Emirates Dirham"); ?></option>
                                                <option value="afn"><?php echo __("AFN - Afghan Afghani"); ?></option>
                                                <option value="all"><?php echo __("ALL - Albanian Lek"); ?></option>
                                                <option value="amd"><?php echo __("AMD - Armenian Dram"); ?></option>
                                                <option value="ang"><?php echo __("ANG - Netherlands Antillean Guilder"); ?></option>
                                                <option value="aoa"><?php echo __("AOA - Angolan Kwanza"); ?></option>
                                                <option value="ars"><?php echo __("ARS - Argentine Peso"); ?></option>
                                                <option value="aud"><?php echo __("AUD - Australian Dollar"); ?></option>
                                                <option value="awg"><?php echo __("AWG - Aruban Florin"); ?></option>
                                                <option value="azn"><?php echo __("AZN - Azerbaijani Manat"); ?></option>
                                                <option value="bam"><?php echo __("BAM - Bosnia-Herzegovina Convertible Mark"); ?></option>
                                                <option value="bbd"><?php echo __("BBD - Barbadian Dollar"); ?></option>
                                                <option value="bdt"><?php echo __("BDT - Bangladeshi Taka"); ?></option>
                                                <option value="bgn"><?php echo __("BGN - Bulgarian Lev"); ?></option>
                                                <option value="bif"><?php echo __("BIF - Burundian Franc"); ?></option>
                                                <option value="bmd"><?php echo __("BMD - Bermudan Dollar"); ?></option>
                                                <option value="bnd"><?php echo __("BND - Brunei Dollar"); ?></option>
                                                <option value="bob"><?php echo __("BOB - Bolivian Boliviano"); ?></option>
                                                <option value="brl"><?php echo __("BRL - Brazilian Real"); ?></option>
                                                <option value="bsd"><?php echo __("BSD - Bahamian Dollar"); ?></option>
                                                <option value="bwp"><?php echo __("BWP - Botswanan Pula"); ?></option>
                                                <option value="bzd"><?php echo __("BZD - Belize Dollar"); ?></option>
                                                <option value="cad"><?php echo __("CAD - Canadian Dollar"); ?></option>
                                                <option value="cdf"><?php echo __("CDF - Congolese Franc"); ?></option>
                                                <option value="chf"><?php echo __("CHF - Swiss Franc"); ?></option>
                                                <option value="clp"><?php echo __("CLP - Chilean Peso"); ?></option>
                                                <option value="cny"><?php echo __("CNY - Chinese Yuan"); ?></option>
                                                <option value="cop"><?php echo __("COP - Colombian Peso"); ?></option>
                                                <option value="crc"><?php echo __("CRC - Costa Rican Colón"); ?></option>
                                                <option value="cve"><?php echo __("CVE - Cape Verdean Escudo"); ?></option>
                                                <option value="czk"><?php echo __("CZK - Czech Koruna"); ?></option>
                                                <option value="djf"><?php echo __("DJF - Diboutian Franc"); ?></option>
                                                <option value="dkk"><?php echo __("DKK - Danish Krone"); ?></option>
                                                <option value="dop"><?php echo __("DOP - Dominican Peso"); ?></option>
                                                <option value="dzd"><?php echo __("DZD - Algerian Dinar"); ?></option>
                                                <option value="egp"><?php echo __("EGP - Egyptian Pound"); ?></option>
                                                <option value="etb"><?php echo __("ETB - Ethiopian Birr"); ?></option>
                                                <option value="eur"><?php echo __("EUR - Euro"); ?></option>
                                                <option value="fjd"><?php echo __("FJD - Fijian Dollar"); ?></option>
                                                <option value="fkp"><?php echo __("FKP - Falkland Islands Pound"); ?></option>
                                                <option value="gbp"><?php echo __("GBP - British Pound"); ?></option>
                                                <option value="gel"><?php echo __("GEL - Georgian Lari"); ?></option>
                                                <option value="gip"><?php echo __("GIP - Gibraltar Pound"); ?></option>
                                                <option value="gmd"><?php echo __("GMD - Gambian Dalasi"); ?></option>
                                                <option value="gnf"><?php echo __("GNF - Guinean Franc"); ?></option>
                                                <option value="gtq"><?php echo __("GTQ - Guatemalan Quetzal"); ?></option>
                                                <option value="gyd"><?php echo __("GYD - Guyanaese Dollar"); ?></option>
                                                <option value="hkd"><?php echo __("HKD - Hong Kong Dollar"); ?></option>
                                                <option value="hnl"><?php echo __("HNL - Honduran Lempira"); ?></option>
                                                <option value="hrk"><?php echo __("HRK - Croatian Kuna"); ?></option>
                                                <option value="htg"><?php echo __("HTG - Haitian Gourde"); ?></option>
                                                <option value="huf"><?php echo __("HUF - Hungarian Forint"); ?></option>
                                                <option value="idr"><?php echo __("IDR - Indonesian Rupiah"); ?></option>
                                                <option value="ils"><?php echo __("ILS - Israeli New Shekel"); ?></option>
                                                <option value="inr"><?php echo __("INR - Indian Rupee"); ?></option>
                                                <option value="isk"><?php echo __("ISK - Icelandic Króna"); ?></option>
                                                <option value="jmd"><?php echo __("JMD - Jamaican Dollar"); ?></option>
                                                <option value="jpy"><?php echo __("JPY - Japanese Yen"); ?></option>
                                                <option value="kes"><?php echo __("KES - Kenyan Shilling"); ?></option>
                                                <option value="kgs"><?php echo __("KGS - Kyrgystani Som"); ?></option>
                                                <option value="kmf"><?php echo __("KMF - Comorian Franc"); ?></option>
                                                <option value="krw"><?php echo __("KRW - South Korean Won"); ?></option>
                                                <option value="kyd"><?php echo __("KYD - Cayman Islands Dollar"); ?></option>
                                                <option value="kzt"><?php echo __("KZT - Kazakhstani Tenge"); ?></option>
                                                <option value="lak"><?php echo __("LAK - Laotian Kip"); ?></option>
                                                <option value="lbp"><?php echo __("LBP - Lebanese Pound"); ?></option>
                                                <option value="lkr"><?php echo __("LKR - Sri Lankan Rupee"); ?></option>
                                                <option value="lrd"><?php echo __("LRD - Liberian Dollar"); ?></option>
                                                <option value="lsl"><?php echo __("LSL - Lesotho Loti"); ?></option>
                                                <option value="mad"><?php echo __("MAD - Moroccan Dirham"); ?></option>
                                                <option value="mdl"><?php echo __("MDL - Moldovan Leu"); ?></option>
                                                <option value="mga"><?php echo __("MGA - Malagasy Ariary"); ?></option>
                                                <option value="mkd"><?php echo __("MKD - Macedonian Denar"); ?></option>
                                                <option value="mmk"><?php echo __("MMK - Myanmar Kyat"); ?></option>
                                                <option value="mnt"><?php echo __("MNT - Mongolian Tugrik"); ?></option>
                                                <option value="mop"><?php echo __("MOP - Macanese Pataca"); ?></option>
                                                <option value="mro"><?php echo __("MRO - Mauritanian Ougiuya"); ?></option>
                                                <option value="mur"><?php echo __("MUR - Mauritian Rupee"); ?></option>
                                                <option value="mvr"><?php echo __("MVR - Maldivian Rufiyaa"); ?></option>
                                                <option value="mwk"><?php echo __("MWK - Malawian Kwacha"); ?></option>
                                                <option value="mxn"><?php echo __("MXN - Mexican Peso"); ?></option>
                                                <option value="myr"><?php echo __("MYR - Malaysian Ringgit"); ?></option>
                                                <option value="mzn"><?php echo __("MZN - Mozambican Metical"); ?></option>
                                                <option value="nad"><?php echo __("NAD - Namibian Dollar"); ?></option>
                                                <option value="ngn"><?php echo __("NGN - Nigerian Naira"); ?></option>
                                                <option value="nio"><?php echo __("NIO - Nicoraguan Córdoba"); ?></option>
                                                <option value="nok"><?php echo __("NOK - Norwegian Krone"); ?></option>
                                                <option value="npr"><?php echo __("NPR - Nepalese Rupee"); ?></option>
                                                <option value="nzd"><?php echo __("NZD - New Zealand Dollar"); ?></option>
                                                <option value="pab"><?php echo __("PAB - Panamanian Balboa"); ?></option>
                                                <option value="pen"><?php echo __("PEN - Peruvian Sol"); ?></option>
                                                <option value="pgk"><?php echo __("PGK - Papue New Guinean Kina"); ?></option>
                                                <option value="php"><?php echo __("PHP - Philippine Peso"); ?></option>
                                                <option value="pkr"><?php echo __("PKR - Pakistani Rupee"); ?></option>
                                                <option value="pln"><?php echo __("PLN - Polish Zloty"); ?></option>
                                                <option value="pyg"><?php echo __("PYG - Paraguayan Guarani"); ?></option>
                                                <option value="qar"><?php echo __("QAR - Qatari Rial"); ?></option>
                                                <option value="ron"><?php echo __("RON - Romanian Leu"); ?></option>
                                                <option value="rsd"><?php echo __("RSD - Serbian Dinar"); ?></option>
                                                <option value="rub"><?php echo __("RUB - Russian Ruble"); ?></option>
                                                <option value="rwf"><?php echo __("RWF - Rwandan Franc"); ?></option>
                                                <option value="sar"><?php echo __("SAR - Saudi Riyal"); ?></option>
                                                <option value="sbd"><?php echo __("SBD - Solomon Islands Dollar"); ?></option>
                                                <option value="scr"><?php echo __("SCR - Seychellois Rupee"); ?></option>
                                                <option value="sek"><?php echo __("SEK - Swedish Krona"); ?></option>
                                                <option value="sgd"><?php echo __("SGD - Singapore Dollar"); ?></option>
                                                <option value="shp"><?php echo __("SHP - St. Helena Pound"); ?></option>
                                                <option value="sll"><?php echo __("SLL - Sierra Leonean Leone"); ?></option>
                                                <option value="sos"><?php echo __("SOS - Somali Shilling"); ?></option>
                                                <option value="srd"><?php echo __("SRD - Surinamese Dollar"); ?></option>
                                                <option value="std"><?php echo __("STD - São Tomé & Príncipe Dobra"); ?></option>
                                                <option value="svc"><?php echo __("SVC - Salvadoran Colón"); ?></option>
                                                <option value="szl"><?php echo __("SZL - Swazi Lilangeni"); ?></option>
                                                <option value="thb"><?php echo __("THB - Thai Baht"); ?></option>
                                                <option value="tjs"><?php echo __("TJS - Tajikistani Somoni"); ?></option>
                                                <option value="top"><?php echo __("TOP - Tongan Pa'anga"); ?></option>
                                                <option value="try"><?php echo __("TRY - Turkish Lira"); ?></option>
                                                <option value="ttd"><?php echo __("TTD - Trinidad & Tobago Dollar"); ?></option>
                                                <option value="twd"><?php echo __("TWD - New Taiwan Dollar"); ?></option>
                                                <option value="tzs"><?php echo __("TZS - Tanzanian Shilling"); ?></option>
                                                <option value="uah"><?php echo __("UAH - Ukranian Hryvnia"); ?></option>
                                                <option value="ugx"><?php echo __("UGX - Ugandan Shilling"); ?></option>
                                                <option value="uyu"><?php echo __("UYU - Uruguayan Peso"); ?></option>
                                                <option value="uzs"><?php echo __("UZS - Uzbekistani Som"); ?></option>
                                                <option value="vnd"><?php echo __("VND - Vietnamese Dong"); ?></option>
                                                <option value="vuv"><?php echo __("VUV - Vanuata Vatu"); ?></option>
                                                <option value="wst"><?php echo __("WST - Samoan Tala"); ?></option>
                                                <option value="xaf"><?php echo __("XAF - Central African CFA Franc"); ?></option>
                                                <option value="xcd"><?php echo __("XCD - East Caribbean Dollar"); ?></option>
                                                <option value="xof"><?php echo __("XOF - West African CFA Franc"); ?></option>
                                                <option value="xpf"><?php echo __("XPF - CFP Franc"); ?></option>
                                                <option value="yer"><?php echo __("YER - Yemeni Rial"); ?></option>
                                                <option value="zar"><?php echo __("ZAR - South African Rand"); ?></option>
                                                <option value="zmw"><?php echo __("ZMW - Zambian Kwacha"); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Price</label>
                                        <div class="col-md-12">
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                <div class="input-group-addon" id="price-addon"></div>
                                                <input type="text" id="price-1" onkeyup="checkPrice0();" class="form-control" pattern="\d+" style="padding-left: 1%;" value="0" placeholder="0" title="<?php echo __("Format: 0"); ?>">
                                                <input type="text" id="price-2" onkeyup="checkPrice1();" class="form-control" pattern="\d+[\.]\d{1}" style="padding-left: 1%;" value="0.0" placeholder="0.0" title="<?php echo __("Format: 0.0"); ?>">
                                                <input type="tet" id="price-3" onkeyup="checkPrice2();" class="form-control" pattern="\d+[\.]\d{2}" style="padding-left: 1%;" value="0.00" placeholder="0.00" title="<?php echo __("Format: 0.00"); ?>">
                                            </div>
                                        </div>
                                    </div>
                                   <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Billing Interval"); ?></label>
                                        <div class="col-md-12">
                                            <select class="form-control select2" name="interval">
                                                <option value="day|1"><?php echo __("Daily"); ?></option>
                                                <option value="week|1"><?php echo __("Weekly"); ?></option>
                                                <option value="month|1" selected><?php echo __("Monthly"); ?></option>
                                                <option value="month|3"><?php echo __("Every 3 Months"); ?></option>
                                                <option value="month|6"><?php echo __("Every 6 Months"); ?></option>
                                                <option value="year|1"><?php echo __("Yearly"); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo __("Trial (Optional)"); ?></label>
                                        <div class="col-md-12">
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                 <input type="text" name="trial" pattern='\d+' class="form-control">
                                                 <div class="input-group-addon">days</div>
                                            </div>
                                                <small class="form-text text-muted"><?php echo __("Subscriptions to this plan will automatically start with a free trial of this length."); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <?php if(isset($mysqldown) && $mysqldown = 'yes') { echo '<span class="d-inline-block" data-container="body" data-toggle="tooltip" title="MySQL Offline">'; } ?>
                                            <button type="submit" class="btn btn-success" <?php if(isset($mysqldown) && $mysqldown == 'yes') { echo 'disabled'; } ?>><?php echo __("Add Plan"); ?></button><?php if(isset($mysqldown) && $mysqldown == 'yes') { echo '</span>'; } ?> &nbsp;
                                            <a href="index.php" style="color: inherit;text-decoration: inherit;"><button onclick="loadLoader();" class="btn btn-muted" type="button"><?php echo __("Back"); ?></button></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <script> 
                    function submitForm() { document.getElementById("form").submit(); };
                    function exitForm() { window.location.href="index.php"; };
                </script>
                <?php hotkeys($configlocation); ?>
                <footer class="footer text-center"><?php footer(); ?></footer>
            </div>
        </div>
        <script src="../components/jquery/jquery.min.js"></script>
        <script src="../components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <script src="../components/sweetalert2/sweetalert2.min.js"></script>
        <script src="../components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../components/metismenu/dist/metisMenu.min.js"></script>
        <script src="../components/select2/select2.min.js"></script>
        <script src="../components/waves/waves.js"></script>
        <script src="../components/footable/footable.min.js"></script>
        <script src="../components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="../components/select2/select2.min.js"></script>
        <script src="../components/bootstrap-select/js/bootstrap-select.min.js"></script>
        <script src="../components/bootstrapvalidator/bootstrapValidator.js"></script>
        <script src="../../js/notifications.js"></script>
        <script src="../../js/main.js"></script>
        <script type="text/javascript">
            var processLocation = "../../process/";
            $(document).ready(function() {
                $('.select2').select2();
            });
            $('#form').submit(function(ev) {
                ev.preventDefault();
                processLoader();
                this.submit();
            });
            (function () {
                [].slice.call(document.querySelectorAll('.sttabs')).forEach(function (el) {
                    new CBPFWTabs(el);
                });
            })();
            function checkPrice0(){
                if(document.getElementById("price-1").value == "") {
                    document.getElementById("price-1").value = "0";
                }
                if(document.getElementById("price-1").value.includes('.') === true) {
                    document.getElementById("price-1").value = document.getElementById("price-1").value.split('.').join('');
                }
                if(document.getElementById("price-1").value.length > 1 && document.getElementById("price-1").value.charAt(0) == '0') {
                    document.getElementById("price-1").value = document.getElementById("price-1").value.substr(1);
                }
            }
            function checkPrice1(){
                if(document.getElementById("price-2").value == "") {
                    document.getElementById("price-2").value = "0.0";
                }
                if(document.getElementById("price-2").value.includes('.') === false && document.getElementById("price-2").value != '0') { 
                    document.getElementById("price-2").value = '0.' + document.getElementById("price-2").value; 
                }
                if(document.getElementById("price-2").value.includes('.') === true && document.getElementById("price-2").value.split(".")[1].length > 1) {
                    document.getElementById("price-2").value = document.getElementById("price-2").value.replace(/\./g,'').slice(0, -1) + '.' + document.getElementById("price-2").value.slice(-1);
                }
                if(document.getElementById("price-2").value.split(".")[0].length > 1 && document.getElementById("price-2").value.split(".")[0].charAt(0) == '0') {
                    document.getElementById("price-2").value = document.getElementById("price-2").value.substr(1);
                }
            }
            function checkPrice2(){
                if(document.getElementById("price-3").value == "") {
                    document.getElementById("price-3").value = "0.00";
                }
                if(document.getElementById("price-3").value.includes('.') === false && document.getElementById("price-3").value != '0') { 
                    document.getElementById("price-3").value = '0.' + document.getElementById("price-3").value; 
                }
                if(document.getElementById("price-3").value.includes('.') === true && document.getElementById("price-3").value.split(".")[1].length > 2) {
                    document.getElementById("price-3").value = document.getElementById("price-3").value.replace(/\./g,'').slice(0, -2) + '.' + document.getElementById("price-3").value.slice(-2);
                }
                if(document.getElementById("price-3").value.split(".")[0].length > 1 && document.getElementById("price-3").value.split(".")[0].charAt(0) == '0') {
                    document.getElementById("price-3").value = document.getElementById("price-3").value.substr(1);
                }
            }
            function checkCurrency(){
                var currency = document.getElementById('selectcurrency').value;
                var currencysymbol = document.getElementById('price-addon');
                
                
                 if (currency == 'bif' || currency == 'cpl' || currency == 'djf' || currency == 'gnf' || currency == 'jpy' || currency == 'kmf' || currency == 'krw' || currency == 'mga' || currency == 'pyg' || currency == 'rwf' || currency == 'vnd' || currency == 'vuv' || currency == 'xaf' || currency == 'xof' || currency == 'xpf') {
                    document.getElementById("price-1").style.display = 'block';
                    document.getElementById("price-1").required = true;
                    document.getElementById("price-1").name = "amount";
                    document.getElementById("price-2").style.display = 'none';
                    document.getElementById("price-2").required = false;
                    document.getElementById("price-2").name = "";
                    document.getElementById("price-3").style.display = 'none';
                    document.getElementById("price-3").required = false;
                    document.getElementById("price-3").name = "";
                }
                else if (currency == 'mro') {
                    document.getElementById("price-1").style.display = 'none';
                    document.getElementById("price-1").required = false;
                    document.getElementById("price-1").name = "";
                    document.getElementById("price-2").style.display = 'block';
                    document.getElementById("price-2").required = true;
                    document.getElementById("price-2").name = "amount";
                    document.getElementById("price-3").style.display = 'none';
                    document.getElementById("price-3").required = false;
                    document.getElementById("price-3").name = "";
                }
                else {
                    document.getElementById("price-1").style.display = 'none';
                    document.getElementById("price-1").required = false;
                    document.getElementById("price-1").name = "";
                    document.getElementById("price-2").style.display = 'none';
                    document.getElementById("price-2").required = false;
                    document.getElementById("price-2").name = "";
                    document.getElementById("price-3").style.display = 'block';
                    document.getElementById("price-3").required = true;
                    document.getElementById("price-3").name = "amount";
                    
                }
                
                if (currency == 'usd') { currencysymbol.innerHTML = '&#36;'; }
                else if (currency == 'aud') { currencysymbol.innerHTML = 'A&#36;'; }
                else if (currency == 'brl') { currencysymbol.innerHTML = 'R&#36;'; }
                else if (currency == 'cad') { currencysymbol.innerHTML = 'CA&#36;'; }
                else if (currency == 'cny') { currencysymbol.innerHTML = 'CN&yen;'; }
                else if (currency == 'eur') { currencysymbol.innerHTML = '&euro;'; }
                else if (currency == 'gbp') { currencysymbol.innerHTML = '&pound;	'; }
                else if (currency == 'hkd') { currencysymbol.innerHTML = 'HK&#36;'; }
                else if (currency == 'ils') { currencysymbol.innerHTML = '&#8362;'; }
                else if (currency == 'inr') { currencysymbol.innerHTML = '&#x20B9;'; }
                else if (currency == 'jpy') { currencysymbol.innerHTML = '&yen;'; }
                else if (currency == 'krw') { currencysymbol.innerHTML = '&#8361;'; }
                else if (currency == 'mxn') { currencysymbol.innerHTML = 'MX&#36;'; }
                else if (currency == 'nzd') { currencysymbol.innerHTML = 'NZ&#36;'; }
                else if (currency == 'twd') { currencysymbol.innerHTML = 'NT&#36;'; }
                else if (currency == 'vnd') { currencysymbol.innerHTML = '&#8363;'; }
                else if (currency == 'xaf') { currencysymbol.innerHTML = 'FCFA'; }
                else if (currency == 'xcd') { currencysymbol.innerHTML = 'EC&#36;'; }
                else if (currency == 'xof') { currencysymbol.innerHTML = 'CFA'; }
                else if (currency == 'xpf') { currencysymbol.innerHTML = 'CFPF'; }
                else { currencysymbol.innerHTML = currency.toUpperCase(); }
            }
            document.getElementById('selectcurrency').onchange = function() {checkCurrency()};
            function toggler(e) {
                if( e.name == 'Hide' ) {
                    e.name = 'Show'
                    document.getElementById('password').type="password";
                } else {
                    e.name = 'Hide'
                    document.getElementById('password').type="text";
                }
            }
            function generatePassword(length) {
                var password = '', character; 
                while (length > password.length) {
                    if (password.indexOf(character = String.fromCharCode(Math.floor(Math.random() * 94) + 33), Math.floor(password.length / 94) * 94) < 0) {
                        password += character;
                    }
                }
                document.getElementById('password').value = password;
                document.getElementById('tg').name='Hide';
                document.getElementById('password').type="text";
            }
            jQuery(function($){
                $('.footable').footable();
            });
            function processLoader(){
                swal.fire({
                    title: '<?php echo __("Processing"); ?>',
                    text: '',
                    onOpen: function () {
                        swal.showLoading()
                    }
                })};
            function loadLoader(){
                swal.fire({
                    title: '<?php echo __("Loading"); ?>',
                    text: '',
                    onOpen: function () {
                        swal.showLoading()
                    }
                })};
            <?php
            processPlugins();
            includeScript();
            
            if(isset($_GET['error']) && $_GET['error'] == "1") {
                echo "swal.fire({title:'" . $errorcode[1]. "', html:'" . __("Please try again or contact support.") . "', icon:'error'});";
            } 
            if(isset($_GET['err']) && $_GET['err'] != "") {
                echo "swal.fire({title:'Stripe Error: " . $_GET['err'] . "', html:'" . __("Please try again or contact support.") . "', icon:'error'});";
            } 
            if(isset($_GET['merr']) && $_GET['merr'] != "") {
                echo " swal.fire({title:'Database Error', html:'" . __("Please try again or contact support.") . "<br><br><span onclick=\"$(\'.errortoggle\').toggle();\" class=\"swal-error-title\">View Error Code <i class=\"errortoggle fa fa-angle-double-right\"></i><i style=\"display:none;\" class=\"errortoggle fa fa-angle-double-down\"></i></span><span class=\"errortoggle\" style=\"display:none;\"><br><br>(MySQL Error: " . $_GET['merr'] . ")</span>', icon:'error'});";
            } 

           
            ?>
                        document.addEventListener('DOMContentLoaded', function(e) {
                FormValidation.formValidation(
                    document.getElementById('form'),
                    {
                        plugins: {
                            declarative: new FormValidation.plugins.Declarative({
                                html5Input: true,
                            }),
                            trigger: new FormValidation.plugins.Trigger(),
                            tachyons: new FormValidation.plugins.Tachyons(),
                            submitButton: new FormValidation.plugins.SubmitButton(),
                            icon: new FormValidation.plugins.Icon({
                                valid: 'fa fa-check',
                                invalid: 'fa fa-times',
                                validating: 'fa fa-refresh',
                            }),
                        },
                    }
                );
            });
        </script>
    </body>
</html>