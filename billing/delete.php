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

require("stripe-php/init.php");

if($username != 'admin') { header("Location: ../../../"); }
if(!isset($_GET['plan']) || $_GET['plan'] == ''){ header("Location: index.php?error=1"); }
if(!isset($_GET['id']) || $_GET['id'] == ''){ header("Location: index.php?error=1"); }
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
               $billingcustomers = json_decode(file_get_contents( $co1 . 'billingcustomers.json'), true);}
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
$r1 = 0;
$plansdata = array_keys($billingplans);
if( strpos( $plansdata[array_search($_GET['plan'], $plansdata)], $_GET['plan']) !== false ) {
    if($configstyle != '2') {
        $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
        $currentplan = mysqli_real_escape_string($con, $_GET['plan']);
        $sql1 = "DELETE FROM `" . $mysql_table . "billing-plans` WHERE `PACKAGE` = '" . $currentplan . "';";
        if (mysqli_query($con, $sql1)) {} else { $r1 = 1; }
        mysqli_close($con);
    }
    else {
        if (!$con) { $r1 = 2; }
        else { 
            $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
            $currentplan = mysqli_real_escape_string($con, $_GET['plan']);
            $sql1 = "DELETE FROM `" . $mysql_table . "billing-plans` WHERE `PACKAGE` = '" . $currentplan . "';";
            if (mysqli_query($con, $sql1)) {} else { $r1 = 1; }
            mysqli_close($con);

        }
    }  
}

\Stripe\Stripe::setApiKey($billingconfig['sec_key']);

try { $currentplan = \Stripe\Plan::retrieve('vwi_plan_' . $_GET['id']); } 
catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
if(isset($err) || $err != '') {}
else {      
    $currentplan->delete();
    try { $currentprod = \Stripe\Product::retrieve('vwi_prod_' . $_GET['id']); } 
    catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
    if(isset($err) || $err != '') {}
    else {
        $currentprod->delete();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="../../css/style.css" rel="stylesheet">
    </head>
    <body class="fix-header">
        <div class="preloader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> 
            </svg>
        </div>

        <form id="form" action="index.php" method="post">
            <?php 
            echo '<input type="hidden" name="d1" value="'.$r1.'">';
            ?>
        </form>
        <script type="text/javascript">
            document.getElementById('form').submit();
        </script>
    </body>
    <script src="../components/jquery/dist/jquery.min.js"></script>
</html>