<?php
define('IPP_PATH','./');
/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/supporting_functions.php';
header('Pragma: no-cache'); //don't cache this page!

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within

if (isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if (!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
} else {
    if (!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
}
//************* SESSION active past here **************************

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}
$our_permission = getStudentPermission($student_id);
if ($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
} else {
    $have_write_permission = false;
}
$student_first_name = $_POST['student_first_name'];
$student_last_name = $_POST['student_last_name'];
$student_id = $_POST['student_id'];
$goal = $_POST['goal_text'];
$review_date = $_POST['goal_review_date'];
$area = $_POST['goal_area'];
$goal_id = $_POST['lto'];
$sql = "UPDATE long_term_goal SET review_date = '$review_date', goal='$goal', area='$area' WHERE goal_id = '$goal_id'";
$result = mysql_query($sql);
if (!$result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: {$sql}<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
    $status = "Your revision has been recorded successfully.";
}
if ($student_id) {
    $student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
    $student_result = mysql_query($student_query);
    if (!$student_result) {
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
<?php print_jumbotron_with_page_name("Goal Revision Submitted", $student_first_name . " " . $student_last_name, $our_permission); ?>
<div class="container">
<?php echo $error_message; ?>
<div class="alert alert-success id="successful_query">Your revision has been recorded successfully.<button type="button" class="close" data-dismiss="alert">&times;</button></div>
<div class="container">

<h2><small>Goal Area</small> <?php echo $area; ?></h2>
<h2><small>Goal</small> <?php echo $goal; ?></h2>
<h2><small>Review Date</small> <?php echo $review_date; ?></h2>

<a class="btn btn-md btn-default" href="<?php echo "add_objectives.php?student_id=" . $student_id . "&lto=" . $goal_id; ?>">Back</a>&nbsp;<a class="btn btn-md btn-success" href="<?php echo "long_term_goal_view.php?student_id=" . $student_id ; ?>">Return to Goals and Objectives</a>
<?php
if ($error_message) {
    echo $error_message;
}
print_complete_footer();
print_bootstrap_js();
?>
</div>
</body>
</html>