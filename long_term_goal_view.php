<?php
/**
 * @file
 *	View goals and access page to edit progress
 *
 *  @copyright 	2014 Chelsea School
 *  @copyright 	2005 Grasslands Regional Division #6
 *  @license	http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @todo change to lowercase throughout the code (standardization and consistency (all caps is reserved for constants, globals)
 * @todo Should probably be recase as parameter for a function
 *
 * @bugs Filter of goal categories fails
 */

/**
 *
 * @var $MINIMUM_AUTHORIZATION_LEVEL = 100
 *     
 *      The authorization level for this page (everybody)
 *     
 *     
 */
$MINIMUM_AUTHORIZATION_LEVEL = 100;

if (isset ( $system_message ))
	$system_message = $system_message;
else
	$system_message = "";

/**
 * Path for IPP required files (constant).
 *
 * @constant define('IPP_PATH','./')
 *
 *
 * @todo make a safe function; this is on every page
 *      
 * @author M. Nielson
 */
define ( 'IPP_PATH', './' );

/**
 * required files
 */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/supporting_functions.php';

header ( 'Pragma: no-cache' ); // don't cache this page!
                               
// checking for valid user and session
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

$student_id = "";
if (isset ( $_GET ['student_id'] ))
	$student_id = $_GET ['student_id'];
	// if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if ($student_id == "") {
	// we shouldn't be here without a student id.
	echo "You've entered this page without supplying a valid student id. Fatal, quitting";
	exit ();
}

// check permission levels
$permission_level = getPermissionLevel ( $_SESSION ['egps_username'] );
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
	$system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER ['REMOTE_ADDR'] . ")";
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	require (IPP_PATH . 'security_error.php');
	exit ();
}

if (! isset ( $_GET ['student_id'] ) || $_GET ['student_id'] == "") {
	// ack
	echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
	exit ();
} else {
	$student_id = $_GET ['student_id'];
}

// check permission levels
$permission_level = getPermissionLevel ( $_SESSION ['egps_username'] );
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
	$system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER ['REMOTE_ADDR'] . ")";
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	require (IPP_PATH . 'security_error.php');
	exit ();
}

$our_permission = getStudentPermission ( $student_id );
if ($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
	// we have write permission.
	$have_write_permission = true;
} else {
	$have_write_permission = false;
}

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

// check if we are adding...
if (isset ( $_GET ['next'] ) && $have_write_permission) {
	if (! isset ( $_GET ['goal_area'] ) || $_GET ['goal_area'] == "") {
		$system_message = $system_message . "You must supply a goal area<BR>";
	} else {
		header ( "Location: ./add_goal_1.php?goal_area=" . $_GET ['goal_area'] . "&student_id=" . $student_id );
	}
}

