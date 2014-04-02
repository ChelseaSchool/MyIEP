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
?>
