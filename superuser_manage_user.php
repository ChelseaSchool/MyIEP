<?php

/** @file
 * @brief 	user managment ?
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Investigate purpose of this page
 */  
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 20;



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message ="";
if(isset($szBackGetVars)) $szBackGetVars = $szBackGetVars; else $szBackGetVars= "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
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
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL && !(isLocalAdministrator($_SESSION['egps_username']))) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$permission_level = getPermissionLevel($_SESSION['egps_username']);

$ippuserid="";
if(isset($_GET['ippuserid'])) $ippuserid=mysql_real_escape_string($_GET['ippuserid']);
   else $ippuserid=mysql_real_escape_string($_POST['ippuserid']);

//we want to run a check to make sure that if we are a local admin that
//we can't access a person not at our school...
if(isLocalAdministrator($_SESSION['egps_username']) && getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
  //we are a local administrator with no other access rights (ie we're a local admin but not a principal as well)
  $user_query= "SELECT * FROM support_member WHERE egps_username='$ippuserid'";
  $user_result = mysql_query($user_query);
  $change_password_button = "<button href=\"" . IPP_PATH . "change_ipp_password.php?username=" . $user_row['egps_username'] . "\" class=\"btn btn-default btn-large\" role=\"button\">Change Password</button>";
  if(!$user_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$user_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    if(mysql_num_rows($user_result) <= 0) "IPP Member not found<BR>Query=$user_query";
    $user_row=mysql_fetch_array($user_result);
    $change_password_button = "";
  }

  $us_query= "SELECT * FROM support_member WHERE egps_username='" . $_SESSION['egps_username'] . "'";
  $us_result = mysql_query($us_query);
  $change_password_button = "<button href=\"" . IPP_PATH . "change_ipp_password.php?username=" . $user_row['egps_username'] . "\" class=\"btn btn-default btn-large\" role=\"button\">Change Password</button>";
  if(!$us_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$us_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    if(mysql_num_rows($us_result) <= 0) $system_message .= "IPP Member not found<BR>Query=$us_query";
    $us_row=mysql_fetch_array($us_result);
    $change_password_button = "";
  }

  if($user_row['school_code'] != $us_row['school_code']) {
     $system_message = $system_message . "You do not have permission to view this page. You must be in the same school as this person to edit their information. (" . $user_row['school_code'] . "!=" . $us_row['school_code'] . ")";
     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     require(IPP_PATH . 'security_error.php');
     exit();
  }
}

//************** validated past here SESSION ACTIVE****************


if(isset($_POST['Update'])) {
   //we are updating this users information...
   $update_query = "UPDATE support_member SET egps_username='$ippuserid',";  //do this so we start with a comma.
   $update_query .= "first_name='" . mysql_real_escape_string($_POST['first_name']) . "',";
   $update_query .= "last_name='" . mysql_real_escape_string($_POST['last_name']) . "',";
   $update_query .= "email='" . mysql_real_escape_string($_POST['email']) . "',";
   if($permission_level <= 20 || (isLocalAdministrator($_SESSION['egps_username']))) {
      if(($_POST['permission_level'] > 20 && (isLocalAdministrator($_SESSION['egps_username']))) || $permission_level==0) {
         $update_query .= " permission_level=" . mysql_real_escape_string($_POST['permission_level']) . ",";
      } else {
         $system_message .= "You do not have permission to make this modification to this IPP members permission level<BR>";
      }
      if($permission_level==0) {
        $update_query .= " school_code=" . mysql_real_escape_string($_POST['school_code']) . ",";
        $update_query .= " is_local_ipp_administrator='";
        if(isset($_POST['is_local_ipp_administrator'])) $update_query .= "Y";
        else $update_query .= "N";
        $update_query .= "',";
      }
      //strip off trailing ','...
      $update_query = substr($update_query, 0, -1);

      $update_query .= " WHERE egps_username='$ippuserid'";
      if($permission_level != 0) $update_query .= " AND permission_level > 20";
      $update_query .= " LIMIT 1";
      //$system_message .= $update_query . "<BR>";
      $update_result = mysql_query($update_query);
      if(!$update_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
      } else {
         //redirect back to the staff list...
      }
   } else {
      $system_message .= "You don't have the permission level necessary to do this<BR>";
   }

   //$system_message .= "-->" . $_POST['is_local_ipp_administrator'] . "<--";
}

