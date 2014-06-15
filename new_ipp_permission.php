<?php

/** @file
 * @brief 	assign new permissions
 * @todo
 * #. access is not intuitive. Make accessible from front end.
 * #. needs bootstrap theme; still legacy style.
 * #. change spacing to 4s indents.
 *
 */



//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 60;  //TA



/**
 * Path for IPP required files.
 */
if(isset($system_message)) $system_message = $system_message; else $system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/mail_functions.php';
require_once IPP_PATH . 'include/navbar.php');

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

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
 $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
 require(IPP_PATH . 'security_error.php');
 exit();
}

$student_id="";

if(isset($_GET['student_id'])) {
 $student_id = $_GET['student_id'];
}

if(isset($_POST['student_id'])) {
 $student_id = $_POST['student_id'];
}

if($student_id=="") {
 //ack
 echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
 exit();
}

//get our permissions for this student...
$our_permission = getStudentPermission($student_id);

if($our_permission != "ASSIGN" && $our_permission != "ALL") {
 //we don't have permission...
 $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
 require IPP_PATH . 'security_error.php';
 exit();
}

//************** validated past here SESSION ACTIVE****************
$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
$student_row="";
if(!$student_result) {
 $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
 $system_message=$system_message . $error_message;
 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);
}

$username="";
if(isset($_GET['username'])) $username=$_GET['username'];
else $username=$_POST['username'];

