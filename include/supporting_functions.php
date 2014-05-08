<?php

/** @file functions occasionally used
 * @brief was just spellcheck for Pspell, but now new functions for inclusion can go here.
 * Contains (from original dev) just checkspelling()
 * @todo
 * 1. Make sure checkspelling is no longer needed
 * 2. Add print_html functions here
 * 		1. Add bootstrap
 * 		2. Add javascript
 * 		3. Add navbars
 * 3. Refactor to exclude this function - it's no longer necessary
 * 4. USE this file for other functions for inclusion
 * @remark
 * 1. Replaced checkSpelling() with HTML spellcheck="TRUE"
 * 2. disabled (commented)
 */

if(!defined('IPP_PATH')) define('IPP_PATH','../');

/** @fn getPermissionName($permission_level='')
 * @brief determines what resources the logged in user has access to.
 * @param string $permission_level
 * @return NULL or string $permission_name
 */


/** @fn checkSpelling ( $string )
 *  @brief	function to make use of pspell (PEAR): given a string, returns error check and makes spelling recommendations
 *  @detail	No longer necessary; Making use of spellcheck attribute in HTML5 and browsers. 
 *  @todo
 *  1. Refactor so nothing calls this function (it's already been done once but needs confirmation)
 *
 *  @param $string
 */
/* function checkSpelling( $string ) //todo: investigate and justify possibly unconventional function syntax
{
   if (!extension_loaded("pspell")) {
      //spell libraries not loaded so just return the same string...
      return $string;
   }

   $pspell = pspell_new("en");
   $words = explode(" ", $string);
   $return = "";
   $trim =  ".!,?();:'\"\n\t\r";

   foreach($words as $word) {
     if (pspell_check($pspell, trim($word,$trim))) {
       // this word is fine; print as-is
       $return .= $word . " ";
     } else {
       //get up to 3 possible spellings for glossover...
       $suggestions = pspell_suggest($pspell,trim($word,$trim));
       $suggest = "";
       for($i = 0; $i < 3; $i++) {
          $suggest .= $suggestions[$i] . ",";
       }
       $suggest = substr($suggest, 0, -1);  //chop off the last comma - good but; todo: why? comment
       $return .= "<span class='mispelt_text' title='$suggest'>$word </span>";
     }
   }
   return $return;
}
*/

/** @fn clean_in_and_out($input)
 * @brief Filters input and escapes output to prepare for MySQL
 * @param $input
 * @return mysql_real_escape_string($input)
 * @detail 		Strips tags, then sanitizes html entities, and then strips slashes. Finally, uses mysql_real_escape_string() to prepare for MySQL use.
 *
 * @warning 	Not for arrays. Must construct stripslashes_deep() for arrays.
 * @todo		
 * 1. Test and implement (not done yet)
 * 2. find system to use on all db input: perhaps when UPDATE query is used.
 *
 */
function clean_in_and_out($input){
	$input = strip_tags($input);
	$input = htmlentities($input,$charset="utf8");
	$input = stripslashes($input);
	return mysql_real_escape_string($input);
}
function filter_html($input) {
	$clean=htmlentities($input, $charset="UTF8");
	return $clean;
}
/* @fn print_html5_primer()
 * @brief to start html5 doc
 * @remark has constant base path to take advantage of favicon, CSS, site wide JS
 * @todo 
 * 1. Do not deploy in this state. Does not work yet.
 * 2. Revise so <head> isn't closed; that way JS and CSS can be added on a per file basis.
  * @remark Doesn't return; instead, echoes $print_head
 */
function print_html5_primer()
{
	if(!defined('IPP_PATH')) define('IPP_PATH','../');
	
	$print_head = <<<EOF
	<!DOCTYPE HTML>
	<html lang="en">
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Edit Short Term Objective">
	<meta name="author" content="Rik Goldman">
	<title>$page_tite</title>
	
EOF;
	echo $print_head;
}



