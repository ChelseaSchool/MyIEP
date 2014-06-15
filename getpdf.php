<?php
/** @file
 * @brief	Gets PDF, I assume of the current IEP state 
 * @remarks
 * 1. not the same style as most of the code by the dev
 * 2. some security measures in place (checking the filename for slashes and backslashes
 * 3. Purpose: It looks like this displays a generated PDF? Yes, it looks like it's supposed to be a tmp file with a limited lifespan. (does not stay on server)
 * 4. Special handling again for IE
 * 5. errors don't seem to go to log
 * 6. Array $http_get_vars (all caps) should be checked into
 * 
 * @todo
 * 1. Docblock comments
 */ 

$f=$HTTP_GET_VARS['f'];
//Check file (don't skip it!)
if(substr($f,0,3)!='tmp' or strpos($f,'/') or strpos($f,'\\'))
    die('Incorrect file name');
if(!file_exists($f))
    die('File does not exist');
//Handle special IE request if needed
if($HTTP_SERVER_VARS['HTTP_USER_AGENT']=='contype')
{
    Header('Content-Type: application/pdf');
    exit;
}
//Output PDF
Header('Content-Type: application/pdf');
Header('Content-Length: '.filesize($f));
readfile($f);
//Remove file
unlink($f);
exit;
?>