//get all authorized ipp support members...
$ipp_username_query="SELECT * FROM support_member WHERE egps_username LIKE '%" . mysql_real_escape_string($username) . "%'";
$ipp_username_result = mysql_query($ipp_username_query);
if(!$ipp_username_result) {
 $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$ipp_username_query'<BR>";
 $system_message=$system_message . $error_message;
 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

//get our permissions for this student...
$our_permission = getStudentPermission($student_id);

if($our_permission != "ASSIGN" && $our_permission != "ALL") {
 //shouldn't be here...
 //we don't have permission...
 $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
 IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
 require(IPP_PATH . 'security_error.php');
 exit();
}

//check if we are adding...
if(isset($_POST['ACTION']) && $_POST['ACTION'] == "Add"   && !isset($_POST['add_username'])) $system_message = $system_message . "You must choose a person<BR>";
if(isset($_POST['ACTION']) && $_POST['ACTION']=="Add" && isset($_POST['add_username'])) {

 //make sure we don't have a duplicate...
 $duplicate_query = "SELECT * FROM support_list WHERE egps_username='". mysql_real_escape_string($_POST['add_username']) . "' and student_id=" . mysql_real_escape_string($_POST['student_id']);
 $duplicate_result = mysql_query($duplicate_query);
 if(mysql_num_rows($duplicate_result) > 0 ) {
  //already have this person as a support member for this student...
  $system_message = $system_message . "This person already appears to be a support member for this student<BR>";
 } else {
  $insert_query = "INSERT INTO support_list (egps_username,student_id,permission,support_area) VALUES ('" . mysql_real_escape_string($_POST['add_username']) . "'," . mysql_real_escape_string($_POST['student_id']) . ",'" . mysql_real_escape_string($_POST['permission_level']) . "','" . mysql_real_escape_string($_POST['support_area']) . "')";
  $insert_result = mysql_query($insert_query);
  if(!$insert_result) {
   $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_query'<BR>";
   $system_message=$system_message . $error_message;
   IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
  } else{
   //get the support list UID before we do another query...
   $support_list_uid = mysql_insert_id();

   //successful add...log information and return to
   //this students ipp main page
   IPP_LOG("Added support member " . mysql_real_escape_string($_POST['add_username']) . " to student #" . mysql_real_escape_string($_POST['student_id']),$_SESSION['egps_username'],'INFORMATIONAL');
   if(isset($_POST['mail_notification'])) {
    //echo "mailing<BR>";
    mail_notification(mysql_real_escape_string($_POST['add_username']),"This email has been sent to you to notify you that you have been given " . mysql_real_escape_string($_POST['permission_level']) . " access to " . $student_row['first_name'] . " " . $student_row['last_name'] . "'s IPP on the $IPP_ORGANIZATION online individual program plan system.");
   }
   header("Location: " . IPP_PATH . "modify_ipp_permission.php?student_id=" . $_POST['student_id']);
   exit();
  }

 }

}


/*************************** popup chooser support function ******************/
function createJavaScript($dataSource,$arrayName='rows'){
 // validate variable name
 if(!is_string($arrayName)){
  $system_message = $system_message . "Error in popup chooser support function name supplied not a valid string  (" . __FILE__ . ":" . __LINE__ . ")";
  return FALSE;
 }

 // initialize JavaScript string
 $javascript='<!--Begin popup array--><script>var '.$arrayName.'=[];';

 // check if $dataSource is a file or a result set
 if(is_file($dataSource)){
   
  // read data from file
  $row=file($dataSource);

  // build JavaScript array
  for($i=0;$i<count($row);$i++){
   $javascript.=$arrayName.'['.$i.']="'.trim($row[$i]).'";';
  }
 }

 // read data from result set
 else{

  // check if we have a valid result set
  if(!$numRows=mysql_num_rows($dataSource)){
   die('Invalid result set parameter');
  }
  for($i=0;$i<$numRows;$i++){
   // build JavaScript array from result set
   $javascript.=$arrayName.'['.$i.']="';
   $tempOutput='';
   //output only the first column
   $row=mysql_fetch_array($dataSource);

   $tempOutput.=$row[0].' ';

   $javascript.=trim($tempOutput).'";';
  }
 }
 $javascript.='</script><!--End popup array-->'."\n";

 // return JavaScript code
 return $javascript;
}

function echoJSServicesArray() {
 global $system_message,$student_id;
 //get a list of program areas...
 $area_query = "SELECT name FROM typical_long_term_goal_category WHERE is_deleted='N' ORDER BY name ASC";
 $area_result = mysql_query($area_query);
 if(!$area_result) {
  $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_query'<BR>";
  $system_message=$system_message . $error_message;
  IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
 }  else {
  if(mysql_num_rows($area_result)) {
   //call the function to create the javascript array...
   echo createJavaScript($area_result,"popuplist");
  }
 }
}
/************************ end popup chooser support funtion  ******************/

?>
<!DOCTYPE HTML>
<HTML lang=en>
<HEAD>
<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
<TITLE><?php echo $page_title; ?></TITLE>
<style type="text/css" media="screen">
<!--
@import "<?php echo IPP_PATH;?>layout/greenborders.css";
-->
</style>

<script language="javascript"
	src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
<script language="javascript"
	src="<?php echo IPP_PATH . "include/autocomplete.js"; ?>"></script>
<?php
//output the javascript array for the chooser popup
echoJSServicesArray();
?>
</HEAD>
<BODY>
	<table class="shadow" border="0" cellspacing="0" cellpadding="0"
		align="center">
		<tr>
			<td class="shadow-topLeft"></td>
			<td class="shadow-top"></td>
			<td class="shadow-topRight"></td>
		</tr>
		<tr>
			<td class="shadow-left"></td>
			<td class="shadow-center" valign="top">
				<table class="frame" width=620px align=center border="0">
					<tr align="Center">
						<td><center>
								<img src="<?php echo $page_logo_path; ?>">
							</center></td>
					</tr>
					<tr>
						<td>
							<center>
								<?php navbar("student_view.php?student_id=$student_id"); ?>
							</center>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<div id="main">
								<?php if ($system_message) { 
								 echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";
} ?>

								<center>
									<table width="80%" cellspacing="0" cellpadding="0">
										<tr>
											<td><center>
													<p class="header">-Add Support Member to Permissions-</p>
												</center></td>
										</tr>
										<tr>
											<td><center>
													<p class="header">
														<?php echo $student_row['first_name'] . " " . $student_row['last_name']?>
													</p>
												</center></td>
										</tr>
									</table>
								</center>
								<BR>

								<center>
									<form enctype="multipart/form-data"
										action="<?php echo IPP_PATH . "new_ipp_permission.php"; ?>"
										method="post">
										<table border="0" cellpadding="0" cellspacing="0" width="80%">
											<input type="hidden" name="student_id"
												value="<?php echo $student_id;?>">
											<input type="hidden" name="egps_username"
												value="<?php echo $egps_username;?>">
											<tr>
												<td colspan="3">
													<p class="info_text">Choose a support member and click add</p>
												</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2" align="left">Username:</td>
												<td bgcolor="#E0E2F2"><?php
												echo "<SELECT MULTIPLE name=\"add_username\" size=\"8\" style=\"width:200px;text-align: left;\">\n";
												while($val = mysql_fetch_array($ipp_username_result)) {
                                  $szUsername = explode("@",$val['egps_username'],2);
                                  echo "\t<OPTION value=" . $szUsername[0] . ">" . $szUsername[0] . "</OPTION>\n";
                              }
                              echo "</SELECT>\n"
    ?>
												</td>
												<td valign="center" align="left" bgcolor="#E0E2F2"
													rowspan="8">&nbsp;&nbsp;<input type="submit" value="Add"
													name="ACTION">
												</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2">Permissions:</td>
												<td bgcolor="#E0E2F2"><SELECT name="permission_level"
													style="width: 200px; text-align: left;">
														<OPTION value="READ" SELECTED>READ
														
														<OPTION value="WRITE">WRITE
														
														<OPTION value="ASSIGN">
															ASSIGN
															<?php
															if($our_permission == 'ALL') echo "<OPTION value=\"ALL\">ALL";
															?>
												
												</SELECT>
												</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2">Send email notification</td>
												<td bgcolor="#E0E2F2"><input type="checkbox"
												<?php if(!isset($_POST['ACTION']) || (isset($_POST['ACTION']) && isset($_POST['mail_notification']))) echo "checked"; ?>
													name="mail_notification">
												</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2" colspan="2">&nbsp;</td>
											</tr>
											<tr>
												<td bgcolor="#E0E2F2">Support Area (Optional):</td>
												<td bgcolor="#E0E2F2"><input type="text" name="support_area"
													onkeypress="return autocomplete(this,event,popuplist)"> <img
													src="<?php echo IPP_PATH . "images/choosericon.png"; ?>"
													height="17" width="17" border=0
													onClick="popUpChooser(this, document.all.support_area)" />
												</td>
											</tr>
										</table>
									</form>
								</center>

							</div>
						</td>
					</tr>
				</table>
				</center>
			</td>
			<td class="shadow-right"></td>
		</tr>
		<tr>
			<td class="shadow-left">&nbsp;</td>
			<td class="shadow-center"><?php navbar("student_view.php?student_id=$student_id"); ?>
			</td>
			<td class="shadow-right">&nbsp;</td>
		</tr>
		<tr>
			<td class="shadow-bottomLeft"></td>
			<td class="shadow-bottom"></td>
			<td class="shadow-bottomRight"></td>
		</tr>
	</table>
	<center></center>
</BODY>
</HTML>
