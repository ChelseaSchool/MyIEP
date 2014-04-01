<?php

/** @file
 * @brief 	display and add to goals database (as superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Filter input
 */ 
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 20;    //assistant_admin



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
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//verified past here...

//check if we are deleting an area...
if(isset($_GET['delete_area'])) {
         $delete_query="UPDATE typical_long_term_goal_category SET is_deleted='Y' where cid=" . mysql_real_escape_string($_GET['delete_area']);
         $delete_result = mysql_query($delete_query);
         if(!$delete_result) {
             $error_message = $error_message . "Delete query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         }
         //$system_message=$system_message . "delete query=$delete_query<BR>";
}

//check if we are deleting a long term goal...
if(isset($_GET['delete_ltg'])) {
         $delete_query="UPDATE typical_long_term_goal SET is_deleted='Y' where ltg_id=" . mysql_real_escape_string($_GET['delete_ltg']);
         $delete_result = mysql_query($delete_query);
         if(!$delete_result) {
             $error_message = $error_message . "Delete query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         }
         //$system_message=$system_message . "delete query=$delete_query<BR>";
}

//check if we are deleting a long term goal...
if(isset($_GET['delete_stg'])) {
         $delete_query="UPDATE typical_short_term_objective SET is_deleted='Y' where stg_id=" . mysql_real_escape_string($_GET['delete_stg']);
         $delete_result = mysql_query($delete_query);
         if(!$delete_result) {
             $error_message = $error_message . "Delete query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         }
         //$system_message=$system_message . "delete query=$delete_query<BR>";
}

//check if we are adding a long term goal...
if(isset($_GET['ltg_name'])) {
    //check if we already have this goal value
    $check_query="SELECT * FROM typical_long_term_goal where cid='" . mysql_real_escape_string($_GET['program_area']) . "' and goal='" . mysql_real_escape_string($_GET['ltg_name']) . "'";
    $check_result=mysql_query($check_query);
    if($check_result && mysql_num_rows($check_result) == 0) {
         $insert_query="INSERT INTO typical_long_term_goal (goal,cid) VALUES ('" . mysql_real_escape_string($_GET['ltg_name']) . "','" . mysql_real_escape_string($_GET['program_area']) . "')";
         $insert_result = mysql_query($insert_query);
         if(!$insert_result) {
             $error_message = $error_message . "Insert query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         } else {; /*dangling else??*/ }
    } else {
         $system_message = $system_message . "Unable to add goal most likely because the value already exists<BR>";
    }
}

//check if we are adding a category...
if(isset($_GET['category_name'])) {
    //check if we already have this goal value
    $check_query="SELECT * FROM typical_long_term_goal_category where name='" . mysql_real_escape_string($_GET['category_name']) . "' AND is_deleted='N'";
    $check_result=mysql_query($check_query);
    if($check_result && mysql_num_rows($check_result) == 0) {
         $insert_query="INSERT INTO typical_long_term_goal_category (name) VALUES ('" . mysql_real_escape_string($_GET['category_name']) . "')";
         $insert_result = mysql_query($insert_query);
         if(!$insert_result) {
             $error_message = $error_message . "Insert query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         } else {; /*dangling else??*/ }
    } else {
         $system_message = $system_message . "Unable to add category most likely because the value already exists<BR>";
    }
}

//check if we are adding a category...
if(isset($_GET['stg_name'])) {
    //check if we already have this goal value
    $check_query="SELECT * FROM typical_short_term_objective where goal='" . mysql_real_escape_string($_GET['stg_name']) . "' AND is_deleted='N' AND ltg_id=" . mysql_real_escape_string($_GET['ltg_id']);
    $check_result=mysql_query($check_query);
    if($check_result && mysql_num_rows($check_result) == 0) {
         $insert_query="INSERT INTO typical_short_term_objective (goal,ltg_id) VALUES ('" . mysql_real_escape_string($_GET['stg_name']) . "'," . mysql_real_escape_string($_GET['ltg_id']) . ")";
         $insert_result = mysql_query($insert_query);
         if(!$insert_result) {
             $error_message = $error_message . "Insert query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
             $system_message=$system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
         } else {; /*dangling else??*/ }
    } else {
         $system_message = $system_message . "Unable to add objective most likely because the value already exists<BR>";
    }
}

$ltg_query = "SELECT typical_long_term_goal_category.cid AS uid,typical_long_term_goal_category.name AS type,typical_long_term_goal.is_deleted AS deleted, typical_long_term_goal.* FROM typical_long_term_goal_category LEFT OUTER JOIN typical_long_term_goal ON typical_long_term_goal_category.cid=typical_long_term_goal.cid WHERE typical_long_term_goal_category.is_deleted='N' ORDER BY typical_long_term_goal_category.name ASC, goal ASC";
//$ltg_query = "SELECT * FROM typical_long_term_goal LEFT JOIN area_type ON typical_long_term_goal.area_type_id=area_type.area_type_id WHERE 1 GROUP BY typical_long_term_goal.area_type_id ORDER BY goal";
$ltg_result = mysql_query($ltg_query);
if(!$ltg_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$ltg_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//$system_message=$system_message . "returned rows: " . mysql_num_rows($ltg_result) . "<BR>";

?> 

<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }

    </SCRIPT>
</HEAD>
<BODY>
        <table class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
        <tr>
          <td class="shadow-topLeft"></td>
            <td class="shadow-top"></td>
            <td class="shadow-topRight"></td>
        </tr>
        <tr>
            <td class="shadow-left"></td>
            <td class="shadow-center" valign="top">
                <table class="frame" width=620px align=center border="0">
                    <tr align="Center">
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">-Goals Database-</p></center></td></tr></table></center>
                        <BR>

                        <?php $colour0="#DFDFDF"; $colour1="#CCCCCC"; ?>

                        <HR>
<!--temporary printout of tables -->
<?php /* echo "<table>\n";
while ($line = mysql_fetch_array($ltg_result, MYSQL_ASSOC)) {
   echo "\t<tr>\n";
   foreach ($line as $col_value) {
       echo "\t\t<td>$col_value</td>\n";
   }
   echo "\t</tr>\n";
}
echo "</table>\n";
mysql_data_seek($ltg_result,0)
 */ ?>
<!--end temporary printout of tables-->

                        <!-- BEGIN  Goals -->
                        <table width="100%" border="0"><tr><td><p class="header" align="left">Goals:&nbsp;&nbsp;</p></td><td align="center" valign="bottom"><?php echo "<form enctype=\"multipart/form-data\" action=\"" . IPP_PATH . "superuser_add_goals.php" . "\" method=\"get\"><p class=\"small_text\">Add Program Area:&nbsp;&nbsp;<input class=\"small\" type=\"text\" size=\"30\" name=\"category_name\"></form>"; ?></td></tr></table>
                        <BR>
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <?php
                        $colour0="#DFDFDF";$colour1="#CCCCCC";
                        $colour=$colour0;
                        $ltg=mysql_fetch_array($ltg_result);
                        while($ltg) {
                            $area_type_id=$ltg['uid'];
                            //echo "<tr><td colspan=\"2\">storage: $area_type_id, array: " . $ltg['uid'] . "</td></tr>";
                            echo "<tr><td colspan=\"2\" class=\"wrap_top\">Program Area:&nbsp;&nbsp;" . $ltg['type'] . "&nbsp;<a href=\"". IPP_PATH . "superuser_add_goals.php?delete_area=" . $ltg['uid'] . "\"><img align=\"top\" border=\"0\" alt=\"Delete\" src=\"" . IPP_PATH . "images/close.gif" . "\"></a></td></tr>\n";
                            while($ltg['uid'] == $area_type_id) {
                                //echo "<tr><td colspan=\"2\" class=\"wrap_top\">Area:&nbsp;&nbsp;" . $ltg['type'] .  "," . $ltg['uid'] . "," . $ltg['goal'] . "</td></tr>\n";
                                echo "<td class=\"wrap_left\" width=\"50%\" bgcolor=\"#FFFFFF\"><center><p class=\"small_text\"><b>Long Term Goals</b></p></td><td class=\"wrap_right\" width=\"50%\" bgcolor=\"#FFFFFF\"><center><p class=\"small_text\"><b>Short Term Objectives</b></p></td>";
                                while($ltg && $ltg['uid'] == $area_type_id) {
                                  if($ltg['deleted']=='N') {
                                    echo "<tr>";
                                    if(!$ltg['goal'])
                                        echo "<td class=\"wrap_left\" width=\"50%\" bgcolor=\"$colour\"><center><p class=\"small_text\"><b>-none-</b></p></td>";
                                    else
                                        echo "<td class=\"wrap_left\" width=\"50%\" bgcolor=\"$colour\" valign=\"top\"><p class=\"small_text\"><b>" . $ltg['goal'] . "&nbsp;<a href=\"". IPP_PATH . "superuser_add_goals.php?delete_ltg=" . $ltg['ltg_id'] . "\"><img align=\"top\" border=\"0\" alt=\"Delete\" src=\"" . IPP_PATH . "images/close.gif" . "\"></a></b></p></td>";
                                    if(!$ltg['ltg_id'])
                                        $stg_query="SELECT * FROM typical_short_term_objective WHERE ltg_id IS NULL";
                                    else
                                        $stg_query="SELECT * FROM typical_short_term_objective WHERE ltg_id=" . $ltg['ltg_id'] . " AND is_deleted='N'"; // . " ORDER BY goal ASC";
                                    $stg_result=mysql_query($stg_query);
                                    if(!$stg_result) {
                                       echo "<td class=\"wrap_right\" width=\"50%\" bgcolor=\"$colour\">Error: " . mysql_error() . "Query=$stg_query</td>";
                                    } else {
                                       echo "<td class=\"wrap_right\" width=\"50%\" bgcolor=\"$colour\">";
                                       if(mysql_num_rows($stg_result) < 1) echo "<center><p class=\"small_text\">-no short term goals added-</p></center>";
                                       while($stg=mysql_fetch_array($stg_result)) {
                                          echo "<ul>";
                                          echo "<li><b><p class=\"small_text\">" . $stg['goal'] . "</b>&nbsp;<a href=\"". IPP_PATH . "superuser_add_goals.php?delete_stg=" . $stg['stg_id'] . "\"><img align=\"top\" border=\"0\" alt=\"Delete\" src=\"" . IPP_PATH . "images/close.gif" . "\"></a>\n";
                                          echo "</ul>";
                                       }
                                       echo "<BR><p class=\"small_text\"><center><form enctype=\"multipart/form-data\" action=\"" . IPP_PATH . "superuser_add_goals.php" . "\" method=\"get\"><p class=\"small_text\">Add Short Term Goal:<BR><input class=\"small\" type=\"text\" size=\"30\" name=\"stg_name\"><input type=\"hidden\" name=\"ltg_id\" value=\"" . $ltg['ltg_id'] . "\"></form></p></center>";
                                       echo "</td>";
                                    }
                                    if($colour==$colour0) $colour=$colour1; else $colour=$colour0;
                                    echo "</tr>\n";
                                  }
                                  $ltg = mysql_fetch_array($ltg_result);

                                }
                                //$ltg = mysql_fetch_array($ltg_result);
                            }
                            echo "<tr><td class=\"wrap_bottom_left\" width=\"50%\"><center><form enctype=\"multipart/form-data\" action=\"" . IPP_PATH . "superuser_add_goals.php" . "\" method=\"get\"><p class=\"small_text\">Add Long Term Goal:</p><input class=\"small\" type=\"text\" size=\"30\" name=\"ltg_name\"><input type=\"hidden\" name=\"program_area\" value=\"$area_type_id\"></form></center></td>";
                            echo "<td class=\"wrap_bottom_right\" width=\"50%\">&nbsp;</td>";
                            echo "</tr>\n";
                            echo "<tr><td>&nbsp;</td><td width=\"50%\">&nbsp;</td></tr>";
                        }
                        /*
                        $done=FALSE;
                        do {
                           //$area_type_id=$ltg['area_type_id'];
                           if(!$ltg = mysql_fetch_array($ltg_result)) $done=TRUE;
                           $area_type_id=$ltg['area_type_id'];
                           while($ltg['area_type_id'] == $area_type_id && !$done) {
                            echo "<tr><td colspan=\"2\" class=\"wrap_top\">Area:&nbsp;&nbsp;" . $ltg['type'] .  "</td></tr>\n";

                            $ltg_id=$ltg['ltg_id'];
                            if(!$ltg_id) {
                                echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" width=\"50%\"><center>-none</center>";

                                echo "</td><td class=\"wrap_right\" width=\"100\" bgcolor=\"$colour0\">&nbsp;</td></tr>\n";
                            
                            }
                            else do {
                                //loop through the long term goals in this program area...
                                echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" width=\"50%\"><center>" . $ltg['goal'] . "</center>";
                                echo "</td><td class=\"wrap_right\" width=\"100\" bgcolor=\"$colour0\"><center><p class=\"small_text\">Add Short Term Goal</p><form enctype=\"multipart/form-data\" action=\"" . IPP_PATH . "superuser_add_goals.php" . "\" method=\"get\"><input type=\"text\" size=\"30\" name=\"stg_name\"><input type=\"hidden\" name=\"ltg_parent\" value=\"" . $ltg['ltg_id'] . "\"></form></center></td></tr>\n";
                            } while($ltg = mysql_fetch_array($ltg_result) && $ltg['ltg_id'] == $ltg_id);


                            echo "<tr><td class=\"wrap_bottom_left\" width=\"50%\"><center><p class=\"small_text\">Add Long Term Goal</p><form enctype=\"multipart/form-data\" action=\"" . IPP_PATH . "superuser_add_goals.php" . "\" method=\"get\"><input type=\"text\" size=\"30\" name=\"ltg_name\"><input type=\"hidden\" name=\"program_area\" value=\"$area_type_id\"></form></center></td>";
                            echo "<td class=\"wrap_bottom_right\" width=\"100\">&nbsp;</td>";
                            echo "</tr>\n";
                            echo "<tr><td>&nbsp;</td><td width=\"100\">&nbsp;</td></tr>";
                            if(!($ltg = mysql_fetch_array($ltg_result))) $done=TRUE;
                          }
                        } while(!$done);
                        */
                        ?>
                        </table>
                        </center>
                        <!-- END Guardian Info -->

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center"><table border="0" width="100%"><tr><td width="60"><a href="
            <?php
                echo IPP_PATH . "main.php";
            ?>"><img src="<?php echo IPP_PATH; ?>images/back-arrow.png" border=0></a></td><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/homebutton.png" border=0></a></td><td valign="bottom" align="center">Logged in as: <?php echo $_SESSION['egps_username'];?></td><td align="right"><a href="<?php echo IPP_PATH;?>"><img src="<?php echo IPP_PATH; ?>images/logout.png" border=0></a></td></tr></table></td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        <center></center>
    </BODY>
</HTML>
