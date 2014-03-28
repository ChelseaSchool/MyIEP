<?php

/** @file
 * @brief 	Manage individual student record
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 *   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *   You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
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

//@todo make authentication routine a function
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

//This is a bad scenario that shouldn't be possible - just in case, there's an error message

if(!isset($_GET['student_id'])) {
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
}

$student_query = "select * from student where student.student_id=" . $_GET['student_id'];
$student_result = mysql_query($student_query);
if(!$student_query) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$student_row=mysql_fetch_array($student_result);
$student_id=$student_row['student_id'];

//find support members...
$support_member_query = "SELECT * FROM support_list WHERE student_id=" . $_GET['student_id'] . " ORDER BY egps_username";
$support_member_result = mysql_query($support_member_query);
if(!$support_member_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$support_member_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


//find the current coding...
$coding_query = "SELECT * FROM coding WHERE student_id=" . $_GET['student_id'] . " AND end_date IS NULL";
$coding_result = mysql_query($coding_query);
if(!$coding_query) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$coding_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$coding_row=mysql_fetch_array($coding_result);

//************** validated past here SESSION ACTIVE****************

//get our permissions for this student...
$our_permission = getStudentPermission($_GET['student_id']);

if($our_permission != "READ" && $our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
  //we don't have permission...
  $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
  IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  require(IPP_PATH . 'security_error.php');
  exit();
}

$supervisor_row="";
$supervisor_query = "SELECT * FROM supervisor WHERE student_id=" . mysql_real_escape_string($_GET['student_id']) . " AND end_date IS NULL";
$supervisor_result = mysql_query($supervisor_query);
if(!$supervisor_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    //In theory, should be only one row - this fetches it
    $supervisor_row=mysql_fetch_array($supervisor_result);
}

$school_row="";


$school_query = "SELECT * FROM school_history LEFT JOIN school on school_history.school_code=school.school_code WHERE end_date IS NULL AND student_id='" . $_GET['student_id'] . "'";
$school_result = mysql_query($school_query);
if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    //there is only one row (or should be...so get it.)
    $school_row=mysql_fetch_array($school_result);
}

/** @fn get_age_by_date($yyyymmdd)
 *  @brief Assuming no errors, calculate age based on DOB and query date.
 *  @todo Reformat date and validation.
 */
