<?php
/** @file
 * @brief 	modify anecdotal attached to IEP
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
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody
/*   INPUTS: $_GET['uid'],$_POST['uid']
 *
*/
/**
 * Path for IPP required files.
 */
$system_message = "";
define('IPP_PATH', './');

/** eGPS required files.
 *
 */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/navbar.php';
require_once IPP_PATH . 'include/supporting_functions.php';

/** No Caching
 *
 */
header('Pragma: no-cache'); //don't cache this page!

/** If no credentials, redirect to index for login - and throw error to log
 *
 */
if (isset($_POST['LOGIN_NAME']) && isset($_POST['PASSWORD'])) {
    if (!validate($_POST['LOGIN_NAME'], $_POST['PASSWORD'])) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
        require (IPP_PATH . 'index.php');
        exit();
    }
} else {
    if (!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
        require (IPP_PATH . 'index.php');
        exit();
    }
}
//************* SESSION active past here **************************
/** Clear variable for security concern
 *
 */
$uid = "";

/** Shorter names for uid value
 *
 */
if (isset($_GET['uid'])) $uid = mysql_real_escape_string($_GET['uid']);
if (isset($_POST['uid'])) $uid = mysql_real_escape_string($_POST['uid']);


/** get the anecdotals for this student from the database
 *
 *  All db actions must be updated for new PHP handlers for MySQL
 */
$anecdotal_row = "";
$anecdotal_query = "SELECT * FROM anecdotal WHERE uid=$uid";
$anecdotal_result = mysql_query($anecdotal_query);
if (!$anecdotal_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$anecdotal_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
} else {
    $anecdotal_row = mysql_fetch_array($anecdotal_result);
}
$student_id = $anecdotal_row['student_id'];
if ($student_id == "") {
    //we shouldn't be here without a student id.
    echo "You've entered this page without supplying a valid student id. Fatal, quitting";
    exit();
}
//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    require (IPP_PATH . 'security_error.php');
    exit();
}
$our_permission = getStudentPermission($student_id);
if ($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
} else {
    $have_write_permission = false;
}
//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************
$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if (!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
} else {
    $student_row = mysql_fetch_array($student_result);
}
//check if we are modifying a student...
if (isset($_POST['edit_anecdotal_report']) && $have_write_permission) {
    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if ($_POST['date'] == "" || $_POST['report'] == "") {
        $system_message = $system_message . "You must supply both a date and a report<BR>";
    } else {
        if (!preg_match($regexp, $_POST['date'])) {
            //no way...
            $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>";
        } else {
            //we add the entry.
            $update_query = "UPDATE anecdotal SET date='" . mysql_real_escape_string($_POST['date']) . "',report='" . mysql_real_escape_string($_POST['report']) . "'";
            $update_query.= " WHERE uid=$uid LIMIT 1";
            $update_result = mysql_query($update_query);
            if (!update_result) {
                $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query' <BR>";
                $system_message = $system_message . $error_message;
                IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
            } else {
                //redirect
                header("Location: " . IPP_PATH . "anecdotals.php?student_id=" . $student_id);
            }
        }
    }
}
print_html5_primer();
print_bootstrap_head();
print_datepicker_depends(); 
?>
</HEAD>
    <BODY>
<?php
print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']);
print_jumbotron_with_page_name("Edit Anecdotal", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);
if ($system_message) {
    echo $system_message;
}
?>
<div class="container">
<!-- BEGIN edit entry -->
<form name="edit_anecdotal_report" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_anecdotal.php"; ?>" method="post" <?php if (!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
<div class="form-group">
<input type="hidden" name="edit_anecdotal_report" value="1">
<input type="hidden" name="uid" value="<?php echo $uid; ?>">
<label>Report</label>
<textarea class="form-control" spellcheck="true" name="report" tabindex="1" cols="40" rows="10" wrap="soft"><?php echo $anecdotal_row['report']; ?></textarea>
<label>Date (YYYY-MM-DD)</label>
<input class="form-control" autocomplete="off" id="datepicker" type="datepicker" tabindex="2" name="date" data-date-format="yyyy-mm-dd" value="<?php echo $anecdotal_row['date']; ?>">
</div>
<button class="btn btn-success btn-md" type="submit" tabindex="3" name="Edit" value="Edit">Submit</button>
</form>
<!-- END edit entry -->

</div>
<footer><?php print_complete_footer(); ?></footer>
<?php print_bootstrap_js(); ?>
    </BODY>
</HTML>