/** @fn print_intellectual_property()
 *	@return string $ip
 *  @brief Print HTML *Comments* with Copyright and license info
 *  @todo
 *	1. works; now get across project
 */
function print_intellectual_property() {
	
		$credit = <<< EOF
<!-- 
-MyIEP
-Copyright &copy; 2014 Chelsea School, Hyattsville, MD.
-License: GPLv2
-Legacy Code (IEP-IPP)
-Licence: GPLv2
-All legacy code copyright &copy; 2005 Grasslands Regional Division #6.</p>
-LICENCE
-This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
-This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
-You should have received a copy of the GNU General Public License along with this program; if not, write to:
-The Free Software Foundation, Inc. / 59 Temple Place, Suite 330, Boston, MA 02111-1307
 USA
//-->
EOF;
return $credit;
}

/** @fn no_cash()
 *
 * Inserts header('Pragma: no-cache'). Used by most pages.
 * @remark	Not used yet.
 * @todo
 * 1. Substitute header function with no_cash()
 * 3. Test to confirm
 * 4. HTML5 seems to use meta instead of headers, so cache control seems to be all that is necessary for this to be efficient.
 * 
 */

function no_cash() {
	echo header("Cache-Control: no-cache, must-revalidate");
	echo header('Pragma: no-cache');

}

/** @fn print_footer()
 *  @param none
 *  @brief echos copyright in footer and div
 *  @remark echos the content already
 *  @todo
 *  1. consider centering this
 */
function print_footer() {
	$footer = <<< EOF
<div class="container"><footer> 
        <p>&copy; Chelsea School 2014</p>
      </footer></div>
EOF;
echo $footer;
}

/** @fn print_complete_footer()
 *  @brief outputs copyright in footer tag and full copyright and license in comment
 *  @remark Combines print_footer() and print_intellectual_property()
 *  @todo
 *  1. List all developers in commented IP statement
 */
function print_complete_footer() {
	print_footer();
	echo print_intellectual_property();
}


/**@fn print_datepicker_depends()
 * @brief 		prints to html the dependencies for  datepicker
 * @detail 		assumes the date form input has an id of "datepicker"; this can be changed to a class instead of an ID.
 * @todo
 * 1. Deploy to anywhere that takes date input
 * @remark use this one
 */
