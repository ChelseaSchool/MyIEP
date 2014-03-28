<?php
/** @file
 * @brief 	modify short-term objective belonging to a student goal
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Filter input
*/
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within

/**
 * edit_short_term_objectives.php -- strength and needs management.
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: February 6, 2006
 * By: M. Nielsen
 * Modified: April 19,2006
 *
 */

/*   INPUTS:
 *           $_GET['sto']
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

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//find the student owner of this objective...
$goal_query="SELECT * FROM short_term_objective LEFT JOIN long_term_goal ON short_term_objective.goal_id=long_term_goal.goal_id WHERE short_term_objective.uid=" . mysql_real_escape_string($_GET['sto']);
$goal_result=mysql_query($goal_query);
if(!$goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$goal_row=mysql_fetch_array($goal_result);
$student_id=$goal_row['student_id'];

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

if($have_write_permission && isset($_GET['edit'])) {
    $update_query = "UPDATE short_term_objective  SET goal_id=" . $goal_row['goal_id'] . ",review_date='" . mysql_real_escape_string($_GET['review_date']) . "',description='" . mysql_real_escape_string($_GET['description']) . "',results_and_recommendations='" . mysql_real_escape_string($_GET['results_and_recommendations']) . "',strategies='" . mysql_real_escape_string($_GET['strategies']) . "',assessment_procedure='" . mysql_real_escape_string($_GET['assessment_procedure']) . "' WHERE uid=" . mysql_real_escape_string($_GET['sto']);
    $update_result = mysql_query($update_query);
    if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
       //lets to to the sto page
       header("Location: " . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id);
    }

}

//now we need to rerun the goal query because we may have added above
//if brain wasn't off might be able to figure out a better way to do this
//find the student owner of this objective...
$goal_query="SELECT * FROM short_term_objective LEFT JOIN long_term_goal ON short_term_objective.goal_id=long_term_goal.goal_id WHERE short_term_objective.uid=" . mysql_real_escape_string($_GET['sto']);
$goal_result=mysql_query($goal_query);
if(!$goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$goal_row=mysql_fetch_array($goal_result);
$student_id=$goal_row['student_id'];

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************



$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.medicationlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + ",";
                     count++;
                  }
              }
          }
          if(!count) { alert("Nothing Selected"); return false; }
          if(confirm(szConfirmMessage))
              return true;
          else
              return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
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
                    <center><?php navbar("long_term_goal_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center>
                          <table>
                            <tr><td>
                              <center><p class="header">- Edit Short Term Objective (<?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?>)-</p></center>
                            </td></tr>
                          </table>
                        </center>
                        <BR>

                        <!-- BEGIN add short term objective -->
                        <center>
                        <form name="edit_objective" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_short_term_objective.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">Edit and click 'Update'.</p>
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                           <input type="hidden" name="sto" value="<?php echo $goal_row['uid']; ?>">
                           <input type="hidden" name="edit" value="1">
                          </td>
                        </tr>
                        <tr>
                            <td valign="center" bgcolor="#E0E2F2" class="row_default">Long Term<BR>Goal:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <textarea disabled name="text" cols="40" rows="3" wrap="soft"><?php echo $goal_row['goal']; ?></textarea>
                            </td>
                        </tr>
                        <tr><td bgcolor="#E0E2F2" valign="center" class="row_default"><p class="row_default">Review Date:&nbsp;</td>
                              <td bgcolor="#E0E2F2">
                                <input type="text" name="review_date" value="<?php echo $goal_row['review_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.review_date, 'yyyy-m-dd', 0, 0)">
                              </td>
                        </tr>
                        <tr>
                            <td valign="center" bgcolor="#E0E2F2" class="row_default">Short Term<BR>Objective:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <textarea name="description" cols="40" rows="3" wrap="soft"><?php echo $goal_row['description']; ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td valign="center" bgcolor="#E0E2F2" class="row_default">Assessment Procedure:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <textarea name="assessment_procedure" cols="40" rows="3" wrap="soft"><?php echo $goal_row['assessment_procedure']; ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td valign="center" bgcolor="#E0E2F2" class="row_default">Strategies:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <textarea name="strategies" cols="40" rows="3" wrap="soft"><?php echo $goal_row['strategies']; ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td valign="center" bgcolor="#E0E2F2" class="row_default">Progress Review:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <textarea name="results_and_recommendations" cols="40" rows="3" wrap="soft"><?php echo stripslashes($goal_row['results_and_recommendations']); ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="row_default" bgcolor="#E0E2F2">&nbsp;</td>
                            <td valign="center" align="center" bgcolor="#E0E2F2"><input type="submit" name="update" value="update"></td>
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
            <?php navbar("long_term_goal_view.php?student_id=$student_id"); ?>
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
