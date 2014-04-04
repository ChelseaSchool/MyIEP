<?php

//* @todo: track db plain text credential file reference (init.php); if we change the location in the interest of security, do we know where to change the code?

/** @file
 * @brief  Two database handling utilities
 * @copyright 	2014 Chelsea School 
 * @copyright 	2005 Grasslands Regional Division #6
 * @copyright		This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @authors		Rik Goldman, Sabre Goldman, Jason Banks, Alex, James, Paul, Bryan, TJ, Jonathan, Micah, Stephen, Joseph
 * @author		M. Nielson
 * @todo		1. Check for credential handling - where is encryption set up?
 * 				2. Contrast two functions in this script. On first glance they look redundant.
 */

/** @fn connectUserDB()
 * 
 * @brief Connects to user DB as specified in config.
 * @return $db_user or false
 */
	function connectUserDB() {
        //connects to the USER DB as specified in the config file.
        //inputs: none
        //returns $db_user - handle to user database or FALSE on fail.
        //if FALSE returns $error_message
        global $mysql_user_host,$mysql_user_username,$mysql_user_password,$mysql_user_database,$error_message;

        $link = mysql_connect($mysql_user_host,$mysql_user_username,$mysql_user_password);
        if($link == FALSE) {
           $error_message = "Could not connect to database: (". __FILE__ . ":" . __LINE__ . ") for the following reason: '" . mysql_error() . "'<BR>\n";
           return FALSE;
        }
        $db_user = mysql_select_db($mysql_user_database);
        if(!$db_user) {
           $error_message = "Could not select database: (". __FILE__ . ":" . __LINE__ . ")'" . $mysql_user_database . "' for the following reason: '" . mysql_error() . "'</BR>\n";
           return FALSE;
        }

        return $db_user;
    }

    /** @fn 	connectIPPDB()
     *  @brief	another function that connects to db
     *  @return $db_user
     *  @todo
     *  1. Identify the difference between these two functions.
     */
    function connectIPPDB() {
        //connects to the eGPS DB
        //inputs: none
        //returns $db_user - handle to user database or FALSE on fail.
        //if FALSE returns $error_message
        global $mysql_data_database,$mysql_data_host,$mysql_data_username,$mysql_data_password,$error_message;

        $link = mysql_connect($mysql_data_host,$mysql_data_username,$mysql_data_password);
        if($link == FALSE) {
           $error_message = "Could not connect to database on $mysql_data_host: (". __FILE__ . ":" . __LINE__ . ") for the following reason: '" . mysql_error() . "'<BR>\n";
           return FALSE;
        }
        $db_user = mysql_select_db($mysql_data_database);
        if(!$db_user) {
           $error_message = "Could not select database: (". __FILE__ . ":" . __LINE__ . ")'" . $mysql_user_database . "' for the following reason: '" . mysql_error() . "'</BR>\n";
           return FALSE;
        }

        return $db_user;
    }


?>
