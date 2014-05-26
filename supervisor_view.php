<?php

/** @file
 * @brief 	 show supervisors, change supervisors, view history
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
 * 1. Filter input
 * 2. Real escape string
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['student_id']
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
require_once 'include/supporting_functions.php';


header('Pragma: no-cache'); //don't cache this page!

//Bounces to login page if auth isn't valid; logs error
/** @todo
 *  * Change validation and authentication check to flexible function
 */
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

//if no value is passed for studentid, log an error
if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "You've entered this page without supplying a valid student id. Fatal, quitting";
   exit();
}

/** @todo
 *  Make the permission level check a function. It should flexible enough to be called from all pages with different parameters.
*/
//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}
//more permission levels to be determined
/** @todo
 * 
 * Make permission level check a function that can be used on all pages.
 * 
 */
$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************
//get student by studentid; notify if error
$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

//check if we are adding...
if(isset($_GET['add']) && $have_write_permission && $_GET['supervisor'] != "SELECT") {

   //check for duplicate...
   $check_query = "SELECT * FROM supervisor WHERE egps_username='" . mysql_real_escape_string($_GET['supervisor']) . "' AND end_date IS NULL AND student_id=" . mysql_real_escape_string($student_id);
   $check_result = mysql_query($check_query);
   if(!$check_result) {
      $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$check_query'<BR>";
      $system_message=$system_message . $error_message;
      IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   } else {
       if(mysql_num_rows($check_result) > 0) {
           $check_row = mysql_fetch_array($check_result);
           $system_message = $system_message . "'" . $check_row['egps_username'] . "' is already a supervisor<BR>";
       } else {
           $add_query = "INSERT INTO supervisor (egps_username,student_id,position,start_date,end_date) VALUES ('" . mysql_real_escape_string($_GET['supervisor']) . "'," . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_GET['position']) . "',NOW(),NULL)";
           $add_result = mysql_query($add_query);
           if(!$add_result) {
              $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
              $system_message=$system_message . $error_message;
              IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           }
       }
    }
   //$system_message = $system_message . $add_query . "<BR>";
}

//if deleting supervisor, check permissions 
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_SUPERVISOR_PERMISSION && $have_write_permission ) {
    $delete_query = "DELETE FROM supervisor WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "uid=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    //$system_message = $system_message . $delete_query . "<BR>";
}

//Demoting a supervisor
if(isset($_GET['set_not_supervisor_x']) && $have_write_permission ) {
    $modify_query = "UPDATE supervisor SET end_date=NOW() WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $modify_query = $modify_query . "uid=" . $key . " OR ";
    }
    //strip trailing 'or' and whitespace
    $modify_query = substr($modify_query, 0, -4);
    //$system_message = $system_message . $modify_query . "<BR>";
    $modify_result = mysql_query($modify_query);
    if(!$modify_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$modify_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}

//check this student's existing supervisor...
$supervisor_query = "SELECT * FROM supervisor WHERE student_id =" . mysql_real_escape_string($student_id) . " AND end_date IS NULL ORDER BY start_date DESC";
$supervisor_result = mysql_query ($supervisor_query);
if(!$supervisor_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get a history...
$supervisor_history_query = "SELECT * FROM supervisor WHERE student_id=" . mysql_real_escape_string($student_id) . " AND end_date IS NOT NULL ORDER BY end_date DESC";
$supervisor_history_result = mysql_query ($supervisor_history_query);
if(!$supervisor_history_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_history_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get a list of all support members to build supervisor list...
$support_member_query = "SELECT * FROM support_list WHERE student_id=" . mysql_real_escape_string($student_id);
$support_member_result = mysql_query($support_member_query);
if(!$support_member_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$support_member_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

print_html5_primer();
print_bootstrap_head();
?> 

    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "delete_supervisor=";
          var szConfirmMessage = "Are you sure you want to modify supervisors:\n";
          var count = 0;
          form=document.supervisorhistorylist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + "\n";
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
<?php 
print_student_navbar($student_row['first_name'] . " " . $student_row['last_name']);
print_jumbotron_with_page_name("Student Case Manager", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);
?>
<div class="container">
<?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>


<!-- BEGIN add supervisor -->
<h2>Modify Case Manager <small>Edit and click 'Submit'</small></h2>
<form name="addsupervisor" enctype="multipart/form-data" action="<?php echo IPP_PATH . "supervisor_view.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
<div class="form-group">                   
<input type="hidden" name="modify_supervisor" value="1">
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<label for supervisor>Supervisor <small>(must be present in <?php echo $student_row['first_name'] . " " . $student_row['last_name'] ?>'s <a href="<?php echo IPP_PATH; ?>/modify_ipp_permission.php?student_id=<?php echo $student_id; ?>">support member</a> list)</small></label>
<select class="form-control">
<?php
while ($support_member_row = mysql_fetch_array($support_member_result)) {
    echo "<option>" . $support_member_row['egps_username'] . "</option>\n";
}
?>
</select>


                        
<label for position>Position</label>
<input class="form-control" value="advisor" autocomplete="off" spellcheck="true" placeholder="Position of Case Manager" required type="text" name="position" value="" size="30">
                      

</div>                        
<button class="btn button-lg button-default" type="submit" name="add" value="add">Submit</button>
</form>                       
<!-- END add supervisor -->

<h2>Case Manager History</h2>
<!-- BEGIN manager history table -->
<form name="supervisorhistorylist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "supervisor_view.php"; ?>" method="get">
<input type="hidden" name="student_id" value="<?php echo $student_id ?>">
<table class="table table-striped table-hover">
<?php
//print the header row...
echo "<tr><th>Select</th><th>UID</th><th>Username</th><th>Position</th><th>Start Date</th><th>End Date</th></tr>\n";
while ($supervisor_row=mysql_fetch_array($supervisor_result)) { //current...
    echo "<tr>\n";
    echo "<td><input type=\"checkbox\" name=\"" . $supervisor_row['uid'] . "\"></td>";
    echo "<td>" . $supervisor_row['uid'] . "</td>";
    echo "<td>" . $supervisor_row['egps_username']  ."</td>\n";
    echo "<td>" . $supervisor_row['position'] . "</td>\n";
    echo "<td>" . $supervisor_row['start_date'] . "</td>\n";
    echo "<td>-Current-</td>\n";
    echo "</tr>\n";
                            
}
while ($supervisor_row=mysql_fetch_array($supervisor_history_result)) { //previous...
    echo "<tr>\n";
    echo "<td><input type=\"checkbox\" name=\"" . $supervisor_row['uid'] . "\"></td>";
    echo "<td>" . $supervisor_row['uid'] . "</td>";
    echo "<td>" . $supervisor_row['egps_username'] ."</td>\n";
    echo "<td>" . $supervisor_row['position'] . "</td>\n";
    echo "<td>" . $supervisor_row['start_date'] . "</td>\n";
    echo "<td>" . $supervisor_row['end_date'] . "</td>\n";
    echo "</tr>\n";
}
?>
</table>                        
 <table>
<tr>
<td nowrap>
<img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
</td>
<td>
<?php
if($have_write_permission) {
    echo "<INPUT NAME=\"set_not_supervisor\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Not Supervisor\" border=\"0\" value=\"set_not_supervisor\">";
}
//if we have permissions also allow delete and set all.
if($permission_level <= $IPP_MIN_DELETE_SUPERVISOR_PERMISSION && $have_write_permission) {
    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"delete\">";
}
?>
</td>
</tr>
</table>
</form>
<!-- end IEP history table -->

</div>
        <?php print_bootstrap_js();?>
    </BODY>
</HTML>
