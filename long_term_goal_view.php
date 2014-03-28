<?php
/*! @file
 *  @brief 	View goals and access page to edit progress
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		
 * 1. Filter input
 * 2. Make sure the duplicate in include/ can be safely removed
 */


$MINIMUM_AUTHORIZATION_LEVEL = 100;




if(isset($system_message)) $system_message = $system_message; else $system_message = "";


define('IPP_PATH','./');

//* @remark required files
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');
require_once(IPP_PATH . 'include/supporting_functions.php');
//require_once("Numbers/Roman.php"); //require pear roman numerals class

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

$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "You've entered this page without supplying a valid student id. Fatal, quitting";
   exit();
}

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

if(!isset($_GET['student_id']) || $_GET['student_id'] == "") {
    //ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
} else {
    $student_id=$_GET['student_id'];
}

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

//check if we are adding...
if(isset($_GET['next']) && $have_write_permission) {
  //if(!isset($_GET['goal_area']) || $_GET['goal_area'] == "") {
  //  $system_message = $system_message . "You must supply a goal area<BR>";
  //} else {
     header("Location: ./add_goal_1.php?goal_area=" . $_GET['goal_area'] . "&student_id=" . $student_id);
  //}
}

if(isset($_GET['setUncompleted']) && $have_write_permission) {
   $update_query = "UPDATE long_term_goal SET is_complete='N' WHERE goal_id=" . mysql_real_escape_string($_GET['setUncompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['deleteSTO']) && $have_write_permission) {
   $update_query = "DELETE FROM short_term_objective WHERE uid=" . mysql_real_escape_string($_GET['deleteSTO']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['deleteLTG']) && $have_write_permission) {
   //delete the sto's
   $update_query = "DELETE FROM short_term_objective WHERE goal_id=" . mysql_real_escape_string($_GET['deleteLTG']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }  else {
     //delete the ltg's
     $update_query = "DELETE FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string($_GET['deleteLTG']);
     $update_result = mysql_query($update_query);
     if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } //else { $system_message = $system_message . "OKIE DOKIE<BR>"; }
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['setCompleted']) && $have_write_permission) {
   $update_query = "UPDATE long_term_goal SET is_complete='Y' WHERE goal_id=" . mysql_real_escape_string($_GET['setCompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['setSTOCompleted']) && $have_write_permission) {
   $update_query = "UPDATE short_term_objective SET achieved='Y' WHERE uid=" . mysql_real_escape_string($_GET['setSTOCompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['setSTOUncompleted']) && $have_write_permission) {
   $update_query = "UPDATE short_term_objective SET achieved='N' WHERE uid=" . mysql_real_escape_string($_GET['setSTOUncompleted']);
   $update_result = mysql_query($update_query);
   if(!$update_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if(isset($_GET['deleteGoal']) && $have_write_permission) {
   $delete_query = "DELETE FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string($_GET['deleteGoal']);
   $delete_result = mysql_query($delete_query);
   if(!$delete_result) {
       $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
       $system_message=$system_message . $error_message;
       IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
   //else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

$long_goal_query = "SELECT * FROM long_term_goal WHERE student_id=$student_id ORDER BY area ASC, is_complete DESC, goal ASC";
$long_goal_result = mysql_query($long_goal_query);
if(!$long_goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$long_goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$area_type_query = "SELECT  * from  typical_long_term_goal_category WHERE is_deleted='N'";
$area_type_result = mysql_query($area_type_query);
if(!$area_type_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_type_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

/*************************** popup chooser support function ******************/

/*
    function createJavaScript($dataSource,$arrayName='rows'){
      // validate variable name
      if(!is_string($arrayName)){
        $system_message = $system_message . "Error in popup chooser support function name supplied not a valid string  (" . __FILE__ . ":" . __LINE__ . ")";
        return FALSE;
      }

    // initialize JavaScript string
      $javascript='<!--Begin popup array--><script>var '.$arrayName.'=[];';

    // check if $dataSource is a file or a result set
      if(is_file($dataSource)){
       
        // read data from file
        $row=file($dataSource);

        // build JavaScript array
        for($i=0;$i<count($row);$i++){
          $javascript.=$arrayName.'['.$i.']="'.trim($row[$i]).'";';
        }
      }

      // read data from result set
      else{

        // check if we have a zero resultant set
        if(!$numRows=mysql_num_rows($dataSource)){
          //zero result set (create empty array)
          $javascript.='</script><!--End popup array-->'."\n";
          return $javascript;
        }
        for($i=0;$i<$numRows;$i++){
          // build JavaScript array from result set
          $javascript.=$arrayName.'['.$i.']="';
          $tempOutput='';
          //output only the first column
          $row=mysql_fetch_array($dataSource);

          $tempOutput.=$row[0].' ';

          $javascript.=trim($tempOutput).'";';
        }
      }
      $javascript.='</script><!--End popup array-->'."\n";

      // return JavaScript code
      return $javascript;
    }

    function echoJSServicesArray() {
        global $system_message;
        //get a list of all available goal categories...
        $catlist_query="SELECT * FROM typical_long_term_goal_category where is_deleted='N' ORDER BY name ASC";
        $catlist_result=mysql_query($catlist_query);
        if(!$catlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$catlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            return;
        }

        while($catlist=mysql_fetch_array($catlist_result)) {
           $objlist_query="SELECT typical_long_term_goal.goal FROM typical_long_term_goal WHERE cid=" . $catlist['cid'] . " AND typical_long_term_goal.is_deleted='N'";
           $objlist_result = mysql_query($objlist_query);
           if(!$objlist_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objlist_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             //call the function to create the javascript array...
             echo createJavaScript($objlist_result,$catlist['name']);
           }
        }
    }

*/
/************************ end popup chooser support funtion  ******************/

?> 

<!DOCTYPE HTML>
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    
     <!--script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
     <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script-->
     <?php
       //output the javascript array for the chooser popup
       //echoJSServicesArray();
     ?>
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }

      function noSelection() {
          alert("You must choose a goal category to enable the chooser"); return false;
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
                    <tr><td>
                    <center><?php navbar("student_view.php?student_id=$student_id"); ?></center>
                    </td></tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">- Goals -</p></center></td></tr><tr><td><center><p class="bold_text"> <?php echo $student_row['first_name'] . " " . $student_row['last_name'] .  ", Permission: " . $our_permission;?></p></center></td></tr></table></center>
                        <BR>

                        <!-- BEGIN add new entry -->
                        <center>
                        <form name="add_long_term_goal" enctype="multipart/form-data" action="<?php echo IPP_PATH . "long_term_goal_view.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">New long term goal</p>
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Goal Area:</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                           <select name="goal_area">
                            <option value="">Choose...</option>
                            <?php
                            while ($area_row = mysql_fetch_array($area_type_result)) {
                               echo "<option value=\"" . $area_row['cid'];
                               if(isset($_GET['goal_area']) && $area_row['name'] == $_GET['goal_area']) echo "\" SELECTED";
                               echo  "\" onclick=\"popuplist=" . $area_row['name'] . ".slice();\">" . $area_row['name'] . "</option>\n";
                            }
                            ?>
                            <option value="">Other</option>
                            </select>
                           </td>
                           <td valign="center" align="center" bgcolor="#E0E2F2" class="row_default"><input type="submit" name="next" value="Add Goal"></td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add new entry -->

                        <?php $colour0="#DFDFDF"; $colour1="#CCCCCC"; ?>

                        <HR>
                        <!-- BEGIN  Goals -->
                        <table width="100%"><tr><td><p class="header" align="left">&nbsp;Goal(s):</p></tr></table>
                        <BR>
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <?php
                        //check if we have no goals...we need to end this table in this case.
                        if(mysql_num_rows($long_goal_result) == 0 ) {
                          echo "<tr><td>&nbsp;</td></tr></table></center>";
                        }
                        $goal_num=1;
                        while($goal = mysql_fetch_array($long_goal_result)) {
                          echo "<table width=\"90%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"goal_text\"><b>Area:</b>&nbsp;<a class=\"large\" href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
                          if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                          else echo "onClick=\"return changeStatusCompleted();\"";
                          echo " class=\"small\">" . $goal['area'] . "</a></td></tr></table>";
                          echo "<table width=\"80%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
                          echo "<tr><td align=\"right\" colspan=\"3\"";
                          $today = time(); #today's date in seconds since January 1, 1970
                          $date_split = split("-",$goal['review_date']);
                          $date_seconds = mktime(0,0,0,$date_split[1],$date_split[2],$date_split[0]); //since j1,1970
                          if($today >= $date_seconds && $goal['is_complete'] != 'Y') {
                            echo " class=\"goal_date_past\">Review date (expired): <a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\">";
                            echo "<b>" . $goal['review_date'] . "</b></a></td></tr>";
                          }
                          else {
                            echo " class=\"goal_date_future\">Review date: <a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\">";
                            echo "<b>" . $goal['review_date'] . "</b></a></td></tr>";
                          }
                          echo "<tr><td valign=\"middle\" width=\"20\">";
                          if($goal['is_complete']=='Y') {
                            echo "<img src=\"" . IPP_PATH . "images/checkmark_black.png\" align=\"left\" border=\"0\" width=\"15\" height=\"15\">&nbsp;&nbsp;";
                          } else {
                            echo "<img src=\"" . IPP_PATH . "images/arrow_black.png\" align=\"left\" border=\"0\" width=\"15\" height=\"15\">&nbsp;&nbsp;";
                          }
                          echo "<td valign=\"top\" width=\"15\" class=\"goal_number\">$goal_num</td>";
                          $goal_num++;
                          echo "</td><td valign=\"top\" class=\"goal_text\" bgcolor=\"#E0E2F2\">" . checkspelling($goal['goal']);
                          //output the complete/uncomplete button...
                          if($goal['is_complete'] == 'Y') {
                            echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setUncompleted=" . $goal['goal_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\"";
                            echo " class=\"small\">Set Uncompleted</a>";
                          } else {
                            echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setCompleted=" . $goal['goal_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\"";
                            echo " class=\"small\">Set Completed</a>";
                          }

                          //output the add objectives button.
                          echo "&nbsp;<a href=\"" . IPP_PATH . "add_objectives.php?&student_id=" . $student_id  . "&lto=" . $goal['goal_id'] . "\"";
                          if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                          else echo "onClick=\"return changeStatusCompleted();\"";
                          echo " class=\"small\">Add Objective</a>";

                          //output the edit button.
                          echo "&nbsp;<a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id']  . "\"";
                          if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                          else echo "onClick=\"return changeStatusCompleted();\"";
                          echo " class=\"small\">Edit</a>";

                          echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id  . "&deleteLTG=" . $goal['goal_id'] . "\"";
                          if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                          else echo "onClick=\"return changeStatusCompleted();\"";
                          echo " class=\"small\">Delete</a>";

                          //finish...
                          echo "</p></td></tr>";
                          echo "</table><BR>\n";

                          //short term objectives
                          $short_term_objective_query = "SELECT * FROM short_term_objective WHERE goal_id=" . $goal['goal_id'] . " ORDER BY achieved ASC";
                          $short_term_objective_result = mysql_query($short_term_objective_query);
                          //check for error
                          if(!$short_term_objective_result) {
                            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$short_term_objective_query'<BR>";
                            $system_message=$system_message . $error_message;
                            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                            echo $system_message;
                          } else {
                            //output this note...
                            //check if we have no notes
                            if(mysql_num_rows($short_term_objective_result) <= 0 ) {
                              echo "<table width=\"60%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
                              echo "<tr><td colspan=\"2\" class=\"objective_text\">No Objectives Added</td></tr>";
                              echo "</table><BR>\n";
                            }
                            $obj_num=1;
                            while ($short_term_objective_row = mysql_fetch_array($short_term_objective_result)) {

                              //output the objective title
                              echo "<table width=\"65%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
                              //begin review date
                              echo "<tr><td align=\"right\" colspan=\"3\"";
                              $now = time(); #today's date in seconds since January 1, 1970
                              $date_split = split("-",$short_term_objective_row['review_date']);
                              $date_seconds = mktime(0,0,0,$date_split[1],$date_split[2],$date_split[0]); //since j1,1970
                              if($now > $date_seconds && $short_term_objective_row['achieved']!='Y') { //$today >= $date_seconds) {
                                        echo " class=\"objective_date_past\">Review Date (expired): <a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\"";
                                        if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                                        else echo "onClick=\"return changeStatusCompleted();\">";
                                        echo "<b>" . $short_term_objective_row['review_date'] . "</b></a></td></tr>";
                              }
                              else {
                                   echo " class=\"objective_date_future\">Review date: <a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
                                   if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                                   else echo "onClick=\"return changeStatusCompleted();\">";
                                   echo "<b>" . $short_term_objective_row['review_date'] . "</b></a></td></tr>";
                              }
                              //end review date
                              echo "<tr><td width=\"20\">";
                              if($short_term_objective_row['achieved']=='Y') {
                                echo "<img src=\"" . IPP_PATH . "images/checkmark_black.png\" border=\"0\" width=\"15\" height=\"15\">";
                              } else {
                                echo "<img src=\"" . IPP_PATH . "images/arrow_black.png\" border=\"0\" width=\"15\" height=\"15\">";
                              }
                              echo "<td valign=\"top\" width=\"15\" class=\"goal_number\">" . $obj_num . ")&nbsp;</td>";
                              $obj_num++;
                              echo "</td><td class=\"objective_text\">" . checkspelling($short_term_objective_row['description']);

                              //output the complete/uncomplete button...
                              if($short_term_objective_row['achieved'] == 'Y') {
                                echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setSTOUncompleted=" . $short_term_objective_row['uid'] . "\"";
                                if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                                else echo "onClick=\"return changeStatusCompleted();\"";
                                echo " class=\"small\">Set Uncompleted</a>";
                              } else {
                                echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setSTOCompleted=" . $short_term_objective_row['uid'] . "\"";
                                if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                                else echo "onClick=\"return changeStatusCompleted();\"";
                                echo " class=\"small\">Set Completed</a>";

                              }

                              //output the add edit button.
                              echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
                              if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                              else echo "onClick=\"return changeStatusCompleted();\"";
                              echo " class=\"small\">Edit</a>";

                              echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&deleteSTO=" . $short_term_objective_row['uid'] . "\"";
                              if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                              else echo "onClick=\"return changeStatusCompleted();\"";
                              echo " class=\"small\">Delete</a>";

                              echo "</td></tr>";
                              echo "</table>";

                              //output the results /assmt / etc...
                              echo "<table width=\"60%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
                              echo "<tr><td class=\"wrap_top_notext\" colspan=\"2\">&nbsp;</td></tr>";
                              echo "<tr><td class=\"wrap_left\">";
                              //output the actual data
                              echo "Assessment Procedure:";

                              //output the add edit button.
                              echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
                              if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                              else echo "onClick=\"return changeStatusCompleted();\"";
                              echo " class=\"small\">Edit</a>";

                              echo "<BR>";
                              echo "<blockquote>" . checkspelling($short_term_objective_row['assessment_procedure']) . "</blockquote>";
                              echo "Strategies:" ;

                              //output the add edit button.
                              echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
                              if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                              else echo "onClick=\"return changeStatusCompleted();\"";
                              echo " class=\"small\">Edit</a>";

                              echo "<BR>";
                              echo "<blockquote>" . checkspelling($short_term_objective_row['strategies']) . "</blockquote>";
                              echo "Progress Review:" ;

                              //output the add edit button.
                              echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id  . "\"";
                              if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                              else echo "onClick=\"return changeStatusCompleted();\"";
                              echo " class=\"small\">Edit</a>";

                              echo "<BR>";
                              echo "<blockquote>" . checkspelling($short_term_objective_row['results_and_recommendations']) . "</blockquote>";
                              //end output the actual data
                              echo "</td><td class=\"wrap_right\">&nbsp;</td></tr>";
                              echo "<tr><td class=\"wrap_bottom_left\">&nbsp;</td><td class=\"wrap_bottom_right\">&nbsp;</td></tr>";
                              echo "</table><BR>\n";
                            }
                          }
                        }
                        ?>
                        
                        <!-- END  goals -->

                        <!-- commented because can't find opening tag </div> -->
                         
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">
              <?php navbar("student_view.php?student_id=$student_id"); ?>
            </td>
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
