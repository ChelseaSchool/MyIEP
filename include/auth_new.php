<?php
/** @file
 *  @brief (in progress) authorization model for reliance on hashing algorithm
 *  @detail
 *  * Should take legacy passwords, generate hash. Check hash against what's stored in the database; if no hash in database, verify hash and update database. During update, it should remove the legacy password from database.
 *  * should generate secure session array
 *  * should check authorization levels
 *  @author Rik Goldman
 *  @copyright Chelsea School
 *  @todo
 *  * test setting session without sensitive password information
 */



/** @brief Define root path of MyIEP if not defined
*/
if (!defined('IPP_PATH')) define('IPP_PATH', '../');

/** @brie not sure of this legacy conditional
 *  @todo 
 *  1. cross check against include/init.php
 */
if (!isset($is_local_student)) $is_local_student=FALSE;

/** @brief required includes for authentication
 *  @remark recommendation of pass_compat is to use require to include password.php
 */

require_once('include/db.php');
require_once ('etc/init.php');
require ('include/password.php');
if (!isset($_POST['PASSWORD'])){
	exit();
}
if (!isset($_POST['LOGIN_NAME'])){
	exit();
}
$szPassword = $_POST['PASSWORD'];
$szLogin = $_POST['LOGIN_NAME'];


/** @fn register($szlogin, $szPassword)
 * @param $szLogin
 * @param $szPassword
 * @return TRUE if no errors
 */
function register($szLogin, $szPassword) {
	global $error_message, $mysql_user_table, $IPP_TIMEOUT;
	//cleared variable to secure from outside data
    $error_message = "";
    //generate and validate hash
    $untested_hash = password_hash($szPassword, PASSWORD_BCRYPT);
    $hash = password_verify($szPassword, $untested_hash);
    //check db connection and throw error if not
    if (!connectUserDB()) {
    	$error_message = $error_message; //why doesn't this get a value?
    	return FALSE;
    }
    

    
    //query db for matching user info combination
  
    
    $query = "SELECT * FROM `users` WHERE `login_name` = \"$szLogin\" AND `stored_hash` = \"$hash\" AND aliased_name IS NULL";
    $result = mysql_query($query);
    
    //check result, throw error if no match
    if (!$result) {
    	$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<br>Query= '$query' <br>";
    	return FALSE;
    }
    
    //set session info
    //removed pw from _Session array, replaced with hash
    $_SESSION = array();
    $_SESSION['uid'] = $szLogin;
    $_SESSION['hash'] = $hash;
    $_SESSION['IPP_double_login'] = TRUE;
    
    if (!connectIPPDB()) {
    	$error_message = $error_message; //We're reminded this is needed by the legacy developer
    	return FALSE;
    }
    
    //setup logged_in table...
    $query = "INSERT INTO logged_in (ipp_username,session_id,last_ip,time) VALUES ('$szLogin','" . session_id(). "','" . $_SERVER['REMOTE_ADDR'] . "', (NOW()+ INTERVAL " . $IPP_TIMEOUT . " MINUTE))";
    $result = mysql_query($query);
    
    if(!$result) {
    	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
    	return FALSE;
    }
    
    //record last ip and last active time into support_member table.
    $query = "UPDATE support_member SET last_ip= \"{$_SERVER['REMOTE_ADDR']}\" , last_active=now() WHERE egps_username=\"{$szLogin}\"";
    $result = mysql_query($query);
    if(!$result) {
    	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
    	return FALSE;
    }
    
    //***following code drops logged in users past time***
    //for system cleanup.
    $query = "DELETE from logged_in WHERE (time - NOW()) < 0;";
    $result = mysql_query($query);
    if(!$result) {
    	$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
    	return FALSE;
    }
    
    //success so return TRUE
    return TRUE;
} // End of Validate Function

/** @fn logout()
 * @brief deletes a logged in user from logged in table in MySQL; or returns error.
 * @return boolean
 */
