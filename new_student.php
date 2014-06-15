<?php

/** @file
 * @brief 	add student to database
 * @todo
 * 1. Filter input, escape output
 * 2. Title case for names
 * 3. spellcheck?
 * 4. Bootstrap
 */

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 50;

/** @remark   INPUTS: $_GET['add_username'] must be a username...
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
require_once IPP_PATH . 'include/supporting_functions.php';

header('Pragma: no-cache'); //don't cache this page!

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

//************** validated past here SESSION ACTIVE****************

//set the get/put variables for the back button and exit fx...
$szBackGetVars="";
if(isset($_GET['szBackGetVars']))$szBackGetVars = $_GET['szBackGetVars'];
if(isset($_POST['szBackGetVars']))$szBackGetVars = $_POST['szBackGetVars'];

function parse_submission()
{
    if(!$_POST['first_name']) return "You must supply a first name<BR>";
    if(!$_POST['last_name']) return "You must supply a last name<BR>";
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['birthday'])) return "Birthday must be in YYYY-MM-DD format<BR>";
    if(!preg_match($regexp,$_POST['at_school_since'])) return "At School Since must be in YYYY-MM-DD format<BR>";
    //if(!$_POST['prov_ed_num']) return "You must supply a Provincial Education Number<BR>";
    //if(!$_POST['ab_ed_code']) return "You must supply an Alberta Education Coding Value<BR>";

    //check duplicate prov ed number...
    if (!connectIPPDB()) {
          $error_message = $error_message;  //just to remember we need this
          $system_message = $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }
     if ($_POST['prov_ed_num'] != "") {
       $duplicate_query = "SELECT * FROM student WHERE prov_ed_num='" . mysql_real_escape_string($_POST['prov_ed_num']) ."'";
       $duplicate_result= mysql_query($duplicate_query);
       if (mysql_num_rows($duplicate_result) > 0) {$duplicate_row = mysql_fetch_array($duplicate_result);return "Duplicate Provincial Education Number (name:" . $duplicate_row['first_name'] . " " . $duplicate_row['last_name'] ."),<BR>This student probably already exists in the database<BR>";}
     }
     //$duplicate_query = "SELECT * FROM student WHERE ab_ed_code='" . mysql_real_escape_string($_POST['ab_ed_code']) ."'";
     //$duplicate_result= mysql_query($duplicate_query);
     //if (mysql_num_rows($duplicate_result) > 0) {$duplicate_row = mysql_fetch_array($duplicate_result);return "Duplicate Alberta Education Code Number (name:" . $duplicate_row['first_name'] . " " . $duplicate_row['last_name'] ."),<BR>This student probably already exists in the database<BR>"; }
    return NULL;
}

//check if we are adding a student...
if (isset($_POST['add_student'])) {

     if (!connectIPPDB()) {
          $error_message = $error_message;  //just to remember we need this
          $system_message = $error_message;
          IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     }

     //do some error checking on data submission...
     $retval = parse_submission();
     if ($retval != NULL) {
         $system_message = $system_message . $retval;
     } else {
       $add_query="INSERT INTO student (first_name,last_name,birthday,prov_ed_num,current_grade,gender) values ('" . mysql_real_escape_string($_POST['first_name']) . "','" .  mysql_real_escape_string($_POST['last_name']) ."','" . mysql_real_escape_string($_POST['birthday']) . "','" .  mysql_real_escape_string($_POST['prov_ed_num']) . "','" . mysql_real_escape_string($_POST['current_grade']) . "','" . mysql_real_escape_string($_POST['gender']) . "')";
       $add_result=mysql_query($add_query);
       if (!$add_result) {
           $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
           $system_message=$system_message . $error_message;
           IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
       } else {
           //get the school information to create a history for this student...
           $school_history_query="SELECT * FROM school WHERE school_code='" . mysql_real_escape_string($_POST['school_code']) . "'";
           $school_history_result=mysql_query($school_history_query);
           $school_history_row="";
           if (!$school_history_result) {
               $error_message = $error_message . "You might need to enter or change some of the school history information for this student. The system  wasunable to automatically determine this information because the database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$add_query'<BR>";
               $system_message=$system_message . $error_message;
               IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
              $school_history_row=mysql_fetch_array($school_history_result);
           }
           //add the school history to the database...
           //just to be safe we update any existing data for this student_id to have
           //todays end date...
           $student_id = mysql_insert_id();
           $history_update_query="UPDATE school_history SET end_date=NOW() where student_id=$student_id";
           $history_update_result=mysql_query($history_update_query); //ignore returned errors.

           //add a new school history...choosen school start today, end NULL...
           $history_insert_query = "INSERT INTO school_history (start_date,end_date,school_code,student_id,school_name,school_address,ipp_present) VALUES ('" . mysql_real_escape_string($_POST['at_school_since']) . "',NULL,'" . $school_history_row['school_code'] ."'," . mysql_real_escape_string($student_id) . ",'" . $school_history_row['school_name']  . "','" . $school_history_row['school_address']  . "','Y')";
           $history_insert_result = mysql_query($history_insert_query); //ignore returned errors. What we don't know can't hurt us.

           //add this user as a support member for this IPP...
           $support_list_query = "INSERT INTO support_list (egps_username,student_id,permission) VALUES ('" . mysql_real_escape_string($_SESSION['egps_username']) . "'," . mysql_real_escape_string($student_id) . ",'ASSIGN')"; //give self assign
           $support_list_result = mysql_query($support_list_query); //ignore returned errors...won't cause major problem.

           //successful add...direct to new_student_3...
           header("Location: manage_student.php");
           exit();
       }
     }

}

if (!connectUserDB()) {
        $error_message = $error_message;  //just to remember we need this
        $system_message = $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//find all of the available schools..
if (!connectIPPDB()) {
   $error_message = $error_message;  //just to remember we need this
   $system_message = $error_message;
   IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$school_query="SELECT * FROM school WHERE 1=1";
$school_result=mysql_query($school_query);

if (!$school_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}
print_html5_primer();
print_bootstrap_head();
?>
<link rel="stylesheet" href="./css/smoothness/jquery-ui.css">
<script src="js/jquery-2.1.1.js"></script>
<script src="js/smoothness/jquery-ui-1.10.4.custom.js"></script>

<script>
$(function () {
    $( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
});
</script>
</HEAD>
<BODY>
<?php
print_general_navbar();
print_lesser_jumbotron("New Student Record", $permission_level);
?>
<div class="container">
<?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>

<form name="addName" enctype="multipart/form-data" action="<?php echo IPP_PATH . "new_student.php"; ?>" method="post">
<div class="form-group">
<input type="hidden" name="add_student" value="1">
<label for first_name>First Name</label>
<input class="form-control" type="text" autocomplete="off" placeholder="First Name" required name="first_name" size="30" maxsize="125" value="<?php if(isset($_POST['first_name'])) echo $_POST['first_name'];?>">

<label for last_name>Last Name</label>
<input class="form-control" type="text" autocomplete="off" placeholder="Last Name" required name="last_name" size="30" maxsize="125" value="<?php if(isset($_POST['last_name']))  echo $_POST['last_name']; ?>">
<label for birthday>Birthdate (YYYY-MM-DD)</label>

<input class="form-control datepicker" autocomplete="off" data-provide="datepicker" required data-date-format="yyyy-mm-dd" type="datepicker" id="datepicker" name="birthday" value="<?php if(isset($_POST['birthday'])) echo $_POST['birthday']; ?>">

<label for school_code>School</label>
<SELECT required class="form-control" name="school_code">
<?php
while ($school_row=mysql_fetch_array($school_result)) {
    if (isset($_POST['school_code']) && $_POST['school_code'] == $school_row['school_code']) {
        echo "<option value=\"" . $school_row['school_code'] . "\" selected>" .  $school_row['school_name'] . "\n";
    } else {
        echo "<option value=\"" . $school_row['school_code'] . "\">" .  $school_row['school_name'] . "\n";
    }
}
?>
</SELECT>
<label for at_school_since>At School Since (YYYY-MM-DD)</label>
<input autocomplete="off" placeholder="Attending school since..." class="form-control datepicker" required type="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="at_school_since" value="<?php if(isset($_POST['at_school_since'])) echo $_POST['at_school_since']; else echo date("Y-m-d"); ?>">

<label for current_grade>Current Grade</label>
<SELECT class="form-control" name="current_grade">
                                 <OPTION value="0" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "0") echo "selected"; ?>>K or Pre-K
                                 <OPTION value="1" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "1") echo "selected"; ?>>1
                                 <OPTION value="2" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "2") echo "selected"; ?>>2
                                 <OPTION value="3" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "3") echo "selected"; ?>>3
                                 <OPTION value="4" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "4") echo "selected"; ?>>4
                                 <OPTION value="5" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "5") echo "selected"; ?>>5
                                 <OPTION value="6" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "6") echo "selected"; ?>>6
                                 <OPTION value="7" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "7") echo "selected"; ?>>7
                                 <OPTION value="8" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "8") echo "selected"; ?>>8
                                 <OPTION value="9" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "9") echo "selected"; ?>>9
                                 <OPTION value="10" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "10") echo "selected"; ?>>10
                                 <OPTION value="11" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "11") echo "selected"; ?>>11
                                 <OPTION value="12" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "12") echo "selected"; ?>>12
                                 <OPTION value="13" <?php if(isset($_POST['current_grade']) && $_POST['current_grade'] == "13") echo "selected"; ?>>13
                            </SELECT>

<label for gender>Gender</label>
<SELECT autocomplete="off" required class="form-control" name="gender">
<option value="M">Male
<option value="F">Female
<option value="O">Other
</SELECT>

<!-- <label>Provincial Education Number</label>
<input type="text" size="30" maxsize="60" name="prov_ed_num" value="<?php if(isset($_POST['prov_ed_num'])) echo $_POST['prov_ed_num'];?>">-->

</div>

<button class="btn btn-default btn-large" type="submit" value="Add Student">Submit</button>
<input type="hidden" name="szBackGetVars" value="<?php echo $szBackGetVars; ?>">

</form>

</div>

<?php print_bootstrap_js();?>
    </BODY>
</HTML>
