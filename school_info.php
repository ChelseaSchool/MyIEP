<?php

/** @file
 * @brief 	view school specific information / add school
 * @copyright 	2014 Chelsea School
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo
 * 1. filter input, escape output
 * 2. do something about legacy JS buttons
 * 3. note that edit school is broken
 */

//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 0; //only super administrator



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

//************** validated past here ****************

function parse_submission()
{
    //returns null on success else returns $szError
    global $content,$fileName,$fileType;
    $regexp='/^[0-9]*$/';
    if(!preg_match($regexp, $_POST['school_code'])) return "You must supply a valid school code (numbers only)<BR>";
    if(!$_POST['school_name']) return "You must supply a school name<BR>";
    if(!$_POST['school_address']) return "You must supply a school address<BR>";
    if(!$_POST['school_colour']) $_POST['school_colour'] = "#FFFFFF";

    //check that color is the correct pattern...
    $regexp = '/^#[0-9a-fA-F]{6}$/';
    if(!preg_match($regexp,$_POST['school_colour'])) return "Color must be in '#RRGGBB' format<BR>";

    return NULL;
}

//check if we are modifying a student...
if (isset($_POST['add_school'])) {
  $retval=parse_submission();
  if ($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $red=substr($_POST['school_colour'],1,2);
    $green=substr($_POST['school_colour'],3,2);
    $blue=substr($_POST['school_colour'],5,2);
    $insert_query = "INSERT INTO school (school_code,school_name,school_address,red,green,blue) VALUES ('" . mysql_real_escape_string($_POST['school_code']) . "','" . mysql_real_escape_string($_POST['school_name']) . "','" . mysql_real_escape_string($_POST['school_address']) . "','$red','$green','$blue')";
    $insert_result = mysql_query($insert_query);
     if (!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . $insert_query . "<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        //clear some fields
        unset($_POST['school_code']);
        unset($_POST['school_name']);
        unset($_POST['school_address']);
        unset($_POST['school_colour']);
     }
  }
}

//check if we are deleting some entries...
if (isset($_GET['delete_x']) && $permission_level <= $IPP_MIN_DELETE_SCHOOL) {
    $delete_query = "DELETE FROM school WHERE ";
    foreach ($_GET as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "school_code=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if (!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
   }
}

//get the medication for this student...
$school_query="SELECT * FROM school WHERE 1 ORDER by school_name ASC";
$school_result = mysql_query($school_query);
if (!$school_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

print_html5_primer();
?>


    <script language="javascript" src="<?php echo IPP_PATH . "include/picker.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked()
      {
          var szGetVars = "schoollist=";
          var szConfirmMessage = "Are you sure you want to delete the following:\n";
          var count = 0;
          form=document.schoollist;
          for (var x=0; x<form.elements.length; x++) {
              if (form.elements[x].type=="checkbox") {
                  if (form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].name + ",";
                     count++;
                  }
              }
          }
          if (!count) { alert("Nothing Selected"); return false; }
          if(confirm(szConfirmMessage))

              return true;
          else
              return false;
      }
    </SCRIPT>
<?php print_bootstrap_head();?>
</HEAD>
    <BODY>
    <?php
    print_general_navbar();
    print_lesser_jumbotron("Manage Schools", $permission_level);
    ?>
    <div class="container">
    <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>

    <h2>View and Edit Schools <small>Scroll Down to Add Schools</small></h2>
                        <!-- BEGIN school table -->
                        <form name="schoollist" onSubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_info.php"; ?>" method="get">

                        <table class="table table-striped table-hover">
                        <?php

                        //print the header row...

                        echo "<tr><th>Select</th><th>Code</th><th>School Name</th><th>School Address</th><th>School Color</th></tr>\n";
                        while ($school_row=mysql_fetch_array($school_result)) { //current...
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $school_row['school_code'] . "\"></td>";
                            echo "<td><a href=\"" . IPP_PATH . "edit_school.php?school_code=" . $school_row['school_code'] . "\" class=\"editable_text\">" . $school_row['school_code']  ."</a></td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_school.php?school_code=" . $school_row['school_code'] . "\" class=\"editable_text\">" . $school_row['school_name']  ."</a></td>\n";
                            echo "<td><address>" . $school_row['school_address'] . "</address></td>\n";
                            echo "<td>" . $school_row['red'] . $school_row['green'] . $school_row['blue']  . "</td>\n";
                            echo "</tr>\n";

                        }
                        ?>
                             </table>
                             <table>
                             <tr>
                             <td nowrap>
                                <img src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With Selected:
                             </td>
                             <td>
                             <?php
                                //if we have permissions also allow delete.
                                if ($permission_level <= $IPP_MIN_DELETE_SCHOOL) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
                                }
                             ?>
                             </td>
                             </tr>
                             </table>

                        </form>
                        <!-- end school table -->

    <h2>Add School <small>Edit and Click "Add School"</small></h2>
                        <!-- BEGIN add school -->

                        <form name="add_school" enctype="multipart/form-data" action="<?php echo IPP_PATH . "school_info.php"; ?>" method="post">
                        <input type="hidden" name="add_school" value="1">
                        
                        <div class="form-group">
                         <label>School Code</label>
                         <input class="form-control" type="text" tabindex="1" name="school_code" value="<?php if(isset($_POST['school_code']))  echo $_POST['school_code']; ?>" size="30" maxsize="254">
                         </div>
                         
                         <div class="form-group">
                            <label>School Name</label>

                            <input required class="form-control" type="text" tabindex="2" name="school_name" value="<?php if(isset($_POST['school_name'])) echo $_POST['school_name']; ?>" size="30" maxsize="254">
                          </div>
                       <div class="form-group">
                           <label>School Address</label>
                          <textarea class="form-control" required spellcheck="true" name="school_address" tabindex="3" cols="30" rows="3" wrap="soft"><?php if(isset($_POST['school_address'])) echo $_POST['school_address']; ?></textarea>
                        </div>
                        <p>
                        <div class="form-group">
                           <label>School Color &nbsp;</label>
                           <INPUT TYPE="color" NAME="school_colour" MAXLENGTH="7" tabindex="4" SIZE="7" value="<?php if(isset($_POST['school_colour']))echo $_POST['school_colour']; ?>">
                           </div> 
                          <button class="btn btn-primary" type="submit" tabindex="5" value="add" value="add">Add School</button>
                        </form>
                        <!-- END add school -->

                       </div>

        <?php print_bootstrap_js();?>
    </BODY>
</HTML>