//make sure they entered a valid date (mm-dd-yyyy)
function get_age_by_date($yyyymmdd)
{
    global $system_message;
    $bdate = explode("-", $yyyymmdd);
    $dob_month=$bdate[1]; $dob_day=$bdate[2]; $dob_year=$bdate[0];
    if (checkdate($dob_month, $dob_day, $dob_year)) {
        $dob_date = "$dob_year" . "$dob_month" . "$dob_day";
        $age = floor((date("Ymd")-intval($dob_date))/10000);
        if (($age < 0) or ($age > 114)) {
            return $age . "<BR> -->Age warning: Negative or Zero (check D.O.B)<--";
        }
        return $age;
    }
    return "-unknown-";
}


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
    
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
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
                    <center><?php navbar("manage_student.php"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">-Student View-</p></center></td></tr>
                                                                                   <tr><td><center><p class="bold_text"> <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?></p></center></td></tr>
                                                                                   <tr><td><center><p class="bold_text"> Current Age: <?php echo get_age_by_date($student_row['birthday']) ?></center></td></tr>
                                                                                   <?php if($school_row['school_name']=="") echo "<tr><td><center><p class=\"message\">-Archived Student-</p></center></td></tr>"  ?>

                        </table></center>
                        <BR>

                        <center>
                        <?php $colour0="#DFDFDF"; $colour1="#CCCCCC"; ?>
                        <center><a href="<?php echo IPP_PATH . "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&file=ipp.pdf";?>" target="_blank"><img src="<?php echo IPP_PATH . "images/view-ippbutton.png";?>" border="0"></a>
                        </center>
                        <HR>
                        <!-- Nav -->
                        <a href="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Guardians";?>" border="0"></a>
                        <!--a href="<?php echo IPP_PATH . "supervisor_view.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Supervisor";?>" border="0"></a -->
                        <a href="<?php echo IPP_PATH . "strength_need_view.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Strength+%26+Needs"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Coord.+of+Services"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Achieve+Level"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Medical+Info."?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Medication"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Testing+to+Support"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Background+Info"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Year+End+Review"?>" border="0" target="_blank"></a>
                        <a href="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Anecdotals"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Asst.+Technology"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Transition+Plan"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Accommodations"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Snapshots"?>" border="0"></a>
                        <a href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/mainbutton.php?title=Goals"?>" border="0"></a>
                        <!-- end NAV -->
                        <HR>
                        <!-- BEGIN CODING INFORMATION -->
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                        <td colspan="3">
                            <p class="bold_text">Coding
                        </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour1; ?>">
                                Current Code:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour1; ?>">
                                <?php
                                if(mysql_num_rows($coding_result) <= 0) {
                                    echo "Currently not coded";
                                } else {
                                    echo $coding_row['code'] . " since<BR> " . $coding_row['start_date'];
                                }
                                ?>
                            </td>
                            <td width="100" rowspan="7" valign="center">
                               <a href="<?php echo IPP_PATH . "coding.php?student_id=" . $student_row['student_id'];?>"><img src="<?php echo IPP_PATH . "images/smallbutton.php?title=Edit";?>" border="0"></a>
                            </td>
                        </tr>
                        </table>

                        <!-- END SCHOOL INFORMATION -->

                        <!-- The general stuff -->
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                        <td colspan="3">
                            <p class="bold_text">General Information
                        </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour0; ?>">
                                Name:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour0; ?>">
                                <?php echo $student_row['first_name'] . " " . $student_row['last_name'];?>
                            </td>
                            <td width="100" rowspan="7" valign="center">
                               <?php
                                   if($our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission !="ALL")
                                       echo "<a href=\"" . IPP_PATH . "security_error.php\" onClick=\"return noPermission();\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Modify\" border=\"0\">";
                                   else
                                       echo "<a href=\"" . IPP_PATH . "edit_general.php?student_id=" . $student_row['student_id'] . "\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit\" border=\"0\">";
                               ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour0; ?>">
                                Sex:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour0; ?>">
                                <?php if($student_row['gender'] =="F") echo "Female"; else echo "Male";?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour0; ?>">
                                Date of Birth:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour0; ?>">
                                <?php echo $student_row['birthday'];?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour0; ?>">
                                Current Grade:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour0; ?>">
                                <?php
                                      switch ($student_row['current_grade']) {
                                        case '0':
                                           echo "K or Pre-K";
                                           break;
                                        case '-1':
                                           echo "District Program";
                                           break;
                                        default:
                                            echo  $student_row['current_grade'];
                                      }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour0; ?>">
                                Student Number:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour0; ?>">
                                <?php echo $student_row['prov_ed_num'];?>
                            </td>
                        </tr>
                        </table>
                        <!-- END The general stuff -->

                        <!-- BEGIN Supervisor INFORMATION -->
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <!-- The general stuff -->
                        <tr>
                        <td colspan="3">
                            <p class="bold_text">Supervisor
                        </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour1; ?>">
                                Current Supervisor:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour1; ?>">
                                <?php echo $supervisor_row['egps_username'];?>
                            </td>
                            <td width="100" rowspan="7" valign="center">
                               <a href="<?php echo IPP_PATH . "supervisor_view.php?student_id=" . $_GET['student_id'];?>"><img src="<?php echo IPP_PATH . "images/smallbutton.php?title=Change";?>" border="0"></a>
                            </td>
                        </tr>
                        </table>

                        <!-- END Supervisor INFORMATION -->




                        <!-- BEGIN Support Member Information -->
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                        <td colspan="3">
                            <p class="bold_text">Support Members
                        </td>
                        <td rowspan="<?php $iSupportNum=mysql_num_rows($support_member_result); if($iSupportNum <= 0) echo "2"; else echo $iSupportNum +1; ?>" valign="center" align="right" width="100">
                            <?php
                            if($our_permission !="ALL" && $our_permission !="ASSIGN" && $our_permission != "WRITE" )
                                echo "<a href=\"" . IPP_PATH . "security_error.php\" onClick=\"return noPermission();\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Modify\" border=\"0\">";
                            else
                                echo "<a href=\"" . IPP_PATH . "modify_ipp_permission.php?student_id=" . $_GET['student_id'] . "\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Manage\" border=\"0\">";
                            ?>
                        </td>
                        <?php
                            if(mysql_num_rows($support_member_result) <=0) {
                                echo "<tr><td colspan=\"3\" align=\"center\" bgcolor=\"$colour1\">-none-</td></tr>";
                            }
                        ?>
                        </tr>
                        <?php
                            while($support_member_row=mysql_fetch_array($support_member_result)) {
                                echo "<tr>\n";
                                echo "<td class=\"field_text\" bgcolor=\"$colour0\">" . $support_member_row['egps_username'] . "</td>\n";
                                echo "<td class=\"result_text\" bgcolor=\"$colour0\">" . $support_member_row['permission'] . "</td>\n";
                                if($support_member_row['support_area'] == "")
                                    echo "<td class=\"result_text\" bgcolor=\"$colour0\">No area assigned</td>\n";
                                else
                                    echo "<td class=\"result_text\" bgcolor=\"$colour0\">" . $support_member_row['support_area'] . "</td>\n";
                                echo "</tr>\n";
                            }
                        ?>
                        </table>
                        <!-- END Support Member Information -->

                        <!-- BEGIN SCHOOL INFORMATION -->
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <!-- The general stuff -->
                        <tr>
                        <td colspan="3">
                            <p class="bold_text">School Information
                        </td>
                        </tr>
                        <tr>
                            <td class="field_text" bgcolor="<?php echo $colour1; ?>">
                                Current School:
                            </td>
                            <td class="result_text" bgcolor="<?php echo $colour1; ?>">
                                <?php
                                 if($school_row['school_name']=="")
                                  echo "-Archived Student-";
                                 else
                                  echo $school_row['school_name'] . " since<BR>" . $school_row['start_date'];
                                ?>
                            </td>
                            <td width="100" rowspan="7" valign="center">
                              <?php if($our_permission !="ALL" && $our_permission !="ASSIGN" && $our_permission != "WRITE" )
                               echo "<a href=\"" . IPP_PATH . "school_history.php?student_id=" . $student_id . "\" onClick=\"return noPermission();\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Move/History" . "\" border=\"0\">";
                              else
                               echo "<a href=\""  . IPP_PATH . "school_history.php?student_id=" . $student_id . "\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Move/History" . "\" border=\"0\">";
                              ?>
                            </td>
                        </tr>
                        </table>

                        <!-- END SCHOOL INFORMATION -->
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
            <?php navbar("manage_student.php"); ?>
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
