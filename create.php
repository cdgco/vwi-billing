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

\Stripe\Stripe::setApiKey($billingconfig['sec_key']);

$sqlplans = array_values($billingplans);
$sqlpackages = array_keys($billingplans);
$interval = explode("|", $_POST['interval']);

if(isset($_POST['trial']) && $_POST['trial'] != ''){
    $trial = intval($_POST['trial']);
}
else {
    $trial = null;
}
if(isset($_POST['statement']) && $_POST['statement'] != ''){
    $statement = intval($_POST['statement']);
}
else {
    $statement = null;
}
if (isset($_POST['package']) && $_POST['package'] != ''){
    if (isset($_POST['name']) && $_POST['name'] != '' && isset($_POST['id']) && $_POST['id'] != '' && isset($_POST['amount']) && $_POST['amount'] != '' && isset($_POST['interval']) && $_POST['interval'] != '' && isset($_POST['currency']) && $_POST['currency'] != ''){
        try { 
            \Stripe\Product::create(array(
              "name" => $_POST['name'],
              "type" => "service",
              "id" => "vwi_prod_" . $_POST['id'],
              "statement_descriptor" => $statement
            )); 
        } 
        catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
        if(isset($err) || $err != '') {
           header("Location: add.php?err=" . $err . "&package=" . $_POST['package']);
        }
        else {
            try { 
                \Stripe\Plan::create(array(
                  "amount" => intval(str_replace(".","", $_POST['amount'])),
                  "id" => "vwi_plan_" . $_POST['id'],
                  "interval" => $interval[0],
                  "interval_count" => intval($interval[1]),
                  "product" => "vwi_prod_" . $_POST['id'],
                  "currency" => $_POST['currency'],
                  "trial_period_days" => $trial,
                ));
            }
            catch (\Stripe\Error\Base $e) { $err = $e->getJsonBody()['error']['code']; }
            if(isset($err) || $err != '') {
                header("Location: add.php?err=" . $err . "&package=" . $_POST['package']);
            }
            else {
                $r1 = 0;
                $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
                $v1 = mysqli_real_escape_string($con, $_POST['package']);
                $v2 = mysqli_real_escape_string($con, $_POST['id']);
                $addtotable= "INSERT INTO `" . $mysql_table . "billing-plans` (PACKAGE, ID) VALUES ('".$v1."','".$v2."') ON DUPLICATE KEY UPDATE ID='".$v2."';";
                if (mysqli_query($con, $addtotable)) {} else { $r1 = $r1 + 1; }
                mysqli_close($con);

            }
        }
    }
    else { header("Location: add.php?error=1&package=" . $_POST['package']); }
}
else { header("Location: index.php?error=1"); }
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
            echo '<input type="hidden" name="a1" value="'.$r1.'">';
            ?>
        </form>
        <script type="text/javascript">
            document.getElementById('form').submit();
        </script>
    </body>
    <script src="../components/jquery/dist/jquery.min.js"></script>
</html>