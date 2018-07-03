<?php

session_start();
$configlocation = "../../includes/";
if (file_exists( '../../includes/config.php' )) { require( '../../includes/includes.php'); }  else { header( 'Location: ../../install' );};

if(base64_decode($_SESSION['loggedin']) == 'true') {}
else { header('Location: ../../login.php?to=plugins/vwi-billing/add.php'); }

if(!isset($_GET['package']) || $_GET['package'] == '') { header("Location: index.php"); }
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
setlocale("LC_CTYPE", $locale); setlocale("LC_MESSAGES", $locale);
bindtextdomain('messages', '../locale');
textdomain('messages');

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
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="image/ico" href="../images/<?php echo $cpfavicon; ?>">
        <title><?php echo $sitetitle; ?> - <?php echo _("Billing"); ?></title>
        <link href="../../bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../components/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
        <link href="../components/footable/css/footable.bootstrap.css" rel="stylesheet">
        <link href="../components/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
        <link href="../components/custom-select/custom-select.css" rel="stylesheet">
        <link href="../../css/animate.css" rel="stylesheet">
        <link href="../../css/style.css" rel="stylesheet">
        <link href="../components/toast-master/css/jquery.toast.css" rel="stylesheet">
        <link href="../../css/colors/<?php if(isset($_COOKIE['theme'])) { echo base64_decode($_COOKIE['theme']); } else {echo $themecolor; } ?>" id="theme" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.11.5/sweetalert2.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css" />
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
                    </ul>
                    <ul class="nav navbar-top-links navbar-right pull-right">

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
                                <li><a href="../../profile.php"><i class="ti-home"></i> <?php echo _("My Account"); ?></a></li>
                                <li><a href="../../profile.php?settings=open"><i class="ti-settings"></i> <?php echo _("Account Settings"); ?></a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="../../process/logout.php"><i class="fa fa-power-off"></i> <?php echo _("Logout"); ?></a></li>
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
                            <span class="hide-menu"><?php echo _("Navigation"); ?></span>
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
                            <h4 class="page-title"><?php echo _("Add Plan"); ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <form class="form-horizontal form-material" autocomplete="off" action="create.php" id="form" method="post">
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Package"); ?></label>
                                        <div class="col-md-12">
                                                <input type="text" disabled class="form-control" value="<?php echo $_GET['package']; ?>">
                                            <input type="hidden" name="package" value="<?php echo $_GET['package']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Product Name"); ?></label>
                                        <div class="col-md-12">
                                                <input type="text" class="form-control" name="name" required>
                                                <small class="form-text text-muted"><?php echo _("This will appear on customers' receipts and invoices."); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Internal ID"); ?></label>
                                        <div class="col-md-12">
                                            <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                <div class="input-group-addon">vwi_prod_</div>
                                                <input type="text" class="form-control" style="padding-left: 0.5%;" pattern="[0-9A-Za-z]{14,}" name="id" title="14 Character Minimum. Letters & Numbers." value="<?php echo randomPassword(); ?>" required>
                                            </div>
                                            <small class="form-text text-muted"><?php echo _("Unique Product ID used in Stripe and VWI Backend"); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Statement Descriptor (Optional)"); ?></label>
                                        <div class="col-md-12">
                                                <input type="text" class="form-control" name="statement">
                                                <small class="form-text text-muted"><?php echo _("This will appear on customers' bank statements, so make sure it's clearly recognizable."); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Currency"); ?></label>
                                        <div class="col-md-12">
                                            <select class="form-control select2" name="currency" id="selectcurrency">
                                                <option value="usd">USD - US Dollars</option>
                                                <option value="aed">AED - United Areb Emirates Dirham</option>
                                                <option value="afn">AFN - Afghan Afghani</option>
                                                <option value="all">ALL - Albanian Lek</option>
                                                <option value="amd">AMD - Armenian Dram</option>
                                                <option value="ang">ANG - Netherlands Antillean Guilder</option>
                                                <option value="aoa">AOA - Angolan Kwanza</option>
                                                <option value="ars">ARS - Argentine Peso</option>
                                                <option value="aud">AUD - Australian Dollar</option>
                                                <option value="awg">AWG - Aruban Florin</option>
                                                <option value="azn">AZN - Azerbaijani Manat</option>
                                                <option value="bam">BAM - Bosnia-Herzegovina Convertible Mark</option>
                                                <option value="bbd">BBD - Barbadian Dollar</option>
                                                <option value="bdt">BDT - Bangladeshi Taka</option>
                                                <option value="bgn">BGN - Bulgarian Lev</option>
                                                <option value="bif">BIF - Burundian Franc</option>
                                                <option value="bmd">BMD - Bermudan Dollar</option>
                                                <option value="bnd">BND - Brunei Dollar</option>
                                                <option value="bob">BOB - Bolivian Boliviano</option>
                                                <option value="brl">BRL - Brazilian Real</option>
                                                <option value="bsd">BSD - Bahamian Dollar</option>
                                                <option value="bwp">BWP - Botswanan Pula</option>
                                                <option value="bzd">BZD - Belize Dollar</option>
                                                <option value="cad">CAD - Canadian Dollar</option>
                                                <option value="cdf">CDF - Congolese Franc</option>
                                                <option value="chf">CHF - Swiss Franc</option>
                                                <option value="clp">CLP - Chilean Peso</option>
                                                <option value="cny">CNY - Chinese Yuan</option>
                                                <option value="cop">COP - Colombian Peso</option>
                                                <option value="crc">CRC - Costa Rican Colón</option>
                                                <option value="cve">CVE - Cape Verdean Escudo</option>
                                                <option value="czk">CZK - Czech Koruna</option>
                                                <option value="cjf">DJF - Diboutian Franc</option>
                                                <option value="dkk">DKK - Danish Krone</option>
                                                <option value="dop">DOP - Dominican Peso</option>
                                                <option value="dzd">DZD - Algerian Dinar</option>
                                                <option value="egp">EGP - Egyptian Pound</option>
                                                <option value="etb">ETB - Ethiopian Birr</option>
                                                <option value="eur">EUR - Euro</option>
                                                <option value="fjd">FJD - Fijian Dollar</option>
                                                <option value="fkp">FKP - Falkland Islands Pound</option>
                                                <option value="gbp">GBP - British Pound</option>
                                                <option value="gel">GEL - Georgian Lari</option>
                                                <option value="gip">GIP - Gibraltar Pound</option>
                                                <option value="gmd">GMD - Gambian Dalasi</option>
                                                <option value="gnf">GNF - Guinean Franc</option>
                                                <option value="gtq">GTQ - Guatemalan Quetzal</option>
                                                <option value="gyd">GYD - Guyanaese Dollar</option>
                                                <option value="hkd">HKD - Hong Kong Dollar</option>
                                                <option value="hnl">HNL - Honduran Lempira</option>
                                                <option value="hrk">HRK - Croatian Kuna</option>
                                                <option value="htg">HTG - Haitian Gourde</option>
                                                <option value="huf">HUF - Hungarian Forint</option>
                                                <option value="idr">IDR - Indonesian Rupiah</option>
                                                <option value="ils">ILS - Israeli New Shekel</option>
                                                <option value="inr">INR - Indian Rupee</option>
                                                <option value="isk">ISK - Icelandic Króna</option>
                                                <option value="jmd">JMD - Jamaican Dollar</option>
                                                <option value="jpy">JPY - Japanese Yen</option>
                                                <option value="kes">KES - Kenyan Shilling</option>
                                                <option value="kgs">KGS - Kyrgystani Som</option>
                                                <option value="kmf">KMF - Comorian Franc</option>
                                                <option value="krw">KRW - South Korean Won</option>
                                                <option value="kyd">KYD - Cayman Islands Dollar</option>
                                                <option value="kzt">KZT - Kazakhstani Tenge</option>
                                                <option value="lak">LAK - Laotian Kip</option>
                                                <option value="lbp">LBP - Lebanese Pound</option>
                                                <option value="lkr">LKR - Sri Lankan Rupee</option>
                                                <option value="lrd">LRD - Liberian Dollar</option>
                                                <option value="lsl">LSL - Lesotho Loti</option>
                                                <option value="mad">MAD - Moroccan Dirham</option>
                                                <option value="mdl">MDL - Moldovan Leu</option>
                                                <option value="mga">MGA - Malagasy Ariary</option>
                                                <option value="mkd">MKD - Macedonian Denar</option>
                                                <option value="mmk">MMK - Myanmar Kyat</option>
                                                <option value="mnt">MNT - Mongolian Tugrik</option>
                                                <option value="mop">MOP - Macanese Pataca</option>
                                                <option value="mro">MRO - Mauritanian Ougiuya</option>
                                                <option value="mur">MUR - Mauritian Rupee</option>
                                                <option value="mvr">MVR - Maldivian Rufiyaa</option>
                                                <option value="mwk">MWK - Malawian Kwacha</option>
                                                <option value="mxn">MXN - Mexican Peso</option>
                                                <option value="myr">MYR - Malaysian Ringgit</option>
                                                <option value="mzn">MZN - Mozambican Metical</option>
                                                <option value="nad">NAD - Namibian Dollar</option>
                                                <option value="ngn">NGN - Nigerian Naira</option>
                                                <option value="nio">NIO - Nicoraguan Córdoba</option>
                                                <option value="nok">NOK - Norwegian Krone</option>
                                                <option value="npr">NPR - Nepalese Rupee</option>
                                                <option value="nzd">NZD - New Zealand Dollar</option>
                                                <option value="pab">PAB - Panamanian Balboa</option>
                                                <option value="pen">PEN - Peruvian Sol</option>
                                                <option value="pgk">PGK - Papue New Guinean Kina</option>
                                                <option value="php">PHP - Philippine Peso</option>
                                                <option value="pkr">PKR - Pakistani Rupee</option>
                                                <option value="pln">PLN - Polish Zloty</option>
                                                <option value="pyg">PYG - Paraguayan Guarani</option>
                                                <option value="qar">QAR - Qatari Rial</option>
                                                <option value="ron">RON - Romanian Leu</option>
                                                <option value="rsd">RSD - Serbian Dinar</option>
                                                <option value="rub">RUB - Russian Ruble</option>
                                                <option value="rwf">RWF - Rwandan Franc</option>
                                                <option value="sar">SAR - Saudi Riyal</option>
                                                <option value="sbd">SBD - Solomon Islands Dollar</option>
                                                <option value="scr">SCR - Seychellois Rupee</option>
                                                <option value="sek">SEK - Swedish Krona</option>
                                                <option value="sgd">SGD - Singapore Dollar</option>
                                                <option value="shp">SHP - St. Helena Pound</option>
                                                <option value="sll">SLL - Sierra Leonean Leone</option>
                                                <option value="sos">SOS - Somali Shilling</option>
                                                <option value="srd">SRD - Surinamese Dollar</option>
                                                <option value="std">STD - São Tomé & Príncipe Dobra</option>
                                                <option value="svc">SVC - Salvadoran Colón</option>
                                                <option value="szl">SZL - Swazi Lilangeni</option>
                                                <option value="thb">THB - Thai Baht</option>
                                                <option value="tjs">TJS - Tajikistani Somoni</option>
                                                <option value="top">TOP - Tongan Pa'anga</option>
                                                <option value="try">TRY - Turkish Lira</option>
                                                <option value="ttd">TTD - Trinidad & Tobago Dollar</option>
                                                <option value="twd">TWD - New Taiwan Dollar</option>
                                                <option value="tzs">TZS - Tanzanian Shilling</option>
                                                <option value="uah">UAH - Ukranian Hryvnia</option>
                                                <option value="ugx">UGX - Ugandan Shilling</option>
                                                <option value="uyu">UYU - Uruguayan Peso</option>
                                                <option value="uzs">UZS - Uzbekistani Som</option>
                                                <option value="vnd">VND - Vietnamese Dong</option>
                                                <option value="vuv">VUV - Vanuata Vatu</option>
                                                <option value="wst">WST - Samoan Tala</option>
                                                <option value="xaf">XAF - Central African CFA Franc</option>
                                                <option value="xcd">XCD - East Caribbean Dollar</option>
                                                <option value="xof">XOF - West African CFA Franc</option>
                                                <option value="xpf">XPF - CFP Franc</option>
                                                <option value="yer">YER - Yemeni Rial</option>
                                                <option value="zar">ZAR - South African Rand</option>
                                                <option value="zmw">ZMW - Zambian Kwacha</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Price</label>
                                        <div class="col-md-12">
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                <div class="input-group-addon" id="price-addon"></div>
                                                <input type="text" id="price-1" onkeyup="checkPrice0();" class="form-control" pattern="\d+" style="padding-left: 1%;" value="0" placeholder="0" title="Format: 0">
                                                <input type="text" id="price-2" onkeyup="checkPrice1();" class="form-control" pattern="\d+[\.]\d{1}" style="padding-left: 1%;" value="0.0" placeholder="0.0" title="Format: 0.0">
                                                <input type="tet" id="price-3" onkeyup="checkPrice2();" class="form-control" pattern="\d+[\.]\d{2}" style="padding-left: 1%;" value="0.00" placeholder="0.00" title="Format: 0.00">
                                            </div>
                                        </div>
                                    </div>
                                   <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Billing Interval"); ?></label>
                                        <div class="col-md-12">
                                            <select class="form-control select2" name="interval">
                                                <option value="day|1">Daily</option>
                                                <option value="week|1">Weekly</option>
                                                <option value="month|1" selected>Monthly</option>
                                                <option value="month|3">Every 3 Months</option>
                                                <option value="month|6">Every 6 Months</option>
                                                <option value="year|1">Yearly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12"><?php echo _("Trial (Optional)"); ?></label>
                                        <div class="col-md-12">
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                 <input type="text" name="trial" pattern='\d+' class="form-control">
                                                 <div class="input-group-addon">days</div>
                                            </div>
                                                <small class="form-text text-muted"><?php echo _("Subscriptions to this plan will automatically start with a free trial of this length."); ?></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <?php if(isset($mysqldown) && $mysqldown = 'yes') { echo '<span class="d-inline-block" data-container="body" data-toggle="tooltip" title="MySQL Offline">'; } ?>
                                            <button type="submit" class="btn btn-success" <?php if(isset($mysqldown) && $mysqldown == 'yes') { echo 'disabled'; } ?>><?php echo _("Add Plan"); ?></button><?php if(isset($mysqldown) && $mysqldown == 'yes') { echo '</span>'; } ?> &nbsp;
                                            <a href="index.php" style="color: inherit;text-decoration: inherit;"><button onclick="loadLoader();" class="btn btn-muted" type="button"><?php echo _("Back"); ?></button></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer text-center">&copy; <?php echo date("Y") . ' ' . $sitetitle; ?>. <?php echo _("Vesta Web Interface"); ?> <?php require '../../includes/versioncheck.php'; ?> <?php echo _("by Carter Roeser"); ?>.</footer>
            </div>
        </div>
        <script src="../components/jquery/dist/jquery.min.js"></script>
        <script src="../components/toast-master/js/jquery.toast.js"></script>
        <script src="../../bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../components/sidebar-nav/dist/sidebar-nav.min.js"></script>
        <script src="../../js/jquery.slimscroll.js"></script>
        <script src="../../js/waves.js"></script>
        <script src="../components/moment/moment.js"></script>
        <script src="../components/footable/js/footable.min.js"></script>
        <script src="../components/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
        <script src="../components/custom-select/custom-select.min.js"></script>
        <script src="../../js/footable-init.js"></script>
        <script src="../../js/custom.js"></script>
        <script src="../../js/dashboard1.js"></script>
        <script src="../../js/cbpFWTabs.js"></script>
        <script src="../components/styleswitcher/jQuery.style.switcher.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.11.5/sweetalert2.all.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
        <script type="text/javascript">
            <?php 
            $pluginlocation = "../"; if(isset($pluginnames[0]) && $pluginnames[0] != '') { $currentplugin = 0; do { if (strtolower($pluginhide[$currentplugin]) != 'y' && strtolower($pluginhide[$currentplugin]) != 'yes') { if (strtolower($pluginadminonly[$currentplugin]) != 'y' && strtolower($pluginadminonly[$currentplugin]) != 'yes') { if (strtolower($pluginnewtab[$currentplugin]) == 'y' || strtolower($pluginnewtab[$currentplugin]) == 'yes') { $currentstring = "<li><a href='" . $pluginlocation . $pluginlinks[$currentplugin] . "/' target='_blank'><i class='fa " . $pluginicons[$currentplugin] . " fa-fw'></i><span class='hide-menu'>" . _($pluginnames[$currentplugin] ) . "</span></a></li>"; } else { $currentstring = "<li><a href='".$pluginlocation.$pluginlinks[$currentplugin]."/'><i class='fa ".$pluginicons[$currentplugin]." fa-fw'></i><span class='hide-menu'>"._($pluginnames[$currentplugin])."</span></a></li>"; }} else { if(strtolower($pluginnewtab[$currentplugin]) == 'y' || strtolower($pluginnewtab[$currentplugin]) == 'yes') { if($username == 'admin') { $currentstring = "<li><a href='" . $pluginlocation . $pluginlinks[$currentplugin] . "/' target='_blank'><i class='fa " . $pluginicons[$currentplugin] . " fa-fw'></i><span class='hide-menu'>" . _($pluginnames[$currentplugin] ) . "</span></a></li>";} } else { if($username == 'admin') { $currentstring = "<li><a href='" . $pluginlocation . $pluginlinks[$currentplugin] . "/'><i class='fa " . $pluginicons[$currentplugin] . " fa-fw'></i><span class='hide-menu'>" . _($pluginnames[$currentplugin] ) . "</span></a></li>"; }}} echo "var plugincontainer" . $currentplugin . " = document.getElementById ('append" . $pluginsections[$currentplugin] . "');\n var plugindata" . $currentplugin . " = \"" . $currentstring . "\";\n plugincontainer" . $currentplugin . ".innerHTML += plugindata" . $currentplugin . ";\n"; } $currentplugin++; } while ($pluginnames[$currentplugin] != ''); } ?>
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
                swal({
                    title: '<?php echo _("Processing"); ?>',
                    text: '',
                    onOpen: function () {
                        swal.showLoading()
                    }
                })};
            function loadLoader(){
                swal({
                    title: '<?php echo _("Loading"); ?>',
                    text: '',
                    onOpen: function () {
                        swal.showLoading()
                    }
                })};
            <?php
            
            includeScript();
            
            if(isset($_GET['error']) && $_GET['error'] == "1") {
                echo "swal({title:'" . $errorcode[1] . "<br><br>" . _("Please try again or contact support.") . "', type:'error'});";
            } 
            if(isset($_GET['err']) && $_GET['err'] != "") {
                echo "swal({title:'Stripe Error: " . $_GET['err'] . "<br><br>" . _("Please try again or contact support.") . "', type:'error'});";
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