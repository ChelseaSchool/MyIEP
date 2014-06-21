<?php
/** @file
 * @brief 	manage anecdotal attachments
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @author		M. Nielson
 * @todo		
 * 1. filter input
 * @remark 
 * * quit working sometime 14.4.9
 */ 
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody



/*   INPUTS: $_GET['student_id']
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/navbar.php';
require_once IPP_PATH . 'include/supporting_functions.php';
require_once IPP_PATH . 'include/config.inc.php';

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

$student_id="";
if(isset($_GET['student_id'])) $student_id= $_GET['student_id'];
if(isset($_POST['student_id'])) $student_id = $_POST['student_id'];

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

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

function asc2hex ($temp) {
   $len = strlen($temp);
   for ($i=0; $i<$len; $i++) $data.=sprintf("%02x",ord(substr($temp,$i,1)));
   return $data;
}

function parse_submission() {
    //returns null on success else returns $szError
    global $content,$fileName,$fileType;

    //check that date is the correct pattern...
    $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
    if(!preg_match($regexp,$_POST['date'])) return "Date must be in YYYY-MM-DD format<BR>";

     if($_POST['report'] == "") return "You must supply report information<BR>";

     if($_FILES['supporting_file']['size'] <= 0) { $fileName=""; $tmpName="";$fileSize=0;$fileType=null; return NULL; } //handle no file upload.
     if($_FILES['supporting_file']['size'] >= 1048576) return "File must be smaller than 1MB (1048567Bytes) but is " . $_FILES['supporting_file']['size'] . "MB"; //Must be less than 1 Megabyte

     //we have a file so get the file information...
     $fileName = mysql_real_escape_string($_FILES['supporting_file']['name']);
     $tmpName  = $_FILES['supporting_file']['tmp_name'];
     $fileSize = mysql_real_escape_string($_FILES['supporting_file']['size']);
     $fileType = "";

     if(is_uploaded_file($tmpName)){
       $ext =explode('.', $fileName);
       $ext = $ext[count($ext)-1];
     } else {
       return "Security problem: file does not look like an uploaded file<BR>";
     }

      $fp      = fopen($tmpName, 'rb');
      if(!$fp) return "Unable to open temporary upload file $tmpname<BR>";
      $content = fread($fp, filesize($tmpName));
      $content = mysql_real_escape_string($content);
      fclose($fp);

      switch($ext) {
         case "txt":
         case "rtf":
         case "TXT":
         case "RTF":
           //make sure we don't have binary data here.
           for($i = 0; $i < strlen($content); $i++){
              if(ord($content[$i]) > 127) { IPP_LOG("Attempted to upload binary data as txt in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "Not a valid Text file: contains binary data<BR>"; }
           }
           $fileType="text/plain";
         break;
         case "pdf":
         case "PDF":
          if(strncmp("%PDF-",$content,5) != 0) { IPP_LOG("Attempted to upload file not recognized as PDF in first few bytes in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "File does not appear to be a valid PDF file<BR>"; }
          $fileType="application/pdf";
         break;
         case "doc":
         case "DOC":
         //check for 0xD0CF (word document magic number)
         for($i=0;$i < 2; $i++) {
            $msg = $msg . $content[$i];
         }
         $msg = "0x" . bin2hex($msg);
         if($msg != "0xd0cf") { IPP_LOG("Attempted to upload file not recognized as MS Word Document in IPP Coordination of Services page for student #$student_id",$_SESSION['egps_username'],'ERROR'); return "File does not appear to be a valid MS Word Document file<BR>"; }
         $fileType="application/msword";
         break;
         default:
           return "File extension '$ext' on '$fileName' is not a recognized type please upload only MS Word, Plain Text, or PDF documents<BR>";
     }

     return NULL;
}

//check if we are modifying a student...
if(isset($_POST['add_anecdotal']) && $have_write_permission) {
  $retval=parse_submission();
  if($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $insert_query = "INSERT INTO anecdotal (student_id,date,report,file,filename) VALUES (" . mysql_real_escape_string($student_id) . ",'" . mysql_real_escape_string($_POST['date']) . "','" . mysql_real_escape_string($_POST['report']) . "','$content','$fileName')";
    $insert_result = mysql_query($insert_query);
     if(!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query' <BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        unset($_POST['report']);
        unset($_POST['date']);
     }

  }
}

//check if we are deleting some entries...
if(isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_ANECDOTAL && $have_write_permission ) {
    $delete_query = "DELETE FROM anecdotal WHERE ";
    foreach($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "uid=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}

//get the coordination of services for this student...
$anecdotal_query="SELECT * FROM anecdotal WHERE student_id=$student_id ORDER BY date DESC";

$anecdotal_result = mysql_query($anecdotal_query);
if(!$anecdotal_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$anecdotal_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}


?> 
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
   
    
    <script src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT>
      function confirmChecked() {
          var szGetVars = "anecdotals=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.anecdotal;
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
       <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
	
	<?php print_datepicker_depends(); ?>
</HEAD>
<BODY>
    <?php  print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']); ?>
    <?php print_jumbotron_with_page_name("Anecdotals", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission) ?> 
    <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>
    <div class="container">
 <div class="alert alert-info alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <strong>Scroll Down</strong> for all Anecdotal options.
</div>
 
   
    
    <div class="row">
    <h2>Print Report<a name="print"></a></h2>
    <p><a class="btn btn-md btn-success" href="<?php echo IPP_PATH . "anecdotal_pdf.php?student_id=$student_id" ?>" target="_blank">Generate PDF</a></p>  
   </div> 
   <div row="row">
   						<h2>Report History<a name="report"></a></h2>
                        <!-- BEGIN annecdotals table -->
                        <form spellcheck="true" name="anecdotal" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "anecdotals.php"; ?>" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $student_id ?>">
                        <table class="table table-striped table-hover">
                        
                        <?php
                        

                        //print the header row...
                        echo "<tr><th>&nbsp;</th><th>uid</th><th>Report</th><th>Date</th><th>File</th></tr>\n";
                        while ($anecdotal_row=mysql_fetch_array($anecdotal_result)) { //current...
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $anecdotal_row['uid'] . "\"></td>";
                            echo "<td>" . $anecdotal_row['uid'] . "</td>";
                            echo "<td><a href=\"" . IPP_PATH . "edit_anecdotal.php?uid=" . $anecdotal_row['uid'] . "\" class=\"editable_text\">" . mysql_real_escape_string($anecdotal_row['report'])  ."</td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_anecdotal.php?uid=" . $anecdotal_row['uid'] . "\" class=\"editable_text\">" . $anecdotal_row['date']  ."</td>\n";
                            echo "<td><center>"; if($anecdotal_row['filename'] =="") echo "-none-"; else echo "<a href=\"" . IPP_PATH . "get_attached.php?table=anecdotal&uid=" . $anecdotal_row['uid'] ."&student_id=" . $student_id ."\">Download</a>"; echo "</center></td>\n";
                            echo "</tr>\n";
                            
                        }
                        ?>
                        <tr>
                          <td colspan="7" align="left">
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                             <?php
                                //if we have permissions also allow delete.
                                if($permission_level <= $IPP_MIN_DELETE_ANECDOTAL && $have_write_permission) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>
                          </td>
                        </tr>
                        </table></center>
                        </form>
                        </div>
    
    <div class="row">
               <h2>Add Anecdotal Report<a name="add"></a></h2>
                        <form name="add_anecdotal" enctype="multipart/form-data" action="<?php echo IPP_PATH . "anecdotals.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <div class="form-group">
                        <input type="hidden" name="add_anecdotal" value="1">
                        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                        <label>Report</label>
                        <textarea class="form-control" spellcheck="true" name="report" tabindex="1" rows="10" wrap="soft"><?php if(isset($_POST['report'])) echo $_POST['report']; ?></textarea>
                        <label>Report Date (YYYY-MM-DD)</label>
                        <input class="form-control datepicker" id="datepicker" type="datepicker" name="date" data-provide="datepicker" data-date-format="yyyy-mm-dd" tabindex="2" value="<?php if(isset($_POST['date'])) echo $_POST['date']; else echo date("Y-m-d")?>">
                        <label>Optional File Upload (.doc,.pdf,.txt,.rtf)</label>
                        <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
                        <input type="file" tabindex="3" name="supporting_file" value="<?php if(isset($_FILES['supporting_file']['name'])) echo $_FILES['supporting_file']['name'] ?>">
                        </div>
                        <button class="btn btn-md btn-success" tabindex="4" type="submit" name="add" value="add">Submit</button>  
                        
                        </form>
                        
                        <!-- END add new entry -->
                </div>

                        </div>
                        </div>
                        
       <?php print_complete_footer(); ?>
       <!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery-2.1.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>   
<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
    </BODY>
</HTML>
