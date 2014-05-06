<?php

/** @file
 * @brief 	display server vars (superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * 
 */  
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0;  //only super administrator



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
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

//check permission levels
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

  //get the list of all users...
  //wonder how php handles dangling else...
  if(!isset($_GET['iLimit']))
    if(!isset($_POST['iLimit'])) $iLimit = 50;
        else $iLimit=$_POST['iLimit'];
  else $iLimit = $_GET['iLimit'];

  if(!isset($_GET['iCur']))
    if(!isset($_POST['iCur'])) $iCur = 0;
    else $iCur=$_POST['iCur'];
  else $iCur = $_GET['iCur'];

  if(!isset($_GET['szLevel']))
    if(!isset($_POST['szLevel'])) $szLevel = "ERROR";
    else $szLevel=$_POST['szLevel'];
  else $szLevel = $_GET['szLevel'];

/**@fn getLog()
 * @return $log_result
 * @brief  Selects from DB table error log by time or by access level
 */
function getLog() {
    global $error_message,$iLimit,$iCur,$szLevel;
    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    if($szLevel == "ALL") {
        $log_query = "SELECT * FROM error_log WHERE 1=1 ORDER BY time DESC LIMIT $iCur,$iLimit";
    } else {
        $log_query = "SELECT * FROM error_log WHERE level='$szLevel' ORDER BY time DESC LIMIT $iCur,$iLimit";

    }
    $log_result = mysql_query($log_query);
    if(!$log_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$log_query'<BR>";
        return NULL;
    }
    return $log_result;
}

function getLogTotals() {
    global $error_message,$iLimit,$iCur,$szLevel;

    if(!connectIPPDB()) {
        $error_message = $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    if($szLevel == "ALL") {
        $log_query = "SELECT * FROM error_log WHERE 1=1";
    } else {
        $log_query = "SELECT * FROM error_log WHERE level='$szLevel'";

    }
    $log_result = mysql_query($log_query);
    if(!$log_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$log_query'<BR>";
        return NULL;
    }
    return mysql_num_rows($log_result);
}


$sqlLog=getLog();
if(!$sqlLog) {
    $system_message = $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$sqlLogTotals=getLogTotals();

//set back vars...
$szBackGetVars="";
foreach($_GET as $key => $value) {
    $szBackGetVars = $szBackGetVars . $key . "=" . $value . "&";
}
//strip trailing '&'
$szBackGetVars = substr($szBackGetVars, 0, -1);


?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
  
  

    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to delete:\n";
          var count = 0;
          form=document.userlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].name + " ";
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
    </SCRIPT>
<?php print_bootstrap_head();?>
</HEAD>
    <BODY>
    <?php print_general_navbar(); ?>
    <?php print_lesser_jumbotron("Server Characteristics", $permission_level); ?>
    <div class="container">
    <table class="table table-striped table-hover"
    <tr><th>Key</th><th>Value</th><tr>
    <?php
    
    	
    foreach ($_SERVER as $key => $value) {
    	
		echo "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>\n";
    	
    	
    }
    
    ?>
     </table>                   
                       
       <footer><?php print_complete_footer(); ?></footer></div>
    </BODY>
</HTML>
