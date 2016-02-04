<?php
/** @file
 * @brief 	modify short-term objective belonging to a student goal
 * @copyright 	2014 Chelsea School
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo
 * 1. Filter input
 * 2. Change heading - unnecessary and inconsistent
 */

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; // everybody check within

/*
 * INPUTS:
 * $_GET['sto']
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

define ( 'IPP_PATH', './' );

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/supporting_functions.php';

require_once (IPP_PATH . 'include/navbar.php');

header ( 'Pragma: no-cache' ); // don't cache this page!

if (isset ( $_POST ['LOGIN_NAME'] ) && isset ( $_POST ['PASSWORD'] )) {
	if (! validate ( $_POST ['LOGIN_NAME'], $_POST ['PASSWORD'] )) {
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
		require (IPP_PATH . 'index.php');
		exit ();
	}
} else {
	if (! validate ()) {
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
		require (IPP_PATH . 'index.php');
		exit ();
	}
}
// ************* SESSION active past here **************************

// check permission levels
$permission_level = getPermissionLevel ( $_SESSION ['egps_username'] );
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
	$system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER ['REMOTE_ADDR'] . ")";
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	require (IPP_PATH . 'security_error.php');
	exit ();
}

// find the student owner of this objective...
$goal_query = "SELECT * FROM short_term_objective LEFT JOIN long_term_goal ON short_term_objective.goal_id=long_term_goal.goal_id WHERE short_term_objective.uid=" . mysql_real_escape_string ( $_GET ['sto'] );
$goal_result = mysql_query ( $goal_query );
if (! $goal_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$goal_query'<BR>";
	$system_message = $system_message . $error_message;
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
}
$goal_row = mysql_fetch_array ( $goal_result );
$student_id = $goal_row ['student_id'];

$our_permission = getStudentPermission ( $student_id );
if ($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
	// we have write permission.
	$have_write_permission = true;
} else {
	$have_write_permission = false;
}

if ($have_write_permission && isset ( $_GET ['edit'] )) {
	$update_query = "UPDATE short_term_objective  SET goal_id=" . $goal_row ['goal_id'] . ",review_date='" . mysql_real_escape_string ( $_GET ['review_date'] ) . "',description='" . mysql_real_escape_string ( $_GET ['description'] ) . "',results_and_recommendations='" . mysql_real_escape_string ( $_GET ['results_and_recommendations'] ) . "',strategies='" . mysql_real_escape_string ( $_GET ['strategies'] ) . "',assessment_procedure='" . mysql_real_escape_string ( $_GET ['assessment_procedure'] ) . "' WHERE uid=" . mysql_real_escape_string ( $_GET ['sto'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	} else {
		// lets to to the sto page
		header ( "Location: " . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id );
	}
}

/*
 * now we need to rerun the goal query because we may have added above
 * if brain wasn't off might be able to figure out a better way to do this
 * find the student owner of this objective...
 */

$goal_query = "SELECT * FROM short_term_objective LEFT JOIN long_term_goal ON short_term_objective.goal_id=long_term_goal.goal_id WHERE short_term_objective.uid=" . mysql_real_escape_string ( $_GET ['sto'] );
$goal_result = mysql_query ( $goal_query );
if (! $goal_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$goal_query'<BR>";
	$system_message = $system_message . $error_message;
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
}
$goal_row = mysql_fetch_array ( $goal_result );
$student_id = $goal_row ['student_id'];

// ************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string ( $student_id );
$student_result = mysql_query ( $student_query );
if (! $student_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$student_query'<BR>";
	$system_message = $system_message . $error_message;
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
} else {
	$student_row = mysql_fetch_array ( $student_result );
}

?>

<!DOCTYPE HTML>
<html lang="en">
<!-- Start Head -->
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Edit Short Term Objective">
<meta name="author" content="Rik Goldman">
<link rel="shortcut icon" href="./assets/ico/favicon.ico">

<title><?php echo $page_title ?></title>

<!-- Bootstrap core CSS -->
<link href="./css/bootstrap.min.css" rel="stylesheet">
<!-- Using Jumbotron Style Sheet for NOw -->
<!-- Custom styles for this template -->
<link href="css/jumbotron.css" rel="stylesheet">
<!-- Bootstrap Datepicker CSS -->
<link href="./css/datepicker.css" rel="stylesheet">


<script>
      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
</SCRIPT>

	<?php print_datepicker_depends(); ?>

</HEAD>

