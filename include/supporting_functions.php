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
 */

if(!defined('IPP_PATH')) define('IPP_PATH','../');
include_once('IPP_PATH' . 'print_html_functions.php');

/** @fn checkSpelling ( $string )
 *  @brief	function to make use of pspell (PEAR): given a string, returns error check and makes spelling recommendations
 *  @detail	No longer necessary; Making use of spellcheck attribute in HTML5 and browsers. 
 *  @todo
 *  1. Refactor so nothing calls this function (it's already been done once but needs confirmation)
 *
 *  @param $string
 */
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
 * @param $input
 * @return mysql_real_escape_string($input)
 * @detail 		Strips tags, then sanitizes html entities, and then strips slashes. Finally, uses mysql_real_escape_string() to prepare for MySQL use.
 *
 * @warning 	Not for arrays. Must construct stripslashes_deep() for arrays.
 * @todo		
 * 1. Test and implement (not done yet)
 * 	* find systematic way to use on all db input: perhaps when UPDATE query is used.
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
 *  @brief Print HTML Comments with Copyright and license info
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
 */
function print_complete_footer() {
	print_footer();
	echo print_intellectual_property();
}


function print_datepicker_depends() {
	$print_depends= <<<EOF
	<!-- Example Invokation of Datepicker -->
	<!-- input type=datepicker name="review_date" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd"  -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/jquery-1.9.1.js"></script>
	<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	</script>
	 <script> 
	$(function() {
	$( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
	});
	</script>
EOF;
	echo $print_depends;
}
	
	?>