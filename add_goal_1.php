<?php

/** @file
 * @brief  purpose is unclear	
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		Filter input
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //everybody (do checks within document)



/**
 * Path for IPP required files.
 */

if(isset($_GET['MESSAGE'])) $system_message = mysql_real_escape_string($_GET['MESSAGE']); else $system_message="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/navbar.php');

header('Pragma: no-cache'); //don't cache this page!

if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'login.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'login.php');
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
$goal_area="";
if(isset($_GET['goal_area'])) $goal_area= $_GET['goal_area'];
if(isset($_POST['goal_area'])) $goal_area = $_POST['goal_area'];

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

$goal_category_name_query= "SELECT * FROM typical_long_term_goal_category where cid=" . mysql_real_escape_string($goal_area) . " limit 1";
$goal_category_name_result=mysql_query($goal_category_name_query);
if(!$goal_category_name_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$goal_category_name_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$goal_category_row= mysql_fetch_array($goal_category_name_result);}

/*************************** popup chooser support function ******************/
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

        //while($catlist=mysql_fetch_array($catlist_result)) {
        if(isset($_GET['goal_area']) && !$_GET['goal_area']=="") {
           $objlist_query="SELECT typical_long_term_goal.goal FROM typical_long_term_goal WHERE cid=" . mysql_real_escape_string($_GET['goal_area']) . " AND typical_long_term_goal.is_deleted='N'";
           $objlist_result = mysql_query($objlist_query);
           if(!$objlist_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objlist_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             //call the function to create the javascript array...
             if(mysql_num_rows($objlist_result)) echo createJavaScript($objlist_result,"popuplist");
           }
         }
        //}
    }
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
    
     <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
     <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
     <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
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

                        <center><table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">-New Goal-</p></center></td></tr><tr><td><center><p class="bold_text"> <?php echo $student_row['first_name'] . " " . $student_row['last_name'] .  ", Permission: " . $our_permission;?></p></center></td></tr></table></center>
                        <BR>

                        <!-- BEGIN add new entry -->
                        <center>
                        <form name="add_long_term_goal" enctype="multipart/form-data" action="<?php echo IPP_PATH . "add_objectives.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">New long term goal</p>
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                           <input type="hidden" name="goal_area" value="<?php echo $goal_area; ?>">
                           <input type="hidden" name="add_goal" value="1">
                          </td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Goal Area:</td>
                           <td bgcolor="#E0E2F2" class="row_default"><b>
                           <?php if(isset($goal_category_row['name']) && $goal_category_row['name'] != "") echo $goal_category_row['name']; else echo "Other"; ?>
                           </b></td>
                           <td valign="center" align="center" rowspan="3" bgcolor="#E0E2F2" class="row_default"><input type="submit" tabindex="3" name="Next" value="Next"></td>
                        </tr>
                        <tr>
                           <td valign="center" bgcolor="#E0E2F2" class="row_default">Goal:</td><td bgcolor="#E0E2F2" class="row_default"><textarea name="description" tabindex="1" cols="23" rows="3" wrap="soft"><?php if(isset($_GET['description'])) echo $_GET['description']; ?></textarea>&nbsp;<img align="top" src="<?php echo IPP_PATH . "images/choosericon.png"; ?>" height="17" width="17" border=0 onClick="popUpChooser(this,document.all.description);" ></td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Review Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                               <input type="text" tabindex="2" size="30" name="review_date" value="<?php  if(isset($_GET['review_date'])) echo $_GET['review_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.review_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add new entry -->


                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center">
              &nbsp;
            </td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        
    <?php print_complete_footer(); ?>
    </BODY>
</HTML>
