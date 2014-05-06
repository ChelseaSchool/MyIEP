<?php
/** @file
 * @brief 	write or revise transition plan
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
 * 1. Filter input
 * 2. Priority UI overhaul
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['uid'],$_POST['uid']
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
//require_once(IPP_PATH . 'include/config.inc.php');
require_once(IPP_PATH . 'include/supporting_functions.php');


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

$uid="";
if(isset($_GET['uid'])) $uid= mysql_real_escape_string($_GET['uid']);
if(isset($_POST['uid'])) $uid = mysql_real_escape_string($_POST['uid']);

//get the coordination of services for this student...
$transition_row = "";
$transition_query="SELECT * FROM transition_plan WHERE uid=$uid";
$transition_result = mysql_query($transition_query);
if(!$transition_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$transition_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
  $transition_row=mysql_fetch_array($transition_result);
}

$student_id=$transition_row['student_id'];
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
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

//check if we are modifying a student...
if(isset($_POST['edit_transition_plan']) && $have_write_permission) {
  //check that date is the correct pattern...
  $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
 if($_POST['date'] == "" || $_POST['plan'] == "") { $system_message = $system_message . "You must supply both a date and a plan<BR>"; }
 else {
  if(!preg_match($regexp,$_POST['date'])) {
    //no way...
    $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>";
  } else {
    //we add the entry.
    $update_query = "UPDATE transition_plan SET date='" . mysql_real_escape_string($_POST['date']) . "',plan='" . mysql_real_escape_string($_POST['plan']) . "'";
    $update_query .= " WHERE uid=$uid LIMIT 1";
    $update_result = mysql_query($update_query);
     if(!update_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query' <BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        //redirect
        header("Location: " . IPP_PATH . "transition_plan.php?student_id=" . $student_id);
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
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.strengthneedslist;
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
    <?php print_bootstrap_head(); ?>
    <?php print_datepicker_depends(); ?>
</HEAD>
    <BODY>
    	<?php print_student_navbar($student_id, $student_row["first_name"] . " " . $student_row["last_name"]); ?>
    	<?php print_jumbotron_with_page_name("Edit Transition Plan", $student_row["first_name"] . " " . $student_row["last_name"], $our_permission); ?>
        <?php if ($system_message) { echo $system_message;} ?>
        <div class=container><div class=row><div class=col-md-6>
         <form name="add_transition_plan" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_transition_plan.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        
           
        <input type="hidden" name="edit_transition_plan" value="1">
        <input type="hidden" name="uid" value="<?php echo $uid; ?>">
                          
                      <p><label>Edit Plan</label></p>
                     <p><textarea spellcheck="true" name="plan" tabindex="1" cols="40" rows="8" wrap="soft"><?php echo $transition_row['plan']; ?></textarea></p>
                       </div>

                       <div class=col-md-6>
                           <p><label>Date (YYYY-MM-DD)<br>
                           [determines school year]</label></p>
                          
                         <p><input class="datepicker" id="datepicker" type="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd" tabindex="2" name="date" value="<?php echo $transition_row['date']; ?>"></p>
                         <p> <input type="submit" tabindex="3" name="edit" value="Submit"></p>
                        </form>
                        </div>
                        </div>
                        </div>
        
        
        
        <?php print_complete_footer(); ?>
        <?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
