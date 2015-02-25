<?php

/** @file
 * @brief 	manage report access?
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		
 * #. Filter input
 * #. escape output
 * #. docblock commenting
 * #. make more accessible from front end
 */

// the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 60; // Teaching staff and up

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

// ************** validated past here SESSION ACTIVE****************

// get our permissions for this student...
$our_permission = getStudentPermission($student_id);

if ($our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
    // we don't have permission...
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    require (IPP_PATH . 'security_error.php');
    exit();
}

// see if we need to update some permission values or delete somebody...
function update_permissions()
{
    global $system_message, $our_permission, $student_id;
    
    // get a list of all affected
    $user_list = "";
    foreach ($_POST as $key => $value) {
        if ($key != "delete_users" && $value == "on")
            $user_list = $user_list . "egps_username='" . str_replace("_", ".", $key) . "' or ";
    }
    // strip trailing 'or' and whitespace
    $user_list = substr($user_list, 0, - 4);
    
    $query = "UPDATE support_list SET ";
    if (isset($_POST['SET_ALL_x'])) {
        if ($our_permission != "ALL") {
            $system_message = $system_message . "You do not have sufficient permission to set this permission<BR>";
            return FALSE;
        }
        $query = $query . "permission='ALL' WHERE student_id=$student_id AND " . $user_list;
    }
    if (isset($_POST['DELETE_x'])) {
        if ($our_permission != "ASSIGN" && $our_permission != "ALL") {
            $system_message = $system_message . "You do not have sufficient permission to delete users<BR>";
            return FALSE;
        }
        $query = "DELETE FROM support_list WHERE $user_list AND student_id=$student_id";
    }
    if (isset($_POST['SET_READ_x'])) {
        if ($our_permission != "ASSIGN" && $our_permission != "ALL") {
            $system_message = $system_message . "You do not have sufficient permission to set this permission<BR>";
            return FALSE;
        }
        $query = $query . "permission='READ' WHERE student_id=$student_id AND " . $user_list;
    }
    if (isset($_POST['SET_WRITE_x'])) {
        if ($our_permission != "ASSIGN" && $our_permission != "ALL") {
            $system_message = $system_message . "You do not have sufficient permission to set this permission<BR>";
            return FALSE;
        }
        $query = $query . "permission='WRITE' WHERE student_id=$student_id AND " . $user_list;
    }
    if (isset($_POST['SET_ASSIGN_x'])) {
        if ($our_permission != "ASSIGN" && $our_permission != "ALL") {
            $system_message = $system_message . "You do not have sufficient permission to set this permission<BR>";
            return FALSE;
        }
        $query = $query . "permission='ASSIGN' WHERE student_id=$student_id AND " . $user_list;
    }
    
    $result = mysql_query($query);
    if (! $result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        $system_message = $system_message . $error_message;
    }
}
if (isset($_POST['SET_ASSIGN_x']) || isset($_POST['SET_WRITE_x']) || isset($_POST['SET_READ_x']) || isset($_POST['SET_ALL_x']) || isset($_POST['DELETE_x'])) {
    update_permissions();
}

if (! isset($_GET['iLimit']))
    $iLimit = 10;
else
    $iLimit = $_GET['iLimit'];
if (! isset($_GET['iCur']))
    $iCur = 0;
else
    $iCur = $_GET['iCur'];

$student_query = "select * from student where student.student_id=" . $student_id;
$student_result = mysql_query($student_query);
if (! $student_query) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
}

$student_row = mysql_fetch_array($student_result);

// get a list of all support members for this IPP...
function getSupportMembers()
{
    global $error_message, $iLimit, $iCur, $student_id;
    if (! connectIPPDB()) {
        $system_message = $system_message . $error_message; // just to remember we need this
        IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
    }
    // LEFT JOIN area_list ON support_list.uid=area_list.support_list_uid LEFT JOIN area_type ON area_list.area_type_id=area_type.area_type_id
    // original $query = "SELECT * FROM support_list where student_id=" . $student_id . " ORDER BY egps_username ASC LIMIT $iCur,$iLimit";
    $query = "SELECT * FROM support_list where student_id=" . $student_id . " ORDER BY egps_username ASC";
    
    $result = mysql_query($query);
    if (! $result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$query'<BR>";
        return NULL;
    }
    return $result;
}

$sqlSupportMembers = getSupportMembers();
if (! $sqlSupportMembers) {
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message, $_SESSION['egps_username'], 'ERROR');
}

// find a total num support members for nav bar...
$total_query = "SELECT * FROM support_list where student_id=" . $student_id;
$total_result = mysql_query($total_query);
if (! $total_result) {
    $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$total_query'<BR>";
}

$total_support_members = mysql_num_rows($total_result);

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
<!-- All code Copyright &copy; 2005 Grasslands Regional Division #6.
         -Concept and Design by Grasslands IPP Focus Group 2005
         -Programming and Database Design by M. Nielsen, Grasslands
          Regional Division #6
         -User Interface Design and Educational Factors by P Stoddart,
          Grasslands Regional Division #6
         -CSS and layout images are courtesy A. Clapton.
     -->
<SCRIPT LANGUAGE="JavaScript">
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to Modify/Delete:\n";
          var count = 0;
          form=document.userlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].name + " ";
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

    </SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
<?php
print_bootstrap_head();
?>
</HEAD>
<BODY>
        <?php
        
