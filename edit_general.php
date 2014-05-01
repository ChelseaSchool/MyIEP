<?php
/** @file
 * @brief 	unsure?
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * * Filter input
 * * Require Fields for form
 * * Bootstrap Theme
 * * Add datepicker
 */ 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 60; //TA
/*
 * Notes: 
 * 1. Vague title offers nothing except this is a page to edit info for the db
 * 2. brief description of page on the leading comments suggests either a dual purpose or poor comments.
 * 3. Looks like input isn't sanitized (all input must be filtered for security of the system)
 * 
 * 
/**
 * edit_general.php -- add student
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: June 21, 2005
 * By: M. Nielsen
 * Modified:  March 30, 2005
 * Modified:  February 17, 2007
 *
 */

/*   INPUTS: $_GET['student_id'] or
 *    $_PUT['student_id] must be a student ID number
 *
 */

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

$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if($student_id=="") {
   //we shouldn't be here without a username.
   echo "You've entered this page without supplying a valid user name. Fatal, quitting";
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
if($our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
    //we don't have write permission...shouldn't be on this page...
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************

function parse_submission() {
    if(!$_POST['first_name']) return "You must supply a first name<BR>";
    if(!$_POST['last_name']) return "You must supply a last name<BR>";
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['birthday'])) return "Birthday must be in YYYY-MM-DD format<BR>";
    //if(!$_POST['prov_ed_num']) return "You must supply a Provincial Education Number<BR>";

    //check duplicate prov ed number...
    if(!connectIPPDB()) {
          $error_message = $error_message;  //just to remember we need this
          $system_message = $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }
     if($_POST['prov_ed_num'] != "") {
       $duplicate_query = "SELECT * FROM student WHERE prov_ed_num='" . mysql_real_escape_string($_POST['prov_ed_num']) . "' AND student_id !=" . $_POST['student_id'];
       $duplicate_result= mysql_query($duplicate_query);
       if(mysql_num_rows($duplicate_result) > 0) {$duplicate_row = mysql_fetch_array($duplicate_result);return "Duplicate Provincial Education Number (name:" . $duplicate_row['first_name'] . " " . $duplicate_row['last_name'] ."),<BR>This student probably already exists in the database<BR>";}
     }
     //$duplicate_query = "SELECT * FROM student WHERE ab_ed_code='" . mysql_real_escape_string($_POST['ab_ed_code']) ."' AND student_id !=" . $_POST['student_id'];
     //$duplicate_result= mysql_query($duplicate_query);
     //if(mysql_num_rows($duplicate_result) > 0) {$duplicate_row = mysql_fetch_array($duplicate_result);return "Duplicate Alberta Education Code Number (name:" . $duplicate_row['first_name'] . " " . $duplicate_row['last_name'] ."),<BR>This student probably already exists in the database<BR>"; }
    

    return NULL;
}

//check if we are modifying a student...
if(isset($_POST['modify_student'])) {

     if(!connectIPPDB()) {
          $error_message = $error_message;  //just to remember we need this
          $system_message = $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }

     //do some error checking on data submission...
     $retval = parse_submission();
     if($retval != NULL) {
         $system_message = $system_message . $retval;
     } else {
       $update_query="UPDATE student SET first_name='" . mysql_real_escape_string($_POST['first_name']) . "',last_name='" .  mysql_real_escape_string($_POST['last_name']) ."',birthday='" . mysql_real_escape_string($_POST['birthday']) . "',prov_ed_num='" .  mysql_real_escape_string($_POST['prov_ed_num']) . "',current_grade='" . mysql_real_escape_string($_POST['current_grade']) . "',gender='" . mysql_real_escape_string($_POST['gender']) . "' WHERE student_id=" . $_POST['student_id'];
       $update_result=mysql_query($update_query);
       if(!$update_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
            $system_message=$system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
           //log this action...
           IPP_LOG("Update General Information",$_SESSION['egps_username'],'INFORMATIONAL',$_POST['student_id']);

           //successful add...direct to new_student_3...
           header("Location: student_view.php?student_id=" . $_POST['student_id']);
           exit();
       }
     }


}

