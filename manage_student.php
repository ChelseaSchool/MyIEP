<?php

/** @file
 * @brief 	find and manage a specific IEP
 * @todo
 * * perhaps move bottom filter controls to top navbar
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100;  //all, decide in the page



/**
 * Path for IPP required files.
 */

if(isset($system_message)) $system_message = $system_message; else $system_message ="";
if(isset($_GET['field'])) $FIELD = $_GET['field']; ELSE $FIELD="";
if(isset($_GET['szSearchVal'])) $szSearchVal=$_GET['szSearchVal']; else $szSearchVal="";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/supporting_functions.php');

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
if(getPermissionLevel($_SESSION['egps_username']) > $MINIMUM_AUTHORIZATION_LEVEL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
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

//check if we are duplicating...

if(isset($_POST['duplicate_x'])) {
   //we can only duplicate one so...
   $count=0;
   foreach($_POST as $key => $value) {
        if(preg_match('/^(\d)*$/',$key)) {
           $id=$key;
           $count++;
        }
   }
   if($count > 1) $system_message = "You can only duplicate one program plan at a time<BR>";
   else header("Location: " . IPP_PATH . "duplicate.php?student_id=" . $id);

}

//check if we are deleting some peeps...
//print_r ($_POST);

if(isset($_POST['delete_x'])) {
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    $delete_query = "DELETE FROM student WHERE ";
    foreach($_POST as $key => $value) {
        if(preg_match('/^(\d)*$/',$key))
        $delete_query = $delete_query . "student_id=" . $key . " or ";
    }
    //strip trailing 'or' and whitespace
    $delete_query = substr($delete_query, 0, -4);
    //echo $delete_query . "<-><BR>";
    //$system_message = $system_message . $delete_query . "<BR>";
    $delete_result = mysql_query($delete_query);
    if(!$delete_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$delete_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }
}

  //get the list of all users...
  //wonder how php handles dangling else...
  if(!isset($_GET['iLimit']))
    if(!isset($_POST['iLimit'])) $iLimit = 50;
        else $iLimit=$_POST['iLimit'];
  else $iLimit = $_GET['iLimit'];

  if(!isset($_GET['iCur']))
    if(!isset($_POST['iCur'])) $iCur = 0;
    else $iCur=$_POST['iCur'];
  else $iCur = $_GET['iCur'];

  if(!isset($_GET['szSchool']))
    if(!isset($_POST['szSchool'])) $szSchool = "ALL";
    else $szSchool=$_POST['szSchool'];
  else $szSchool = $_GET['szSchool'];

$szTotal=0;

/** @fn 	getStudents()
 *  @brief	Gets a count of students from the database that go to a member
 *  @return NULL|resource
 *  @todo	get_student_count()
 */

function getStudents() {
    global $error_message,$IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS,$permission_level,$system_message,$IPP_MIN_VIEW_LIST_ALL_STUDENTS,$iLimit,$iCur,$szSchool,$szTotalStudents;
    if(!connectIPPDB()) {
        $system_message = $system_message . $error_message;  //just to remember we need this
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    }

    //do a subquery to find our school code...easier than messing with the ugly
    //query below...
    $school_code_query="SELECT school_code FROM support_member WHERE egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "'";
    $school_code_result=mysql_query($school_code_query);
    if(!$school_code_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$school_code_query'<BR>";
        return NULL;
    }
    $school_code_row=mysql_fetch_array($school_code_result);
    $school_code= $school_code_row['school_code'];

    //$student_query = "SELECT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE end_date IS NULL ";
    $student_query = "SELECT DISTINCT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE ((support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND school_history.end_date IS NULL AND support_list.student_id IS NOT NULL) OR (";
    //prior to march 18/06: $student_query = "SELECT DISTINCT student.student_id,last_name,first_name,school_history.school_code,school.* FROM student LEFT JOIN support_list ON student.student_id = support_list.student_id LEFT JOIN school_history ON student.student_id=school_history.student_id LEFT JOIN school ON school_history.school_code=school.school_code WHERE (support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND support_list.student_id IS NOT NULL) OR (";
    
    if(!($IPP_MIN_VIEW_LIST_ALL_STUDENTS >= $permission_level)) { //$IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS >= $permission_level) {
          $student_query = $student_query . "school_history.school_code='$school_code' AND "; //prior to 2006-03-21: $student_query = $student_query . "school_history.school_code='$school_code' AND ";
      if($IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS < $permission_level)
        {
          //$system_message .= "debug: permission level: $IPP_MIN_VIEW_LIST_ALL_LOCAL_STUDENTS < $permission_level<BR><BR>";
          $student_query .= "support_list.egps_username='" . mysql_real_escape_string($_SESSION['egps_username']) . "' AND ";
        }
          $student_query .= "end_date IS NULL) ";
    } else {
        $student_query = $student_query . "end_date IS NULL) ";
    }
    if(isset($_GET['SEARCH'])) {
        switch ($_GET['field']) {
           case 'last_name':
               $student_query = $student_query . "AND student.last_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'first_name':
               $student_query = $student_query . "AND student.first_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'last_name':
               $student_query = $student_query . "AND student.last_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'school_name':
               $student_query = $student_query . "AND school.school_name LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
           break;
           case 'school_code':
               $student_query = $student_query . "AND school_history.school_code LIKE '". mysql_real_escape_string($_GET['szSearchVal']) ."' ";
        
        }
    }
    //added 2006-04-20: to prevent null school histories from showing up as active.
    $student_query .= ") AND EXISTS (SELECT school_history.student_id FROM school_history WHERE school_history.student_id=student.student_ID) ";
    //end added 2006-04-20
    $student_query_limit = $student_query . "ORDER BY school_history.school_code,student.last_name ASC LIMIT $iCur,$iLimit";
    $student_result_limit = mysql_query($student_query_limit);
    if(!$student_result_limit) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query_limit'<BR>";
        return NULL;
    }

    //$system_message = $system_message . "debug: " . $student_query_limit . "<BR>";

    //find the totals...
    $student_result_total = mysql_query($student_query);
    if(!$student_result_total) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
        return NULL;
    }
    $szTotalStudents =  mysql_num_rows($student_result_total);
    return $student_result_limit;
}