function print_datepicker_depends() {
	$print_depends= <<<EOF
	<!-- Example Invokation of Datepicker -->
	<!-- input type=datepicker name="review_date" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd"  -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
	<script src="js/jquery-2.1.1.js"></script>
	<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
	</script>
	 <script> 
	$(function() {
	$( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
	});
	</script>
EOF;
	echo $print_depends;
}



/**fn print_bootrap_head()
 * @brief stuff for jumbotron and bootstrap.min.css to go in html head.
* @remark 
* 1. doesn't require echo
* 2. doesn't work yet
*/

function print_bootstrap_head() {
	$myieppath='IPP_PATH';
	$bootstrap_depends=<<<EOF
	<!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/jumbotron.css" rel="stylesheet">
	<style type="text/css">body { padding-bottom: 70px; }</style>
EOF;
	echo $bootstrap_depends;
}

/**@fn print_jumbotron_with_page_name($page_name)
 * @brief	Outputs to HTML bootstrap banner with names and permissions
 * @param string $page_name
 * @param string $student
 * @param string $perms
 * @remark echoes HTML jumbotron populated with data
 */

function print_jumbotron_with_page_name($page_name, $student, $permission) {
	
	$jumbotron = <<<EOF
	<div class="jumbotron"><div class="container">
<h1>$page_name: &nbsp; <small>$student</small></h1>
<h2>Logged in as: <small>{$_SESSION['egps_username']} (Permission: $permission)</small></h2>
<h3>$system_message</h3>

</div> <!-- close container -->

</div> <!-- Close Jumbotron -->

EOF;
	echo $jumbotron;
}

function getPermissionName($permission_level) {
	//returns permission level or NULL on fail.
	global $error_message;

	$error_message = "";
	
	$query = "SELECT level_name FROM permission_levels WHERE level=$permission_level";
	$result = mysql_query($query);

	$row=mysql_fetch_array($result);
	$permission_name=$row[0];
	return $permission_name;
}


function print_lesser_jumbotron($page_name, $permission) {
	$lesser_jumbotron = <<<EOF
	<div class="jumbotron"><div class="container">
    <h1>$page_name</h1>
    <h2>Logged in as: <small>{$_SESSION['egps_username']} (Authorization: $permission) </small></h2>
<h3>$system_message</h3>

</div> <!-- close container -->

</div> <!-- Close Jumbotron -->

EOF;
	echo $lesser_jumbotron;
}
/** @fn print_student_navbar($student)
 * @brief Outputs HTML student context navbar (bootstrap)
 * @remark
 * * outputs echo directly
 *
 * @param $student
 * @return NULL|string
 */
function print_student_navbar($student_id, $student) {
	$student_nav = <<<EOF
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="main.php">MyIEP</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li><a href="main.php">Home</a></li>
<li><a href="sprint_feedack.php">User Feedback</a></li>
<li><a href="index.php">Logout</a></li>
<li><a href="about.php">About</a></li>
<li><a href="help.php">Help</a></li>
<li><a onclick="history.go(-1);">Back</a></li>
	<li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Student Records: $student<b class="caret"></b></a>
              <ul class="dropdown-menu">
              	<li><a href="student_view?student_id=$student_id">Student Overview</a></li>
              	<li><a href="long_term_goal_view.php?student_id=$student_id">Goals</a></li>
              	<li class="divider"></li>
              	<li><a href="guardian_view.php?student_id=$student_id">Guardians</a></li>
              	<li><a href="strength_need_view.php?student_id=$student_id">Strengths &amp; Needs</a></li>
              	<li><a href="coordination_of_services.php?student_id=$student_id">Coordination of Services</a></li>
              	<li><a href="achieve_level.php?student_id=$student_id">Achievement Level</a></li>
              	<li><a href="medical_info.php?student_id=$student_id">Medical Information</a></li>
              	<li><a href="medication_view.php?student_id=$student_id">Medication</a></li>
              	<li><a href="testing_to_support_code.php?student_id=$student_id">Support Testing</a></li>
              	<li><a href="background_information.php?student_id=$student_id">Background Information</a></li>
              	<li><a href="year_end_review.php?student_id=$student_id">Year-End Review</a></li>
              	<li><a href="anecdotals.php?student_id=$student_id">Anecdotals</a></li>
              	<li><a href="assistive_technology.php?student_id=$student_id">Assistive Techology</a></li>
              	<li><a href="transition_plan.php?student_id=$student_id">Transition Plan</a></li>
              	<li><a href="accomodations.php?student_id=$student_id">Accomodations</a></li>
              	<li><a href="snapshots.php?student_id=$student_id">Snapshots</a></li></ul>
            </ul>
       
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="student_archive.php">Archive</a></li>
                <li><a href="user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
         </div>
         <!--/.nav-collapse -->
        

       
      </div>
    </div>
EOF;
	echo $student_nav;
}

/** @fn print_bootstrap_datepicker_depends()
 *  @brief old depends function for datepicker - kept for properity
 *  @remark
 *  * use other datepicker function in this file
 *  * doesn't require echo, already outputs
 *  * unused
 *  @todo
 *  1. Remove
 */
 

function print_bootstrap_datepicker_depends() {
$dependencies = <<<EOF
<!-- Example Invokation of Datepicker -->
	<!-- input type=datepicker name="review_date" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd"  -->
	<!-- Bootstrap Datepicker CSS -->
	<link href="./css/datepicker.css" rel="stylesheet">
	 <!-- jQuery Libraries -->
	 <script src="js/jquery-2.1.1.js"></script>
	 <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

	 <script type="text/javascript" src="./js/bootstrap-datepicker.js">$('.datepicker').datepicker()</script>
	 <!-- jQuery Instantiation -->
	 <script>
	$(function() {
	$( "#datepicker" ).datepicker();
	});
	</script>
EOF;
   echo $dependencies;
}


/** @fn print_bootsrap_js()
 *  @brief Prints JavaScript references that bootsrap relies on
 *  @remark
 *  1. Already Echoes
 *  2. Goes within HTML, but at the very bottom to increase load time
 *
 */
function print_bootstrap_js(){
	$bootsrapjs=<<<EOF
<script src="js/jquery-2.1.1.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
EOF;
	echo $bootsrapjs;
}

/** @fn print_general_navbar()
 * @brief Outputs HTML general context navbar (bootstrap)
 * @remark Remember, use echo
 * @param int $student_id
 * @return NULL|string
 */
function print_general_navbar() {
	$general_nav = <<<EOF
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="main.php">MyIEP</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li><a href="main.php">Home</a></li>
<li><a href="sprint_feedback.php">User Feedback</a></li>
<li><a href="index.php">Logout</a></li>
<li><a href="about.php">About</a></li>
<li><a href="help.php">Help</a></li>
<li><a onclick="history.go(-1);">Back</a></li>
    
          <!--<ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="student_archive.php">Archive</a></li>
                <li><a href="user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>-->
         </div>
         <!--/.nav-collapse -->
        
      </div>
    </div>
EOF;
	echo $general_nav;
}
	
/**@fn get_logged_users($count_logged)
 * @param $count_logged bool
 */
function get_logged_users($count_logged)
{
	$query = "SELECT * FROM 'logged_in'";
	$result = mysql_query($query);
	$rows = mysql_num_rows($result);
	
	if (!isset($count_logged)) 
	{
		$start_logged_table = "<form action=\"superuser_view_logged_in.php\" method=\"post\"><table class=\"table table-hover table-striped\"><tr><th>uid</th><th>username</th><th>session_id</th><th>Last IP</th><th>Time</th><th>Action</th></tr>";

	
		for ($j = 0; $j < $rows ; ++$j):
			$row = mysql_fetch_row($result);
			$logged_user_table = "
				<tr>
				<td>{$row[0]}</td>
				<td>{$row[1]}</td>
				<td><input type=\"hidden\" name=\"delete\" value={$row[2]}</td>
				<td>{$row[3]}</td>
				<td>{$row[4]}</td>
				<td><button type=\"button\" value=\"delete\" class=\"btn btn-danger\">Delete Record</button></td>
				</tr>";	
		
endfor;//end of loop
		
		$print_logged_users = "$start_logged_table" . $logged_user_table . "</table></form>";
		
		echo $print_logged_users; //return if param is not null
		
	} //end of if
	else 
	{
		$count_logged_users = $rows;
		echo $count_logged_users; //return if param not null
	} //end else

} //end function

function print_meta_for_html5($page_title)
{
	$metadata = <<<EOF
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="$page_title">
	<meta name="author" content="Rik Goldman" >
	<link rel="shortcut icon" href="./assets/ico/favicon.ico">
EOF;
	echo $metadata;
} 

/** fn random_password($length)
 * @brief uses curl for php to grap a random password of $length characters
 * @param integer $length
 * @return string $pw_suggestion
 */
function random_password($length)
{
	$ch = curl_init("https://infamia.com/hints/pwgen.php?length=" . $length . "&quiet");
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	$pw_suggestion = curl_exec($ch);
	curl_close($ch);
	return $pw_suggestion;

}

function generate_password
($length=8,$characters='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()-_{}[]|:<>') {

	if (!is_int($length) || $length<0) {
		return false;
	}
	$characters_length=strlen($characters) -1;
	$proposed_password = '';
	for ($i = $length; $i > 0; $i--) {
		$proposed_password .= $characters[mt_rand(0, $characters_length)];
	}
	echo $proposed_password;
}



?>