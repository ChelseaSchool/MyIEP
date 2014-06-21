<?php
/** @file
 * 
 *  @brief	display guardian information
 *  
 *  @todo
 *  #. Bootsrap UI
 *  #. Docblock commenting
 *  
 * 
 */

// the authorization level for this page!
// everybody (do checks within document)
$MINIMUM_AUTHORIZATION_LEVEL = 100;

/**
 * Path for IPP required files.
 */

if (isset ( $system_message ))
    $system_message = $system_message;
else
    $system_message = "";

define ( 'IPP_PATH', './' );

/* eGPS required files. */
require_once IPP_PATH . 'etc/init.php';
require_once IPP_PATH . 'include/db.php';
require_once IPP_PATH . 'include/auth.php';
require_once IPP_PATH . 'include/log.php';
require_once IPP_PATH . 'include/user_functions.php';
require_once IPP_PATH . 'include/navbar.php';
require_once IPP_PATH . 'include/supporting_functions.php';

header ( 'Pragma: no-cache' ); // don't cache this page!

if (isset ( $_POST ['LOGIN_NAME'] ) && isset ( $_POST ['PASSWORD'] )) {
    if (! validate ( $_POST ['LOGIN_NAME'], $_POST ['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
        require (IPP_PATH . 'index.php');
        exit ();
    }
} else {
    if (! validate ()) {
        $system_message = $system_message . $error_message;
        IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
        require (IPP_PATH . 'index.php');
        exit ();
    }
}
// ************* SESSION active past here **************************

// check permission levels
$permission_level = getPermissionLevel ( $_SESSION ['egps_username'] );
if ($permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER ['REMOTE_ADDR'] . ")";
    IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
    require (IPP_PATH . 'security_error.php');
    exit ();
}

if (! isset ( $_GET ['student_id'] ) || $_GET ['student_id'] == "") {
    // ack
    echo "You've come to this page without a valid student ID<BR>To what end I wonder...<BR>";
    exit ();
} else {
    $student_id = $_GET ['student_id'];
}

$student_query = "select * from student where student.student_id=" . $_GET ['student_id'];
$student_result = mysql_query ( $student_query );
if (! $student_query) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$student_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
}

$student_row = mysql_fetch_array ( $student_result );

// get out permissions for this student...
$current_student_permission = getStudentPermission ( $student_row ['student_id'] );

// check if we need to update the guardian list and have the required permissions to do so...
if (isset ( $_GET ['setGuardian'] ) && ($current_student_permission == "ALL" || $current_student_permission == "ASSIGN" || $current_student_permission == "WRITE")) {
    // update this guardian student combination...
    $update_query = "UPDATE guardians SET to_date=NULL WHERE student_id=" . $student_row ['student_id'] . " AND uid=" . $_GET ['setGuardian'];
    $update_result = mysql_query ( $update_query );
    if (! $update_query) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
        $system_message = $system_message . $error_message;
        IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
    }
}

// check if we need to update the guardian list and have the required permissions to do so...
if (isset ( $_GET ['setNotGuardian'] ) && ($current_student_permission == "ALL" || $current_student_permission == "ASSIGN" || $current_student_permission == "WRITE")) {
    // update this guardian student combination...
    $update_query = "UPDATE guardians SET to_date=now() WHERE student_id=" . $student_row ['student_id'] . " AND uid=" . $_GET ['setNotGuardian'];
    $update_result = mysql_query ( $update_query );
    if (! $update_query) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
        $system_message = $system_message . $error_message;
        IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
    }
}

// check if we need to delete and have the required permissions to do so...
if (isset ( $_GET ['deleteGuardian'] ) && ($current_student_permission == "ALL" || $current_student_permission == "ASSIGN" || $current_student_permission == "WRITE")) {
    // update this guardian student combination...
    $update_query = "DELETE FROM guardians WHERE student_id=" . $student_row ['student_id'] . " AND uid=" . $_GET ['deleteGuardian'];
    $update_result = mysql_query ( $update_query );
    if (! $update_query) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$update_query'<BR>";
        $system_message = $system_message . $error_message;
        IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
    }
}

// find guardians...
$guardians_query = "SELECT * FROM guardians LEFT JOIN guardian ON guardians.guardian_id=guardian.guardian_id LEFT JOIN address on guardian.address_id=address.address_id WHERE guardians.student_id=" . $_GET ['student_id'] . " AND guardians.to_date IS NULL";
$guardians_result = mysql_query ( $guardians_query );
if (! $guardians_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$guardians_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
}

