<?php

/** @file
 * @brief 	manage code availability and validity (as superuser)
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
$MINIMUM_AUTHORIZATION_LEVEL = 0; //only super administrator

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
require_once 'include/supporting_functions.php';

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
    if(!preg_match($regexp, $_POST['code'])) return "You must supply a valid code number (numbers only)<BR>";
    if(!$_POST['code_text']) return "You must supply a code description<BR>";

    return NULL;
}

//check if we are adding a code...
if (isset($_POST['add_code'])) {
  $retval=parse_submission();
  if ($retval != NULL) {
    //no way...
    $system_message = $system_message . $retval;
  } else {
    //we add the entry.
    $insert_query = "INSERT INTO valid_coding (code_number,code_text) VALUES ('" . mysql_real_escape_string($_POST['code']) . "','" . mysql_real_escape_string($_POST['code_text']) . "')";
    $insert_result = mysql_query($insert_query);
     if (!$insert_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '" . $insert_query . "<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
     } else {
        //clear some fields
        unset($_POST['code']);
        unset($_POST['code_text']);
     }
  }
}

//check if we are deleting some entries...
if (isset($_POST['delete_x']) && $permission_level <= $IPP_MIN_DELETE_CODE) {
    $delete_query = "DELETE FROM valid_coding WHERE ";
    foreach ($_POST as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "code_number=" . $key . " or ";
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

$code_query="SELECT * FROM valid_coding WHERE 1 ORDER by code_number ASC";
$code_result = mysql_query($code_query);
if (!$code_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$code_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

?>
<?php print_html5_primer(); ?>
<title><?php echo $page_title; ?></title>


<script language="JavaScript">
      function confirmChecked()
      {
          var szGetVars = "codelist=";
          var szConfirmMessage = "Are you sure you want to delete the following:\n";
          var count = 0;
          form=document.codelist;
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
    </script>
<?php print_bootstrap_head();?>
</head>
<body>
    <?php
    print_general_navbar();
    print_lesser_jumbotron("Manage Codes", $permission_level);
    ?>
    <div class="container">
        <?php if ($system_message) { echo "<p>" . $system_message . "</p>";} ?>


<!-- BEGIN codes table -->
<h2>Code Reference <small>Scroll Down to Add Codes</small></h2>
<form name="codelist" onsubmit="return confirmChecked();" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_coding.php"; ?>" method="post">

<table class="table table-striped table-hover">
<?php
//print the header row...
                        echo "<tr><th>Select</th><th>Code</th><th>Code Description</th></tr>\n";
                        while ($code_row=mysql_fetch_array($code_result)) { //current...
                            echo "<tr>\n";
                            echo "<td><input type=\"checkbox\" name=\"" . $code_row['code_number'] . "\"></td>";
                            echo "<td>" . $code_row['code_number'] . "</td>\n";
                            echo "<td>" . $code_row['code_text']  . "</td>\n";
                            echo "</tr>\n";

                        }
                        ?>

                        <table>
                                <tr>
                                    <td nowrap><img
                                        src="<?php echo IPP_PATH . "images/table_arrow.png"; ?>">&nbsp;With
                                        Selected:</td>
                                    <td><?php
                                //if we have permissions also allow delete.
                                if ($permission_level <= $IPP_MIN_DELETE_SCHOOL) {
                                    echo "<INPUT NAME=\"delete\" TYPE=\"image\" SRC=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" value=\"1\">";
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
        <!-- end codes table -->

<!-- BEGIN add code -->
<h2>Enter New Coding and Click "Create Code"</h2>
<form name="add_code" enctype="multipart/form-data" action="<?php echo IPP_PATH . "superuser_manage_coding.php"; ?>" method="post">
<input type="hidden" name="add_code" value="1">
<div class="form-group">
<label>Code Number</label>
<input required class="form-control" type="text" tabindex="1" name="code" value="<?php if(isset($_POST['code']))  echo $_POST['code']; ?>" size="10" maxsize="10">

<label>Code Description</label>
<input required class="form-control" type="text" tabindex="2" name="code_text" value="<?php if(isset($_POST['code_text'])) echo $_POST['code_text']; ?>" size="30" maxsize="254"></td>
</div>
<button type="submit" tabindex="3" value="add" value="add">Create Code</button>

</form>
<!-- END add code -->

    <footer>
        <?php print_complete_footer(); ?>
    </footer>
    <?php print_bootstrap_js(); ?>
    </div>
</body>
</html>