<BODY>

    <div class="navbar navbar-inverse navbar-fixed-top"
        role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle"
                    data-toggle="collapse"
                    data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span> <span
                        class="icon-bar"></span> <span class="icon-bar"></span>
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
                    <li><a
                        href='<?php echo "long_term_goal_view.php?student_id=$student_id" ?>'>Return
                            to Student</a>
                
                </ul>


                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown"><a href="#"
                        class="dropdown-toggle" data-toggle="dropdown">Administration
                            <b class="caret"></b>
                    </a>
                        <ul class="dropdown-menu">
                            <li><a href="./manage_student.php">Students</a></li>
                            <li class="divider"></li>
                            <li><a href="change_ipp_password.php">Reset
                                    Password</a></li>
                            <!-- <li><a href="superuser_add_goals.php">Goals Database</a></li>-->
                            <li><a href="./student_archive.php">Archive</a></li>
                            <li><a href="./user_audit.php">Audit</a></li>
                            <li><a href="superuser_manage_coding.php">Manage
                                    Codes</a></li>
                            <li><a href="school_info.php">Manage Schools</a></li>
                            <li><a href="superuser_view_logs.php">View
                                    Logs</a></li>
                        </ul></li>
                </ul>

            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="page-header">
        <p>&nbsp;</p>
        <p align="center">
            <em>Edit Short Term Objective</em>
        </p>
        <p align=center><?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?></p>
    </div>

    <div class=container>
        <form name="edit_objective" enctype="multipart/form-data"
            action='<?php echo IPP_PATH . "edit_short_term_objective.php"; ?>'
            method="get"
            <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
            <!-- Begin Single Row -->
            <div class="row">
                <!-- Begin Left Column -->
                <div class="col-md-6">
                    <fieldset>
                        <label>Short Term Objective</label>
                        <p>
                            <textarea spellcheck="true"
                                name="description" cols="40" rows="3"
                                wrap="soft"><?php echo $goal_row['description']; ?></textarea>
                        </p>
                        <label>Progress Review</label>
                        <p>
                            <textarea spellcheck="true"
                                name="results_and_recommendations"
                                cols="40" rows="10" autofocus
                                wrap="soft"><?php echo stripslashes($goal_row['results_and_recommendations']); ?></textarea>
                        </p>
                        <div class="form-actions">
                            <button type="submit"
                                class="btn btn-primary">Save changes</button>
                        </div>

                        <!-- <input id="submit" name="submit" type="submit" value="Submit"> -->
                    </fieldset>
                </div>
                <!-- Begin Right Column -->
                <div class="col-md-6">
                    <fieldset>
                        <!-- Hidden Fields -->
                        <input type="hidden" name="student_id"
                            value="<?php echo $student_id; ?>"> <input
                            type="hidden" name="sto"
                            value="<?php echo $goal_row['uid']; ?>"> <input
                            type="hidden" name="edit" value="1"> <label>Long
                            Term Goal</label>
                        <p>
                            <textarea spellcheck="true" disabled
                                name="text" cols="40" rows="3"
                                wrap="soft"><?php echo $goal_row['goal']; ?></textarea>
                        </p>
                        <label>Review Date</label>
                        <!-- Datepicker was here -->
                        <p>
                            <input class="datepicker" id="datepicker"
                                type=datepicker name="review_date"
                                data-provide="datepicker"
                                data-date-format="yyyy-mm-dd"
                                value="<?php echo $goal_row['review_date']; ?>">
                        </p>
                        <!-- Lagacy Datepicker: &nbsp;<img	src='<?php echo IPP_PATH . "images/calendaricon.gif"; ?>' height="17" width="17" border="0" onClick="popUpCalendar(this, document.all.review_date, 'yyyy-m-dd', 0, 0)" alt="calendar">-->
                        <label>Assessment Procedure</label>
                        <p>
                            <textarea spellcheck="true"
                                spellcheck="true"
                                name="assessment_procedure"
                                class="wideInput" cols="40" rows="3"
                                wrap="soft"><?php echo $goal_row['assessment_procedure']; ?></textarea>
                        </p>
                        <label>Strategies</label>
                        <p>
                            <textarea spellcheck="true"
                                spellcheck="true" name="strategies"
                                class="wideInput" cols="40" rows="3"
                                wrap="soft"><?php echo $goal_row['strategies']; ?></textarea>
                        </p>
                        <p>
                    
                    </fieldset>
                </div>
            </div>
    
    </div>
    </form>
    </div>
    <!-- Bootstrap core JavaScript
 ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-2.1.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/jquery-ui-1.10.4.custom.min.js"></script>
</body>
</html>
