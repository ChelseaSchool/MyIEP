<?php

/** @file
 * @brief 	display logs (superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * * reenable paging php - currently commented out
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
    <?php print_general_navbar();
      
    print_lesser_jumbotron("View Logs", $permission_level); ?>
    
    <div class="container">

                        <?php //display users... ?>
                        <form name="loglist" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_view_logs.php"; ?>" method="post">
                        <input type="hidden" name="select_level" value="1">
                        <input type="hidden" name="iCur" value="0">
                        <input type="hidden" name="iLimit" value="<?php echo $iLimit; ?>">
                        <BR>
                        <div class="form-group"><label>View</label>
                        <SELECT name="szLevel">
                            <OPTION value="ALL">ALL</OPTION>
                            <OPTION value="ERROR" selected>Errors</OPTION>
                            <OPTION value="WARNING">Warnings</OPTION>
                            <OPTION value="INFORMATIONAL">Informational</OPTION>
                        </SELECT>
                        <label>Limit</label>
                        <INPUT name="iLimit" value="<?php echo $iLimit; ?>" size="5">
                        
                        <button type="submit" value="Go">Go</button></div>
                        
                        
                        

                        <?php
                       /*
						echo "<table>";
                        //print the next and prev links...
                       
                        if($iCur != 0) {
                            //we have previous values...
                            echo "<tr><td>";
							echo "<a href=\"./superuser_view_logs.php?iCur=" . ($iCur-$iLimit) . "&iLimit=$iLimit&szLevel=$szLevel\" class=\"default\">previous $iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }
                        echo "</td>";
                        
                        
                        if(($iLimit+$iCur < $sqlLogTotals)) {
                            echo "<td align=\"right\"><a href=\"./superuser_view_logs.php?iCur=" . ($iCur+$iLimit) . "&iLimit=$iLimit&szLevel=$szLevel\" class=\"default\">next ";
                            if( $sqlLogTotals-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($sqlLogTotals-($iCur+$iLimit)) . "</td>";
                            }
                        } 
                        
                        echo "</tr>\n";
                        echo "</table>";
                        */
                        //end print next and prev links
						echo "<table class=\"table table-striped table-hover\">";
                        //print the header row...
                        echo "<tr><th>UID</th><th>Level</th><th>Username</th><th>Student ID</th><th>Date & Time</th><th>Message</th></tr>\n";
                        while ($log_row=mysql_fetch_array($sqlLog)) {
                            echo "<tr>\n";
                            echo "<td>" . $log_row['uid'] . "<p></td>\n";
                            echo "<td>" . $log_row['level'] . "<p></td>\n";
                            echo "<td>" . $log_row['username'] . "<p></td>\n";
                            echo "<td>" . $log_row['student_id'] . "<p></td>\n";
                            echo "<td>" . $log_row['time'] . "<p></td>\n";
                            echo "<td>" . $log_row['message'] . "<p></td>\n";
                            echo "</tr>\n";
                            
                        }
                        ?>
                        </table></center>
                        </form>
        <?php print_complete_footer(); ?></div>
        <?php print_bootstrap_js();?>
    </BODY>
</HTML>
