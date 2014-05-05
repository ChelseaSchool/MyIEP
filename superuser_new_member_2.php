<?php

/** @file
 * @brief 	create a new user
 * @copyright 	2014 Chelsea School
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @remark		password encryption is mentioned as a field in a table, but no method for deriving an encrypted password is alluded to.
 * @todo
 * * password pattern
 * * password match indicator
 * * password advisor
 * * password alert
 */


//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0;



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message = "";
if(isset($szBackGetVars)) $szBackGetVars = $szBackGetVars; else $szBackGetVars="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/mail_functions.php');
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
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL && !(isLocalAdministrator($_SESSION['egps_username']))) {
	$system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
	IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
	require(IPP_PATH . 'security_error.php');
	exit();
}

//************** validated past here SESSION ACTIVE****************

//set the get/put variables for the back button and exit fx...
$szBackGetVars = "";
if(isset($_GET['szBackGetVars'])) $szBackGetVars = $_GET['szBackGetVars'];
if(isset($_POST['szBackGetVars'])) $szBackGetVars = $_POST['szBackGetVars'];

if(isset($_GET['add_user']) && !$_GET['add_username']) {
	$system_message = $system_message . "You must choose a username to add<BR>";
}

if(isset($_GET['add_username'])) {
	if(!connectIPPDB()) {
		$error_message = $error_message;  //just to remember we need this
		$system_message = $error_message;
		IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
	}

	//check to make sure passwords match.
	$regexp='/^[a-zA-Z0-9]*$/';
	if(mysql_real_escape_string($_GET['pwd1']) != mysql_real_escape_string($_GET['pwd2'])) {
		$system_message .= "Passwords do not match<BR>";
	} elseif (!preg_match($regexp, $_GET['add_username'])) {
		$system_message .= "Username must be a combination letters and numbers only (no spaces or punctuation)<BR>";
	} else {
		//do a quick check for existing username to prevent an ugly error msg...
		$duplicate_query = "SELECT * FROM support_member WHERE egps_username='" . $_GET['add_username'] . "'";
		$duplicate_result = mysql_query($duplicate_query);
		if(!$duplicate_result) {
			$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
			$system_message=$system_message . $error_message;
			IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
		}

		$pwd = str_replace("$mysql_user_append_to_login","",mysql_real_escape_string($_GET['pwd1']));

		if(mysql_num_rows($duplicate_result) > 0) {
			$system_message = $system_message . "User already exists in database<BR>";
		} else {
			if($permission_level != 0 && $_GET['permission_level'] <= 20) {
				$system_message = $system_message . "You can't add this level of permission<BR>";
			} else {
				if($permission_level != 0 && ($_GET['school_code'] != getUserSchoolCode($_SESSION['egps_username']))) {
					$system_message = $system_message . "You are limited to adding persons to your school<BR>"; // . $_GET['school_code'] ." != " . getUserSchoolCode($_SESSION['egps_username']) . "<BR>";
				} else {
					//we need to add this user...
					$update_query = "INSERT INTO support_member (egps_username,permission_level,first_name,last_name,email,school_code) VALUES ('" . mysql_real_escape_string($_GET['add_username']) . "'," . mysql_real_escape_string($_GET['permission_level']) . ",'" . mysql_real_escape_string($_GET['first_name']) . "','" . mysql_real_escape_string($_GET['last_name']) . "','" . mysql_real_escape_string($_GET['email']) . "'," . mysql_real_escape_string($_GET['school_code']) . ")";
					$update_result = mysql_query($update_query);
					if(!$update_result) {
						$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
						$system_message=$system_message . $error_message;
						IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
					} else {
						//we need to update or add into the users table...
						$update_users_query = "UPDATE users SET unencrypted_password='" . mysql_real_escape_string($pwd) . "', encrypted_password=PASSWORD('" . mysql_real_escape_string($pwd) . "') WHERE login_name=concat('" . mysql_real_escape_string($_GET['add_username']) ."','$mysql_user_append_to_login') LIMIT 1";
						$update_users_result = mysql_query($update_users_query);
						if(!$update_users_result) {
							$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_users_query'<BR>";
							$system_message=$system_message . $error_message;
							IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
						}  else {
							//if we don't have this user already in the users database then add them
							if(!mysql_affected_rows()) {
								$insert_users_query = "INSERT INTO users (login_name,encrypted_password,unencrypted_password,school_code,aliased_name) values (concat('" . mysql_real_escape_string($_GET['add_username']) ."','$mysql_user_append_to_login'),PASSWORD('" . mysql_real_escape_string($pwd) . "'),'" . mysql_real_escape_string($pwd) . "'," . mysql_real_escape_string($_GET['school_code']) . ",NULL)";
								$insert_users_result = mysql_query($insert_users_query);
								if(!$insert_users_result) {
									$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_users_query'<BR>";
									$system_message=$system_message . $error_message;
									IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
								}
							}
						}
						if(isset($_GET['mail_notification'])) {
							mail_notification(mysql_real_escape_string($_GET['add_username']),"This email has been sent to you to notify you that you have been added to the $IPP_ORGANIZATION online IPP system. You are able to access the system now by going to " . $IPP_PAGE_ROOT . ". Enter your username as '" .  $_GET['add_username'] . "'  and use the password '" . $pwd . "'");
						}
						require(IPP_PATH . "superuser_manage_users.php");
						exit();
					}
				}
			}
		}
	}
}

