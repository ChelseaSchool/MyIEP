<?php
/* @file
 * @remarks
 * 1. line 23 needs clarification and perhaps justification
 * 2. Relies on mysql_real_escape_string() builtin function to escape output; standardize on alternative contemporary best practice
 * 3. Copyright header to be updated per license recommendation
 * 4. Check HTML standardization against James' findings
 */
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;    //everybody (do checks within document)



/**
 * Path for IPP required files.
 */

$system_message = $system_message;

define('IPP_PATH','../');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');

header('Pragma: no-cache'); //don't cache this page!

if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'login.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'login.php');
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

if(!isset($_GET['student_id']) || $_GET['student_id'] == "") {
    //ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit();
} else {
    $student_id=$_GET['student_id'];
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

//check if we are adding...
if(isset($_GET['add_long_term_goal']) && $have_write_permission) {
    if(!isset($_GET['description']) || $_GET['description'] == "") {
        $system_message = $system_message . "You must supply a description of this goal<BR>";
    }  else {
        //check that date is the correct pattern...
        $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
        if(!preg_match($regexp,$_GET['review_date'])) { $system_message = $system_message . "Date must be in YYYY-MM-DD format<BR>"; }
        else {
            if($_GET['area_type_id'] == "") {
                $area_type_id = "NULL";
            } else {
                $area_type_id = $_GET['area_type_id'];
            }
            $insert_goal_query="INSERT INTO long_term_goal (goal,student_id,review_date,area_type_id) VALUES ('" . mysql_real_escape_string($_GET['description']) . "',$student_id,'" . mysql_real_escape_string($_GET['review_date']) . "'," . mysql_real_escape_string($area_type_id) . ")";
            $insert_goal_result = mysql_query($insert_goal_query);
            if(!$insert_goal_result) {
                $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$insert_goal_query'<BR>";
                $system_message=$system_message . $error_message;
                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            }  else {
                unset($_GET['description']);
                unset($_GET['review_date']);
                unset($_GET['area_type_id']);
            }
        }
    }
}

$long_goal_query = "SELECT * FROM long_term_goal LEFT JOIN area_type on long_term_goal.area_type_id=area_type.area_type_id WHERE student_id=$student_id AND is_complete='N'";
$long_goal_result = mysql_query($long_goal_query);
if(!$long_goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$long_goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$long_completed_goal_query = "SELECT * FROM long_term_goal LEFT JOIN area_type on long_term_goal.area_type_id=area_type.area_type_id WHERE student_id=$student_id AND is_complete='Y'";
$long_completed_goal_result = mysql_query($long_completed_goal_query);
if(!$long_completed_goal_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$long_goal_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

$area_type_query = "SELECT  * from  typical_long_term_goal_category WHERE 1";
$area_type_result = mysql_query($area_type_query);
if(!$area_type_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$area_type_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
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
        global $system_message;
        //get a list of all available goal categories...
        $catlist_query="SELECT * FROM typical_long_term_goal_category where is_deleted='N'";
        $catlist_result=mysql_query($catlist_query);
        if(!$catlist_result) {
            $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$catlist_query'<BR>";
            $system_message= $system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
            return;
        }

        while($catlist=mysql_fetch_array($catlist_result)) {
           $objlist_query="SELECT typical_long_term_goal.goal FROM typical_long_term_goal WHERE cid=" . $catlist['cid'] . " AND typical_long_term_goal.is_deleted='N'";
           $objlist_result = mysql_query($objlist_query);
           if(!$objlist_result) {
             $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$objlist_query'<BR>";
             $system_message= $system_message . $error_message;
             IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
           } else {
             //call the function to create the javascript array...
             echo createJavaScript($objlist_result,$catlist['name']);
           }
        }
    }
/************************ end popup chooser support funtion  ******************/

?> 

<!DOCTYPE HTML>
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
    <TITLE><?php echo $page_title; ?></TITLE>
    
   
     <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
     <script language="javascript" src="<?php echo IPP_PATH . "include/popupchooser.js"; ?>"></script>
     <?php
       //output the javascript array for the chooser popup
       echoJSServicesArray();
     ?>
     <SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }

      function noSelection() {
          alert("You must choose a goal category first"); return false;
      }

    </SCRIPT>
</HEAD>
<BODY>
<?php navigate_student_records() ?>
   
        <table class="shadow" border="0" cellspacing="0" cellpadding="0" align="center">  
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
                    <td><center><img src="<?php echo $page_logo_path; ?>"></center></td>
                    </tr>
                    <tr>
                        <td valign="top">
                        <div id="main">
                        <?php if ($system_message) { echo "<center><table width=\"80%\"><tr><td><p class=\"message\">" . $system_message . "</p></td></tr></table></center>";} ?>

                        <center><table width="80%" cellspacing="0" cellpadding="0"><tr><td><center><p class="header">- IPP Long Term Goal (<?PHP echo $student_row['last_name'] . "," . $student_row['first_name']; ?>)-</p></center></td></tr><tr><td><center><p class="bold_text"> <?php echo $student_row['first_name'] . " " . $student_row['last_name'] .  ", Permission: " . $our_permission;?></p></center></td></tr></table></center>
                        <BR>

                        <!-- BEGIN add new entry -->
                        <center>
                        <form name="add_long_term_goal" enctype="multipart/form-data" action="<?php echo IPP_PATH . "long_term_goal_view.php"; ?>" method="get" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
                        <table border="0" cellspacing="0" cellpadding ="0" width="80%">
                        <tr>
                          <td colspan="3">
                          <p class="info_text">New long term goal</p>
                           <input type="hidden" name="add_long_term_goal" value="1">
                           <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                          </td>
                        </tr>
                        <tr>
                           <td bgcolor="#F4EFCF" class="row_default">Goal Category</td>
                           <td bgcolor="#F4EFCF" class="row_default">
                           <select name="area_type_id">
                            <option value="">SELECT</option>
                            <option value="">not applicable</option>
                            <?php
                            while ($area_row = mysql_fetch_array($area_type_result)) {
                               echo "<option value=" . $area_row['cid'];
                               if($area_row['cid'] == $_GET['cid']) echo " SELECTED";
                               echo  " onclick=\"popuplist=" . $area_row['name'] . ".slice();\">" . $area_row['name'] . "</option>\n";
                            }
                            ?>
                            </select>
                           </td>
                           <td valign="center" align="center" bgcolor="#F4EFCF" rowspan="3" class="row_default"><input type="submit" name="add" value="add"></td>
                        </tr>
                        <tr>
                           <td valign="center" bgcolor="#F4EFCF" class="row_default">Description:</td><td bgcolor="#F4EFCF" class="row_default"><textarea name="description" cols="23" rows="2" wrap="hard"><?php echo $_GET['description']; ?></textarea>&nbsp;<img align="top" src="<?php echo IPP_PATH . "images/choosericon.png"; ?>" height="17" width="17" border=0 onClick="if(add_long_term_goal.area_type_id.value == '')noSelection(); else popUpChooser(this,document.all.description);" ></td>
                        </tr>
                        <tr>
                           <td bgcolor="#F4EFCF" class="row_default">Review Date: (YYYY-MM-DD)</td>
                           <td bgcolor="#F4EFCF" class="row_default">
                               <input type="text" size="30" name="review_date" value="<?php echo $_GET['review_date']; ?>">&nbsp;<img src="<?php echo IPP_PATH . "images/calendaricon.gif"; ?>" height="17" width="17" border=0 onClick="popUpCalendar(this, document.all.review_date, 'yyyy-m-dd', 0, 0)">
                           </td>
                        </tr>
                        </table>
                        </form>
                        </center>
                        <!-- END add new entry -->

                        <?php $colour0="#DFDFDF"; $colour1="#CCCCCC"; ?>

                        <HR>
                        <!-- BEGIN  Incomplete Goals -->
                        <table width="100%"><tr><td><p class="header" align="left">&nbsp;Goal(s) in Progress:</p></tr></table>
                        <BR>
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <?php
                        //Loops through list of goals in progress
                        while($goal = mysql_fetch_array($long_goal_result)) {
                            echo "<tr><td colspan=\"2\" class=\"wrap_top\"><a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&setCompleted=" . $goal['goal_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Set+Achieved\" border=\"0\" width=\"100\" height=\"25\" ></a>";
                            echo "&nbsp;<a href=\"" . IPP_PATH . "long_term_goal_view.php?student_id=" . $student_id . "&deleteGoal=" . $goal['goal_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            else echo "onClick=\"return changeStatusCompleted();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" width=\"100\" height=\"25\" ></a>";
                            echo "</td></tr>\n";

                            //begin description
                            //width = 100% in first column is workaround for IE6 issue...
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" width=\"100%\"><CENTER><B>" . $goal['goal'] . "</B> (Review: " . $goal['review_date'] . ")</CENTER></td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "</tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><CENTER>(area: ";
                            if($goal['type']) echo $goal['type']; else echo "not applicable";
                            echo ")</CENTER></td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">Short Term Objectives:</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">&nbsp;</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "<tr>\n";
                            echo "<td bgcolor=\"$colour0\" class=\"wrap_bottom_left\">\n";
                            //short term objectives
                            $short_term_objective_query = "SELECT * FROM short_term_objective WHERE goal_id=" . $goal['goal_id'] . " ORDER BY review_date DESC";
                            $short_term_objective_result = mysql_query($short_term_objective_query);
                            //check for error
                            if(!$short_term_objective_result) {
                                $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$short_term_objective_query'<BR>";
                                $system_message=$system_message . $error_message;
                                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                                echo $system_message;
                            } else {
                               //output this note...
                               //check if we have no notes
                               if(mysql_num_rows($short_term_objective_result) <= 0 ) {
                                   //yes so output non-breaking space
                                   echo "<center>-none-</center>";
                               }
                               
                               //loops through objectives in progress
                               while ($short_term_objective_row = mysql_fetch_array($short_term_objective_result)) {
                                   echo "<hr>\n";
                                   echo "<table width=\"100%\"><tr>\n";
                                   echo "<td width=\"32\">&nbsp;</td>"; //no priority flag.
                                   echo "<td class=\"wrap_none\">" . $short_term_objective_row['description'] ."</td>";
                                   echo "</tr></table>\n";
                               }
                            }
                            //end guardian notes
                            echo "</td>\n";
                            echo "<td class=\"wrap_bottom_right\" width=\"100\"><a href=\"" . IPP_PATH . "short_term_objectives.php?goal_id=" . $goal['goal_id'] ."\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit\" border=\"0\" width=\"100\" height=\"25\"></td>";
                            echo "</tr>\n";
                            echo "<tr><td>&nbsp;</td><td width=\"100\">&nbsp;</td></tr>";
                        }
                        ?>
                        </table>
                        </center>
                        <!-- END incomplete goals -->

                        <!-- BEGIN  complete goals -->
                        <p class="header" align="left">Completed Goal(s):</p><BR>
                        <center>
                        <table width="80%" border="0" cellpadding="0" cellspacing="0">
                        <?php
                        while($completed_goal = mysql_fetch_array($long_completed_goal_result)) {
                            echo "<tr><td colspan=\"2\" class=\"wrap_top\">" . $guardian['last_name'] . "," . $guardian['first_name'] . "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "guardian_view.php?student_id=" . $student_id . "&setGuardian=" . $guardian['uid'] . "\"";
                            if($have_write_permission) echo "onclick=\"return changeStatusGuardian();\"";
                            else echo "onclick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Change Status\" border=\"0\" width=\"100\" height=\"25\"></a>&nbsp;&nbsp;<a href=\"" . IPP_PATH . "notyetimplemented.php" . "\" onClick=\"return notYetImplemented();\"><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Involvement\" border=\"0\" width=\"100\" height=\"25\"></a></td></tr>\n";
                            //begin lost guardianship date
                            //width=100% first column is workaround for IE6 issue.
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\" width=\"100%\">Guardianship Terminated:<BR><BR><CENTER>" . $guardian['to_date'] . "</CENTER></td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            //begin address
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">Address:</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><center>\n";
                            if($guardian['po_box']) echo "P.O. Box " . $guardian['po_box'] . "<BR>";
                            echo $guardian['street'] . "<BR>\n";
                            $cpc = "";
                            if($guardian['city']) $cpc=$cpc . $guardian['city'] . " ";
                            if($guardian['province']) $cpc=$cpc . $guardian['province'] . " ";
                            if($guardian['country']) $cpc=$cpc . $guardian['country'];
                            //add the commas...
                            $cpc = str_replace(" ",",",$cpc);
                            echo $cpc . "<BR>\n";
                            echo $guardian['postal_code'] . "<BR><BR>\n";
                            echo "</center></td>";
                            echo "<td class=\"wrap_right\" width=\"100\"><a href=\"" . IPP_PATH . "edit_address.php?student_id=" . $_GET['student_id'] . "&target=guardian&guardian_id=" . $guardian['guardian_id'] . "\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit Contact\" border=\"0\" width=\"100\" height=\"25\"></td>";
                            echo "</tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">Contact:</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\"><center>\n";
                            if($guardian['home_ph'] != "") echo "Ph: " . $guardian['home_ph'] . "<BR>\n";
                            if($guardian['business_ph'] != "")echo "Business: " . $guardian['business_ph'] . "<BR>\n";
                            if($guardian['cell_ph'] != "")echo "Cell Ph: " . $guardian['cell_ph'] . "<BR>\n";
                            if($guardian['email_address'] != "")echo "Email: " . $guardian['email_address'] . "<BR>\n";
                            echo "&nbsp;"; //just in case we have a blank
                            echo "</center></td>";
                            echo "<td class=\"wrap_right\" width=\"100\">&nbsp;</td>";
                            echo "</tr>\n";
                            echo "<tr><td class=\"wrap_left\" bgcolor=\"$colour0\">Notes:</td><td class=\"wrap_right\" width=\"100\">&nbsp;</td></tr>\n";
                            echo "<tr>\n";
                            echo "<td bgcolor=\"$colour0\" class=\"wrap_bottom_left\">\n";
                            //guardian notes
                            $guardian_note_query = "SELECT * FROM guardian_note WHERE guardian_id=" . $guardian['guardian_id'] . " ORDER BY date ASC,priority_note DESC";
                            $guardian_note_result = mysql_query($guardian_note_query);
                            //check for error
                            if(!$guardian_note_result) {
                                $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$guardian_note_query'<BR>";
                                $system_message=$system_message . $error_message;
                                IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
                                echo $system_message;
                            } else {
                               //output this note...
                               //check if we have no notes
                               if(mysql_num_rows($guardian_note_result) <= 0 ) {
                                   //yes so output non-breaking space
                                   echo "&nbsp;";
                               }
                               while ($guardian_note_row = mysql_fetch_array($guardian_note_result)) {
                                   echo "<hr>\n";
                                   echo "<table width=\"100%\"><tr>\n";
                                   if($guardian_note_row['priority_note'] == 'Y')
                                       echo "<td width=\"32\"><img src=\"" . IPP_PATH . "images/caution.gif\" border=\"0\" width=\"32\" height=\"32\"></td>"; //priority flag.
                                   else
                                       echo "<td width=\"32\">&nbsp;</td>"; //no priority flag.
                                   echo "<td class=\"wrap_none\">" . $guardian_note_row['note'] ."</td>";
                                   echo "</tr></table>\n";
                               }
                            }
                            //end guardian notes
                            echo "</td>\n";
                            echo "<td class=\"wrap_bottom_right\" width=\"100\"><a href=\"" . IPP_PATH . "guardian_notes.php?guardian_id=" . $guardian['guardian_id'] . "&student_id=" . $student_row['student_id'] ."\"";
                            if (!$have_write_permission) echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Notes\" border=\"0\" width=\"100\" height=\"25\"></td>";
                            echo "</tr>\n";
                            echo "<tr><td>&nbsp;</td><td width=\"100\">&nbsp;</td></tr>";
                        }
                        ?>
                        </table>
                        </center>
                        <!-- END Previous Guardian Info -->

                        </div>
                        </td>
                    </tr>
                </table></center>
            </td>
            <td class="shadow-right"></td>   
        </tr>
        <tr>
            <td class="shadow-left">&nbsp;</td>
            <td class="shadow-center"><table border="0" width="100%"><tr><td width="60"><a href="
            <?php
                echo IPP_PATH . "student_view.php?student_id=" . $student_row['student_id'];
            ?>"><img src="<?php echo IPP_PATH; ?>images/back-arrow.png" border=0></a></td><td width="60"><a href="<?php echo IPP_PATH . "main.php"; ?>"><img src="<?php echo IPP_PATH; ?>images/homebutton.png" border=0></a></td><td valign="bottom" align="center">Logged in as: <?php echo $_SESSION['egps_username'];?></td><td align="right"><a href="<?php echo IPP_PATH;?>"><img src="<?php echo IPP_PATH; ?>images/logout.png" border=0></a></td></tr></table></td>
            <td class="shadow-right">&nbsp;</td>
        </tr>
        <tr>
            <td class="shadow-bottomLeft"></td>
            <td class="shadow-bottom"></td>
            <td class="shadow-bottomRight"></td>
        </tr>
        </table> 
        <center>System Copyright &copy; 2005 Grasslands Regional Division #6.</center>
    </BODY>
</HTML>
