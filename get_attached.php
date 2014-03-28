<?php

/* Notes
 * 1. there's no html here - this is included in another page somewhere
 * 2. in the lead comments, file name doesn't match description. Update.
 * 3. Check switch code - is it complete?
 * 4. Some comments show dev frustration with IE. We will not code for compatibility with IE
 * 5. stripslashes() is used to *filter input*. Replase with best practice and secure alternative
 */

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;


/*
 *  Inputs: _GET of student_id,table,uid
*/

/**
 * Path for ipp required files.
 */

$system_message = $system_message;

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/fileutils.php');
require_once(IPP_PATH . 'include/log.php');

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
//************** validated past here SESSION ACTIVE****************
$permission_level=getPermissionLevel($_SESSION['egps_username']);
//check permission levels
if($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

//make sure we have an acceptable table name...
switch($_GET['table'])  {
  case "coordination_of_services":  //can query these tables only...
  case "performance_testing":
  case "medical_info":
  case "testing_to_support_code":
  case "snapshot":
  case "anecdotal":
  break;
  default:
      echo "unknown table name, fatal- quitting";
      exit();
}

//check our student name...
$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "You've entered this page without supplying a valid student id. Fatal, quitting";
   exit();
}

//and get out permission levels on this student...
$our_permission = getStudentPermission($student_id);
if (!($our_permission =="READ" || $our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL")) {
    //we don't have permission to be here.
    echo "You don't have the permission to view this file, fatal- quiting. Your permission='$our_permission'";
    exit();
}


//OK, past here we can spit out files- we have permission...
$file_query = "SELECT file,filename FROM " . mysql_real_escape_string($_GET['table']) . " WHERE uid=" . mysql_real_escape_string($_GET['uid']) . " AND student_id=" . mysql_real_escape_string($_GET['student_id']);
$file_result = mysql_query($file_query);
if(!$file_result) {
  echo "<HTML><body>";
  echo "SQL query ('$file_query') failed, fatal- quiting";
  echo "</html></body>";
  exit();
}
$file_row = mysql_fetch_array($file_result);

//get the extension...
$ext =explode('.', $file_row['filename']);
$ext = $ext[count($ext)-1];



//output the content-type


//header("Cache-control: max-age=31536000");
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
//header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");    // always modified

//header("Content-type: " . $file_row['filetype']); //filetype always octet-stream or it might open in browser (we want a dialog box up)
//header("Content-Disposition: attachment; filename=\"" . $file_row['filename'] . "\""); //open file download box!! don't open in IE/browser because we are closing this window...
//header("Content-Transfer-Encoding: binary");

if (strstr($HTTP_USER_AGENT,"MSIE 5.5")) { // had to make it MSIE 5.5 because if 6 has no "attachment;" in it it defaults to "inline"
    $attachment = "";
} else {
    $attachment = "attachment;";
}

//@#$@#$% this stupid thing took me hours...need this for SSL file
//send in IE (6?) becuase the @#$@#$in thing automatically decides
//the headers.
header("Pragma: ");
header("Cache-Control: ");

header("Content-Length: " . strlen($file_row['file']));
header("Content-type: application/octet-stream");
$filename=$file_row['filename'];
header("Content-disposition: $attachment filename=\"{$filename}\"");
//header("Content-Transfer-Encoding: binary");

//check if we're text or rtf and if so we need to stripslashes.
 switch($ext) {
         case "txt":
         case "TXT":
            header("Content-type: text/plain");
            header("Content-disposition: $attachment filename=\"{$filename}\"");
            $file= stripslashes($file_row['file']);
            echo $file;
         break;
         case "rtf":
         case "RTF":
            header("Content-type: text/rtf");
            header("Content-disposition: $attachment filename=\"{$filename}\"");
            $file= stripslashes($file_row['file']);
            echo $file;
         break;
         default:
            header("Content-type: application/octet-stream");
            header("Content-disposition: $attachment filename=\"{$filename}\"");
            echo $file_row['file'];
 }
?>
