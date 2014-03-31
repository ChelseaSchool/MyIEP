<?php
/** @file
 * @brief 	produces IEP PDF report
 * 
 */
 
 
/* Notes
 * 1. Produces the IEP PDF report - note it relies on old pdf tool.
 * 2. some code is commented out. If it serves no testing or other productive purpose, consider removing it
 * 3. setting the variable $message to " " is a security precaution. check for consistency.
 * 4. note the file name is wrong in the header
 * 5. Note "IE6 sucks" in comments. Dev to self and others - IE complicating things. Still the case.
 * 6. Some uncertainty by the dev about how to handle cookies/sessions. Should the commented attempts be removed? why?
 * 7. Mostly relies on include code - specifically create_pdf. Output from PHP should be escaped.
 */
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



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
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/fpdf/fpdf.php');
//require_once("Numbers/Roman.php"); //require pear roman numerals class
require_once(IPP_PATH . 'include/create_pdf.php');

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

$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "You've entered this page without supplying a valid student id. Fatal, quitting";
   exit();
}

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$our_permission = getStudentPermission($student_id);
if($our_permission != "WRITE" && $our_permission != "READ" && $our_permission != "ALL" && $our_permission != "ASSIGN") {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************


  $pdf=create_pdf($student_id);

  $pdf->Output();

  exit();
?> 

