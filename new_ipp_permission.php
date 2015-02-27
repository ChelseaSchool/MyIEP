<?php

/** @file
 * @brief 	assign new permissions
 * @todo
 * #. access is not intuitive. Make accessible from front end.
 * #. needs bootstrap theme; still legacy style.
 * #. change spacing to 4s indents.
 *
 */

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 60; // TA

/**
 * Path for IPP required files.
 */
if (isset($system_message))
    $system_message = $system_message;
else
    $system_message = "";

define('IPP_PATH', './');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/mail_functions.php';
require_once IPP_PATH . 'include/navbar.php';
require_once IPP_PATH . 'include/supporting_functions.php';

header('Pragma: no-cache'); // don't cache this page!

if (isset($_POST['LOGIN_NAME']) && isset($_POST['PASSWORD'])) {
    if (! validate($_POST['LOGIN_NAME'], $_POST['PASSWORD'])) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
        require (IPP_PATH . 'index.php');
        exit();
    }
} else {
    if (! validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
        require (IPP_PATH . 'index.php');
        exit();
    }
}
// ************* SESSION active past here **************************

// check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    require (IPP_PATH . 'security_error.php');
    exit();
}

$student_id = "";

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
}

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
}

if ($student_id == "") {
    // ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
}

// get our permissions for this student...
$our_permission = getStudentPermission($student_id);

if ($our_permission != "ASSIGN" && $our_permission != "ALL") {
    // we don't have permission...
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    require IPP_PATH . 'security_error.php';
    exit();
}

// ************** validated past here SESSION ACTIVE****************
$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
$student_row = "";
if (! $student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
} else {
    $student_row = mysql_fetch_array($student_result);
}

$username = "";
if (isset($_GET['username']))
    $username = $_GET['username'];
else
    $username = $_POST['username'];
    
    // get all authorized ipp support members...
$ipp_username_query = "SELECT * FROM support_member WHERE egps_username LIKE '%" . mysql_real_escape_string($username) . "%'";
$ipp_username_result = mysql_query($ipp_username_query);
if (! $ipp_username_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$ipp_username_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
}

// get our permissions for this student...
$our_permission = getStudentPermission($student_id);

if ($our_permission != "ASSIGN" && $our_permission != "ALL") {
    // shouldn't be here...
    // we don't have permission...
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    require (IPP_PATH . 'security_error.php');
    exit();
}

// check if we are adding...
if (isset($_POST['ACTION']) && $_POST['ACTION'] == "Add" && ! isset($_POST['add_username']))
    $system_message = $system_message . "You must choose a person<BR>";
if (isset($_POST['ACTION']) && $_POST['ACTION'] == "Add" && isset($_POST['add_username'])) {
    
    // make sure we don't have a duplicate...
    $duplicate_query = "SELECT * FROM support_list WHERE egps_username='" . mysql_real_escape_string($_POST['add_username']) . "' and student_id=" . mysql_real_escape_string($_POST['student_id']);
    $duplicate_result = mysql_query($duplicate_query);
    if (mysql_num_rows($duplicate_result) > 0) {
        // already have this person as a support member for this student...
        $system_message = $system_message . "This person already appears to be a support member for this student<BR>";
    } else {
        $insert_query = "INSERT INTO support_list (egps_username,student_id,permission,support_area) VALUES ('" . mysql_real_escape_string($_POST['add_username']) . "'," . mysql_real_escape_string($_POST['student_id']) . ",'" . mysql_real_escape_string($_POST['permission_level']) . "','" . mysql_real_escape_string($_POST['support_area']) . "')";
        $insert_result = mysql_query($insert_query);
        if (! $insert_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
            $system_message = $system_message . $error_message;
            IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
        } else {
            // get the support list UID before we do another query...
            $support_list_uid = mysql_insert_id();
            
            // successful add...log information and return to
            // this students ipp main page
            IPP_LOG("Added support member " . mysql_real_escape_string($_POST['add_username']) . " to student #" . mysql_real_escape_string($_POST['student_id']), $_SESSION['egps_username'], 'INFORMATIONAL');
            if (isset($_POST['mail_notification'])) {
                // echo "mailing<BR>";
                mail_notification(mysql_real_escape_string($_POST['add_username']), "This email has been sent to you to notify you that you have been given " . mysql_real_escape_string($_POST['permission_level']) . " access to " . $student_row['first_name'] . " " . $student_row['last_name'] . "'s IPP on the $IPP_ORGANIZATION online individual program plan system.");
            }
            header("Location: " . IPP_PATH . "modify_ipp_permission.php?student_id=" . $_POST['student_id']);
            exit();
        }
    }
}

/**
 * ************************* popup chooser support function *****************
 */
