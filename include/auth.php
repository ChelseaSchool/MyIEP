<?php
/** @file
 * @brief Authorization routine
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
 * 1. a lot of things to check here.
 * 2. line 31 needs a why? for our purposes
 * 3. check session use; ensure security and best contemporary practice
 * 4. check that variable names for user and pw don't match table fields in db (a cursory look suggests we're ok there)
 * 5. line 12 says "files" instead of functions
 * 6. Clean session effectively on logout function
 */



    if(!defined('IPP_PATH')) define('IPP_PATH','../');
    if(!isset($is_local_student)) $is_local_student=FALSE;

    require_once(IPP_PATH . 'include/db.php');
    require_once(IPP_PATH . 'etc/init.php');

 /** @fn register($szLogin='',$szPassword='')
  * @details
  * Starts session if login is successful. Throws errors and returns false if:
  * 1. connect to MySQL fails
  * 2. Get username fails
  * 3. MySQL returns zero rows
  * 
  * @param string $szLogin
  * @param string $szPassword
  * @return boolean
  */
    function register($szLogin='',$szPassword='') {
        global $mysql_user_append_to_login,$error_message, $mysql_user_select_login, $mysql_user_select_password, $mysql_user_table, $mysql_user_append_to_login,$IPP_TIMEOUT;

        $error_message = "";

         if(!connectUserDB()) {
             $error_message = $error_message;  //just to remember we need this
             return FALSE;
         }

         //strip off the $mysql_user_append_to_login
         $szLogin = str_replace($mysql_user_append_to_login,'',$szLogin);

         $query = "SELECT * FROM $mysql_user_table WHERE (" . $mysql_user_select_login . "='" . $szLogin . $mysql_user_append_to_login . "' or " . $mysql_user_select_login . "='" . $szLogin . "') and " . $mysql_user_select_password . "='" . $szPassword . "' AND aliased_name IS NULL";
         $result = mysql_query($query);
         if(!$result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
             return FALSE;
         }

         //check if we gotten no result (no user with that password)
         if(mysql_num_rows($result) <= 0 ) {
            $error_message = "Login failed: Unknown username and password<BR>";
            return FALSE;
         }

         //set session info..
         $_SESSION['egps_username'] = $szLogin;
         $_SESSION['password'] = $szPassword;
         $_SESSION['IPP_double_login'] = TRUE;

         if(!connectIPPDB()) {
             $error_message = $error_message;  //just to remember we need this
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
         $query = "UPDATE support_member SET last_ip='" . $_SERVER['REMOTE_ADDR'] . "', last_active=now() WHERE egps_username='$szLogin'";
         $result = mysql_query($query);
         if(!$result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
            return FALSE;
         }

         //***following code drops logged in users past time***
         //for system cleanup.
         $query = "DELETE from logged_in WHERE (time - NOW()) < 0";
         $result = mysql_query($query);
         if(!$result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
            return FALSE;
         }

         //success so return TRUE
         return TRUE;

    }

    /** @fn logout()
     * @brief deletes a logged in user from logged in table in MySQL; or returns error.
     * @return boolean
     * @todo
     * 1. Add logout mechanism to main page navbar
     */
    function logout() {
        if(!session_id()) session_start();
        unset($_SESSION['egps_username']);
        unset($_SESSION['password']);
        unset($_SESSION['IPP_double_login']);
		$_SESSION = array(); // Destroy the variables.
        session_destroy();
        if(!connectIPPDB()) {
             $error_message = $error_message;  //just to remember we need this
             return FALSE;
         }
         $query = "DELETE FROM logged_in WHERE session_id='" . session_id() . "'";
         $result = mysql_query($query);
         if(!$result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
            return FALSE;
         }
    }

/** @fn validate($szLogin='',$szPassword='')
 * @brief Begins session, checks username and password against user table in MySQL. Returns false and throws error message if there's a problem.
 * @param string $szLogin
 * @param string $szPassword
 * @return boolean
 */

    function validate($szLogin='',$szPassword='') {
         //check username and password against user database
         //returns TRUE if successful or FALSE on fail.
         //if FALSE returns $error_message
         //session_start must be called prior to this function.
         global $error_message, $mysql_user_select_login, $mysql_user_select_password, $mysql_user_table, $mysql_user_append_to_login,$IPP_TIMEOUT;

         $error_message = "";
         //start session...
         //session_cache_limiter('private');  //IE6 sucks
         session_cache_limiter('nocache');
         if(session_id() == "") session_start();

         //we must double login for IPP
         //if(!isset($_SESSION['IPP_double_login'])) return FALSE;

         //check if we already have registered session info
         //for login and passwd.
         if(!isset($_SESSION['IPP_double_login'])) {
             if(!register($szLogin,$szPassword)) {
                 $error_message = $error_message;
                 return FALSE;
             }
         }

         //connect DB:

         if(!connectIPPDB()) {
             $error_message = $error_message;  //just to remember we need this
             return FALSE;
         }

         //check session logged in...
         $query = "SELECT * FROM logged_in WHERE ipp_username = '" . $_SESSION['egps_username'] . "' AND last_ip = '" . $_SERVER["REMOTE_ADDR"] . "' AND (time - NOW()) > 0";
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
             $error_message = $error_message;  //just to remember we need this
             return FALSE;
         }

         $query = "SELECT * FROM $mysql_user_table WHERE (" . $mysql_user_select_login . "='" . $_SESSION['egps_username'] . $mysql_user_append_to_login . "' or " . $mysql_user_select_login . "='" . $_SESSION['egps_username'] . "') and " . $mysql_user_select_password . "='" . $_SESSION['password'] . "' AND aliased_name IS NULL";
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
             $error_message = $error_message;  //just to remember we need this
             return FALSE;
         }

         //update the timeout.
         $query = "UPDATE logged_in SET TIME=(NOW()+ INTERVAL " . $IPP_TIMEOUT . " MINUTE) where session_id='" . session_id() . "'";
         $result = mysql_query($query);
         if(!$result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
             return FALSE;
         }

       //*******  authorized from now on! *******
       return TRUE;

    }

