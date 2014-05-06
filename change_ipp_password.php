<?php

/** @file
 * @brief 	user password reset
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo
 * * Filter input
 * * bootstrap ui overhaul
 * * hash
 * * add password strength guage
 * * add valid password info
 * * add valid password pattern
 * * add valid password match indicator (and mismatch indicator)
 * @remark		Password encryption is mentioned on 120 or so, but encryption method is unclear. See also superuser_new_member_2.php
 */
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;  //don't rely on this on this page.



/**
 * Path for IPP required files.
 */

$system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once 'include/supporting_functions.php';
//require_once 'include/page_troubleshoot.php';

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
if(isset($ippuserid)) $ippuserid=$ippuserid; else $ippuserid="";
if(($_SESSION['egps_username'] != $ippuserid ) && (getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL && !(isLocalAdministrator($_SESSION['egps_username'])))) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$permission_level = getPermissionLevel($_SESSION['egps_username']);

if(isset($_GET['username'])) $ippuserid=mysql_real_escape_string($_GET['username']);
   else if(isset($_POST['username'])) $ippuserid=mysql_real_escape_string($_POST['username']);
   else $ippuserid = $_SESSION['egps_username'];

//we want to run a check to make sure that if we are a local admin that
//we can't access a person not at our school...
if(($_SESSION['egps_username'] != $ippuserid ) && (isLocalAdministrator($_SESSION['egps_username']) && getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL)) {
  //we are a local administrator with no other access rights (ie we're a local admin but not a principal as well)
  $user_query= "SELECT * FROM support_member WHERE egps_username='$ippuserid'";
  $user_result = mysql_query($user_query);
  if(!$user_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$user_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    if(mysql_num_rows($user_result) <= 0) "IPP Member not found<BR>Query=$user_query";
    $user_row=mysql_fetch_array($user_result);
  }

  $us_query= "SELECT * FROM support_member WHERE egps_username='" . $_SESSION['egps_username'] . "'";
  $us_result = mysql_query($us_query);
  if(!$us_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$us_query'<BR>";
    $system_message= $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else {
    if(mysql_num_rows($us_result) <= 0) $system_message .= "IPP Member not found<BR>Query=$us_query";
    $us_row=mysql_fetch_array($us_result);
  }

  if($user_row['school_code'] != $us_row['school_code']) {
     $system_message = $system_message . "You do not have permission to view this page. You must be in the same school as this person to edit their information. (" . $user_row['school_code'] . "!=" . $us_row['school_code'] . ")";
     IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     require(IPP_PATH . 'security_error.php');
     exit();
  }
} else {
  //we are either not a local admin, we are this user or we have a permission level above MIN.
  //so make sure we hmmm...
}

//************** validated past here SESSION ACTIVE****************


if(isset($_POST['Update'])) {
   //check for blanks passwords...
   if(mysql_real_escape_string($_POST['pwd1']) == mysql_real_escape_string($_POST['pwd2']) && mysql_real_escape_string($_POST['pwd2']) == "") {
       $system_message .= "Passwords cannot be blank, try again<BR>";
   } {
     if(mysql_real_escape_string($_POST['pwd1']) != mysql_real_escape_string($_POST['pwd2'])) {
       $system_message .= "Passwords do not match, try again<BR>";
     } else {
       $pwd = mysql_real_escape_string($_POST['pwd1']);
       $update_query = "UPDATE users SET unencrypted_password='" . mysql_real_escape_string($pwd) . "', encrypted_password=PASSWORD('" . mysql_real_escape_string($pwd) . "') WHERE login_name=concat('" . $ippuserid ."','$mysql_user_append_to_login') LIMIT 1";
          $update_result = mysql_query($update_query);
          //$system_message .= $update_query . "<BR>";
          if(!$update_result) {
              $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
              $system_message=$system_message . $error_message;
              IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
          } else {
             //success...
             if(($_SESSION['egps_username'] != $ippuserid )) {
                //header("Location: https://" . $_SERVER['HTTP_HOST']. dirname($_SERVER['PHP_SELF']). "/" . IPP_PATH . "main.php" );
                header("Location: " . IPP_PATH . "main.php");
                exit();
             } else {
                //header("Location: https://" . $_SERVER['HTTP_HOST']. dirname($_SERVER['PHP_SELF']). "/" . IPP_PATH );
        header("Location: " . IPP_PATH);
                exit();
             }
          }
     }
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
<!DOCTYPE HTML>
<HTML lang=en>
<?php print_html5_primer(); ?>
    <TITLE><?php echo $page_title; ?></TITLE>
    <?php print_bootstrap_head(); ?>
<script src="js/caps_lock.js" type="text/javascript"></script>    
<script>

$(document).ready(function() {

	$('#pwd1').keypress(function(e) {
		  var $password = $(this),
		      tooltipVisible = $('.tooltip').is(':visible'),
		      s = String.fromCharCode(e.which);
		 
		  //Check if capslock is on. No easy way to test for this
		  //Tests if letter is upper case and the shift key is NOT pressed.
		  if ( s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey ) {
		    if (!tooltipVisible)
		        $password.tooltip('show');
		  } else {
		    if (tooltipVisible)
		        $password.tooltip('hide');
		  }
		 
		  //Hide the tooltip when moving away from the password field
		  $password.blur(function(e) {
		    $password.tooltip('hide');
		  });
		});
</script>
</HEAD>
    <BODY>
<header>
<?php 
print_general_navbar();
print_lesser_jumbotron("Change Password", $permission_level);
?>
</header>
<div class = "container">
<?php 
if ($system_message) 
{ 
	echo $system_message;
} 
?>
<h2>Enter &amp; Confirm New Password <small>and click Update</small></h2>




<div class="alert alert-block alert-info"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Notice</strong>: For password support, look for the question mark icon beside the password field.</div>




<form enctype="multipart/form-data" action="<?php echo IPP_PATH . "change_ipp_password.php"; ?>" method="post">
<input type="hidden" name="username" value="<?php echo $user_row['egps_username']; ?>">
                        

<div class="form-group">                       
<label>Username</label>
<input type="text" class="form-control" value="<?php echo $user_row['egps_username']; ?>" disabled name="userid">

         


<label>Password</label>
<div class="input-group">


<input type="password" class="form-control" data-toggle="tooltip" data-placement="top" data-title="Caps lock is on" name="pwd1" size="30" maxsize="30" tabindex="1" required pattern="(?=^.{6,30}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[!@#$%^&*])(?=.*[A-Z])(?=.*[a-z]).*$" placeholder="Please enter a complex password"><span class="input-group-addon" data-toggle="modal" data-target="#pw_support">?</span>
</div>
                        
<label>Password (retype)</label>
<input type="password" required class="form-control" data-toggle="tooltip" data-placement="top" data-title="Caps lock is on" name="pwd2" size="30" maxsize="30" tabindex="2" required pattern="(?=^.{6,30}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[!@#$%^&*])(?=.*[A-Z])(?=.*[a-z]).*$" placeholder="Please confirm password">
</div>                      
                      
<input type="hidden" required name="szBackGetVars" value="<?php echo $szBackGetVars; ?>">

<button class="btn btn-default btn-large" type="submit" name="Update" value="Update" tabindex="3">Update</button>
</form>
                   
</div>
                        
<!-- Modal--> 
<div class="modal fade" id="pw_support" tabindex="-1" role="dialog" aria-labelledby="options" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4>Password Support</h4>
      </div><!-- Modal Header end -->
      <div class="modal-body"><p>Please choose a strong password. Passwords for MyIEP must include:</p>
      <ul><li>At least one capital letter;</li>
<li>At least one lower-case letter;</li>
<li>At least one numeral;</li>
<li>At least one special character (keyboard symbol);</li>
<li>At least 6 characters (max 30 characters).</li>
</ul> 
<h5>Password Resources</h5>
<ul>
<li><a target="_new" href="https://www.microsoft.com/en-gb/security/pc-security/password-checker.aspx">Password Check</a> <small>A Microsoft site to help evaluate passwords</small></li>
<li><a target="_new" href="http://blog.kaspersky.com/password-check/">Password Evaluator</a> <small>Very helpful password evaluation tool from Kaspersky</small></li>
<li>Click <a target="_new" href="https://infamia.com/hints/pwgen.php?length=10&quiet">here</a> to generate a random, secure password.</li>
</ul>

<hr>
<h5>Proposed, Random, and Complex Password(s)</h5>
<ul>
<li>System Generated: <strong><?php generate_password();?></strong></li>

<li>Externally Harvested: <strong><?php random_password(8);?></strong></li> 

</ul>	
	  </div><!-- end modal body -->
      <div class="modal-footer">
          </div><!-- end modal footer -->
    </div><!-- end modal content -->
  </div>
  <!-- end modal dialog -->
</div>
<!-- end modal fade -->    
            
       
<footer><?php print_complete_footer(); ?></footer>        

<?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