if(!connectUserDB()) {
        $error_message = $error_message;  //just to remember we need this
        $system_message = $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//find all of the available schools..
if(!connectIPPDB()) {
   $error_message = $error_message;  //just to remember we need this
   $system_message = $error_message;
   IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//find this students general information...
$student_query="Select * FROM student WHERE student_id='$student_id'";

$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   //there should be only one row...so
   $student_row = mysql_fetch_array($student_result);
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
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>

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
                    <center><?php navbar("student_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table><tr><td><center><p class="header">- Edit General Information-</p></center></td></tr></table></center>
                        <BR>

                        <center>
                        <form name="addName" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_general.php"; ?>" method="post">
                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                        <tr>
                          <td colspan="2">
                          <p class="info_text">Edit and click 'Update Student'.</p>
                          <input type="hidden" name="modify_student" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>

                        <tr>
                          <td bgcolor="#E0E2F2" align="left">First Name:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" required name="first_name" size="30" maxsize="125" value="<?php echo $student_row['first_name']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Last Name:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" required name="last_name" size="30" maxsize="125" value="<?php echo $student_row['last_name']; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Birthdate: (YYYY-MM-DD)&nbsp;</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" required name="birthday" value="<?php echo $student_row['birthday']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.birthday, 'yyyy-m-dd', 0, 0)">
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Current Grade:</td>
                          <td bgcolor="#E0E2F2">
                            <SELECT name="current_grade">
                                 <OPTION value="-1" <?php if($student_row['current_grade'] == "-1") echo "selected"; ?>>District Program
                                 <OPTION value="0" <?php if($student_row['current_grade'] == "0") echo "selected"; ?>>K or Pre-K
                                 <OPTION value="1" <?php if($student_row['current_grade'] == "1") echo "selected"; ?>>1
                                 <OPTION value="2" <?php if($student_row['current_grade'] == "2") echo "selected"; ?>>2
                                 <OPTION value="3" <?php if($student_row['current_grade'] == "3") echo "selected"; ?>>3
                                 <OPTION value="4" <?php if($student_row['current_grade'] == "4") echo "selected"; ?>>4
                                 <OPTION value="5" <?php if($student_row['current_grade'] == "5") echo "selected"; ?>>5
                                 <OPTION value="6" <?php if($student_row['current_grade'] == "6") echo "selected"; ?>>6
                                 <OPTION value="7" <?php if($student_row['current_grade'] == "7") echo "selected"; ?>>7
                                 <OPTION value="8" <?php if($student_row['current_grade'] == "8") echo "selected"; ?>>8
                                 <OPTION value="9" <?php if($student_row['current_grade'] == "9") echo "selected"; ?>>9
                                 <OPTION value="10" <?php if($student_row['current_grade'] == "10") echo "selected"; ?>>10
                                 <OPTION value="11" <?php if($student_row['current_grade'] == "11") echo "selected"; ?>>11
                                 <OPTION value="12" <?php if($student_row['current_grade'] == "12") echo "selected"; ?>>12
                                 <OPTION value="13" <?php if($student_row['current_grade'] == "13") echo "selected"; ?>>13
                            </SELECT>
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Gender</td>
                          <td bgcolor="#E0E2F2">
                            <SELECT name="gender">
                                <option value="M" <?php if($student_row['gender'] == "M") echo "SELECTED"; ?>>Male
                                <option value="F" <?php if($student_row['gender'] == "F") echo "SELECTED"; ?>>Female
                            </SELECT>
                          </td>
                        </tr>
                        <tr>
                          <td bgcolor="#E0E2F2" align="left">Student Number:</td>
                          <td bgcolor="#E0E2F2">
                            <input type="text" size="30" maxsize="60" name="prov_ed_num" value="<?php echo $student_row['prov_ed_num'];?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td valign="bottom" align="center" bgcolor="#E0E2F2" colspan="2">&nbsp;&nbsp;<input type="submit" value="Update Student"></td>
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
            <?php navbar("student_view.php?student_id=$student_id"); ?>
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