function logout() {
    	if(!session_id()) session_start();
    	unset($_SESSION['uid']);
    	unset($_SESSION['hash']);
    	//unset($_SESSION['password']); /can we avoid using this in the _SESSION array?
    	unset($_SESSION['IPP_double_login']);
    	$_SESSION = array(); // Destroy the variables.
    	session_destroy();
    	if(!connectIPPDB()) {
    		$error_message = $error_message; //just to remember we need this
    		return FALSE;
    	}
    	$query = "DELETE FROM logged_in WHERE session_id='" . session_id() . "'";
    	$result = mysql_query($query);
    	if(!$result) {
    		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
    		return FALSE;
    	}
}

function validate($szLogin,$szPassword) {
	//check username and password against user database
	//returns TRUE if successful or FALSE on fail.
	//if FALSE returns $error_message
	//session_start must be called prior to this function.
	global $error_message, $mysql_user_select_login, $mysql_user_select_password, $mysql_user_table, $mysql_user_append_to_login,$IPP_TIMEOUT;

	$error_message = "";
	//generate and validate hash
	//generate and validate hash
	$untested_hash = password_hash($szPassword, PASSWORD_BCRYPT);
	$hash = password_verify($szPassword, $untested_hash);
	
	//start session...
	//session_cache_limiter('private'); //IE6 sucks
	session_cache_limiter('nocache');
	if(session_id() == "") session_start();

	//we must double login for IPP
	//if(!isset($_SESSION['IPP_double_login'])) return FALSE;

	//check if we already have registered session info
	//for login and passwd.
	if(!isset($_SESSION['IPP_double_login'])) {
		if(!register($szLogin,$hash)) {
			$error_message = $error_message;
			return FALSE;
		}
	}

	//connect DB:

	if(!connectIPPDB()) {
		$error_message = $error_message; //just to remember we need this
		return FALSE;
	}

	//check session logged in...
	$query = "SELECT * FROM `logged_in` WHERE `ipp_username` = \"{$szLogin}\" AND `last_ip` = \"{$_SERVER['REMOTE_ADDR']}\" AND (`time` - NOW()) > 0";
	$result = mysql_query($query);
	if(!$result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
		return FALSE;
	}

	if(mysql_num_rows($result) <= 0 ) {
		$error_message = "Session has expired<BR>";
		logout();
		return FALSE;
	}

	//check if we have a valid login/password combination.

	if(!connectUserDB()) {
		$error_message = $error_message; //just to remember we need this
		return FALSE;
	}

	$query = "SELECT * FROM users WHERE `login_name` = \"{$szLogin}\" AND `aliased_name` IS NULL";
	$result = mysql_query($query);
	if(!$result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
		return FALSE;
	}

	//check if we got no result (no user with that password)
	if(mysql_num_rows($result) <= 0 ) {
		$error_message = "Login failed: Unknown username and password<BR>";
		return FALSE;
	}

	if(!connectIPPDB()) {
		$error_message = $error_message; //just to remember we need this
		return FALSE;
	}

	//update the timeout.
	$session_id = session_id();
	$query = "UPDATE `logged_in` SET TIME = (NOW()+ INTERVAL \"{$IPP_TIMEOUT}\" MINUTE) where `session_id` = \"{$_SESSION['session_id']}\" ";
	$result = mysql_query($query);
	if(!$result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
		return FALSE;
	}

	//******* authorized from now on! *******
	return TRUE;

}

/** @fn getPermissionLevel($szUsername='')
 * @brief determines what resources the logged in user has access to.
 * @param string $szUsername
 * @return NULL or row from database
 */
function getPermissionLevel($szUsername='') {
	//returns permission level or NULL on fail.
	global $error_message;

	$error_message = "";
	if(!connectIPPDB()) {
		$error_message = $error_message; //just to remember we need this
		return NULL;
	}
	//select the lowest username (just in case)...
	$query = "SELECT permission_level FROM support_member WHERE egps_username='$szUsername' ORDER BY permission_level ASC";
	$result = mysql_query($query);
	if(!$result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
		return NULL;
	}
	if(mysql_num_rows($result) <= 0) {
		$error_message = $error_message . "You are not an authorized support member(" . __FILE__ . ":" . __LINE__ . ")<BR>" ;
		return NULL;
	}
	$row=mysql_fetch_array($result);

	return $row[0];
}

