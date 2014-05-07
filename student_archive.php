<?php

/** @file
 * @brief 	Archives a student (disenrolls from a member institution)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. archived students - confirm what is meant by that
 */ 
 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;  //all, decide in the page



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');
require_once 'include/supporting_functions.php';
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
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//check if we are deleting some peeps...
//print_r ($_POST);

if(isset($_POST['delete_x'])) {
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $delete_query = "DELETE FROM student WHERE ";
    foreach($_POST as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "student_id=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //echo $delete_query . "<-><BR>";
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}

  //get the list of all users...
  //wonder how php handles dangling else...
  if(!isset($_GET['iLimit']))
    if(!isset($_POST['iLimit'])) $iLimit = 50;
        else $iLimit=$_POST['iLimit'];
  else $iLimit = $_GET['iLimit'];

  if(!isset($_GET['iCur']))
    if(!isset($_POST['iCur'])) $iCur = 0;
    else $iCur=$_POST['iCur'];
  else $iCur = $_GET['iCur'];

  if(!isset($_GET['szSchool']))
    if(!isset($_POST['szSchool'])) $szSchool = "ALL";
    else $szSchool=$_POST['szSchool'];
  else $szSchool = $_GET['szSchool'];

$szTotal=0;
function getStudents() {
    global$error_message,$permission_level,$system_message,$IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS,$IPP_MIN_VIEW_LIST_ALL_STUDENTS,$iLimit,$iCur,$szSchool,$szTotalStudents;
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    //do a subquery to find our school code...easier than messing with the ugly
    //query below...
    $school_code_query="SELECT school_code FROM support_member WHERE egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "'";
    $school_code_result=mysql_query($school_code_query);
    if(!$school_code_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_code_query'<BR>";
        return NULL;
    }
    $school_code_row=mysql_fetch_array($school_code_result);
    $school_code= $school_code_row['school_code'];

    $student_query = "SELECT DISTINCT student.student_id,last_name,first_name FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id WHERE ((support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND support_list.student_id IS NOT NULL)";
    //prior to april 20/06: $student_query = "SELECT DISTINCT student.student_id,last_name,first_name FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id WHERE ((support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND support_list.student_id IS NOT NULL)";
    //prior to march 18/06: $student_query = "SELECT DISTINCT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE (support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND support_list.student_id IS NOT NULL) OR (";
    
    if(($IPP_MIN_VIEW_LIST_ALL_STUDENTS >= $permission_level)) {
        //orig 2006-04-20: $student_query = $student_query . " OR (end_date IS NOT NULL)";
        $student_query = $student_query . " OR (student.student_id IS NOT NULL)";
    }
    $student_query .= ") AND NOT EXISTS (SELECT student.student_id,last_name,first_name FROM school_history WHERE school_history.student_id=student.student_id AND school_history.end_date IS NULL)";
    $student_query_limit = $student_query . " ORDER BY student.last_name ASC LIMIT $iCur,$iLimit";
    $student_result_limit = mysql_query($student_query_limit);
    if(!$student_result_limit) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query_limit'<BR>";
        return NULL;
    }
    //$system_message = $system_message . "rows returned: " . mysql_num_rows($student_result_limit) . "<BR>";
    //$system_message = $system_message . $student_query_limit . "<BR>";

    //find the totals...
    $student_result_total = mysql_query($student_query);
    if(!$student_result_total) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
        return NULL;
    }
    $szTotalStudents =  mysql_num_rows($student_result_total);
    $system_message = $system_message . "Number of archived students: $szTotalStudents<BR>";
    $system_message = $system_message . "(Showing: " . mysql_num_rows($student_result_limit) . ")<BR>";
    //$system_message = $system_message . "<BR>$student_query<BR><BR>";
    return $student_result_limit;
}



$sqlStudents=getStudents(); //$szTotalStudents contains total number of stdnts.


//get totals...