$user_query= "SELECT * FROM support_member WHERE egps_username='$ippuserid'";
$user_result = mysql_query($user_query);

if(!$user_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$user_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
  if(mysql_num_rows($user_result) <= 0) $system_message .= "IPP Member not found<BR>";
  $user_row=mysql_fetch_array($user_result);
  $change_password_button = "";
}

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$permission_query = "SELECT * FROM permission_levels WHERE 1=1 ORDER BY level DESC ";
$permission_result = mysql_query($permission_query);
if(!$permission_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$permission_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

?> 
<?php 
print_html5_primer();
print_bootstrap_head()
?>    
<BODY>
<?php 
print_general_navbar();
print_lesser_jumbotron("Manage User", $permission_level);
?>
<div class="container">
<?php 
if ($system_message) {
	echo "<p>" . $system_message . "</p>";
} 

?>
<h2>Edit User <small>and click "update" or, alternatively, "change password."</small></h2>
                        
<form enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_user.php"; ?>" method="post">
<div class="form-group">
<input type="hidden" name="ippuserid" value="<?php echo $user_row['egps_username']; ?>">

<label>Username</label>
<input class="form-control" type="text" value="<?php echo $user_row['egps_username']; ?>" disabled name="userid" length="30">

<label>First Name</label>
<input class="form-control" type="text" name="first_name" value="<?php echo $user_row['first_name'];?>">
<label>Last Name</label>
<input class="form-control" type="text" name="last_name" value="<?php echo $user_row['last_name'];?>">
<label>Email</label>
<input class="form-control" type="email" name="email" value="<?php echo $user_row['email'];?>">
<label>School</label>
<SELECT class="form-control" name="school_code" <?php if($permission_level != 0) echo "disabled"; ?>>
                        <?php
                            while($school_row=mysql_fetch_array($school_result)) {
                                if($user_row['school_code'] == $school_row['school_code']) {
                                    echo "<option value=\"" . $school_row['school_code'] . "\" selected>" .  $school_row['school_name'] . "\n";
                                } else {
                                    echo "<option value=\"" . $school_row['school_code'] . "\">" .  $school_row['school_name'] . "\n";
                                }
                            }
                        ?>
</SELECT>
<label>Permission Level</label>
<?php
echo "<SELECT class=\"form-control\" name=\"permission_level\" style=\"width:200px;text-align: left;\">\n";
	while($pval = mysql_fetch_array($permission_result)) {
		if($permission_level == 0 || $pval['level'] > 20) //only allow school based to add up to principal.
			echo "\t<OPTION value=" . $pval['level'];
			if($user_row['permission_level'] == $pval['level']) echo " selected ";
				echo  ">" . $pval['level_name'] . "</OPTION>\n";
}
echo "</SELECT>\n"
?>
<label>Local Administrator</label>
<input type="checkbox" name="is_local_ipp_administrator" <?php if($user_row['is_local_ipp_administrator'] =='Y') echo "checked"; ?> <?php if($permission_level != 0) echo "disabled"; ?>>
<input type="hidden" name="szBackGetVars" value="<?php echo $szBackGetVars; ?>">

</div>
<div class="button-group">
<button class="btn btn-default btn-large" type="submit" name="Update" value="Update">Update</button>
<?php 
if (isset($user_row['egps_username'])){
	$change_password_button = "<button href=\"" . IPP_PATH . "change_ipp_password.php?username=" . $user_row['egps_username'] . "\" class=\"btn btn-default btn-large\" role=\"button\">Change Password</button>";
	echo $change_password_button;
}
?>

</div>
</form>

<footer><?php print_complete_footer();?></footer>  
<?php print_bootstrap_js();?>      
    </BODY>
</HTML>