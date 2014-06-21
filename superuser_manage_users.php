<?php

/** @file
 * @brief 	mangage users main menu (superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo
 * * Bootstrap UI overhaul
 */  
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0; //Super admin only (note: exception is local administrators)



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message = "";

if(!defined('IPP_PATH')) define('IPP_PATH','./');

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
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL && !(isLocalAdministrator($_SESSION['egps_username']))) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//check if we are deleting some people
if(isset($_GET['delete_users']) || isset($_GET['delete_users_x'])) {

    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    $delete_query="";
    if($permission_level == 0) {
       $delete_query = "DELETE FROM support_member WHERE ";
    } else {
       $delete_query = "DELETE FROM support_member WHERE school_code='" . getUserSchoolCode($_SESSION['egps_username']) . "' AND ";
    }
    foreach($_GET as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
        $delete_query = $delete_query . "egps_username='" . str_replace("_",".",$key) . "' or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //echo $delete_query;

    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    //$system_message = $delete_query;
}

//check if we are deleting some people
if((isset($_GET['set_local_admin_users']) || isset($_GET['set_local_admin_users_x'])) && $permission_level==0 ) {  //only super admins

    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $update_query = "UPDATE support_member SET is_local_ipp_administrator='Y' WHERE ";
    foreach($_GET as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
        $update_query = $update_query . "egps_username='" . str_replace("_",".",$key) . "' or ";
    }
    //strip trailing 'or' and whitespace
    $update_query = substr($update_query, 0, -4);
    //echo $delete_query;

    $update_result = mysql_query($update_query);
    if(!$update_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
      //send a notification to the people set as site based ipp admin.
      foreach($_POST as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
           mail_notification(mysql_real_escape_string(str_replace("_",".",$key)),
"This email has been sent to you to notify you 
	that you have been set as one of the school based IPP administrators for your school.
	This means you have full access to all of the IPP's at your school to move and assign permissions to the IPP's there. You are able to add teaching and TA staff members onto the IPP system for your school and you will be sent notifications when students are moved into your school so that you are able to assign the IPPs to the appropriate person.
");
      }
    }
    //$system_message = $delete_query;
}

//check if we are deleting some people
if((isset($_GET['unset_local_admin_users']) || isset($_GET['unset_local_admin_users_x'])) && $permission_level==0) { //only super admins
    //$system_message .= "Debug Msg: unsetting local admin<BR>";
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $update_query = "UPDATE support_member SET is_local_ipp_administrator='N' WHERE ";
    foreach($_GET as $key => $value) {
        if($key != "delete_users" && $value=="on"  )
        $update_query = $update_query . "egps_username='" . str_replace("_",".",$key) . "' or ";
    }
    //strip trailing 'or' and whitespace
    $update_query = substr($update_query, 0, -4);
    //echo $delete_query;

    $update_result = mysql_query($update_query);
    if(!$update_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    //$system_message = $delete_query;
}


//find number of support_members
$iNumSupportMembers = getNumUsers();
if($iNumSupportMembers == NULL) {
    //throw an error
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//find number of support_members online
$iNumSupportMembersOnline = getNumUsersOnline();
if($iNumSupportMembersOnline == NULL) {
    //throw an error
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get the list of all users...

if(!isset($_GET['iLimit'])) $iLimit = 10; else $iLimit = $_GET['iLimit'];
if(!isset($_GET['iCur'])) $iCur = 0; else $iCur = $_GET['iCur'];
if(isset($_POST['iCur']) && $_POST['iCur'] != "" ) $iCur=$_POST['iCur'];
function getUsers() {
    global $error_message,$iLimit,$iCur,$bShowNav,$system_message;
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    if(!isset($_GET['username'])) {
        if(isset($_GET['showall'])) {
            $query = "SELECT * FROM support_member LEFT JOIN school ON support_member.school_code=school.school_code where 1=1 ORDER BY egps_username ASC";
        } else {
            $query = "SELECT * FROM support_member LEFT JOIN school ON support_member.school_code=school.school_code where 1=1 ORDER BY egps_username ASC LIMIT $iCur,$iLimit";
            $bShowNav = TRUE;
        }
    } else {
        $query = "SELECT * FROM support_member LEFT JOIN school ON support_member.school_code=school.school_code WHERE egps_username LIKE '" . $_GET['username'] . "' ORDER BY egps_username ASC";

    }

    if(isset($_GET['index'])) {
      $query = "SELECT * FROM support_member LEFT JOIN school on support_member.school_code=school.school_code WHERE ASCII(LOWER(egps_username)) >= ASCII('" . mysql_real_escape_string($_GET['index']) . "') ORDER BY egps_username ASC LIMIT $iLimit";
      //do some moronic thing to find our index- were I not so lazy I'd find a more elegant method.
      $get_index_query="SELECT * FROM support_member LEFT JOIN school on support_member.school_code=school.school_code WHERE ASCII(LOWER(egps_username)) < ASCII('" . mysql_real_escape_string($_GET['index']) . "')";
      $get_index_result=mysql_query($get_index_query);
      if($get_index_result) $iCur=mysql_num_rows($get_index_result);
      else $system_message .= "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$get_index_query'<BR>";
    }
    $result = mysql_query($query);
    if(!$result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return NULL;
    }
    return $result;
}

$sqlUsers=getUsers();
if(!$sqlUsers) {
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//set back vars...
$szBackGetVars="";
foreach($_GET as $key => $value) {
    $szBackGetVars = $szBackGetVars . $key . "=" . $value . "&";
}
//strip trailing '&'
$szBackGetVars = substr($szBackGetVars, 0, -1);

print_html5_primer();
?> 

    <SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to modify or delete:\n";
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
    <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
<?php print_bootstrap_head(); ?>
</HEAD>
    <BODY>
    <?php 
    print_general_navbar();
    ?>
    
 		<div class="jumbotron"><div class="container">
<h1>Manage Users</h1>
<h2>Logged in as: <small><?php echo $_SESSION['egps_username'];?>  (Permission: <?php echo $permission_level; ?>)</small></h2>
<h3><?php if ($system_message) {
		echo $system_message;
	} 
	?></h3>
<?php 
echo "<a class=\"btn btn-primary btn-large\" 
		href=\"" . IPP_PATH . "superuser_new_member_2.php?" . $szBackGetVars . "\">
		Add New User</a>\n";
?>
</div> <!-- close container -->

</div> <!-- Close Jumbotron -->

    

<div class="container">
<?php 
echo "<div class=\"alert alert-block alert-warning\">
<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Notification</strong>: There are currently $iNumSupportMembers support members 
registered on the system district-wide; 
$iNumSupportMembersOnline currently online.
</div>";

?>
                          


<h2>Alphabetical User Index</h2>
<div class="btn-group" align="center">
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=a">a</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=b">b</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=c">c</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=d">d</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=e">e</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=f">f</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=g">g</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=h">h</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=i">i</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=j">j</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=k">k</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=l">l</a>
    <a class="btn btn-default btn-small" href="superuser_manage_users.php?index=m">m</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=n">n</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=o" >o</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=p" >p</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=q" >q</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=r" >r</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=s" >s</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=t" >t</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=u" >u</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=v" >v</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=w" >w</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=y" >y</a>
	<a class="btn btn-default btn-small" href="superuser_manage_users.php?index=z" >z</a>
</div>

<h2>Browse Users <small>Click a user to edit</small></h2>

<form name="userlist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_users.php"; ?>" method="get">
<table class="table table-striped table-hover">
<input type="hidden" name="iCur" value="<?php echo $iCur; ?>">
                        <?php
                        //print the next and prev links...
                        
                        if($bShowNav && $iCur != 0) {
                            //we have previous values...
                            echo "<a href=\"./superuser_manage_users.php?iCur=";
                            if($iCur-$iLimit < 0) echo "0"; else echo ($iCur-$iLimit);
                            echo "\" class=\"default\">previous ";
                            if($iCur-$iLimit < 0) echo $iCur; else echo "$iLimit</a>";
                        } else {
                            echo "&nbsp;";
                        }

                        if($bShowNav && ($iLimit+$iCur < $iNumSupportMembers)) {
                            echo "<td colspan=\"2\" align=\"right\"><a href=\"./superuser_manage_users.php?iCur=" . ($iCur+$iLimit) . "\" class=\"default\">next ";
                            if( $iNumSupportMembers-($iCur+$iLimit) > $iLimit) {
                                echo $iLimit . "</td>";
                            } else {
                                echo ($iNumSupportMembers-($iCur+$iLimit)) . "</td>";
                            }
                        }
                        
                        echo "</tr>\n";
                        //end print next and prev links

                        //print the header row...
                        echo "<tr><th>Select</th><th>Username</th><th>School</th><th>Permission level</th><th>Last IP</th><th>Last Active</th></tr>\n";
                        while ($users_row=mysql_fetch_array($sqlUsers)) {
                            //get the permission_level name...
                            $level_query = "SELECT * FROM permission_levels WHERE level=" . $users_row['permission_level'];
                            $level_result = mysql_query($level_query);
                            if(!$level_result) {
                                $system_message = $system_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$level_query'<BR>";
                                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                            }
                            $level_row = mysql_fetch_array($level_result);
                            //if we have a local admin colour row red...
                            if($users_row['is_local_ipp_administrator']=='Y') { $temp_bgcolor=$bgcolor; $bgcolor="FF9999";}
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $users_row['egps_username'] . "\"></td>";
                            echo "<td><a href=\"" . IPP_PATH . "superuser_manage_user.php?ippuserid=" . $users_row['egps_username'] . "\" class=\"default\">" . $users_row['egps_username'] . "</a></td>\n";
                            echo "<td>" . $users_row['school_name'] . "</td>\n";
                            echo "<td>" . $level_row['level_name'];
                            if($users_row['is_local_ipp_administrator']=='Y') echo "<BR><B>(school ipp admin)<B>";
                            echo "</td>\n";
                            echo "<td>" . $users_row['last_ip'] . "</td>\n";
                            echo "<td>" . $users_row['last_active'] . "</td>\n";
                            echo "</tr>\n";
                            //if we have a local admin reset the colour to what it was
                            if($users_row['is_local_ipp_administrator']=='Y') { $bgcolor=$temp_bgcolor; }
                            if($bgcolor=="#DFDFDF") $bgcolor="#CCCCCC";
                            else $bgcolor="#DFDFDF";
                        }
                        ?>
                        </table>
                           <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected: &nbsp;
                             </td>
                             <td>
                               <INPUT TYPE="image" NAME="delete_users" SRC="<?php echo IPP_PATH . "images/smallbutton.php?title=Delete"; ?>" border="0" value="1">
                               <?php
                               if($permission_level == 0) { //only super admins...
                                echo "<INPUT TYPE=\"image\" NAME=\"set_local_admin_users\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Set+Local+Admin" . "\" border=\"0\" value=\"1\"><INPUT TYPE=\"image\" NAME=\"unset_local_admin_users\" SRC=\"" .  IPP_PATH . "images/smallbutton.php?title=Unset+Local+Admin" . "\" border=\"0\" value=\"1\">";
                               }
                               ?>
                             </td>
                             </tr>
                            </table>
                        
                        </form>
                       

    <footer><?php print_complete_footer();?></footer>
    <?php print_bootstrap_js();?>                 
    </BODY>
</HTML>
