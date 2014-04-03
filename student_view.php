<?php

/** @file
 * @brief 	Manage individual student record
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 *   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *   You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * 1. confirm navbar
 */ 
 
 

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //everybody (do checks within document)


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
require_once(IPP_PATH . 'include/navbar.php');


header('Pragma: no-cache'); //don't cache this page!

//@todo make authentication routine a function
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

//This is a bad scenario that shouldn't be possible - just in case, there's an error message

if(!isset($_GET['student_id'])) {
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
}

$student_query = "select * from student where student.student_id=" . $_GET['student_id'];
$student_result = mysql_query($student_query);
if(!$student_query) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$student_row=mysql_fetch_array($student_result);
$student_id=$student_row['student_id'];

//find support members...
$support_member_query = "SELECT * FROM support_list WHERE student_id=" . $_GET['student_id'] . " ORDER BY egps_username";
$support_member_result = mysql_query($support_member_query);
if(!$support_member_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$support_member_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


//find the current coding...
$coding_query = "SELECT * FROM coding WHERE student_id=" . $_GET['student_id'] . " AND end_date IS NULL";
$coding_result = mysql_query($coding_query);
if(!$coding_query) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$coding_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
$coding_row=mysql_fetch_array($coding_result);

//************** validated past here SESSION ACTIVE****************

//get our permissions for this student...
$our_permission = getStudentPermission($_GET['student_id']);

if($our_permission != "READ" && $our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
  //we don't have permission...
  $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
  IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  require(IPP_PATH . 'security_error.php');
  exit();
}

$supervisor_row="";
$supervisor_query = "SELECT * FROM supervisor WHERE student_id=" . mysql_real_escape_string($_GET['student_id']) . " AND end_date IS NULL";
$supervisor_result = mysql_query($supervisor_query);
if(!$supervisor_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$supervisor_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    //In theory, should be only one row - this fetches it
    $supervisor_row=mysql_fetch_array($supervisor_result);
}

$school_row="";


$school_query = "SELECT * FROM school_history LEFT JOIN school on school_history.school_code=school.school_code WHERE end_date IS NULL AND student_id='" . $_GET['student_id'] . "'";
$school_result = mysql_query($school_query);
if(!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    //there is only one row (or should be...so get it.)
    $school_row=mysql_fetch_array($school_result);
}

/** @fn get_age_by_date($yyyymmdd)
 *  @brief Assuming no errors, calculate age based on DOB and query date.
 *  @todo Reformat date and validation.
 */
//make sure they entered a valid date (mm-dd-yyyy)
function get_age_by_date($yyyymmdd)
{
    global $system_message;
    $bdate = explode("-", $yyyymmdd);
    $dob_month=$bdate[1]; $dob_day=$bdate[2]; $dob_year=$bdate[0];
    if (checkdate($dob_month, $dob_day, $dob_year)) {
        $dob_date = "$dob_year" . "$dob_month" . "$dob_day";
        $age = floor((date("Ymd")-intval($dob_date))/10000);
        if (($age < 0) or ($age > 114)) {
            return $age . "<BR> -->Age warning: Negative or Zero (check D.O.B)<--";
        }
        return $age;
    }
    return "-unknown-";
}


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Rik Goldman" >
    <link rel="shortcut icon" href="./assets/ico/favicon.ico">
    <TITLE><?php echo $page_title; ?></TITLE>
    
    
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
<!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./jumbotron.css" rel="stylesheet">

   

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
    <BODY>
 
 <!-- Bootstrap fixed navbar-->
 <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="main.php">MyIEP</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="main.php">Home</a></li>
            <li><a href="index.php">Logout</a></li>
            <li><a href="about.php">About</a></li>
            <li><a onclick="history.go(-1);">Back</a></li>
            <li><a href=<?php echo "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&file=ipp.pdf"; ?>>Get PDF</li></a>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Records: <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?><b class="caret"></b></a>
              <ul class="dropdown-menu">
              	<li><a href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>">Goals</a></li>
              	<li class="divider"></li>
              	<li><a href="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>">Guardians</a></li>
              	<li><a href="<?php echo IPP_PATH . "strength_need_view.php?student_id=" . $student_row['student_id'];?>">Strengths &amp; Needs</a></li>
              	<li><a href="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>">Coordination of Services</a></li>
              	<li><a href="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>">Achievement Level</a></li>
              	<li><a href="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>">Medical Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>">Medication</a></li>
              	<li><a href="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>">Support Testing</a></li>
              	<li><a href="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>">Background Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>">Year-End Review</a></li>
              	<li><a href="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>">Anecdotals</a></li>
              	<li><a href="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>">Assistive Techology</a></li>
              	<li><a href="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>">Transition Plan</a></li>
              	<li><a href="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>">Accomodations</a></li>
              	<li><a href="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>">Snapshots</a></li></ul>
            </ul>
             
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
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
         <!--/.nav-collapse -->
        <!--<div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" nctype="multipart/form-data" action="jumbotron.php" method="post">
            <div class="form-group">
              <input type="text" placeholder="User Name" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control" name="PASSWORD" value="">
            </div>
            <button type="submit" value="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
 
 <!-- End Navbar -->
        <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <h1>Student View: <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?></h1>
        <p>Current Age: <?php echo get_age_by_date($student_row['birthday']) ?></p>
		<p>Grade: <?php echo $student_row['current_grade']; ?></p>
		<p>User: <?php echo $_SESSION['egps_username'] ?> (Access Level: <?php echo $our_permission ?>)
<?php if($school_row['school_name']=="") echo "<p>Archived Student</p>"  ?>
<!-- Placeholder in event of system message -->        
<?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>
        <p><a class="btn btn-primary btn-lg" href="<?php echo IPP_PATH . "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&file=ipp.pdf";?>" target="_blank" role="button">View IEP &raquo;</a></p>
  
      </div>
    </div>
 
 <!-- First Row -->
  <div class="container">               
<div class="row">
<div class="col-md-4">
<h2>Quick Access</h2>
<h4>Goals &amp; Objectives</h4>
<p><a class="btn btn-default btn-lg" href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>" role="button">View &raquo;</a></p>
<hr>
<h3>More Records: <small><?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?></small></h3>
<form>

<select name="records" onchange="window.location.href= this.form.records.options[this.form.records.selectedIndex].value" class="form-control">
<option>Select Record to View</option>
<option value="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>">Goals &amp; Objectives</option>
<option class="divider"></option>
<option  value="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>">Guardians</option>
<option value="strength_need_view.php?student_id=<?php echo $student_row['student_id'];?>">Strengths &amp; Needs</option>
<option value="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>">Coordination of Services</option>
<option value="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>">Achievement Level</option>
<option value="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>">Medical Information</option>
<option value="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>">Medication</option>
<option value="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>">Support Testing</option>
<option value="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>">Background Information</option>
<option value="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>">Year-End Review</option>
<option value="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>">Anecdotals</option>
<option value="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>">Assistive Techology</option>
<option value="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>">Transition Plan</option>
<option value="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>">Accomodations</option>
<option value="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>">Snapshots</option>
<option value="<?php echo IPP_PATH , "school_history.php?student_id=" . $student_row['student_id'];?>">School History</option>
</select>
</form>
              
</div><!-- End Column -->
<div class="col-md-4">
<h2>General Information</h2>
<h4><small>Name: </small><?php echo $student_row['first_name'] . " " . $student_row['last_name'];?></h4>
<h4><small>Gender: </small><?php if($student_row['gender'] =="F") echo "Female"; else echo "Male";?></h4>
<h4><small>Birthdate: </small><?php echo $student_row['birthday'];?></h4>
<h4><small>Grade: </small><?php
                                      switch ($student_row['current_grade']) {
                                        case '0':
                                           echo "K or Pre-K";
                                           break;
                                        case '-1':
                                           echo "District Program";
                                           break;
                                        default:
                                            echo  $student_row['current_grade'];
                                      }
                                ?></h4>
<h3><small>Student Number: </small><?php echo $student_row['prov_ed_num'];?></h4>

<p><a class="btn btn-default btn-lg" href="<?php echo IPP_PATH . "edit_general.php?student_id=" . $student_id ?>" role="button">Edit &raquo;</a></p>
<!-- End col --></div>
<div class="col-md-4">
<h2>IEP Team</h2>
<h4><small>Case Manager: </small><?php echo $supervisor_row['egps_username'];?></h4>
<p><a class="btn btn-default btn-lg" href="<?php echo IPP_PATH . "supervisor_view.php?student_id=" . $_GET['student_id'];?>" role="button">Update Case Manager &raquo;</a></p>
<h4><small>Support Team: </small></h4>
<table class="table table-striped">
<?php if(mysql_num_rows($support_member_result) <=0) {
     echo "<tr><td>none specified</td></tr>";
     }
?>
<?php 
while($support_member_row=mysql_fetch_array($support_member_result)) {
	echo "<tr><td>" . $support_member_row['egps_username'] . "</td>";
	echo  "<td>" . $support_member_row['permission'] . "</td>";
 	if($support_member_row['support_area'] == "")
		echo "<td>No area assigned</td>";
	else
		echo "<td>" . $support_member_row['support_area'] . "</td></tr>";                         
	 } ?>
</table>
<p><a class="btn btn-default btn-lg" href="<?php echo IPP_PATH . "modify_ipp_permission.php?student_id=" . $_GET['student_id']; ?>" role="button">Update Team &raquo;</a></p>

<!-- End col --></div> 
 <!-- Second Row -->
<div class="container">               
<div class="row">
<div class="col-md-4">
<h2>School Information</h2>
<h4><small>School Name: </small><?php
if($school_row['school_name']=="")
echo "Archived Student</h4>";
else
echo $school_row['school_name'] . "</h4> <p>(since " . $school_row['start_date'] . ")</p>";
?>

 <p><a class="btn btn-default btn-lg" href="<?php echo IPP_PATH . "school_history.php?student_id=" . $student_id ?>" role="button">Update School History &raquo;</a></p>
 
 
</div><!--End Column -->


<div class="col-md-4">

</div><!-- End Column -->
<!-- End Row -->
</div>
<!-- Bottom Navbar -->
<div class="navbar navbar-inverse navbar-fixed-bottom" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="main.php">MyIEP</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="main.php">Home</a></li>
            <li><a href="index.php">Logout</a></li>
            <li><a href="about.php">About</a></li>
            <li><a onclick="history.go(-1);">Back</a></li>
            <li><a href=<?php echo "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&file=ipp.pdf"; ?>>Get PDF</li></a>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Records: <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?><b class="caret"></b></a>
              <ul class="dropdown-menu">
              	<li><a href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>">Goals</a></li>
              	<li class="divider"></li>
              	<li><a href="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>">Guardians</a></li>
              	<li><a href="<?php echo IPP_PATH . "strength_need_view.php?student_id=" . $student_row['student_id'];?>">Strengths &amp; Needs</a></li>
              	<li><a href="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>">Coordination of Services</a></li>
              	<li><a href="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>">Achievement Level</a></li>
              	<li><a href="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>">Medical Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>">Medication</a></li>
              	<li><a href="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>">Support Testing</a></li>
              	<li><a href="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>">Background Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>">Year-End Review</a></li>
              	<li><a href="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>">Anecdotals</a></li>
              	<li><a href="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>">Assistive Techology</a></li>
              	<li><a href="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>">Transition Plan</a></li>
              	<li><a href="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>">Accomodations</a></li>
              	<li><a href="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>">Snapshots</a></li></ul>
            </ul>
             
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
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
         <!--/.nav-collapse -->
        <!--<div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form" nctype="multipart/form-data" action="jumbotron.php" method="post">
            <div class="form-group">
              <input type="text" placeholder="User Name" class="form-control" value="<?php echo $LOGIN_NAME;?>">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control" name="PASSWORD" value="">
            </div>
            <button type="submit" value="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
                          

                       
                      

                       
           
            
           
           
            
       

<!-- To end main container -->        
 </div>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    </BODY>
</HTML>
