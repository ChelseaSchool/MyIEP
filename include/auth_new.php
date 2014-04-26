<?php
/** @file
 *  @brief (in progress) authorization model for reliance on hashing algorithm
 *  @detail
 *  * Should take legacy passwords, generate hash. Check hash against what's stored in the database; if no hash in database, verify hash and update database. During update, it should remove the legacy password from database.
 *  * should generate secure session array
 *  * should check authorization levels
 *  @author Rik Goldman
 *  @copyright Chelsea School
 */



/** @brief Define root path of MyIEP if not defined
*/
if (!defined('IPP_PATH')) define('IPP_PATH', '../'));

/** @breif not sure of this legacy conditional
 *  @todo 
 *  1. cross check against include/init.php
 */
if (!isset($is_local_student)) $is_local_student=FALSE;

/** @brief required includes for authentication
 *  @remark recommendation of pass_compat is to use require to include password.php
 */

require_once(IPP_PATH  . 'include/db.php');
require_once (IPP_PATH . 'etc/init.php');
require (IPP_PATH . 'include/password.php');

/** @fn register($szlogin='', $szPassword='')
 * @param $szLogin
 * @param $szPassword
 * @return TRUE if no errors
 */

/** fn generate_hash($szPassword)
 * @param $szPassword (escaped/filtered from form input)
 * @return $untested_hash
 * @remark
 * 1. use first to validate hash, then authenticate
 * 
 */
function generate_hash($szPassword) {
    $untested_hash = password_hash($szPassword, PASSWORD_BCRYPT);
	return $untested_hash;
}
function register($szLogin='', $szPassword='') {
	global $error_message, $mysql_user_table, $IPP_TIMEOUT;
	//cleared variable to secure from outside data
    $error_message = "";
    
    //check db connection and throw error if not
    if (!connectUserDB()) {
    	$error_message = $error_message; //why doesn't this get a value?
    	return FALSE;
    }
    
    
  
    
    $query = "SELECT * FROM $mysql_user_table WHERE (user = $szLogin and 
    }
