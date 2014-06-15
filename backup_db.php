<?php

/** @file
 * @brief 	superadmin page to backup database
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0; //superadmin only

 

/*   INPUTS: $_GET['student_id'] || $_PUT['student_id']
 *
 */
 //

/**
 * Path for IPP required files.
 */

$system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';


//header('Pragma: no-cache'); //don't cache this page!
//header("Cache-Control: no-cache, must-revalidate");
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
Header('Pragma: public, no-cache');  //IE6 SUCKS


if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
}
//************* SESSION active past here **************************

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}


//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

//include class
require_once(IPP_PATH . 'include/MySQLDump.class.php');

//create new instance of MySQLDump
$backup = new MySQLDump();
 
//set drop table if exists
$backup->droptableifexists = true;

//connect to mysql server (host, user, pass, db)
$backup->connect($mysql_data_host,$mysql_data_username,$mysql_data_password,'ipp');

//if not connected, display error
if (!$backup->connected) { die('Error: '.$backup->mysql_error); }

//get all tables in db
$backup->list_tables();

//reset buffer
$buffer = '';

//go through all tables and dump them to buffer
foreach ($backup->tables as $table) {
    $buffer .= $backup->dump_table($table);
}

if (strstr($HTTP_USER_AGENT,"MSIE 5.5")) { // had to make it MSIE 5.5 because if 6 has no "attachment;" in it it defaults to "inline"
    $attachment = "";
} else {
    $attachment = "attachment;";
}

header("Pragma: ");
header("Cache-Control: ");

header("Content-Length: " . strlen($buffer));

//display dumped buffer
header("Content-Type: text/x-sql");
//header("Content-Disposition: attachment; filename=ipp_database_" . date("Y-m-d_H.m.s") . ".sql");
header("Content-disposition: $attachment filename=\"ipp_database_" . date("Y-m-d_H.m.s") . ".sql\"");

echo $buffer; //we can use htmlspecialchars in case that there are some html tags in database



  exit();
?>
