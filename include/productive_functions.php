<?php

/*! @file
 *  @brief 	Conversion of commonly used legacy code to functions
 * @copyright 	2014 Chelsea School 
  * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph, Sean
 * @todo		Implement, test, one at a time
 */


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


/** @fn clean_in_and_out($input)
 * 
 * Filters input and escapes output to prepare for MySQL
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


/** @fn define_app_path()
 * 
 * Defines the path to the application as ./
 * 
 * @warning 	Needs login before use on files in subdirectories (and a parameter)
 * @todo		
 * 1. Test and implement.
 * 2. Rename IPP-PATH
 *  
 */
function app_path() {
	$output = define('IPP_PATH','./');
	echo $output;
}
/** @fn print_system_message()
 * 
 * Sets the $system_message based on session? or clears it so it's not tampered with.
 * 
 * @warning 	We need to track down how $system_message is handled between pages
 * @todo		
 * 1. Research
 * 2. Test
 * 3. Find all occurences of the meaty function line in code; replace with function call.
 *  
 */
function print_system_message() {
	if(isset($system_message)) $system_message = $system_message; else $system_message="";
	return $system_message;
}
?>