print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']);
        print_jumbotron_with_page_name("Manage Support Members", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission);
        ?>
        

        
                
                    
                    
        <div class="container">
        <?php if ($system_message) { echo "<p class=\"message\">" . $system_message . "</p>";} ?>

                        
                        

                        

                        <?php //display support... ?>
                        <form name="userlist"
            onSubmit="return deleteChecked()"
            enctype="multipart/form-data"
            action="<?php echo IPP_PATH . "modify_ipp_permission.php"; ?>"
            method="post">
            <input type="hidden" name="student_id"
                value="<?php echo $student_id ?>">



            <h3>Browse Current Support Group <small>Scroll down to add members to team.</small></h3>
            <p>Please note: removing yourself from this list will remove
                your access to this student's IPP (even if you are still
                listed as the supervisor for this student). You will
                have to contact your school based IPP administrator to
                have your permissions restored.</p>
                        
                        
                        
                        <?php
                        
                        // print the next and prev links...
                        ?>
                        
                        <?php
                        /*
                         *
                         * echo "<tr><td>";
                         * if($iCur != 0) {
                         * //we have previous values...
                         * echo "<a href=\"./modify_ipp_permissions.php?iCur=" . ($iCur-$iLimit) . "\" class=\"default\">previous $iLimit</a>";
                         * } else {
                         * echo "&nbsp;";
                         * }
                         */
                        // echo "</td></tr></table>";
                        ?>
                        
                        <?php
                        /**
                         * if ( ($iLimit+$iCur) < $total_support_members) {
                         * echo "<tr></tr><td align=\"right\"><a href=\"./modify_ipp_permissions.php?iCur=" . ($iCur+$iLimit) . "\" class=\"default\">next ";
                         * if( $total_support_members-($iCur+$iLimit) > $iLimit) {
                         * echo $iLimit . "</td>";
                         * } else {
                         * echo ($total_support_members-($iCur+$iLimit)) . "</td>";
                         * }
                         * } else {
                         * echo "<td>&nbsp;</td>";
                         * }
                         * echo "</tr>\n";
                         * //end print next and prev links
                         */
                        
                        // print the header row...
                        ?>
                        <table class="table table-hover table-striped">
                <tr>
                    <th>&nbsp;</th>
                    <th align="center">Username</th>
                    <th align="center">permission_level</th>
                    <th align="center">Support Area</th>
                    <th align="center">&nbsp;</th>
                </tr>
                        <?php
                        while ($users_row = mysql_fetch_array($sqlSupportMembers)) {
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $users_row['egps_username'] . "\"></td>";
                            echo "<td>" . $users_row['egps_username'] . "</td>\n";
                            echo "<td>" . $users_row['permission'] . "</td>\n";
                            echo "<td>";
                            if (! $users_row['support_area'])
                                echo "None assigned";
                            else
                                echo $users_row['support_area'];
                            echo "</td>\n";
                            echo "<td><a href=\"" . IPP_PATH . "edit_support_member.php?username=" . $users_row['egps_username'] . "&student_id=$student_id" . "\"><IMG SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Edit\" border=\"0\"></a></td>";
                            echo "</tr>\n";
                        }
                        ?>
                        <tr>
                    <td colspan="5" align="left">
                        <table>
                            <tr>
                                <td nowrap><img
                                    src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With
                                    Selected:</td>
                                <td><INPUT NAME="SET_ASSIGN"
                                    TYPE="image"
                                    SRC="<?php echo IPP_PATH . "images/smallbutton.php?title=Set Assign"; ?>"
                                    border="0" value="SET_ASSIGN"> <INPUT
                                    NAME="SET_WRITE" TYPE="image"
                                    SRC="<?php echo IPP_PATH . "images/smallbutton.php?title=Set Write"; ?>"
                                    border="0" value="SET_WRITE"> <INPUT
                                    NAME="SET_READ" TYPE="image"
                                    SRC="<?php echo IPP_PATH . "images/smallbutton.php?title=Set Read"; ?>"
                                    border="0" value="SET_READ">
                             <?php
                            // if we have all permissions also allow delete and set all...
                            if ($our_permission == "ALL") {
                                echo "<INPUT NAME=\"SET_ALL\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Set All\" border=\"0\" value=\"SET_ALL\">";
                            }
                            if ($our_permission == "ASSIGN" || $our_permission == "ALL") {
                                echo "<INPUT NAME=\"DELETE\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"DELETE\">";
                            }
                            ?>
                             </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </center>
        </form>

        <BR>

    </div>
    </td>
    </tr>
    </table>
    </center>
    </td>
    <td class="shadow-right"></td>
    </tr>


    </table>
<section id="add">
    <div class="container">

    <!-- BEGIN new member add -->

    <form enctype="multipart/form-data"
        action="<?php echo IPP_PATH . "new_ipp_permission.php"; ?>"
        method="get"
        <?php if($our_permission != "ASSIGN" && $our_permission != "ALL") echo "onSubmit=\"return noPermission();\"" ?>>

        <input type="hidden" name="student_id"
            value="<?php echo $student_id;?>">
        <h3>
            Add member <small>(firstinitial and lastname)</small>
        </h3>

        <p>IEP-IPP Username: 
        <p>
                        
        
        
        <p>
            
            <input type="text" name="username" length="30">
        
        </p>
                        <p>(Wildcards: '%'=match any '_'=match single)</p>
                        <p></p>
        
        <input type="submit" value="Search">
        
        </p>
                        
                        
                        
                       
                        </form>
                        
                        <!-- END NEW MEMBER ADD -->
</div>
</section>
        <?php print_bootstrap_js(); ?>
    
        
        </BODY>
</HTML>
