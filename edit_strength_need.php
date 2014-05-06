<?php
/** @file
 * @brief 	articulate student strengths and needs
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
 * 2. Charset should be UTF-8; check HTML and database and form
 * 3. Use correct escape string
 * 4. Priority UI Overhaul
 * @remark last edit 
 */ 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['uid']  or $_POST['uid']
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
require_once(IPP_PATH . 'include/supporting_functions.php');
//require_once(IPP_PATH . 'include/config.inc.php');


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
if(isset($_POST['uid'])) $uid=$_POST['uid'];
else $uid=$_GET['uid'];
//run the strength/need query first then validate...
//get the strengths/needs for this student...
$strength_query="SELECT * FROM area_of_strength_or_need WHERE uid=" . mysql_real_escape_string($uid);
$strength_result = mysql_query($strength_query);
if(!$strength_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$strength_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
   $strength_row = mysql_fetch_array($strength_result);
}

$student_id=$strength_row['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "This entry has generated a 'null' student id, fatal error- quitting";
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

//check if we are adding...
if(isset($_POST['edit_strength_or_need']) && $have_write_permission) {
   //minimal testing of input...
     if($_POST['strength_or_need'] == "") $system_message = $system_message . "You must choose either strength or need<BR>";
     if($_POST['is_valid'] != "Y" && $_POST['is_valid'] != "N") $system_message = $system_message . "Unknown 'ongoing' field value<BR>";
     else {
         $edit_query = "UPDATE area_of_strength_or_need SET strength_or_need='" . mysql_real_escape_string($_POST['strength_or_need']) . "',description='" . mysql_real_escape_string($_POST['description']) . "',is_valid='" . mysql_real_escape_string($_POST['is_valid']) . "' WHERE uid=" . mysql_real_escape_string($_POST['uid']) . " LIMIT 1";
         $edit_result = mysql_query($edit_query);
         if(!$edit_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$edit_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         } else {
           //redirect here...
           header("Location: " . IPP_PATH . "strength_need_view.php?student_id=" . $student_id);
         }
     }

   //$system_message = $system_message . $add_query . "<BR>";
}


?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
    <SCRIPT LANGUAGE="JavaScript">
      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
    <?php print_bootstrap_head(); ?>
</HEAD>
    <BODY>
   <?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']); ?>
   <?php print_jumbotron_with_page_name("Edit Strengths &amp; Needs", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission); ?>
   <div class="container">
   <?php if ($system_message) { echo $system_message;} ?>     
         
<!-- BEGIN edit strength/need -->
<form name="edit_strength_or_need" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_strength_need.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>

<h2>Edit and Click <em>Update</em></h2>
<input class="form-control" type="hidden" name="edit_strength_or_need" value="1">
<input class="form-control" type="hidden" name="uid" value="<?php echo $strength_row['uid'];?>">
<label>Strength or Need</label>
<div class="form-group">
<select class="form-control" name="strength_or_need" tabindex="1">
<option value="">-Choose-</option>
<option value="Strength" <?php if($strength_row['strength_or_need'] == 'Strength') echo "SELECTED"; ?>>Strength</option>
<option value="Need" <?php if($strength_row['strength_or_need'] == 'Need') echo "SELECTED"; ?>>Need</option>
</select>
</div>

<div class="form-group">                      
<label>Description</label>
<textarea class="form-control" spellcheck="true" name="description" tabindex="2" cols="30" rows="5" wrap="soft"><?php echo $strength_row['description'];?></textarea>
</div>
<div class="form-group">
<label>Ongoing</label>
<select class="form-control" name="is_valid" tabindex="3">
<option value="">-Choose-</option>
<option value="Y" <?php if($strength_row['is_valid'] == 'Y') echo "SELECTED"; ?>>Yes</option>
<option value="N" <?php if($strength_row['is_valid'] == 'N') echo "SELECTED"; ?>>No</option>
</select>
</div>
                   
<input type="submit" tabindex="4" name="Update" value="Update">

</form>
<!-- END add supervisor -->

                   
            
<?php print_complete_footer(); ?>
</div>
<?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
