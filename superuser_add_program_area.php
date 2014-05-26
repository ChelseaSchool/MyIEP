<?php

/** @file
 * @brief 	manage global program areas (as superuser)
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		Filter input
 */  
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 20;  //assistant administrators


/**
 * Path for IPP required files.
 */

$system_message = $system_message;

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

//check if we do add...
if(isset($_GET['category'])) {
    
	$add_query = "INSERT INTO `typical_long_term_goal_category` (`name`, `is_deleted`) VALUES (\"". mysql_real_escape_string($_GET['category']) . "\", \"N\")";
	
    $add_result = mysql_query($add_query);
    if(!$add_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
        $system_message=$system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}
//"UPDATE `MyIEP`.`typical_long_term_goal_category` SET `is_deleted` = \'Y\' WHERE `typical_long_term_goal_category`.`cid` = 50
//check if we are deleting some peeps...
if($_GET['delete_x'] && $permission_level <= $IPP_MIN_DELETE_AREA_PERMISSION) {
    $delete_query = "UPDATE typical_long_term_goal_category SET is_deleted = 'Y' WHERE name = " . $_GET['category'];
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
    $system_message = $system_message . $delete_query . "<BR>";
}

$area_query = "SELECT *
FROM `typical_long_term_goal_category` ORDER BY `typical_long_term_goal_category`.`name` ASC";
$area_result = mysql_query($area_query);
if(!$area_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


?> 
<?php print_html5_primer();?>
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
     
    
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "delete_supervisor=";
          var szConfirmMessage = "Are you sure you want to delete area:\n";
          var count = 0;
          form=document.arealist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + "\n";
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

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
<?php print_bootstrap_head();?>
</HEAD>
    <BODY>
    <?php print_general_navbar(); ?>
    
    <?php print_lesser_jumbotron("Manage Program Areas", $permission_level);?>
    <div class="container">   
          
       
        
                
                        <?php if ($system_message) { echo "<h2>" . $system_message . "</h2>";} ?>

                        
                        <!-- BEGIN add area -->
                        <h2>Add Goal Area <small>Scroll down for existing goal areas</small></h2>
                        <form name="addarea" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_add_program_area.php"; ?>" method="get">
                               <input type="hidden" name="addarea" value="1">
                               <div class="form-group">
                               <label>Goal Area</label>
                               <input class="form-control" spellcheck="true" placeholder="Enter new goal area." type="text" name="category">
                               </div>
                               <button type="submit" class="btn btn-success">Add Goal Area</button>
                               
                         </form>
                        <!-- END add area -->
						
                        <!-- BEGIN area table -->
                        <h2>Program Areas <small>Delete not yet available</small></h2>
                        <form name="arealist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_add_program_area.php"; ?>" method="get">
                      
						<table class="table table-striped table-hover">
                        <?php 
                        //print the header row...
                        echo "<tr><th>Select</th><th>Goal Area</th><th>Deleted</th></tr>\n";
                        while ($area_row=mysql_fetch_array($area_result)) { //current...
                            echo "<tr>\n";
                            echo "<td>\n\t<button class=\"btn btn-small btn-danger\">Delete</button>";
                            echo "\n\t<input type=\"text\" hidden name=\"area\" value=\"" . $area_row['name'] . "\">";
                            
                            echo "<td>" . $area_row['name'] . "</td>";
                            echo "<td>" . $area_row['is_deleted']  ."</td>\n";
                            echo "</tr>\n";
                            
                        }
                        ?>
                        </table>
                       
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                             <?php
                                //if we have permissions also allow delete and set all.
                                if($permission_level <= $IPP_MIN_DELETE_AREA_PERMISSION) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"delete\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          
                        </form>
                        <!-- end area table -->
                        
               
           
       
        </div>
        <?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