/** @fn		getPermissionLevel($szUsername='')
 * @brief	determines what resources the logged in user has access to.
 * @param string $szUsername
 * @return NULL or row from database
 */
    function getPermissionLevel($szUsername='') {
         //returns permission level or NULL on fail.
         global $error_message;

         $error_message = "";
         if(!connectIPPDB()) {
             $error_message = $error_message;  //just to remember we need this
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
/** @fn	get_services($PERMISSION_LEVEL=100)
 *  @brief	sets permissions for lowest authentication level
 *  @param	$PERMISSION_LEVEL = 100	
 *  @todo 	untangle this mess. Refactor as necessary.
 *  @bug
 *  
*/
    function get_services($PERMISSION_LEVEL=100) {

        global $error_message;

        $error_message = "";

        //Special case we are the school-based IPP administrator
        //get our school code
         $error_message = "";
         if(!connectIPPDB()) {
             $error_message = $error_message;  //just to remember we need this
             return NULL;
         }

         $is_local_ipp_administrator = FALSE;
         $system_query = "SELECT * from support_member WHERE egps_username='" . $_SESSION['egps_username'] . "'";
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
        if($PERMISSION_LEVEL == 0 || $is_local_ipp_administrator)  {
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

    /** @fn 		getStudentPermission($student_id='')
     *  @brief		Determines user's access to specific student's records
     *  @detail
     *  1. Returns error or null under some circumstances.
     *  2. Otherwise, may return NONE,ERROR,READ,WRITE(READ,WRITE),ASSIGN(READ,WRITE,ASSIGN),ALL(READ,WRITE,ASSIGN,DELETE), or support_list['permission'] or NONE for no permissions.		
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

         $permission_level = getPermissionLevel($_SESSION['egps_username']);
         if($permission_level == NULL) return "ERROR";

         //find the currently logged in persons school code...
         if(!connectUserDB()) {
             $error_message = $error_message;  //just to remember we need this
             return "ERROR";
         }

         $query = "SELECT * FROM $mysql_user_table WHERE (" . $mysql_user_select_login . "='" . $_SESSION['egps_username'] . $mysql_user_append_to_login . "' or " . $mysql_user_select_login . "='" . $_SESSION['egps_username'] . "') and " . $mysql_user_select_password . "='" . $_SESSION['password'] . "' AND aliased_name IS NULL";
         $result = mysql_query($query);
         if(!$result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
             return "ERROR";
         }
         $user_row=mysql_fetch_array($result);
         $school_code=$user_row['school_code'];

         if(!connectIPPDB()) {
             $error_message = $error_message;  //just to remember we need this
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
             $error_message = $error_message;  //just to remember we need this
             return NULL;
         }
         $system_query = "SELECT * from support_member WHERE egps_username='" . $_SESSION['egps_username'] . "'";
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
             case 0:     //Super Admin
             case 10:    //Administrator
                return "ALL";

             case 30:    //Principal (assign local) special case
                         //fall through and return ALL for local students.
             case 20:    //Assistant Admin. (view all) special case
                         //fall through and return at least read...
             case 40:    //Vice Principal (view local)
             default:
                //we need to find the permissions from the support list
                //as this user has no inherent permissions...
                $support_query="SELECT * FROM support_list WHERE egps_username='" . $_SESSION['egps_username'] . "' AND student_id=$student_id";
                $support_result=mysql_query($support_query);
                //if(mysql_num_rows($support_result) <= 0) {
                    switch($permission_level) {
                        case 30:
                        case 40:   //changed as per s. chomistek (2006-03-23)
                           if($is_local_student) return "ASSIGN";
                           else return "NONE";
                        case 20: //Asst admin special case of read for all
                           if($is_local_student) return "ASSIGN";
                           else return "READ";
                        //case 40: //vp special case read local
                        //   if($is_local_student) return "READ";
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
?>
