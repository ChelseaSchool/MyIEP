<?php
define('IPP_PATH','./');
/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/supporting_functions.php');

header('Pragma: no-cache'); //don't cache this page!

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within

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


$student_first_name = $_POST['student_first_name'];
$student_last_name = $_POST['student_last_name'];
$student_id = $_POST['student_id'];
$objective = $_POST['objective'];
$assessment = $_POST['assessment_procedure'];
$strategies = $_POST['strategies'];
$review_date = $_POST['objective_review_date'];
$results = $_POST['results_and_recommendations'];
$goal_id = $_POST['lto'];
$goal = $_POST['goal'];
$sql = "INSERT INTO short_term_objective SET review_date = '$review_date', assessment_procedure='$assessment', results_and_recommendations='$results', strategies='$strategies', description='$objective', goal_id = '$goal_id'";
$result = mysql_query($sql);
$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}
if(!$result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: {$sql}<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

if ($result) {
    $status = "Your changes have been recorded successfully.";
}

if($student_id) {
    $student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
    $student_result = mysql_query($student_query);
    if(!$student_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
        $system_message=$system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    } else {$student_row= mysql_fetch_array($student_result);
        $student_first_name = $student_row['first_name'];
        $student_last_name = $student_row['last_name'];
    }
}
print_html5_primer();
?>
<title><?php echo $page_title; ?></title>
<?php print_bootstrap_head(); ?>
</head>
<body>
<?php print_student_navbar($student_id, $student_first_name . " " . $student_last_name); ?>
<?php print_jumbotron_with_page_name("Objective Receipt", $student_first_name . " " . $student_last_name, $our_permission)?>
<div class="container">
<?php 
if ($result) {
    echo "<div class=\"alert alert-success\" id=\"successful_query\">Your addition of an objective has been recorded successfully.<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}
else {
    echo "<div class=\"alert alert-danger\" id=\"unsuccessful_query\">Your addition of an objective was not recorded successfully.<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}

?>

<h2>Goal <small><?php echo $goal; ?></small></h2>
<h2>Objective <small><?php echo $objective; ?></small></h2>
<h2>Review Date <small><?php echo $review_date; ?></small></h2>
<h2>Strategies <small><?php echo $strategies; ?></small></h2>
<h2>Assessment Procedures <small><?php echo $assessment; ?></small></h2>


<a class="btn btn-lg btn-warning" href="<?php echo "add_objectives.php?student_id=" . $student_id . "&lto=" . $goal_id; ?>">Edit</a>&nbsp;<a class="btn btn-lg btn-success" href="<?php echo "long_term_goal_view.php?student_id=" . $student_id ; ?>">Return to Goals and Objectives</a>
<?php 
if ($error_message) {
    echo $error_message;
}
if (!isset($goal)) echo "Goal is Empty";
print_complete_footer();
print_bootstrap_js();
?>
</div>
</body>
</html>