// find previous guardians...
$previous_guardians_query = "SELECT * FROM guardians LEFT JOIN guardian ON guardians.guardian_id=guardian.guardian_id LEFT JOIN address on guardian.address_id=address.address_id WHERE guardians.student_id=" . $_GET ['student_id'] . " AND guardians.to_date IS NOT NULL";
$previous_guardians_result = mysql_query ( $previous_guardians_query );
if (! $previous_guardians_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$previous_guardians_query'<BR>";
    $system_message = $system_message . $error_message;
    IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
}

// reevaluate our permissions for this student...
$our_permission = getStudentPermission ( $_GET ['student_id'] );

if ($our_permission != "READ" && $our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
    // we don't have permission...
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER ['REMOTE_ADDR'] . ")";
    IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
    require (IPP_PATH . 'security_error.php');
    exit ();
}

// check permissions if necessary...
$have_write_permission = false;
$our_permission = getStudentPermission ( $_GET ['student_id'] );
if ($our_permission != "WRITE" && $our_permission != "ASSIGN" && $our_permission != "ALL") {
    // we don't have write permission...
    // do nothing.
} else {
    $have_write_permission = true;
}

print_html5_primer ();
?>





<SCRIPT LANGUAGE="JavaScript">
      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }

      function changeStatusGuardian() {
          if(!confirm("Are you sure you would like to set this\nPersons status back to Guardian?")) {
             return false;
          }
          return true;
      }

      function changeStatusNotGuardian() {
          if(!confirm("Are you sure you would like to set this\nPersons status to Previous Guardian?")) {
             return false;
          }
          return true;
      }

      function deleteGuardian() {
          if(!confirm("Are you sure you would like to delete this Guardian?")) {
             return false;
          }
          return true;
      }

    </SCRIPT>
<?php print_bootstrap_head(); ?>
</HEAD>
<BODY>
<?php
print_student_navbar ( $student_id, $student_row ['first_name'] . " " . $student_row ['last_name'] );
print_jumbotron_with_page_name ( "View Guardians", $student_row ['first_name'] . " " . $student_row ['last_name'], $our_permission );

if ($system_message) {
    echo "<p>" . $system_message . "</p>";
}

?>
<div class="container">

		<!-- BEGIN  Current Guardian Info -->
		<h2>Guardian Information</h2>
		<a
			href="<?php echo IPP_PATH . "add_guardian.php?student_id=" . $student_row['student_id'];?>"
			<?php if (!$have_write_permission) echo "onClick=\"return noPermission();\""; ?>><img
			src="<?php echo IPP_PATH . "images/smallbutton.php?title=New Guardian";?>"
			border="0"></a>
                       