function createJavaScript($dataSource, $arrayName = 'rows')
{
    // validate variable name
    if (! is_string($arrayName)) {
        $system_message = $system_message . "Error in popup chooser support function name supplied not a valid string  (" . __FILE__ . ":" . __LINE__ . ")";
        return FALSE;
    }
    
    // initialize JavaScript string
    $javascript = '<!--Begin popup array--><script>var ' . $arrayName . '=[];';
    
    // check if $dataSource is a file or a result set
    if (is_file($dataSource)) {
        
        // read data from file
        $row = file($dataSource);
        
        // build JavaScript array
        for ($i = 0; $i < count($row); $i ++) {
            $javascript .= $arrayName . '[' . $i . ']="' . trim($row[$i]) . '";';
        }
    }     

    // read data from result set
    else {
        
        // check if we have a valid result set
        if (! $numRows = mysql_num_rows($dataSource)) {
            die('Invalid result set parameter');
        }
        for ($i = 0; $i < $numRows; $i ++) {
            // build JavaScript array from result set
            $javascript .= $arrayName . '[' . $i . ']="';
            $tempOutput = '';
            // output only the first column
            $row = mysql_fetch_array($dataSource);
            
            $tempOutput .= $row[0] . ' ';
            
            $javascript .= trim($tempOutput) . '";';
        }
    }
    $javascript .= '</script><!--End popup array-->' . "\n";
    
    // return JavaScript code
    return $javascript;
}

function echoJSServicesArray()
{
    global $system_message, $student_id;
    // get a list of program areas...
    $area_query = "SELECT name FROM typical_long_term_goal_category WHERE is_deleted='N' ORDER BY name ASC";
    $area_result = mysql_query($area_query);
    if (! $area_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_query'<BR>";
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    } else {
        if (mysql_num_rows($area_result)) {
            // call the function to create the javascript array...
            echo createJavaScript($area_result, "popuplist");
        }
    }
}
/**
 * ********************** end popup chooser support funtion *****************
 */

?>
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
<TITLE><?php echo $page_title; ?></TITLE>



<script language="javascript"
    src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
<script language="javascript"
    src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
<?php
// output the javascript array for the chooser popup
echoJSServicesArray();
print_bootstrap_head();
?>

</HEAD>
<BODY>
<?php
print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']);
print_jumbotron_with_page_name("Define Support Staff", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);

?>
<?php


if ($system_message) {
    echo "<p>" . $system_message . "</p>";
}
?>
<div class="container">
        <h2>Add Support Member to Permissions</h2>
        <div class="form-group">
        <p><label for="student_id">Choose a support member and click add:</label></p>
        <form enctype="multipart/form-data"
            action="<?php echo IPP_PATH . "new_ipp_permission.php"; ?>"
            method="post">
            <input type="hidden" name="student_id"
                value="<?php echo $student_id;?>"> 
            <input type="hidden"
                name="egps_username"
                value="<?php echo $egps_username;?>"> 
          
                		
<?php
echo "<SELECT MULTIPLE name=\"add_username\" size=\"8\" style=\"width:200px;text-align: left;\">\n";
while ($val = mysql_fetch_array($ipp_username_result)) {
    $szUsername = explode("@", $val['egps_username'], 2);
    echo "\t<OPTION value=" . $szUsername[0] . ">" . $szUsername[0] . "</OPTION>\n";
}
echo "</SELECT>\n" ?></div>								
										<div class="form-group">
										<label for="permission_level">Set Permission(s):</label>
												 <SELECT class="form-control" name="permission_level">
														<OPTION value="READ" SELECTED>READ
												          <OPTION value="WRITE">WRITE
														<OPTION value="ASSIGN">
															ASSIGN
															<?php
            if ($our_permission == 'ALL')
                echo "<OPTION value=\"ALL\">ALL";
            ?> </OPTION> 
            </SELECT>
            </div>
            
            <div class="form-group">
            <label for="mail_notification">Send email notification</label>
			<input type="checkbox"
                                        <?php if(!isset($_POST['ACTION']) || (isset($_POST['ACTION']) && isset($_POST['mail_notification']))) echo "checked"; ?>
                                        name="mail_notification">
                                        </div>
	

<div class="form-group">                
<label for="support_area">Support Area (Optional)</label>:
<input type="text" name="support_area"
                                        onkeypress="return autocomplete(this,event,popuplist)"> <img
                                        src="<?php echo IPP_PATH . "images/choosericon.png"; ?>"
                                        height="17" width="17" border=0
                                        onClick="popUpChooser(this, document.all.support_area)" />
</div>                

                                        
                                        <input class="btn btn-primary"
                                        type="submit" value="Add"
                                        name="ACTION">

</form>
								
                    
                    
			
                    
                    
	
    
</div>
	<?php  print_bootstrap_js(); ?>
</BODY>
</HTML>
