<?php

session_start();
$configlocation = "../../includes/";
if (file_exists( '../../includes/config.php' )) { require( '../../includes/includes.php'); }  else { header( 'Location: ../../install' );};

if(base64_decode($_SESSION['loggedin']) == 'true') {}
else { header('Location: ../../login.php?to=plugins/vwi-billing'); }

require("stripe-lib/init.php");

if($configstyle != '2') {
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $billingconfig = array(); $billingresult=mysqli_query($con,"SELECT VARIABLE,VALUE FROM `" . $mysql_table . "billing-config`");
    while ($bcrow = mysqli_fetch_assoc($billingresult)) { $billingconfig[$bcrow["VARIABLE"]] = $bcrow["VALUE"]; }
    mysqli_free_result($billingresult); mysqli_close($con);
    
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $billingplans = array(); $billingresult2=mysqli_query($con,"SELECT PACKAGE,ID FROM `" . $mysql_table . "billing-plans`");
    while ($bprow = mysqli_fetch_assoc($billingresult2)) { $billingplans[$bprow["PACKAGE"]] = $bprow["ID"]; }
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
        $billingplans = array(); $billingresult2=mysqli_query($con,"SELECT PACKAGE,ID FROM `" . $mysql_table . "billing-plans`");
        while ($bprow = mysqli_fetch_assoc($billingresult2)) { $billingconfig2[$bprow["PACKAGE"]] = $bcrow["ID"]; }
        mysqli_free_result($billingresult2); mysqli_close($con);
        if (!file_exists( $co1 . 'billingplans.json' )) { 
            file_put_contents( $co1 . "billingplans.json",json_encode($billingplans));
        }  
        elseif ((time()-filemtime( $co1 . "billingplans.json")) > 1800 || $billingplans != json_decode(file_get_contents( $co1 . 'billingplans.json'), true)) { 
            file_put_contents( $co1 . "billingplans.json",json_encode($billingplans)); 
        }
        
    }
}
\Stripe\Stripe::setApiKey($billingconfig['KEY']);

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
$sqlplans = array_values($billingplans);
$sqlpackages = array_keys($billingplans);
$useremail = $admindata['CONTACT'];
if(isset($admindata['LANGUAGE'])){ $locale = $ulang[$admindata['LANGUAGE']]; }
setlocale("LC_CTYPE", $locale); setlocale("LC_MESSAGES", $locale);
bindtextdomain('messages', '../../locale');
textdomain('messages');

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
        <link rel="icon" type="image/ico" href="../../plugins/images/<?php echo $cpfavicon; ?>">
        <title><?php echo $sitetitle; ?> - <?php echo _("Billing"); ?></title>
        <link href="../../bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../plugins/components/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
        <link href="../../plugins/components/footable/css/footable.bootstrap.css" rel="stylesheet">
        <link href="../../plugins/components/bootstrap-select/bootstrap-select.min.css" 
              rel="stylesheet">
        <link href="../../plugins/components/custom-select/custom-select.css" rel="stylesheet">
        <link href="../../css/animate.css" rel="stylesheet">
        <link href="../../css/style.css" rel="stylesheet">
        <link href="../../plugins/components/toast-master/css/jquery.toast.css" rel="stylesheet">
        <link href="../../css/colors/<?php if(isset($_COOKIE['theme'])) { echo base64_decode($_COOKIE['theme']); } else {echo $themecolor; } ?>" id="theme" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.11.5/sweetalert2.min.css" />
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
                            <h4 class="page-title"><?php echo _("Billing"); ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <h3 class="box-title m-b-0"><?php echo _("Active Plans"); ?></h3><br>
                                <div class="table-responsive">
                                <table class="table footable m-b-0" data-paging="false" data-sorting="true">
                                    <thead>
                                        <tr>
                                            <th><?php echo _("Package"); ?></th>
                                            <th><?php echo _("Product Name"); ?></th>
                                            <th><?php echo _("Price"); ?></th>
                                            <th><?php echo _("Trial"); ?></th>
                                            <th><?php echo _("Created"); ?></th>
                                            <th data-sortable="false"><?php echo _("Action"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($packname[0] != '') { 
                                            $x1 = 0; 

    do {
        $searchpackage = array_search($packname[$x1], $sqlpackages);
        if(strpos($sqlpackages[$searchpackage], $packname[$x1]) !== false ) {
            try { $currentplan = \Stripe\Plan::retrieve('vwi_plan_' . $sqlplans[$searchpackage])->__toArray(true); } 
            catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
            if(isset($err) || $err != '') {}
            else {
                try { $currentproduct = \Stripe\Product::retrieve('vwi_prod_' . $sqlplans[$searchpackage])->__toArray(true); } 
                catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
                if(isset($err) || $err != '') {}
                else {
                    echo '<tr>
                        <td>' . $packname[$x1] . '</td>
                        <td>' . $currentproduct['name'] . '</td>
                        <td>';
                    
                    if($currentplan['currency'] == "aed" || $currentplan['currency'] == "afn" || $currentplan['currency'] == "dkk" || $currentplan['currency'] == "dzd" || $currentplan['currency'] == "egp" || $currentplan['currency'] == "lbp" || $currentplan['currency'] == "mad" || $currentplan['currency'] == "nok" || $currentplan['currency'] == "qar" || $currentplan['currency'] == "sar" || $currentplan['currency'] == "sek" || $currentplan['currency'] == "yer"){
                        echo number_format(($currentplan['amount']/100), 2, '.', ' ') . ' ' .  $currencies[$currentplan['currency']];
                    }
                    elseif($currentplan['currency'] == "bif" || $currentplan['currency'] == "clp" || $currentplan['currency'] == "djf" || $currentplan['currency'] == "gnf" || $currentplan['currency'] == "jpy" || $currentplan['currency'] == "kmf" || $currentplan['currency'] == "krw" || $currentplan['currency'] == "mga" || $currentplan['currency'] == "pyg" || $currentplan['currency'] == "rwf" || $currentplan['currency'] == "vnd" || $currentplan['currency'] == "vuv" || $currentplan['currency'] == "xaf" || $currentplan['currency'] == "xof" || $currentplan['currency'] == "xpf"){
                        echo $currencies[$currentplan['currency']] . ' ' . $currentplan['amount'];
                    }
                    elseif($currentplan['currency'] == "mro"){
                        echo $currencies[$currentplan['currency']] . ' ' .  number_format(($currentplan['amount']/10), 1, '.', ' ');
                    }
                    else {
                       echo $currencies[$currentplan['currency']] . ' ' . $currentplan['amount'] . ' ' .  strtoupper($currentplan['currency']);
                    }
                    echo ' / ';

                    if ($currentplan['interval_count'] > 1) {
                            echo $currentplan['interval_count'] . ' ';
                        }
                        echo $currentplan['interval'];

                        if ($currentplan['interval_count'] > 1) {
                            echo 's';
                        }

                    echo '</td>
                        <td>'; if(!is_null($currentplan['trial_period_days']) && isset($currentplan['trial_period_days']) && $currentplan['trial_period_days'] != ''){
                            echo $currentplan['trial_period_days'] . ' days';
                        }
                    else { echo 'Disabled'; }
                    echo '</td>
                        <td>' . date("Y-d-m", $currentplan['created']) . '</td>
                        <td><a href="edit.php?package=' . $packname[$x1] . '"><button type="button" data-toggle="tooltip" data-original-title="' . _("Edit") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-edit"></i></button></a><span>
                        <button onclick="confirmDeactivate(\'' . $packname[$x1] . '\', \'' . $sqlplans[$searchpackage] . '\')" type="button" data-toggle="tooltip" data-original-title="' . _("Deactivate") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-times"></i></button>
                    </td>
                    </tr>'; 
                }
            }
        }
        $x1++;
    } while (isset($packname[$x1])); }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                                <br>
                                <h3 class="box-title m-b-0"><?php echo _("Inactive Plans"); ?></h3><br>
                                <div class="table-responsive">
                                <table class="table footable m-b-0" data-paging="false" data-sorting="true">
                                    <thead>
                                        <tr>
                                            <th><?php echo _("Package"); ?></th>
                                            <th data-sortable="false"><?php echo _("Action"); ?></th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($packname[0] != '') { 
                                            $x2 = 0; 

                                            do {
                                                
                                                $searchpackage = array_search($packname[$x2], $sqlpackages);
                                                
                                                if( strpos( $sqlpackages[$searchpackage], $packname[$x2] ) === false ) {
    
                                                echo '<tr>
                                                    <td>' . $packname[$x2] . '</td>
                                                    <td><a href="add.php?package=' . $packname[$x2] . '"><button type="button" data-toggle="tooltip" data-original-title="' . _("Setup") . '" class="btn color-button btn-outline btn-circle btn-md m-r-5"><i class="fa fa-cog"></i></button></a><span></td>
                                                </tr>'; }
                                                $x2++;
                                                    
                                            } while (isset($packname[$x2])); }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer text-center">&copy; <?php echo date("Y") . ' ' . $sitetitle; ?>. <?php echo _("Vesta Web Interface"); ?> <?php require '../../includes/versioncheck.php'; ?> <?php echo _("by Carter Roeser"); ?>.</footer>
            </div>
        </div>
        <script src="../../plugins/components/jquery/dist/jquery.min.js"></script>
        <script src="../../plugins/components/toast-master/js/jquery.toast.js"></script>
        <script src="../../bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="../../plugins/components/sidebar-nav/dist/sidebar-nav.min.js"></script>
        <script src="../../js/jquery.slimscroll.js"></script>
        <script src="../../js/waves.js"></script>
        <script src="../../plugins/components/moment/moment.js"></script>
        <script src="../../plugins/components/footable/js/footable.min.js"></script>
        <script src="../../plugins/components/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
        <script src="../../plugins/components/custom-select/custom-select.min.js"></script>
        <script src="../../js/footable-init.js"></script>
        <script src="../../js/custom.js"></script>
        <script src="../../js/dashboard1.js"></script>
        <script src="../../js/cbpFWTabs.js"></script>
        <script src="../../plugins/components/styleswitcher/jQuery.style.switcher.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.11.5/sweetalert2.all.js"></script>
        <script src="../../plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script type="text/javascript">
            jQuery(function($){
                $('.footable').footable();
            });
            function confirmDeactivate(e, f){
                e1 = String(e);
                f1 = String(f);
                swal({
                    title: '<?php echo _("Deactivate Plan:"); ?> ' + e1 + ' ?',
                    text: "<?php echo _("You won't be able to revert this!"); ?>",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '<?php echo _("Yes, deactivate it!"); ?>'
                }).then(function () {
                    swal({
                        title: '<?php echo _("Processing"); ?>',
                        text: '',
                        onOpen: function () {
                            swal.showLoading()
                        }
                    }).then(
                        function () {},
                        function (dismiss) {}
                    )
                    window.location.replace("delete.php?plan=" + e1 + "&id=" + f1);
                })}

            <?php 
            
            includeScript();
            
            $pluginlocation = "../"; if(isset($pluginnames[0]) && $pluginnames[0] != '') { $currentplugin = 0; do { if (strtolower($pluginhide[$currentplugin]) != 'y' && strtolower($pluginhide[$currentplugin]) != 'yes') { if (strtolower($pluginadminonly[$currentplugin]) != 'y' && strtolower($pluginadminonly[$currentplugin]) != 'yes') { if (strtolower($pluginnewtab[$currentplugin]) == 'y' || strtolower($pluginnewtab[$currentplugin]) == 'yes') { $currentstring = "<li><a href='" . $pluginlocation . $pluginlinks[$currentplugin] . "/' target='_blank'><i class='fa " . $pluginicons[$currentplugin] . " fa-fw'></i><span class='hide-menu'>" . _($pluginnames[$currentplugin] ) . "</span></a></li>"; } else { $currentstring = "<li><a href='".$pluginlocation.$pluginlinks[$currentplugin]."/'><i class='fa ".$pluginicons[$currentplugin]." fa-fw'></i><span class='hide-menu'>"._($pluginnames[$currentplugin])."</span></a></li>"; }} else { if(strtolower($pluginnewtab[$currentplugin]) == 'y' || strtolower($pluginnewtab[$currentplugin]) == 'yes') { if($username == 'admin') { $currentstring = "<li><a href='" . $pluginlocation . $pluginlinks[$currentplugin] . "/' target='_blank'><i class='fa " . $pluginicons[$currentplugin] . " fa-fw'></i><span class='hide-menu'>" . _($pluginnames[$currentplugin] ) . "</span></a></li>";} } else { if($username == 'admin') { $currentstring = "<li><a href='" . $pluginlocation . $pluginlinks[$currentplugin] . "/'><i class='fa " . $pluginicons[$currentplugin] . " fa-fw'></i><span class='hide-menu'>" . _($pluginnames[$currentplugin] ) . "</span></a></li>"; }}} echo "var plugincontainer" . $currentplugin . " = document.getElementById ('append" . $pluginsections[$currentplugin] . "');\n var plugindata" . $currentplugin . " = \"" . $currentstring . "\";\n plugincontainer" . $currentplugin . ".innerHTML += plugindata" . $currentplugin . ";\n"; } $currentplugin++; } while ($pluginnames[$currentplugin] != ''); }
            
            if(isset($_POST['a1']) && $_POST['a1'] == "0") {
                echo "swal({title:'" . _("Successfully Created!") . "', type:'success'});";
            } 
            if(isset($_POST['a1']) && $_POST['a1'] > "0") { echo "swal({title:'Error Creating Plan.<br>Please Try Again.', type:'error'});";
                                                          }
            ?>
        </script>
    </body>
</html>