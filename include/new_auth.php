 <?php   
    
    /** @fn login($szLogin='',$szPassword='')
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
    function login($szLogin='',$szPassword='') {
        global $mysql_user_append_to_login,$error_message, $mysql_user_select_login, $mysql_user_select_password, $mysql_user_table, $mysql_user_append_to_login,$IPP_TIMEOUT;
    
        $error_message = "";
    
        if(!connectUserDB()) {
            $error_message = $error_message; //just to remember we need this
            return FALSE;
        }
    
        //strip off the $mysql_user_append_to_login
        $szLogin = str_replace($mysql_user_append_to_login,'',$szLogin);
    
        $query = "SELECT * FROM users WHERE login_name='$szLogin'";
        $result = mysql_query($query);

        if(!$result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
            return FALSE;
        }
    
        //check if we gotten no result (no user with that name)
        if(mysql_num_rows($result) <= 0 ) {
            $error_message = "Login failed: Unknown username and password<BR>";
            return FALSE;
        }
        
        //verify hash matches db
        $storedHash = $result['unencrypted_password'];
        $verified=password_verify($szPassword, $storedHash);
        if (!verified) {
			$error_message = "Login failed: Incorrect password<BR>";
            return FALSE;
            
        }
        //set session info..
        $_SESSION['egps_username'] = $szLogin;
        $_SESSION['password'] = $storedHash;
        $_SESSION['IPP_double_login'] = TRUE;
    
        if(!connectIPPDB()) {
            $error_message = $error_message; //just to remember we need this
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
         //session_cache_limiter('private'); //IE6 sucks
         session_cache_limiter('nocache');
         if(session_id() == "") session_start();

  
         if(!isset($_SESSION['IPP_double_login'])) {
             if(!logon($szLogin,$szPassword)) {
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
             $error_message = $error_message; //just to remember we need this
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
             $error_message = $error_message; //just to remember we need this
             return FALSE;
         }

         //update the timeout.
         $query = "UPDATE logged_in SET TIME=(NOW()+ INTERVAL " . $IPP_TIMEOUT . " MINUTE) where session_id='" . session_id() . "'";
         $result = mysql_query($query);
         if(!$result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
             return FALSE;
         }

       //******* authorized from now on! *******
       return TRUE;

    }

?>  