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
if (file_exists( '../../../includes/config.php' )) { require( '../../../includes/includes.php'); }  else { header( 'Location: ../../../install' ); exit();};
if(base64_decode($_SESSION['loggedin']) == 'true') {}
else { header('Location: ../../../login.php'); exit();}

if(!in_array('vwi-billing', $plugins)) {
    header( 'Location: ../../../register.php' ); exit();
}    

if ((!isset($_POST['verified'])) || ($_POST['verified'] == '')) { header('Location: ../index.php?error=1'); exit();}
elseif ((!isset($_POST['package'])) || ($_POST['package'] == '')) { header('Location: ../index.php?error=1'); exit();}
elseif ((!isset($_POST['type'])) || ($_POST['type'] == '')) { header('Location: ../index.php?error=1'); exit();}

if($_POST['type'] == 'free') {
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $v1 = mysqli_real_escape_string($con, $_POST['package']);
    $droprow= "INSERT INTO `" . $mysql_table . "billing-plans` (PACKAGE, ID, DISPLAY) VALUES ('".$v1."','', 'true') ON DUPLICATE KEY UPDATE DISPLAY='true';";
    if (mysqli_query($con, $droprow)) { $r1 = '0'; } else { $r1 = mysqli_errno($con); }
    mysqli_close($con);
}
if($_POST['type'] != 'free') {
    $con=mysqli_connect($mysql_server,$mysql_uname,$mysql_pw,$mysql_db);
    $v1 = mysqli_real_escape_string($con, $_POST['package']);
    $droprow= "UPDATE `" . $mysql_table . "billing-plans` SET `DISPLAY` = 'true' WHERE `package` = '".$v1."';";
    if (mysqli_query($con, $droprow)) { $r1 = '0'; } else { $r1 =  mysqli_errno($con); }
    mysqli_close($con);
}
echo $r1;
?>