//connect to the user database to search for names..
//if(!connectUserDB()) {
//        $error_message = $error_message;  //just to remember we need this
//        $system_message = $error_message;
//        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
//}

//$egps_username_query="SELECT login_name FROM users WHERE  login_name LIKE '%" . $_GET['egps_username'] . "%' AND aliased_name IS NULL";
//$egps_username_result = mysql_query($egps_username_query);
//if(!$egps_username_result) {
//    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$egps_username_query'<BR>";
//    $system_message=$system_message . $error_message;
//    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
//}

//$iNumResults = mysql_num_rows($egps_username_result);
//if($iNumResults > 50) {
//    $system_message = "";
//    $system_message = "Your selection yielded $iNumResults names. Please try to refine your search";
//   require (IPP_PATH . "superuser_new_member.php");
//   exit();
//}

//if($iNumResults <= 0) {
//    $system_message = "";
//    $system_message = "Your selection yielded $iNumResults names.";
//    require (IPP_PATH . "superuser_new_member.php");
//    exit();
//}

//get the permission levels from db
if(!connectIPPDB()) {
	$error_message = $error_message;  //just to remember we need this
	$system_message = $error_message;
	IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$permission_query = "SELECT * FROM permission_levels WHERE 1=1 ORDER BY level DESC ";
$permission_result = mysql_query($permission_query);
if(!$permission_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$permission_query'<BR>";
	$system_message=$system_message . $error_message;
	IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if(!$school_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
	$system_message=$system_message . $error_message;
	IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

print_html5_primer();
?>

<script language="JavaScript" type="text/javascript">
    <!--
        function CheckNum() {
        // initialize the counter
        var Counter = 0;
        // Loop through the select box to see how many are selected;
        for (i=0; i<document.addName.add_username.length; i++){
            // If an element is selected, increment the counter
            if (document.addName.add_username[i].selected == true){
                Counter++;
            }
        }
        // If the counter is greater than 3, display an alert message.
        if (Counter > 1){
            alert("You can only select 1 username. Please unselect some and try again");
            return false;
        } else {
            // Just display a message, we don't really want the form to submit
            // for testing.  In the case of production, the else condition
            // would be removed
            //alert("Form passed validation!");
            return true;
        }
}
//-->
</script>
<?php print_bootstrap_head(); ?>
<head></head>
<body>
	<?php
	print_general_navbar();
	print_lesser_jumbotron("Create New Account", $permission_level);
	?>
	<div class="container">
		<?php if ($system_message) 
		{
			echo "<p>" . $system_message . "</p>";
		}
		?>
		<h2>Enter Account Details</h2>
		<form name="addName" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_new_member_2.php"; ?>"	method="get" onsubmit="return CheckNum()">
			<div class="row">
				<!-- Start Left Column -->
				<div class="col-md-6">
					<div class="form-group">
						<input type="hidden" name="add_user" value="1"> <label>Login
							Username</label> <input type="text" required class="form-control"
							name="add_username" value="" tabindex="1">
						<?php echo $mysql_user_append_to_login; ?>
						<label>First Name</label> <input required class="form-control"
							type="text" name="first_name" value="" tabindex="2"> <label>Last
							Name</label> <input class="form-control" required type="text"
							name="last_name" value="" tabindex="3"> <label>Email Address</label>
						<input class="form-control" required type="EMAIL" name="email"
							tabindex="6">
					</div>
				</div>
				<div class="col-md-6 form-group">
					<label>Password</label>
					<input class="form-control" required type="password" name="pwd1" pattern="(?=^.{6,30}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[!@#$%^&*])(?=.*[A-Z])(?=.*[a-z]).*$" tabindex="4"> 
					<label>Confirm Password</label>
					<input class="form-control" required type="password" name="pwd2" pattern="(?=^.{6,30}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[!@#$%^&*])(?=.*[A-Z])(?=.*[a-z]).*$"  tabindex="5">
					<label>Permission Level</label>
					<?php
					echo "<SELECT class=\"form-control\" tabindex=\"7\" name=\"permission_level\">\n";
					while($pval = mysql_fetch_array($permission_result)) {
                                 if($permission_level == 0 || $pval['level'] > 20) //only allow school based to add up to principal.
                                  echo "\t<OPTION value=" . $pval['level'] . ">" . $pval['level_name'] . "</OPTION>\n";
                              }
                              echo "</SELECT>\n"
 		?>

					<label>School</label> <select class="form-control" tabindex="8"
						name="school_code"
						<?php //if($permission_level != 0) echo "disabled"; ?>>
						<?php
						if($permission_level==0) {
                                  while($school_row=mysql_fetch_array($school_result)) {
                                      if(isset($_GET['school_code']) && $_GET['school_code'] == $school_row['school_code']) {
                                          echo "<option value=\"" . $school_row['school_code'] . "\" selected>" .  $school_row['school_name'] . "\n";
                                      } else {
                                          echo "<option value=\"" . $school_row['school_code'] . "\">" .  $school_row['school_name'] . "\n";
                                      }
                                  }
                               } else {
                                   //get our school code
                                   $our_code=getUserSchoolCode($_SESSION['egps_username']);
                                   while($school_row=mysql_fetch_array($school_result)) {
                                      if($school_row['school_code'] == $our_code) {
                                          echo "<option value=\"" . $school_row['school_code'] . "\" selected>" .  $school_row['school_name'] . "\n";
                                      } else {
                                          echo "<option value=\"" . $school_row['school_code'] . "\">" .  $school_row['school_name'] . "\n";
                                      }
                                  }
                               }
                               ?>
					</select> <label>Send email notification</label> <input
						tabindex="9" type="checkbox"
						<?php if(!isset($_GET['add_username']) || (isset($_GET['add_username']) && isset($_GET['mail_notification']))) echo "checked"; ?>
						name="mail_notification"> <input type="hidden"
						name="szBackGetVars" value="&lt;?php echo $szBackGetVars; ?&gt;"> <input
						type="hidden" name="egps_username"
						value="&lt;?php echo $_GET['egps_username'] ?&gt;">
					<p>
						<button class="btn btn-default" type="submit" value="Add"
							tabindex="10">Create Account</button>
					</p>

				</div>
			</div>
		</form>



	</div>
	<!-- close container -->
</body>
<html></html>