if(!$sqlStudents) {
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


//set back vars...
$szBackGetVars="";
foreach($_GET as $key => $value) {
    $szBackGetVars = $szBackGetVars . $key . "=" . $value . "&";
}
//strip trailing '&'
$szBackGetVars = substr($szBackGetVars, 0, -1);


?> 
<?php print_html5_primer();?>
    <TITLE><?php echo $page_title; ?></TITLE>
    
    

    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to delete:\n";
          var count = 0;
          form=document.studentlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].value + " (ID #" + form.elements[x].name + ")\n";
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

      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permissions"); return false;
      }
    </SCRIPT>
<?php print_bootstrap_head();?>
</HEAD>
    <BODY>
<?php 
print_general_navbar();
print_general_navbar();
print_lesser_jumbotron("Student Archive", $permission_level);
?>
<div class="container">
<?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>

<form name="studentlist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php echo IPP_PATH . "student_archive.php"; ?>" method="post">
<table class="table table-striped table-hover">
<?php
                        //print the next and prev links...
                        
                        if($iCur != 0) {
                            //we have previous values...
                            echo "<a href=\"./student_archive.php?iCur=" . ($iCur-$iLimit) . "&iLimit=$iLimit&szSearch=&szSearchVal=" . $_GET['szSearchVal'] . "&field=" . $_GET['field'] . "&SEARCH=" . $_GET['SEARCH'] . "\" class=\"default\">previous $iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }
                        
                        if(($iLimit+$iCur < $szTotalStudents)) {
                            echo "<a href=\"./student_archive.php?iCur=" . ($iCur+$iLimit) . "&iLimit=$iLimit&szSearchVal=" . $_GET['szSearchVal'] . "&field=" . $_GET['field'] . "&SEARCH=" . $_GET['SEARCH'] . "\">next ";
                            if( $sqlLogTotals-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($szTotalStudents-($iCur+$iLimit));
                            }
                        } else {
                            echo "&nbsp;";
                        }
                        //echo "</tr>\n";
                        //end print next and prev links

                        //print the header row...
                        echo "<tr><th>Select</th><th>UID</th><th>Last Name, First Name</th><th>School</th><th>Permission</th></tr>\n";
                        while ($student_row=mysql_fetch_array($sqlStudents)) {
                            $current_student_permission = getStudentPermission($student_row['student_id']);
                            echo "<tr>\n";
                            
                            echo "<td><input type=\"checkbox\" name=\"" . $student_row['student_id'] . "\" value=\"" . $student_row['first_name'] . " " . $student_row['last_name'] . "\"></td>";
                            echo "<td>" . $student_row['student_id'] . "<p></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "student_view.php?student_id=" . $student_row['student_id'] . "\"  ";
                            if($current_student_permission == "NONE" || $current_student_permission == "ERROR") {
                                echo "onClick=\"return noPermission();\" ";
                            }
                            echo ">" .  $student_row['last_name'] . "," . $student_row['first_name'] . "</a>";
                            if($current_student_permission == "READ" || $current_student_permission != "WRITE" || $current_student_permission != "ALL") {
                                echo "<a href=\"". IPP_PATH . "ipp_pdf.php?student_id=" . $student_row['student_id'] . "\" class=\"default\" target=\"_blank\"";
                                if($current_student_permission == "NONE" || $current_student_permission == "ERROR") {
                                echo "onClick=\"return noPermission();\" ";
                                }
                                echo ">&nbsp;<img src=\"". IPP_PATH . "images/pdf.png\" align=\"top\" border=\"0\"></a>";
                            }
                            echo "</td>\n";
                            echo "<td><p>-none-<p></td>\n";
                            echo "<td><p>$current_student_permission<p></td>\n";
                            echo "</tr>\n";
                            
                        }
                        if($permission_level <= $IPP_MIN_DELETE_STUDENT_PERMISSION)
                            echo "<tr><td colspan=\"5\" align=\"left\"><img src=\"" . IPP_PATH . "images/table_arrow.png\">&nbsp;With Selected: <INPUT TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" name=\"delete\" value=\"1\"></td></tr>\n";
                        
                        ?>
                        </table>
                        </form>
<footer><?php print_complete_footer(); ?></footer>                       
 </div>
 
<?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