if (isset ( $_GET ['setUncompleted'] ) && $have_write_permission) {
	$update_query = "UPDATE long_term_goal SET is_complete='N' WHERE goal_id=" . mysql_real_escape_string ( $_GET ['setUncompleted'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if (isset ( $_GET ['deleteSTO'] ) && $have_write_permission) {
	$update_query = "DELETE FROM short_term_objective WHERE uid=" . mysql_real_escape_string ( $_GET ['deleteSTO'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if (isset ( $_GET ['deleteLTG'] ) && $have_write_permission) {
	// delete the sto's
	$update_query = "DELETE FROM short_term_objective WHERE goal_id=" . mysql_real_escape_string ( $_GET ['deleteLTG'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	} else {
		// delete the ltg's
		$update_query = "DELETE FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string ( $_GET ['deleteLTG'] );
		$update_result = mysql_query ( $update_query );
		if (! $update_result) {
			$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
			$system_message = $system_message . $error_message;
			IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
		} // else { $system_message = $system_message . "OKIE DOKIE<BR>"; }
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if (isset ( $_GET ['setCompleted'] ) && $have_write_permission) {
	$update_query = "UPDATE long_term_goal SET is_complete='Y' WHERE goal_id=" . mysql_real_escape_string ( $_GET ['setCompleted'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if (isset ( $_GET ['setSTOCompleted'] ) && $have_write_permission) {
	$update_query = "UPDATE short_term_objective SET achieved='Y' WHERE uid=" . mysql_real_escape_string ( $_GET ['setSTOCompleted'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if (isset ( $_GET ['setSTOUncompleted'] ) && $have_write_permission) {
	$update_query = "UPDATE short_term_objective SET achieved='N' WHERE uid=" . mysql_real_escape_string ( $_GET ['setSTOUncompleted'] );
	$update_result = mysql_query ( $update_query );
	if (! $update_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

if (isset ( $_GET ['deleteGoal'] ) && $have_write_permission) {
	$delete_query = "DELETE FROM long_term_goal WHERE goal_id=" . mysql_real_escape_string ( $_GET ['deleteGoal'] );
	$delete_result = mysql_query ( $delete_query );
	if (! $delete_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$delete_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	}
	// else $system_message = $system_message . "Goal Deleted: $delete_query<BR>";
}

$long_goal_query = "SELECT * FROM long_term_goal WHERE student_id=$student_id ORDER BY area ASC, is_complete DESC, goal ASC";
$long_goal_result = mysql_query ( $long_goal_query );
if (! $long_goal_result) {
	$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$long_goal_query'<BR>";
	$system_message = $system_message . $error_message;
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
}

/**
 * @fn print_goal_area_checklist()
 * @brief While there are goal areas to list, list them as checkbox input
 * @remark
 * * Currently show deleted and current goal categories
 *
 * @todo
 *
 * @param $area_result 1.        	
 *
 *
 */
function print_goal_area_checklist() {
	$area_query = "SELECT * FROM `typical_long_term_goal_category` WHERE `is_deleted` = 'N'";
	$area_result = mysql_query ( $area_query );
	if (! $area_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$area_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	} else {
		while ( $area_row = mysql_fetch_array ( $area_result ) ) {
			echo "<input class=\"area\" id=\"{$area_row['name']}\" checked type=\"checkbox\">" . $area_row ['name'] . "<br>\n";
		} // closes loop
	} // close Else
} // closes function

/**
 * @fn print_goal_area_jQuery()
 * @brief Print jQuery to toggle goals by area based on check list choices
 *
 * @param unknown $area_result        	
 *
 */
function print_goal_area_jQuery() {
	$area_query = "SELECT * FROM `typical_long_term_goal_category` WHERE `is_deleted` = 'N'";
	$area_result = mysql_query ( $area_query );
	if (! $area_result) {
		$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$area_query'<BR>";
		$system_message = $system_message . $error_message;
		IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	} // closes if
	  
	// Start javascript conditionals
	echo "<script type=\"text/javascript\"> \n";
	echo "$(document).ready(function () { \n";
	while ( $area_row = mysql_fetch_array ( $area_result ) ) {
		
		echo "\n\t var area = \"" . $area_row ['name'] . "\" \n;";
		echo "\t $('.area#" . $area_row ['name'] . "').click(function () {  \n";
		// echo "\t\t if($(\".goal#" . $area_row['name'] . ".prop(\"value\")==\"" . $area_row['name'] . "\") \n";
		// echo "\t { \n";
		echo "\t \t if ($(\".area#" . $area_row ['name'] . "\").is(\":checked\"))";
		echo "\t{ \n";
		echo "\t $('.goal#" . $area_row ['name'] . "').show() ; \n";
		echo "\t $('.objective#" . $area_row ['name'] . "').show(); \n";
		// echo ") \n";
		echo "\t } \n";
		echo "\t else \n";
		echo "\t { \n";
		echo "\t $('.goal#" . $area_row ['name'] . "').hide(); \n";
		echo "\t $('.objective#" . $area_row ['name'] . "').hide(); \n";
		echo "} \n";
		echo "});";
	} // closes loop
	echo "});\n";
	echo "</script> \n";
} // closes function
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Goals and Objectives">
<meta name="author" content="Rik Goldman">
<link rel="shortcut icon" href="./assets/ico/favicon.ico">

<TITLE><?php echo $page_title; ?></TITLE>
<!-- Bootstrap core CSS -->
<link href="./css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="./css/jumbotron.css" rel="stylesheet">
<style type="text/css">
body {
	padding-bottom: 70px;
}
</style>
<script type="text/javascript" src="./js/jquery-2.1.0.min.js"></script>

<link rel="stylesheet" type="text/css"
    href="./css/jquery-ui-1.10.4.custom.css">

<script type="text/javascript">
$(document).ready(function () {
$("#toggle-detail").change(function () {
    $("div#detail").toggle("explode", 100);
}
);
});
</script>

<script type="text/javascript">
function toggle () //toggles objective details
{
    $("div#details").toggle ("explode", 100);
}
</script>

<?php print_goal_area_jQuery(); ?>

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
                    <li><a href="main.php" title="Return to Home"><span
                            class="glyphicon glyphicon-home"></span></a></li>
                    <li><a href="index.php" title="Logout of MyIEP"><span
                            class="glyphicon glyphicon-off"></span></a></li>
                    <li><a href="about.php" title="About MyIEP"><span
                            class="glyphicon glyphicon-info-sign"></span></a></li>
                    <li><a href="help.php" title="Some Help Here"><span
                            class="glyphicon glyphicon-question-sign"></span></a></li>
                    <li><a onclick="history.go(-1);" title="Back a Page"><span
                            class="glyphicon glyphicon-circle-arrow-left"></span></a></li>

                    <li class="dropdown"><a href="#"
                        class="dropdown-toggle" data-toggle="dropdown"><span
                            class="glyphicon glyphicon-file"></span> Records: <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?><b
                            class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a
                                href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>">Goals</a></li>
                            <li class="divider"></li>
                            <li><a
                                href="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>">Guardians</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "strength_need_view.php?student_id=" . $student_row['student_id'];?>">Strengths
                                    &amp; Needs</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>">Coordination
                                    of Services</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>">Achievement
                                    Level</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>">Medical
                                    Information</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>">Medication</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>">Support
                                    Testing</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>">Background
                                    Information</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>">Year-End
                                    Review</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>">Anecdotals</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>">Assistive
                                    Techology</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>">Transition
                                    Plan</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>">Accomodations</a></li>
                            <li><a
                                href="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>">Snapshots</a></li>
                        </ul></li>


                    <!-- Dropdown to print Reports -->
                    <li class="dropdown"><a href="#"
                        class="dropdown-toggle" data-toggle="dropdown"><span
                            class="glyphicon glyphicon-print"></span>
                         Reports: <?php echo $student_row['first_name'] . " " . $student_row['last_name']; ?><b
                            class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a
                                href="<?php echo "ipp_pdf.php?student_id=" . $student_row['student_id'] . "&amp;file=ipp.pdf"; ?>"
                                target="_blank">Get IEP</a></li>
                            <li><a
                                href="<?php echo "year_end_review.php?student_id=" . $student_row['student_id'] . "&amp;file=progress.pdf"; ?>"
                                target="_blank">Get Progress Report</a></li>
                        </ul></li>




                    <!--  End dropdown for printing reports -->


                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li><a href="index.php">Logout</a></li>
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

    <div class="jumbotron">
        <div class="container">

            <h1>
                Goals: <small><?php echo $student_row['first_name'] . " " . $student_row['last_name']?>
                </small>
            </h1>
            <h2>
                Logged in as: <small><?php echo $_SESSION['egps_username']; ?>
                    (Permission: <?php echo $our_permission; ?>)</small>
            </h2>
            <?php if ($system_message) echo $system_message; ?>
            <!-- Button trigger modal -->
            <button class="btn btn-primary btn-lg" data-toggle="modal"
                data-target="#filter_options">Show Filters &raquo;</button>
            <a class="btn btn-primary btn-lg"
                href="<?php echo IPP_PATH . "add_goal_1.php?student_id=" . $student_row['student_id'] ;?>">Add
                New Goal &raquo;</a>

            <!-- Modal-->
            <div class="modal fade" id="filter_options" tabindex="-1"
                role="dialog" aria-labelledby="options"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close"
                                data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="Filters" id="Filters">Show only
                                these Areas:</h4>
                        </div>
                        <!-- Modal Header end -->
                        <div class="modal-body">
                            <small><?php print_goal_area_checklist(); ?></small>
                            <hr>
                            <!-- Toggle displayed objectives' details -->
                            <label><input type="checkbox"
                                id="toggle_detail" onclick="toggle ()"
                                checked value="">Hide Objective Details</label>


                        </div>
                        <!-- end modal body -->
                        <div class="modal-footer">
                            <button type="button"
                                class="btn btn-default"
                                data-dismiss="modal">Close</button>

                        </div>
                        <!-- end modal footer -->
                    </div>
                    <!-- end modal content -->
                </div>
                <!-- end modal dialog -->
            </div>
            <!-- end modal fade -->

        </div>
        <!-- close container -->

    </div>
    <!-- Close Jumbotron -->


    <div class="container">

        <!--  jQuery Alert to guide users through filters -->
        <div class="alert alert-block alert-info">
            <a href="#" class="close" data-dismiss="alert">&times;</a> <strong>Release
                Note</strong>: Objective details, such as progress, are
            hidden by default. Click "Show Filter" button above to
            activate or manipulate filters.
        </div>


        <?php
								
								// check if we have no goals...we need to end this table in this case.
								if (mysql_num_rows ( $long_goal_result ) == 0) {
									echo "<p>There are no goals to view</p>\n";
								}
								$goal_num = 1;
								while ( $goal = mysql_fetch_array ( $long_goal_result ) ) {
									// div for use by jquery filter action
									$div_id = $goal ['area'];
									echo "<div class=\"container\">\n";
									echo "<div class=\"goal\" id=\"$div_id\">\n<div class=\"col-md-12\">\n";
									echo "<h2>";
									
									// if (! $have_write_permission)
									// echo " onClick=\"return noPermission();\">\n";
									// else
									// echo " onClick=\"return changeStatusCompleted();\">\n";
									
									echo "<h2>" . $goal ['area'] . "</h2>";
									
									if ($goal ['is_complete'] == 'Y') {
										echo "<h3><small><span class=\"label label-warning\">closed</span> &nbsp; &nbsp;";
									} else {
										echo "<h3><small><span class=\"label label-success\">in progress</span> &nbsp; &nbsp;";
									}
									echo $goal_num . ". </small>\n";
									$goal_num ++; // increment goal
									echo $goal ['goal'] . "</h3>\n"; // output goal
									                                 
									// Review Date
									/*
									 * $today = time(); #today's date in seconds since January 1, 1970 $date_split = split("-",$goal['review_date']); $date_seconds = mktime(0,0,0,$date_split[1],$date_split[2],$date_split[0]); //since j1,1970 if ($today >= $date_seconds && $goal['is_complete'] != 'Y') { echo "<p>Review date (expired):" . "<a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id'] . "\""; if (!$have_write_permission) echo "onClick=\"return noPermission();\""; else echo "onClick=\"return changeStatusCompleted();\">"; echo $goal['review_date'] . "</a></p>"; } else { echo " <p>Review date: <a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal['goal_id'] . "\""; if (!$have_write_permission) echo "onClick=\"return noPermission();\""; else echo "onClick=\"return changeStatusCompleted();\">"; echo $goal['review_date'] . "</a></p>"; }
									 */
									
									echo "<div class=\"btn-group\">\n";
									// output the complete/uncomplete button...
									if ($goal ['is_complete'] == 'Y') {
										echo "<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setUncompleted=" . $goal ['goal_id'] . "\"";
										if (! $have_write_permission)
											echo " onClick=\"return noPermission();";
										else
											echo " onClick=\"return changeStatusCompleted();";
										echo "\">\n<button type=\"button\" class=\"btn btn-xs btn-primary\">Set Uncompleted</button></a>\n";
									} else {
										echo "<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setCompleted=" . $goal ['goal_id'] . "\"";
										if (! $have_write_permission)
											echo "onClick=\"return noPermission();\">\n";
										else
											echo " onClick=\"return changeStatusCompleted();\n";
										echo "\">\n<button type=\"button\" class=\"btn btn-xs btn-primary\">Set Completed</button></a>\n";
									}
									// output the add objectives button.
									// echo "<a href=\" . IPP_PATH . "add_objectives.php?&student_id=" . $student_id . "&lto=" . $goal['goal_id'] . "\"";
									// if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
									// else echo "onClick=\"return changeStatusCompleted();\"";
									// echo "\"><button type=\"button\" class=\"btn btn-xs btn-primary\">Add Objective</a>";
									
									// output the edit button.
									echo "<a href=\"" . IPP_PATH . "add_objectives.php?student_id=$student_id&lto=" . $goal ['goal_id'] . "\"";
									if (! $have_write_permission)
										echo "onClick=\"return noPermission();\"";
									else
										echo "onClick=\"return changeStatusCompleted();\"";
									echo "\"><button type=\"button\" class=\"btn btn-xs btn-primary\">Edit Goal</button></a>";
									
									echo "<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&deleteLTG=" . $goal ['goal_id'] . "\"";
									if (! $have_write_permission)
										echo " onClick=\"return noPermission();\"";
									else
										echo " onClick=\"return changeStatusCompleted();\" \n";
									echo "\">\n<button type=\"button\" class=\"btn btn-xs btn-primary\">Delete</button></a></div>\n";
									echo "<hr>\n";
									echo "</div>\n</div>\n</div>"; // close row and column
									                               
									// finish...
									                               
									// short term objectives
									
									$short_term_objective_query = "SELECT * FROM short_term_objective WHERE goal_id=" . $goal ['goal_id'] . " ORDER BY achieved ASC";
									$short_term_objective_result = mysql_query ( $short_term_objective_query );
									// check for error
									if (! $short_term_objective_result) {
										$error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$short_term_objective_query'<BR>";
										$system_message = $system_message . $error_message;
										IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
										echo $system_message;
									} else {
										// output this note...
										// check if we have no notes
										/*
										 * if (mysql_num_rows($short_term_objective_result) <= 0 ) { echo "<div class=\"container\"><div class=\"alert alert-warning\">No Objectives Added</div></div>"; }
										 */
										$obj_num = 1;
										while ( $short_term_objective_row = mysql_fetch_array ( $short_term_objective_result ) ) {
											echo "<div class=\"container objective\" id=$div_id  " . "\">\n<div class=\"col-md-12\">\n<div class=\"container\">\n";
											
											echo "<h4><small>" . $obj_num . ")&nbsp;</small>\n";
											$obj_num ++; // increment goal
											echo $short_term_objective_row ['description'] . "&nbsp";
											
											if ($short_term_objective_row ['achieved'] == 'Y') {
												echo "<span class=\"label label-warning\">closed</span></h4>\n";
											} else {
												echo "<span class=\"label label-success\">in progress</span></h4>\n";
											}
											// begin review date
											
											$now = time (); // today's date in seconds since January 1, 1970
											$date_split = explode ( "-", $short_term_objective_row ['review_date'] );
											$date_seconds = mktime ( 0, 0, 0, $date_split [1], $date_split [2], $date_split [0] ); // since j1,1970
											                                                                                       
											// render review date
											/**
											 * if ($now > $date_seconds && $short_term_objective_row['achieved']!='Y') {
											 * $today >= $date_seconds) {
											 * echo "<p>Review Date (expired)</p><a href=\"" .
											 *
											 *
											 *
											 *
											 *
											 *
											 *
											 *
											 *
											 * IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\""; if (!$have_write_permission) echo "onClick=\"return noPermission();\""; else echo "onClick=\"return changeStatusCompleted();\">"; echo $short_term_objective_row['review_date'] . "</a></p>"; } else { echo "<p>Review date: <a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\""; if (!$have_write_permission) echo "onClick=\"return noPermission();\""; else echo "onClick=\"return changeStatusCompleted();\">"; echo $short_term_objective_row['review_date'] . "</a></p>"; }
											 * }
											 */
											// end review date
											echo "<div class=\"container\">\n";
											// output the complete/uncomplete button...
											echo "<div class=\"btn-group\">\n";
											echo "<button class=\"btn btn-xs btn-primary\" onclick=\"toggle ()\" role=\"button\">Toggle Details</button>\n";
											if ($short_term_objective_row ['achieved'] == 'Y') {
												echo "<a class=\"btn btn-xs btn-primary\" href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setSTOUncompleted=" . $short_term_objective_row ['uid'] . "\"";
												if (! $have_write_permission)
													echo "&nbsp; onClick=\"return noPermission();\"";
												else
													echo "&nbsp; onClick=\"return changeStatusCompleted();\"";
												echo "\">\nSet Incomplete\n</a>\n";
											} else {
												echo "<a class=\"btn btn-xs btn-primary\" href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setSTOCompleted=" . $short_term_objective_row ['uid'] . "\"";
												if (! $have_write_permission)
													echo "&nbsp; onClick=\"return noPermission();\"";
												else
													echo "&nbsp; onClick=\"return changeStatusCompleted();\"";
												echo " \">\nSet Completed\n</a>\n";
											}
											
											// output the add edit button.
											// echo "<button href=\" . IPP_PATH . \"edit short_term_objective.php?sto=\" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . /"";
											
											echo "<a class=\"btn btn-xs btn-primary\"" . "&nbsp; href=\"./edit_short_term_objective.php?sto=" . $short_term_objective_row ['uid'] . "&student_id=" . $student_id . "\"" . ">\n";
											echo "Edit Objective</a>\n";
											
											// output delete button
											echo "<a class=\"btn btn-xs btn-primary\" href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&deleteSTO=" . $short_term_objective_row ['uid'] . "\"";
											if (! $have_write_permission)
												echo "onClick=\"return noPermission();\"";
											else
												echo "onClick=\"return changeStatusCompleted();\"";
											echo "\">Delete Objective</a>\n";
											
											echo "<a class=\"btn btn-xs btn-primary\" &nbsp; href=\"./edit_short_term_objective.php?sto=" . $short_term_objective_row ['uid'] . "&student_id=" . $student_id . "\"" . ">";
											echo "Report on Progress</a>\n";
											
											echo "<hr>\n";
											echo "</div>\n";
											echo "</div>\n";
											
											// output the results /assmt / etc...
											
											// output the add edit button.
											/*
											 * echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\""; if (!$have_write_permission) echo "onClick=\"return noPermission();\""; else echo "onClick=\"return changeStatusCompleted();\""; echo "\">Edit</a>";
											 */
											
											echo "<div class=\"container\" id=\"details\" style=\"display:none\">\n";
											// output the actual data
											if ($short_term_objective_row ['assessment_procedure'] != "") {
												echo "<strong>Assessment Procedure</strong>\n";
												echo "<p>" . $short_term_objective_row ['assessment_procedure'] . "</p>\n";
											}
											// Strategies
											if ($short_term_objective_row ['strategies'] != "") {
												echo "<strong>Strategies</strong>\n";
												echo "<P>" . $short_term_objective_row ['strategies'] . "</P>\n";
											}
											
											// Progress Review
											if ($short_term_objective_row ['results_and_recommendations'] != "") {
												echo "<strong>Progress Review</strong>\n";
												echo "<p>" . $short_term_objective_row ['results_and_recommendations'] . "</p>\n";
											}
											// output the add edit button.
											// echo "&nbsp;<a href=\"" . IPP_PATH . "edit_short_term_objective.php?sto=" . $short_term_objective_row['uid'] . "&student_id=" . $student_id . "\"";
											// if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
											// else echo "onClick=\"return changeStatusCompleted();\"";
											// echo " class=\"small\">Edit</a>";
											echo "</div>\n"; // end objective details container
											                 
											// end output the actual data
											                 // end toggle
											                 // echo "</div>";//show/hide objectives
											echo "</div>\n</div>\n</div>\n";
										}
									}
								}
								
								?>

        <!-- END  goals -->

        <!-- commented because can't find opening tag </div> -->

    </div>
    <!-- end container -->

    <!-- Bootstrap core JavaScript
 ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-2.1.0.min.js" type="text/javascript"></script>
    <script src="./js/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript"
        src="./js/jquery-ui-1.10.4.custom.min.js"></script>

</BODY>
</HTML>