$sqlStudents=getStudents(); //$szTotalStudents contains total number of stdnts.

//get totals...

if(!$sqlStudents) {
    $system_message = $system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
}

function list_names_for_autocomplete($student_row, $sqlStudents){
	echo "<script>\n";
	echo "var available_names = [";
	while ($student_row=mysql_fetch_array($sqlStudents)) {
		echo "\"" . $student_row['first_name'] . " " . $student_row['last_name'] . "\",";
	}; //end loop
	echo "];\n";
	echo "</script>\n";	
}


//set back vars...
$szBackGetVars="";
foreach($_GET as $key => $value) {
    $szBackGetVars = $szBackGetVars . $key . "=" . $value . "&";
}
//strip trailing '&'
$szBackGetVars = substr($szBackGetVars, 0, -1);

list_names_for_autocomplete($student_row, $sqlStudents);

function print_jquery_autocomplete() {
echo <<< EOF
<script>
$(function() {
    var person;
    $('#tags').autocomplete({
        source: available_names,
        open: function () {
             $('ul.ui-autocomplete')
             .addClass('opened') 
             $('#students').hide()
		},
        close: function () 
        { 
		  
          $('ul.ui-autocomplete')
           .removeClass('opened'),
		  $('#students').show()
		  
		  
		   person = $('#tags').val(),
		   show_name(person)
			},
        autofocus: true,
        minlength: 1,
        
        //change: function( event, ui ) 
        //{
		
         
     
		
		})
	 
    });
   </script>
EOF;
}

?>

<!DOCTYPE HTML>
<HTML lang="en">
<HEAD>

<?php print_meta_for_html5($page_title); ?>
<TITLE><?php echo $page_title; ?></TITLE>
<?php print_bootstrap_head(); ?>
<link href="assets/glyphs/bootstrap-glyphicons.css" rel="stylesheet">   
<!--  <script src="js/autocomplete-html.js" type="text/javascript"></script>-->


    <SCRIPT>
      function deleteChecked() {
          var szGetVars = "delete_users=";
          var szConfirmMessage = "Are you sure you want to delete or duplicate:\n";
          var count = 0;
          form=document.studentlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].id + "|";
                     szConfirmMessage = szConfirmMessage + form.elements[x].value + " (ID #" + form.elements[x].name + ")\n";
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

      function notYetImplemented() {
          alert("Functionality not yet implemented"); return false;
      }

      function noPermission() {
          alert("You don't have the permissions"); return false;
      }
    </SCRIPT>

	<!-- jQuery -->
	<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
    <script src="js/jquery-2.1.0.min.js"></script>
	<script src="js/jquery.autocomplete.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.min.css">   
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<script>   
function show_name(person) {
	if (person != null)
	{
		$("tr.student").hide();
		$(document.getElementById(person)).show();
	
	}
	else $("#students").show();
}
</script> 

<script>
$(document).ready (function(){
$("#filter-clear").click(function() {
	$( "#tags" ).val("");
	$( "tr.student" ).show();
});
});


