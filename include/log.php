<?php
/* @file
 * @remarks
 * 1. handles the logging of data for administration.
 * 2. In future sprint, set up emailed alert for specific events
 * 3. note there's poor error handling - exhausted dev burned out at end of release cycle?
 * 4. cases are incomplete for switch
 * 5. output of $log_query doesn't appear escaped, but have to check where it's defined
 */
    require_once(IPP_PATH . 'include/db.php');

    /** @fn		IPP_Log($szMsg='', $username="-UNKNOWN-", $level='ERROR',$student_id='')
     *  @brief  Puts log entry in MySQL database
     *  @detail	Currently only logs errors; Warnings and Information don't have complete code.
     * @param string $szMsg
     * @param string $username
     * @param string $level
     * @param string $student_id
     * @return void|boolean
     * @todo
     * 1. Rename as function that *puts* information in the database
     */
    function IPP_Log($szMsg='', $username="-UNKNOWN-", $level='ERROR',$student_id='') {
        //Error Handler
        switch($level) {
            case 'WARNING' :
            case 'INFORMATIONAL':
            case 'ERROR':
                //connect
                if(!connectIPPDB()) {
                    return;  //crappy...but...oh well.
                }
                $log_query = "INSERT INTO error_log (level,username,time,message,student_id) VALUES ('$level','$username',now(),'" . addslashes($szMsg) . "',";
                if($student_id=="") $log_query=$log_query . "NULL";
                else $log_query=$log_query . "'$student_id'";
                $log_query = $log_query . ")";
                $log_result = mysql_query($log_query);
                //don't care about the result...if she don't log, she don't log.
                if(!$log_result) {
                  echo "log error: " . mysql_error() . "<BR>Query= " . $log_query . "<BR>";
                }
            break;
        }
        return TRUE;
    }

?>
