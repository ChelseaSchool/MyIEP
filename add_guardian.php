<?php

/** @file
 * @brief 	add student guardian	
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //everybody (do checks within document)



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');

header('Pragma: no-cache'); //don't cache this page!

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

if(!isset($_GET['student_id'])) {
    //ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
} else {
    $student_id=$_GET['student_id'];
}

$student_query = "select * from student where student.student_id=" . $_GET['student_id'];
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$student_row=mysql_fetch_array($student_result);

//get our permissions for this student...
$current_student_permission = getStudentPermission($student_row['student_id']);

//check if we need to update the guardian list and have the required permissions to do so...
if(!($current_student_permission == "ALL" || $current_student_permission == "ASSIGN" || $current_student_permission == "WRITE")) {
    //yeah, we don't have permission to be here throw a security fail...
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

function parse_submission() {
    if(!$_GET['first_name']) return "You must supply a first name<BR>";
    if(!$_GET['last_name']) return "You must supply a last name<BR>";

    return NULL;
}

//ok, are we adding now??
if(isset($_GET['add_guardian'])) {
  //parse??
  $retval = parse_submission();
  if($retval != NULL) {
      $system_message = $system_message . $retval;
  } else {
      $guardian_query="INSERT INTO guardian (first_name,last_name) VALUES ('" . mysql_real_escape_string($_GET['first_name']) . "','" . mysql_real_escape_string($_GET['last_name']) . "')";
      $guardian_result=mysql_query($guardian_query);
       if(!$guardian_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$guardian_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
         //attach to student ID and redirect...
            $guardian_id = mysql_insert_id();
            $guardians_query="INSERT INTO guardians (student_id,guardian_id,from_date,to_date) VALUES (" . mysql_real_escape_string($_GET['student_id']) . ",$guardian_id,NOW(),null)";
            $guardians_result=mysql_query($guardians_query);
            if(!$guardians_result) {
                 $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$guardians_query'<BR>";
                 $system_message=$system_message . $error_message;
                 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            } else {
               //redirect to student page....
               header("Location: ./guardian_view.php?student_id=" . mysql_real_escape_string($_GET['student_id']));
               exit();
            }
         }
  }
}

?> 

<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }
    </SCRIPT>
</HEAD>
<BODY>
        <table class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
        <tr>
          <td class="shadow-topLeft"></td>
            <td class="shadow-top"></td>
            <td class="shadow-topRight"></td>
        </tr>
        <tr>
            <td class="shadow-left"></td>
            <td class="shadow-center" valign="top">
                <table class="frame" width=620px align=center border="0">
                    <tr align="Center">
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>
                    <tr><td>
                    <center><?php navbar("guardian_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center>
                        <table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">- IPP Add Guardian-</p></center></td></tr><tr><td><center><p class="bold_text"> <?php echo $student_row['first_name'] . " " . $student_row['last_name'] .  ", Permission: " . $current_student_permission;?></p></center></td></tr></table>
                        </center>
                        <BR>
                        <center>
                        <form name="addGuardian" enctype="multipart/form-data" action="<?php echo IPP_PATH . "add_guardian.php"; ?>" method="get">
                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                        <tr>
                          <td colspan="2">
                          <p class="info_text">Fill out and click 'Add Guardian'.</p>
                          <input type="hidden" name="add_guardian" value="1">
                          <input type="hidden" name="student_id" value="<?php echo $student_row['student_id']; ?>">
                          </td>
                        </tr>

                        <tr>
                          <td bgcolor="#E0E2F2" align="left">First Name:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" tabindex="1" name="first_name" size="30" maxsize="125" value="<?php if(isset($user_row['first_name'])) echo $user_row['first_name']; else if(isset($_POST['first_name'])) echo $_POST['first_name'];?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Last Name:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" name="last_name" tabindex="2" size="30" maxsize="125" value="<?php if(isset($user_row['last_name'])) echo $user_row['last_name']; else if(isset($_POST['last_name'])) echo $_POST['last_name']; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;&nbsp;<input tabindex="3" type="submit" value="Add Guardian"></td>
                        </tr>
                        </table>
                        </form>
                        </center>

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">
            &nbsp;
            </td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        <center></center>
    </BODY>
</HTML>