</script>
<script>
$(document).ready (function(){
	$(.alert).hide();
}

</script>
<script>
$(document).ready(function(){
	$("#filter-tip").popover();
});
</script>
<?php print_jquery_autocomplete(); ?>

<?php $sqlStudents=getStudents(); ?>  

   
</HEAD>
<BODY>   	
<?php echo print_general_navbar(); ?>
<div class="jumbotron"><div class="container">     

<?php if ($system_message) echo $system_message; ?>

<h1>Manage Students</h1>
<h2>Logged in as: <small><?php echo $_SESSION['egps_username']; ?></small></h2>
<!-- Button trigger modal -->
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#filter_options">
  Manage Filters &raquo;
</button>
<a class="btn btn-primary btn-lg" href="./new_student.php">New Student &raquo;</a>
<?php if ($system_message) { echo "<h3>System Message <small>" . $system_message . "</small></h3>";} ?>

</div> <!-- close container -->

</div> <!-- Close Jumbotron -->
    
    
<div class="container">  


<!-- Modal--> 
<div class="modal fade" id="filter_options" tabindex="-1" role="dialog" aria-labelledby="options" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="Filters" id="Filters">Manage Filters</h4>
      </div><!-- Modal Header end -->
      <div class="modal-body">
      	
      		<button role="button" class="btn btn-default btn-med" id="filter-clear">Clear Student Filter &raquo;</button>
     		<!-- <button role="button" class="btn btn-default btn-med" id="toggle" alt="Show Only Students I have Access To">Show Only Students to Whom I have Access &raquo;</button>-->		
	    
	  </div><!-- end modal body -->
      <div class="modal-footer">
          </div><!-- end modal footer -->
    </div><!-- end modal content -->
  </div>
  <!-- end modal dialog -->
</div>
<!-- end modal fade -->

<div class="alert alert-block alert-info"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Release Note</strong>: All students are shown by default.  Use the search box to filter students by name. Use the "Manage Filters" button above to manipulate the filters (clear, etc.).</div>


<!--  form for autocomplete first and last names-->



<div class="row">
<div class="input-group">
  <span class="input-group-addon">
  <span id="filter-tip" data-toggle="popover" data-placement="top" data-title="Filter Info" data-content="Type part of a student's name to filter the results on this page; see the black bar at the bottom of the page to work with filters. (To dismiss this message, click the magnifying glass icon again.)" class="glyphicon glyphicon-search">
  </span>
  </span>
  <input id="tags" class="form-control" placeholder="Search for student by name...">
</div>
<p>&nbsp;</p>
<div class="row">
	
    <table id="students" class="table table-hover table-striped">
  	<!-- <form name="studentlist" onSubmit="return deleteChecked()" enctype="multipart/form-data" action="<?php //echo IPP_PATH . "manage_student.php"; ?>" method="post">-->
  	<tr><th>Select</th><th>UID</th><th>Student Name</th><th><abbr title="Individual Education Plan">IEP</abbr> (<abbr title="Portable Document Format">PDF</abbr>)</th><th>School</th><th>Permission</th></tr>
	
	<!-- loop -->
	<?php 
	while ($student_row=mysql_fetch_array($sqlStudents)) {
                            $current_student_permission = getStudentPermission($student_row['student_id']);
                            $tablerow = <<<EOF
                             <tr class="$current_student_permission student" id="{$student_row['first_name']} {$student_row['last_name']}">
                            	<td><input id="{$student_row['student_id']}" type="checkbox"></td>
                                <td>{$student_row['student_id']}</td>
                            	<td><a href="student_view.php?student_id={$student_row['student_id']}">{$student_row['first_name']} &nbsp;{$student_row['last_name']}</a></td>
                            	<td width="20%"><center><a href="ipp_pdf.php?student_id={$student_row['student_id']}" target="_blank">IEP (PDF)<img alt="IEP (PDF)" src="images/pdf-icon.png" height="20%" width="20%"></a></center></td>
                            	<td>{$student_row['school_name']}</td>
                            	<td>$current_student_permission</td>
                            </tr></div>
EOF;

                            echo $tablerow;
}
 ?>

  
  
  
                           <?php /* if($current_student_permission == "READ" || $current_student_permission != "WRITE" || $current_student_permission != "ALL")
                                echo "<a href=\"". IPP_PATH . "ipp_pdf.php?student_id=" . $student_row['student_id'] . "\" class=\"default\" target=\"_blank\"";
                            if($current_student_permission == "NONE" || $current_student_permission == "ERROR") {
                                echo "onClick=\"return noPermission();\" ";
                            	echo "<img src=\"". IPP_PATH . "images/pdf.png\" align=\"top\" border=\"0\"></a>";
                            }
                            echo "</td>"; //end pdf column
                            //school name column
                            echo "<td>" . $student_row['school_name'] . "</td>";
                            //permission
                            echo "<td>" . $current_student_permission . "</td>";
                            echo "</tr>";//close row */
						 ?>
</table>							
</div>

	
 
   			
  				
  					
					

        
    

<hr>                      
<footer><?php print_complete_footer(); ?></footer>
  
 
        <?php print_bootstrap_js() ?>

        
		 
    </BODY>
</HTML>