<?php
while ( $guardian = mysql_fetch_array ( $guardians_result ) ) {
    echo "<div class=\"row\"><div class=\"container\">\n";
    echo "<h3>" . $guardian ['last_name'] . "," . $guardian ['first_name'] . "</h3>
                                <p><a href=\"" . IPP_PATH . "guardian_view.php?student_id=" . $student_id . "&setNotGuardian=" . $guardian ['uid'] . "\"";
    if (! $have_write_permission)
        echo "onClick=\"return noPermission();\"";
    else
        echo "onClick=\"return changeStatusNotGuardian();\"";
    echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Change Status\" border=\"0\" width=\"100\" height=\"25\" ></a>\n";
    
    echo "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "guardian_view.php?student_id=" . $student_id . "&deleteGuardian=" . $guardian ['uid'] . "\"";
    if (! $have_write_permission)
        echo "onClick=\"return noPermission();\"";
    else
        echo "onClick=\"return deleteGuardian();\"";
    echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" width=\"100\" height=\"25\" ></a>\n";
    
    echo "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "edit_address.php?student_id=" . $student_id . "&target=guardian&guardian_id=" . $guardian ['guardian_id'] . "\"";
    if (! $have_write_permission)
        echo "onClick=\"return noPermission();\"";
    echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit Contact Info\" border=\"0\" width=\"100\" height=\"25\"></a>";
    
    // edit notes button
    echo "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "guardian_notes.php?guardian_id=" . $guardian ['guardian_id'] . "&student_id=" . $student_row ['student_id'] . "\"";
    if (! $have_write_permission)
        echo "onClick=\"return noPermission();\"";
    echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit Notes\" border=\"0\" width=\"100\" height=\"25\"></p></a>";
    
    // begin address information
    
    echo "<div class=\"col-md-6\">\n";
    echo "<h4>Address</h4>";
    echo "<p><address>\n";
    if ($guardian ['po_box'])
        echo $guardian ['po_box'] . "<br>\n";
    echo $guardian ['street'] . "<BR>\n";
    $cpc = "";
    if ($guardian ['city'])
        $cpc = $cpc . $guardian ['city'] . " ";
    if ($guardian ['province'])
        $cpc = $cpc . $guardian ['province'] . " ";
    if ($guardian ['country'])
        $cpc = $cpc . $guardian ['country'];
        // add the commas...
    $cpc = str_replace ( " ", ",", $cpc );
    echo $cpc . "<BR>\n";
    echo $guardian ['postal_code'] . "<BR><BR>\n";
    echo "</address></p>\n";
    echo "<p>&nbsp;</p>\n</div>";
    // begin contact info
    echo "<div class=\"col-md-6\">\n";
    echo "<h4>Contact Details</h4>\n";
    
    if ($guardian ['home_ph'] != "")
        echo "Ph: " . $guardian ['home_ph'] . "<BR>\n";
    if ($guardian ['business_ph'] != "")
        echo "Business: " . $guardian ['business_ph'] . "<BR>\n";
    if ($guardian ['cell_ph'] != "")
        echo "Cell Ph: " . $guardian ['cell_ph'] . "<BR>\n";
    if ($guardian ['email_address'] != "")
        echo "Email: " . $guardian ['email_address'] . "<BR>\n";
    echo "&nbsp;"; // just in case we have a blank
    echo "</div></div>";
    
    // guardian notes
    echo "<div class=\"row\"><div class=\"container\">\n";
    echo "<h4>Notes</h4>\n";
    
    $guardian_note_query = "SELECT * FROM guardian_note WHERE guardian_id=" . $guardian ['guardian_id'] . " ORDER BY priority_note,date ASC";
    $guardian_note_result = mysql_query ( $guardian_note_query );
    // check for error
    if (! $guardian_note_result) {
        $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$guardian_note_query'<BR>";
        $system_message = $system_message . $error_message;
        IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
        echo $system_message;
    } else {
        // output this note...
        // check if we have no notes
        if (mysql_num_rows ( $guardian_note_result ) <= 0) {
            // yes so output non-breaking space
            echo "&nbsp;";
        }
        echo "<table class=\"table table-striped table-hover\">\n";
        while ( $guardian_note_row = mysql_fetch_array ( $guardian_note_result ) ) {
            
            if ($guardian_note_row ['priority_note'] == 'Y')
                echo "<tr><td><img src=\"" . IPP_PATH . "images/caution.gif\" border=\"0\" width=\"32\" height=\"32\"></td>"; // priority flag.
            else
                echo "<td>&nbsp;</td>"; // no priority flag.
            echo "<td>" . $guardian_note_row ['note'] . "</td></tr>";
        }
        echo "</table>\n";
    }
    // end guardian notes
    echo "</div></div>";
}
?>
                        </table>

		<!-- END Current Guardian Info -->

		<!-- BEGIN  Previous Guardian Info -->
		<div class="container">
			<h2>Previous Guardian(s)</h2>
                        
                        
                        <?php
                        // loop through guardians marked as previous
                        while ( $guardian = mysql_fetch_array ( $previous_guardians_result ) ) {
                            echo "<div class=\"row\"><div class=\"container\">";
                            echo "<h3>" . $guardian ['last_name'] . "," . $guardian ['first_name'] . "</h3>\n";
                            echo "<a href=\"" . IPP_PATH . "guardian_view.php?student_id=" . $student_id . "&setGuardian=" . $guardian ['uid'] . "\"";
                            if ($have_write_permission)
                                echo "onclick=\"return changeStatusGuardian();\"";
                            else
                                echo "onclick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Change Status\" border=\"0\" width=\"100\" height=\"25\"></a>";
                            
                            echo "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "guardian_view.php?student_id=" . $student_id . "&deleteGuardian=" . $guardian ['uid'] . "\"";
                            if (! $have_write_permission)
                                echo "onClick=\"return noPermission();\"";
                            else
                                echo "onClick=\"return deleteGuardian();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Delete\" border=\"0\" width=\"100\" height=\"25\" ></a>\n";
                            
                            echo "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "edit_address.php?student_id=" . $student_id . "&target=guardian&guardian_id=" . $guardian ['guardian_id'] . "\"";
                            if (! $have_write_permission)
                                echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit Contact Info\" border=\"0\" width=\"100\" height=\"25\"></a>";
                            
                            echo "&nbsp;&nbsp;<a href=\"" . IPP_PATH . "guardian_notes.php?guardian_id=" . $guardian ['guardian_id'] . "&student_id=" . $student_row ['student_id'] . "\"";
                            if (! $have_write_permission)
                                echo "onClick=\"return noPermission();\"";
                            echo "><img src=\"" . IPP_PATH . "images/smallbutton.php?title=Edit Notes\" border=\"0\" width=\"100\" height=\"25\"></a>";
                            
                            echo "<p>&nbsp;</p>";
                            echo "<div class=\"alert alert-warning\"><strong>Guardianship Terminated</strong>: &nbsp;" . $guardian ['to_date'] . "</div>\n";
                            // begin address
                            echo "<div class=\"col-md-6\">";
                            echo "<h4>Address</h4>\n<address>";
                            if ($guardian ['po_box'])
                                echo "P.O. Box " . $guardian ['po_box'] . "<BR>";
                            echo $guardian ['street'] . "<BR>\n";
                            $cpc = "";
                            if ($guardian ['city'])
                                $cpc = $cpc . $guardian ['city'] . " ";
                            if ($guardian ['province'])
                                $cpc = $cpc . $guardian ['province'] . " ";
                            if ($guardian ['country'])
                                $cpc = $cpc . $guardian ['country'];
                                // add the commas...
                            $cpc = str_replace ( " ", ",", $cpc );
                            echo $cpc . "<BR>\n";
                            echo $guardian ['postal_code'];
                            echo "</address></div>";
                            
                            // More contact info, right column
                            echo "<div class=\"col-md-6\">";
                            echo "<h4>Contact Information</h4>\n";
                            
                            if ($guardian ['home_ph'] != "")
                                echo "Ph: " . $guardian ['home_ph'] . "<BR>\n";
                            if ($guardian ['business_ph'] != "")
                                echo "Business: " . $guardian ['business_ph'] . "<BR>\n";
                            if ($guardian ['cell_ph'] != "")
                                echo "Cell Ph: " . $guardian ['cell_ph'] . "<BR>\n";
                            if ($guardian ['email_address'] != "")
                                echo "Email: " . $guardian ['email_address'] . "<BR>\n";
                            echo "</div></div>";
                            
                            /**
                             * guardian notes
                             *
                             * @TODO	align (left) with rest of guardian content
                             */
                            echo "<div class=\"row\"><div class=\"container\">";
                            echo "<h4>Notes</h4>\n";
                            $guardian_note_query = "SELECT * FROM guardian_note WHERE guardian_id=" . $guardian ['guardian_id'] . " ORDER BY date ASC,priority_note DESC";
                            $guardian_note_result = mysql_query ( $guardian_note_query );
                            // check for error
                            if (! $guardian_note_result) {
                                $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error () . "<BR>Query: '$guardian_note_query'<BR>";
                                $system_message = $system_message . $error_message;
                                IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
                                echo $system_message;
                            } else {
                                // output this note...
                                // check if we have no notes
                                if (mysql_num_rows ( $guardian_note_result ) <= 0) {
                                    // yes so output non-breaking space
                                    echo "&nbsp;";
                                }
                                echo "<table class=\"table table-hover table-striped\">";
                                while ( $guardian_note_row = mysql_fetch_array ( $guardian_note_result ) ) {
                                    
                                    if ($guardian_note_row ['priority_note'] == 'Y')
                                        echo "<td width=\"32\"><img src=\"" . IPP_PATH . "images/caution.gif\" border=\"0\" width=\"32\" height=\"32\"></td>"; // priority flag.
                                    else
                                        echo "<td>&nbsp;</td>"; // no priority flag.
                                    echo "<td>" . $guardian_note_row ['note'] . "</td>";
                                    echo "</tr>\n";
                                }
                            }
                            // end guardian notes
                            echo "</table>\n";
                        }
                        ?>
                        
                        <!-- END Previous Guardian Info -->


		</div>
		<footer><?php print_complete_footer(); ?>
</footer>        <?php print_bootstrap_js(); ?>
    

</BODY>
</HTML>
