<?php

/** @file
 * @brief 	display objectives related to a goal belonging to a student
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. Filter input
 * 2. Escape output
 * 3. Confirm spellcheck
 * 4. check navbar
 * 5. Datepicker is here; use it as a model for other pages
 */ 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within



/*   INPUTS: $_GET['student_id']
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

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

//$student_id="";
//if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
//if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

//find the student owner of this objective...
$long_term_goal_query="SELECT * FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string($_GET['goal_id']);
$long_term_goal_result=mysql_query($long_term_goal_query);
if(!$long_term_goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$long_term_goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$long_term_goal_row=mysql_fetch_array($long_term_goal_result);
$student_id=$long_term_goal_row['student_id'];


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

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//This works by using regular expressions to get rid of dangerous, meaningful stuff (a filter)
//check if we are adding an objective...
if(isset($_GET['add_objective']) && $have_write_permission) {
  $description=strip_tags($_GET['description']);
  $description=eregi_replace("\r\n",' ',$description);
  $description=eregi_replace("\r",' ',$description);
  $description=eregi_replace("\n",' ',$description);
  //after the filter, it's escaped output
  $description= mysql_real_escape_string($description);
  //check if we have this objective already...
  $check_query="SELECT * FROM short_term_objective WHERE DESCRIPTION='$description' AND goal_id=" . $long_term_goal_row['goal_id'];
  $check_result=mysql_query($check_query);
  if(mysql_num_rows($check_result) > 0) { $system_message = $system_message . "This objective is already added<BR>"; }
  else {
     $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
     //regular expression to filter date input
     //if it doesn't match the pattern, error
     if(!preg_match($regexp,$_GET['review_date'])) { $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>"; }
     else {
      if($_GET['description']=="") { $system_message = $system_message . "You must supply a description"; } else
      {
       //puts new info into database.. there is not reporting at this point, so nothing is put that field
       $insert_query = "INSERT INTO short_term_objective (goal_id,description,review_date) VALUES (" . $long_term_goal_row['goal_id'] . ",'$description','" . mysql_real_escape_string($_GET['review_date']) . "')";
       $insert_result = mysql_query($insert_query);
       if(!$insert_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
           unset($_GET['review_date']);
          unset($_GET['description']);
      }
     }
    }
  }
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

//if conditions are met, deletes a short term objective
if($have_write_permission && $_GET['delete']) {
    $delete_query = "DELETE from short_term_objective WHERE uid=" . mysql_real_escape_string($_GET['sto']);
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
      $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
      $system_message=$system_message . $error_message;
      IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
      $system_message = $system_message . "Deleted short term objective<BR>";
    }
}
//updates db when student achieves an objective
if($have_write_permission && $_GET['set_achieved']) {
    $achieved_query = "UPDATE short_term_objective SET achieved='Y' WHERE uid=" . mysql_real_escape_string($_GET['sto']);
    $achieved_result = mysql_query($achieved_query);
    if(!$achieved_result) {
      $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$achieved_query'<BR>";
      $system_message=$system_message . $error_message;
      IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
      $system_message = $system_message . "Set short term objective achieved<BR>";
    }
}
//change achieved objective to not achieved
if($have_write_permission && $_GET['set_not_achieved']) {
    $achieved_query = "UPDATE short_term_objective SET achieved='N' WHERE uid=" . mysql_real_escape_string($_GET['sto']);
    $achieved_result = mysql_query($achieved_query);
    if(!$achieved_result) {
      $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$achieved_query'<BR>";
      $system_message=$system_message . $error_message;
      IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {
      $system_message = $system_message . "Set short term objective not achieved<BR>";
    }
}
//try to get all objectives attached to a student in the db
$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}
//try to get all objectives attached to a certain goal
$objectives_query="SELECT * FROM short_term_objective WHERE goal_id=" . mysql_real_escape_string($long_term_goal_row['goal_id']) . " and achieved='Y'";
$objectives_result=mysql_query($objectives_query);
if(!$objectives_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objectives_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
//if conditions are met, find all objectives not completed
$completed_objectives_query="SELECT * FROM short_term_objective WHERE goal_id=" . mysql_real_escape_string($long_term_goal_row['goal_id']) . " and achieved='N'";
$completed_objectives_result=mysql_query($completed_objectives_query);
if(!$completed_objectives_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$completed_objectives_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

/*************************** popup chooser support function ******************/
	/** @createJavaScript($dataSource,$arrayName='rows')
	 * @brief uses javascript to output assessment procedures as a popup
	 * @param unknown $dataSource
	 * @param string $arrayName
	 * @return boolean|string
	 * @todo rename this function to show_ something
	 */    

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

        // check if we have a valid result set
        if(!$numRows=mysql_num_rows($dataSource)){
          $error_message = "PopupChooser: Bad Data Source (" . __FILE__ . ":" . __LINE__ . ")<BR>";
          $system_message= $system_message . $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
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
//interesting. Makes a function available that makes a very long query. we can check in phpmyadmin
    function echoJSServicesArray() {
        global $system_message;
        //get a list of all available goal categories...
        $catlist_query="SELECT typical_short_term_objective.goal FROM long_term_goal RIGHT JOIN typical_long_term_goal ON long_term_goal.goal LIKE typical_long_term_goal.goal RIGHT JOIN typical_short_term_objective ON typical_long_term_goal.ltg_id=typical_short_term_objective.ltg_id WHERE long_term_goal.goal_id=" . mysql_real_escape_string($_GET['goal_id']) . " AND student_id=" . mysql_real_escape_string($_GET['student_id']);
        $catlist_result=mysql_query($catlist_query);
        if(!$catlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$catlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            return;
        } else {
            //$system_message = $system_message . "Rows returned=" . mysql_num_rows($catlist_result) . " Query=$catlist_query<BR><BR>";
            echo createJavaScript($catlist_result,"popuplist");
        }

//This code block was commented out by dev.

        //while($catlist=mysql_fetch_array($catlist_result)) {
           //$objlist_query="SELECT typical_long_term_goal.goal FROM typical_long_term_goal WHERE cid=" . $catlist['cid'] . " AND typical_long_term_goal.is_deleted='N'";
           //$objlist_result = mysql_query($objlist_query);
           //if(!$objlist_result) {
            // $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objlist_query'<BR>";
            // $system_message= $system_message . $error_message;
           //  IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           //} else {
             //call the function to create the javascript array...
           //  echo createJavaScript($objlist_result,$catlist['name']);
           //}
        //}
    }
/************************ end popup chooser support funtion  ******************/

?> 

<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Edit Short Term Objective">
<meta name="author" content="Rik Goldman">
<link rel="shortcut icon" href="./assets/ico/favicon.ico">
<title><?php echo $page_tite ?></title>

<!-- Bootstrap core CSS -->
<link href="./css/bootstrap.min.css" rel="stylesheet">
<!-- Using Jumbotron Style Sheet for NOw -->
 <!-- Custom styles for this template -->
    <link href="./jumbotron.css" rel="stylesheet">
<!-- Bootstrap Datepicker CSS -->
<link href="./css/datepicker.css" rel="stylesheet">

    <style type="text/css" media="screen">
        <!--
            @import "<?php echo IPP_PATH;?>layout/greenborders.css";
        -->
    </style>
    <!-- Nielson's JS -->
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
     <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
     ?>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.medicationlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + ",";
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

</HEAD>
<BODY>
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span> 
					<span class="icon-bar"></span> <span class="icon-bar"></span>
						<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="main.php">MyIEP</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li><a href="main.php">Home</a></li>
					<li><a href="about.php">About</a></li>
					<li><a onclick="history.go(-1);">Back</a></li>



					
					<li><a href="index.php">Logout</a></li>
					<li><a href='<?php echo "long_term_goal_view.php?student_id=$student_id" ?>'>Return to Student</a>
				</ul>
				
				
		<ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Navigation <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="./student_archive.php">Archive</a></li>
                <li><a href="./user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
				
			</div>
		</div>
	</div>

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

                        <center>
                          <table>
                            <tr><td>
                              <center><p class="header">- Short Term Objectives (<?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?>)-</p></center>
                            </td></tr>
                          </table>
                        </center>
                        <BR>

                        <!-- BEGIN add short term objective -->
                        <center>
                        <form name="add_objective" enctype="multipart/form-data" action="<?php echo IPP_PATH . "short_term_objectives.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <center><HR><p class="bold_text">Long Term Goal: <?php echo $long_term_goal_row['goal']; ?> </p><HR></center>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">Edit and click 'Add'.</p>
                           <input type="hidden" name="add_objective" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                           <input type="hidden" name="goal_id" value="<?php echo $long_term_goal_row['goal_id']; ?>">
                          </td>
                        </tr>
                        <tr>
                            <td valign="bottom" bgcolor="#E0E2F2" class="row_default">Description:</td>
                            <td bgcolor="#E0E2F2" class="row_default">
                            <textarea spellcheck="true" name="description" cols="25" rows="3" wrap="hard"><?php echo $_GET['description']; ?></textarea>&nbsp;<img align="top" src="<?php echo IPP_PATH . "images/choosericon.png"; ?>" height="17" width="17" border=0 onClick="popUpChooser(this,document.all.description);" >
                            </td>
                            <td valign="center" align="center" bgcolor="#E0E2F2" rowspan="3" class="row_default"><input type="submit" name="add" value="add"></td>
                        </tr>
                        <tr>
                           <td bgcolor="#E0E2F2" class="row_default">Review Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#E0E2F2" class="row_default">
                           <script type="text/javascript" src="./js/bootstrap-datepicker.js"id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd">$('.datepicker').datepicker()</script>
                               <input type="text" name="review_date" value="<?php echo $_GET['review_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.review_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add short term objective -->

                        <!-- BEGIN  Incomplete Goals -->
                        <center><table width="80%"><tr><td>
                            <p align="left" style="info_text"><b>Not yet Achieved Objective(s)</b></p>
                        </td></tr></table></center>
                        <BR>
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <?php
                        
                        //Output HTML for each objective?
                        //so it's in the array called $goal that needs filtered and escaped
                        while($goal = mysql_fetch_array($completed_objectives_result)) {
                            echo "<tr><td colspan=\"2\" class=\"wrap_top\">";

                            echo "<p class=\"info_text\"><B>Short Term Objective:</B> " . $goal['description'] . "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setCompleted=" . $goal['goal_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\"";
                            echo "></p>";
                            echo "</td></tr>\n";

                            //begin description
                            //width = 100% in first column is workaround for IE6 issue...
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" width=\"100%\"><CENTER>(Next Review: " . $goal['review_date'] . ")</CENTER></td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            //echo "</tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">&nbsp;</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            //echo "<tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><b>Assessment Procedure:</b><blockquote>" . $goal['assessment_procedure'] . "</blockquote></td><td class=\"wrap_right\" rowspan=\"5\" width=\"100\">";
                            echo "<a href=\"" . IPP_PATH . "edit_short_term_objective.php?student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit\" border=\"0\" width=\"100\" height=\"25\" ></a>";

                            echo "</td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><b>Strategies:</b><blockquote>" . $goal['strategies'] . "</blockquote></tr>\n";
                            
                            //"results_and_recommendations" is our concern: make sure all of $goal is filtered and escaped
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><b>Results and Recommendations:</b><blockquote>" . $goal['results_and_recommendations'] . "</blockquote></td></tr>\n";

                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><BR></td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" align=\"right\">";
                            echo "<a href=\"" . IPP_PATH . "review_short_term_objective.php?student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Review\" border=\"0\" width=\"100\" height=\"25\" ></a>";

                            echo "<a href=\"" . IPP_PATH . "short_term_objectives.php?set_achieved=1&goal_id=" . $long_term_goal_row['goal_id'] . "&student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Set+Achieved\" border=\"0\" width=\"100\" height=\"25\" ></a>";

                            echo "<a href=\"" . IPP_PATH . "short_term_objectives.php?delete=1&s&goal_id=" . $long_term_goal_row['goal_id'] . "&student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" width=\"100\" height=\"25\" ></a>";
                            echo "</td></tr>\n";


                            echo "<tr><td bgcolor=\"$colour0\" class=\"wrap_bottom_left\">\n";
                            echo "&nbsp;";
                            echo "</td>\n";
                            echo "<td class=\"wrap_bottom_right\">&nbsp;</td>";
                            echo "</tr>\n";
                            echo "<tr><td>&nbsp;</td><td width=\"100\">&nbsp;</td></tr>";
                        }
                        ?>
                        </table>
                        </center>
                        <!-- END incomplete goals -->

                        <!-- BEGIN  complete Goals -->
                        <center><table width="80%"><tr><td><p align="left" style="info_text"><b>Achieved Objective(s)</b></p></td></tr></table></center>
                        <BR>
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <?php
                        while($goal = mysql_fetch_array($objectives_result)) {
                            echo "<tr><td colspan=\"2\" class=\"wrap_top\">";

                            echo "<p class=\"info_text\"><B>Short Term Objective:</B> " . $goal['description'] . "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setCompleted=" . $goal['goal_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\"";
                            echo "></p>";
                            echo "</td></tr>\n";

                            //begin description
                            //width = 100% in first column is workaround for IE6 issue...
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" width=\"100%\"><CENTER>(Next Review: " . $goal['review_date'] . ")</CENTER></td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            //echo "</tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">&nbsp;</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            //echo "<tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><b>Assessment Procedure:</b><blockquote>" . $goal['assessment_procedure'] . "</blockquote></td><td class=\"wrap_right\" rowspan=\"5\" width=\"100\">";
                            echo "<a href=\"" . IPP_PATH . "edit_short_term_objective.php?student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit\" border=\"0\" width=\"100\" height=\"25\" ></a>";

                            echo "</td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><b>Strategies:</b><blockquote>" . $goal['strategies'] . "</blockquote></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><b>Results and Recommendations:</b><blockquote>" . $goal['results_and_recommendations'] . "</blockquote></td></tr>\n";

                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><BR></td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" align=\"right\">";
                            echo "<a href=\"" . IPP_PATH . "review_short_term_objective.php?student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Review\" border=\"0\" width=\"100\" height=\"25\" ></a>";

                            echo "<a href=\"" . IPP_PATH . "short_term_objectives.php?set_not_achieved=1&goal_id=" . $long_term_goal_row['goal_id'] . "&student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Set+Not+Achieved\" border=\"0\" width=\"100\" height=\"25\" ></a>";

                            echo "<a href=\"" . IPP_PATH . "short_term_objectives.php?delete=1&s&goal_id=" . $long_term_goal_row['goal_id'] . "&student_id=" . $student_id . "&sto=" . $goal['uid'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" width=\"100\" height=\"25\" ></a>";
                            echo "</td></tr>\n";


                            echo "<tr><td bgcolor=\"$colour0\" class=\"wrap_bottom_left\">\n";
                            echo "&nbsp;";
                            echo "</td>\n";
                            echo "<td class=\"wrap_bottom_right\">&nbsp;</td>";
                            echo "</tr>\n";
                            echo "<tr><td>&nbsp;</td><td width=\"100\">&nbsp;</td></tr>";
                        }
                        ?>
                        </table>
                        </center>
                        <!-- END complete goals -->

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center"><table border="0" width="100%"><tr><td><a href="
            <?php
                echo IPP_PATH . "long_term_goal_view.php?goal_id=" . $long_term_goal_row['goal_id'] . "&student_id=" . $long_term_goal_row['student_id'];
            ?>"><img src="<?php echo IPP_PATH; ?>images/back-arrow.png" border=0></a></td><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/homebutton.png" border=0></a></td><td valign="bottom" align="center">Logged in as: <?php echo $_SESSION['egps_username'];?></td><td align="right"><a href="<?php echo IPP_PATH;?>"><img src="<?php echo IPP_PATH; ?>images/logout.png" border=0></a></td></tr></table></td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        <?php print_footer(); ?>
    </BODY>
</HTML>