// plugin authorization check...a leeetle bit of OO...
class service {

	var $SERVICENAME;
	var $LOCATION;

	//constructor;
	function service($SERVICENAME='',$LOCATION='') {
		$this->SERVICENAME = $SERVICENAME;
		$this->LOCATION = $LOCATION;
	}

	function getLocation() {
		return $this->LOCATION;
	}

	function getTitle() {
		return $this->SERVICENAME;
	}


}
/** @fn get_services($PERMISSION_LEVEL=100)
 * @brief sets permissions for lowest authentication level
 * @param $PERMISSION_LEVEL = 100
 * @todo untangle this mess. Refactor as necessary.
 * @bug
 *
 */
function get_services($PERMISSION_LEVEL=100) {

	global $error_message;

	$error_message = "";

	//Special case we are the school-based IPP administrator
	//get our school code
	$error_message = "";
	if(!connectIPPDB()) {
		$error_message = $error_message; //just to remember we need this
		return NULL;
	}

	$is_local_ipp_administrator = FALSE;
	$system_query = "SELECT * from support_member WHERE egps_username='" . $_SESSION['uid'] . "'";
	$system_result = mysql_query($system_query);
	if(!$system_result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$system_query'<BR>";
		return "ERROR";
	} else {
		$system_row=mysql_fetch_array($system_result);
		if($system_row['is_local_ipp_administrator'] == 'Y') $is_local_ipp_administrator=TRUE;
	}

	//returns array[service]
	unset($retval); //clear
	$retval=array(); //declare an empty array.
	if($PERMISSION_LEVEL==NULL) return NULL;

	//special case for 'manage ipp users'...
	if($PERMISSION_LEVEL == 0 || $is_local_ipp_administrator) {
		array_push($retval,new service("Manage Users",IPP_PATH . "superuser_manage_users.php"));
	}

	switch($PERMISSION_LEVEL) {
		case 0: // super administrator level
			//array_push($retval,new service("Manage IPP Users",IPP_PATH . "superuser_manage_users.php"));
			array_push($retval,new service("View Logs",IPP_PATH . "superuser_view_logs.php"));
			//$retval[0] = new service("Manage IPP Users",IPP_PATH . "superuser_manage_users.php");
			//$retval[1] = new service("Manage IPP Users",IPP_PATH . "superuser_manage_users.php");
			array_push($retval,new service("Schools",IPP_PATH . "school_info.php"));
			array_push($retval,new service("Manage Codes",IPP_PATH . "superuser_manage_coding.php"));
			array_push($retval,new service("User Audit",IPP_PATH . "user_audit.php"));
		case 10: //admin
		case 20: //asst.admin.
			//array_push($retval,new service("Program Areas",IPP_PATH . "superuser_add_program_area.php"));
			array_push($retval,new service("Goals Database",IPP_PATH . "superuser_add_goals.php"));
		case 30: //principal...
		case 40: //vice principal
		case 50: //Teaching Staff Level
		case 60: //Teaching Asst.
			array_push($retval,new service("Archives",IPP_PATH . "student_archive.php"));
			//array_push($retval,new service("Bugs",IPP_PATH . "bug_report.php"));
			array_push($retval,new service("Students",IPP_PATH . "manage_student.php"));
		case 100:
			array_push($retval,new service("Change Password",IPP_PATH . "change_ipp_password.php"));
			break;
		default:
			$error_message = $error_message . "You are not an authorized support member(" . __FILE__ . ":" . __LINE__ . ")<BR>";
			return NULL;
	}


	return $retval;
}

