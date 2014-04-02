<?php

/* @file functions occasionally used
 * 
 * Contains from original dev just checkspelling()
 * @todo
 * 1. Make sure checkspelling is no longer needed
 * 2. Add print_html functions here
 * 		1. Add bootstrap
 * 		2. Add javascript
 * 		3. Add navbars
 */

//summary: Just a spell check function...dev differentiates from user functions

if(!defined('IPP_PATH')) define('IPP_PATH','../');

//spell checking functions
function checkSpelling ( $string ) //todo: investigate and justify possibly unconventional function syntax
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

/** @fn clean_in_and_out($input)
 * @brief Filters input and escapes output to prepare for MySQL
 *
 * @detail 		Strips tags, then sanitizes html entities, and then strips slashes. Finally, uses mysql_real_escape_string() to prepare for MySQL use.
 *
 * @warning 	Not for arrays. Must construct stripslashes_deep() for arrays.
 * @todo		Test and implement.
 *
 */
function clean_in_and_out($input){
	$input = strip_tags($input);
	$input = htmlentities($input);
	$input = stripslashes($input);
	return mysql_real_escape_string($input);
}

/* @fn print_html5_primer()
 * @brief to start html5 doc
 * @todo fix this
 */
function print_html5_primer()
{
	$print_head = <<<EOF
	<!DOCTYPE HTML>
	<html lang="en">
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Edit Short Term Objective">
	<meta name="author" content="Rik Goldman">
	<link rel="shortcut icon" href="./assets/ico/favicon.ico">
	<title>$page_tite</title>
	</head>
	<body>
EOF;
	echo $print_head;
}

/** @fn no_cash()
 *
 * Inserts header('Pragma: no-cache'). Used by most pages.
 * @remark	Not used yet.
 * @todo
 * 1. add productive_functions.php to require_once pile
 * 2. Substitute header function with no_cash()
 * 3. Test to confirm
 * 4. Add rest of standard headers for this application to the function and remove header info from html
 * 5. add content type line
 */

function no_cash() {
	echo header("Cache-Control: no-cache, must-revalidate");
	echo header('Pragma: no-cache');

}

function print_student_navbar($position){
	$nav = <<<EOF
<div class="navbar navbar-inverse navbar-fixed-$position" role="navigation">
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
            <li><a href="index.php">Logout</a></li>
            <li><a href="about.php">About</a></li>
            <li><a onclick="history.go(-1);">Back</a></li>
            <li><a href="./ipp_pdf.php?student_id=$student_row['student_id']&file=ipp.pdf">Get PDF</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Records: $student_row['first_name'] $student_row['last_name']<b class="caret"></b></a>
              <ul class="dropdown-menu">
              	<li><a href="<?php echo IPP_PATH . "long_term_goal_view.php?student_id=" . $student_row['student_id']; ?>">Goals</a></li>
              	<li class="divider"></li>
              	<li><a href="<?php echo IPP_PATH . "guardian_view.php?student_id=" . $student_row['student_id'];?>">Guardians</a></li>
              	<li><a href="<?php echo IPP_PATH . "strength_need_view.php?student_id=" . $student_row['student_id'];?>">Strengths &amp; Needs</a></li>
              	<li><a href="<?php echo IPP_PATH . "coordination_of_services.php?student_id=" . $student_row['student_id'];?>">Coordination of Services</a></li>
              	<li><a href="<?php echo IPP_PATH . "achieve_level.php?student_id=" . $student_row['student_id'];?>">Achievement Level</a></li>
              	<li><a href="<?php echo IPP_PATH . "medical_info.php?student_id=" . $student_row['student_id'];?>">Medical Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "medication_view.php?student_id=" . $student_row['student_id'];?>">Medication</a></li>
              	<li><a href="<?php echo IPP_PATH . "testing_to_support_code.php?student_id=" . $student_row['student_id'];?>">Support Testing</a></li>
              	<li><a href="<?php echo IPP_PATH . "background_information.php?student_id=" . $student_row['student_id'];?>">Background Information</a></li>
              	<li><a href="<?php echo IPP_PATH . "year_end_review.php?student_id=" . $student_row['student_id'];?>">Year-End Review</a></li>
              	<li><a href="<?php echo IPP_PATH . "anecdotals.php?student_id=" . $student_row['student_id'];?>">Anecdotals</a></li>
              	<li><a href="<?php echo IPP_PATH . "assistive_technology.php?student_id=" . $student_row['student_id'];?>">Assistive Techology</a></li>
              	<li><a href="<?php echo IPP_PATH . "transition_plan.php?student_id=" . $student_row['student_id'];?>">Transition Plan</a></li>
              	<li><a href="<?php echo IPP_PATH . "accomodations.php?student_id=" . $student_row['student_id'];?>">Accomodations</a></li>
              	<li><a href="<?php echo IPP_PATH . "snapshots.php?student_id=" . $student_row['student_id'];?>">Snapshots</a></li></ul>
            </ul>
             
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php">Logout</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./manage_student.php">Students</a></li>
                <li class="divider"></li>
                <li><a href="change_ipp_password.php">Reset Password</a></li>
                <li><a href="superuser_add_goals.php">Goals Database</a></li>
                <li><a href="./student_archive.php">Archive</a></li>
                <li><a href="./user_audit.php">Audit</a></li>
                <li><a href="superuser_manage_coding.php">Manage Codes</a></li>
                <li><a href="school_info.php">Manage Schools</a></li>
                <li><a href="superuser_view_logs.php">View Logs</a></li>
              </ul>
            </li>
          </ul>
         </div>
         <!--/.nav-collapse -->
        <!--<div class="navbar-collapse collapse">
        </div><!--/.navbar-collapse -->
      </div>
    </div>	
		
		
EOF;
return $nav;
}
?>
