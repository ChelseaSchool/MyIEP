<?php

/** @file
 *  @brief		Functions called on in other scripts to provide user functionality
 *  @todo
 *  1. Document functions thoroughly (in progress)
 *  
 *  
 */

if(!defined('IPP_PATH')) define('IPP_PATH','../');

/** @fn		 	getNumUsers()
 *  @brief		Gets total number of users from the support_member table
 *  @detail		Errors are trapped, logged
 *  @return		count of fetched rows, I think
 *  @todo		Locate where this is called from. I don't think it's used throughout the code. There may be a better place for it.	
 * 
 */
function getNumUsers() {
    //returns the number of users in support_member tables
    //or NULL on fail.
    global $error_message;

    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this - todo: give the why?
        return NULL;
    }

    $query = "SELECT * FROM support_member WHERE 1=1";
    $result = mysql_query($query);
    if(!$result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return NULL;
    }

    return mysql_num_rows($result);
}

/** @fn 		getUserSchoolCode($egps_username="")
 *  @brief		get school code of selected user (by egps_username)
 *  @detail		traps and logs errors
 *  @remark		changed mysql_real_escape_string to mysql_real_escape_string - but why was mysql_real_escape_string() there? 
 * @param 		$egps_username
 * @return		row with school code 
 */
function getUserSchoolCode($egps_username="") {
   global $error_message;

    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this - todo: give the why?
        return NULL;
    }

    $query = "SELECT school_code FROM support_member WHERE egps_username='" . mysql_real_escape_string($egps_username) . "'"; //todo: find alternative escape mechanism for output; standardize
    $result = mysql_query($query);
    if(!$result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return NULL;
    }
    $user_row=mysql_fetch_array($result);
    return $user_row['school_code'];
}

/** @fn isLocalAdministrator($egps_username="")
 * 
 * Determines if a "member" is a local (school?) administrator
 * @param $egps_username
 * @return	returns boolean, assuming no errors
 */
function isLocalAdministrator($egps_username="") {
   global $error_message;

    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this todo: explain why?
        return NULL;
    }

    $query = "SELECT is_local_ipp_administrator FROM support_member WHERE egps_username='" . mysql_real_escape_string($egps_username) . "'";
    $result = mysql_query($query);
    if(!$result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return FALSE;
    }
    $user_row=mysql_fetch_array($result);
    if($user_row['is_local_ipp_administrator'] == 'Y') return TRUE;
    return FALSE;
}
/** @fn			getNumUsersOnline()
 *  @brief		gets count of logged-in support_members
 *  @detail		Returns NULL on failure
 *  @todo		See where this function is called; perhaps move to a better place
 */
function getNumUsersOnline() {
    //returns the number of users in support_member tables
    //or NULL on fail.
    global $error_message;

    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this - todo: make this a why? comment
        return NULL;
    }

    $query = "SELECT * FROM logged_in WHERE 1=1";
    $result = mysql_query($query);
    if(!$result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return NULL;
    }

    return mysql_num_rows($result);
}

/** @fn				username_to_common($username="-unknown-")
 *  @breif			change first.last to common name with title case
 *  @detail			No error catching. Starts with unknown username. Then returns proper name string represented by $username
 *  @todo			Dev comments suggest this function could be better. Why is it needed (check SQL); is it used? Can it be improved?
 */

//get change firstname.lastname to Firsteame Lastname
  function username_to_common($username="-unknown-") {
    //capitalize the first char...my kingdom for a pointer - todo: find a solution to the dev's call for help; how can we provide a pointer that improves this function?
    if(ord($username[0]) < 123 && ord($username[0]) >  96)
      $username[0] =chr(ord($username[0]) - 32);
    $index = strpos($username, '.');
    $username[$index] = ' ';
    $index++;
    if(ord($username[$index]) < 123 && ord($username[$index]) >  96)
      $username[$index] =chr(ord($username[$index]) - 32);
    return $username;
  }
  


?>