/** @fn getStudentPermission($student_id='')
 * @brief Determines user's access to specific student's records
 * @detail
 * 1. Returns error or null under some circumstances.
 * 2. Otherwise, may return NONE,ERROR,READ,WRITE(READ,WRITE),ASSIGN(READ,WRITE,ASSIGN),ALL(READ,WRITE,ASSIGN,DELETE), or support_list['permission'] or NONE for no permissions.
 * @param string $student_id
 * @return string|NULL|Ambigous
 * @todo
 * 1. Rename function because it is a confusing name
 * 2. It can start with get_. Separate words with underscores. Perhaps get_access_to_student_record().
 */
function getStudentPermission($student_id='') {
	//returns NONE,ERROR,READ,WRITE(READ,WRITE),ASSIGN(READ,WRITE,ASSIGN),ALL(READ,WRITE,ASSIGN,DELETE),
	//or support_list['permission'] or NONE for no permissions.
	global $error_message, $mysql_user_select_login, $mysql_user_select_password, $mysql_user_table, $mysql_user_append_to_login;

	$error_message = "";

	$permission_level = getPermissionLevel($_SESSION['uid']);
	if($permission_level == NULL) return "ERROR";

	//find the currently logged in persons school code...
	if(!connectUserDB()) {
		$error_message = $error_message; //just to remember we need this
		return "ERROR";
	}

	$query = "SELECT * FROM $mysql_user_table WHERE (" . $mysql_user_select_login . "='" . $_SESSION['uid'] . $mysql_user_append_to_login . "' or " . $mysql_user_select_login . "='" . $_SESSION['uid'] . "') and " . $mysql_user_select_password . "='" . $_SESSION['hash'] . "' AND aliased_name IS NULL";
	$result = mysql_query($query);
	if(!$result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
		return "ERROR";
	}
	$user_row=mysql_fetch_array($result);
	$school_code=$user_row['school_code'];

	if(!connectIPPDB()) {
		$error_message = $error_message; //just to remember we need this
		return "ERROR";
	}

	//check if this staff member is local to this student...
	$local_query="SELECT * FROM school_history WHERE student_id=$student_id AND school_code='$school_code' AND end_date IS NULL";
	$local_result=mysql_query($local_query); //ignore errors...
	$is_local_student=FALSE;
	if($local_result && mysql_num_rows($local_result) > 0) $is_local_student=TRUE;

	//Special case we are the school-based IPP administrator
	//get our school code
	$error_message = "";
	if(!connectIPPDB()) {
		$error_message = $error_message; //just to remember we need this
		return NULL;
	}
	$system_query = "SELECT * from support_member WHERE egps_username='" . $_SESSION['uid'] . "'";
	$system_result = mysql_query($system_query);
	if(!$system_result) {
		$error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$system_query'<BR>";
		return "ERROR";
	} else {
		$system_row=mysql_fetch_array($system_result);
		if($is_local_student && $system_row['is_local_ipp_administrator'] == 'Y') return "ASSIGN";
	}
	//base our permission on the level we're assigned.
	switch($permission_level) {
		case 0: //Super Admin
		case 10: //Administrator
			return "ALL";

		case 30: //Principal (assign local) special case
			//fall through and return ALL for local students.
		case 20: //Assistant Admin. (view all) special case
			//fall through and return at least read...
		case 40: //Vice Principal (view local)
		default:
			//we need to find the permissions from the support list
			//as this user has no inherent permissions...
			$support_query="SELECT * FROM support_list WHERE egps_username='" . $_SESSION['uid'] . "' AND student_id=$student_id";
			$support_result=mysql_query($support_query);
			//if(mysql_num_rows($support_result) <= 0) {
			switch($permission_level) {
				case 30:
				case 40: //changed as per s. chomistek (2006-03-23)
					if($is_local_student) return "ASSIGN";
					else return "NONE";
				case 20: //Asst admin special case of read for all
					if($is_local_student) return "ASSIGN";
					else return "READ";
					//case 40: //vp special case read local
					// if($is_local_student) return "READ";
					//else return "NONE";
				default:
					//return "NONE";
			}
			//} //else {
			$row=mysql_fetch_array($support_result);
			if($row['permission'] !='') return $row['permission'];
			return "NONE";
			//}
	}
}


