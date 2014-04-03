<?php
/** @file
 *  @brief  Contains functions allowing system to send mail
 *  @todo
 *  1. Refactor - right now this file contains a single function. Move to other included file containing collections of functions.
 *  
 */

// functions allowing system to send mail
if (! defined ( 'IPP_PATH' ))
	define ( 'IPP_PATH', '../' );

require_once (IPP_PATH . 'etc/init.php'); // make sure we have this. Todo: shouldn't there be a test and definition of what to do if test fails?
require_once (IPP_PATH . 'include/log.php');

// make sure we aren't accessing this file directly...todo: check function syntax on 11
if (realpath ( $_SERVER ["SCRIPT_FILENAME"] ) == realpath ( __FILE__ )) {
	$system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER ['REMOTE_ADDR'] . ")";
	IPP_LOG ( $system_message, $_SESSION ['egps_username'], 'ERROR' );
	require (IPP_PATH . 'security_error.php');
	exit ();
}

/**
 * @fn		mail_notification($recipients="",$message="-unknown message-")
 * @brief	sends email to one recipient, if proper dependencies are installed
 * 
 * @param string $recipients        	
 * @param string $message        	
 * @return void number Requires:
 *         1. Pear/Main
 *         2. Net/SMTP
 */
function mail_notification($recipients = "", $message = "-unknown message-") {
	// Recipients currently only one!(separated by , ) todo: how do we implement ability for more than one mail recipient?
	global $system_message, $enable_email_notification, $mail_host, $append_to_username, $email_reply_address, $IPP_ORGANIZATION;
	
	if (! $enable_email_notification)
		return;
	if (! @include_once ("Mail.php")) {
		$system_message = "Your administrator does not have the Pear Mail Class installed<BR>No email notification has been sent<BR>"; // todo: add netSMTP to installation requirements
		return 0;
	}
	; // pear mail module.
	if (! @include_once ("Mail/mime.php")) {
		$system_message = "You do not have the Pear Mail Class installed<BR>No email notification sent<BR>";
		return 0;
	}
	; // mime class
	if (! @include_once ("Net/SMTP.php")) {
		$system_message = "Your administrator does not have the Net/SMTP Pear Class installed<BR>No email notification has been sent<BR>";
		return 0;
	}
	
	$recipients = $recipients . $append_to_username; // Recipients (separated by , )
	                                                 
	// echo "send to: " . $recipients . "<BR>"; todo: this is commented out; justify its existence or strip
	
	$headers ["From"] = $email_reply_address;
	$headers ["Subject"] = "IPP System ($IPP_ORGANIZATION)"; // Subject of the address
	$headers ["MIME-Version"] = "1.0";
	$headers ["To"] = $recipients;
	// $headers["Content-type"] = "text/html; charset=UTF-8"; todo: note charset. Determine charset and standardize; this code is out - determine why
	
	$mime = new Mail_mime ( "\r\n" ); // dangerous characters escaped
	                             // $mime->setTxtBody("This is an HTML message only"); todo: why is this disabled (commented)?
	                             // $mime->_build_params['text_encoding']='quoted_printable'; todo: why is this also disabled? justify keeping it or strip
	$mime->setHTMLBody ( "<html><body>$message</body></html>" );
	$mime->setTXTBody ( $message );
	// $mime->addAttachment("Connection.pdf","application/pdf"); todo: note this is disabled by commenting characters. What do we need to do to make the system email a pdf?
	
	$body = $mime->get ();
	$hdrs = $mime->headers ( $headers );
	
	$params ["host"] = $mail_host; // SMTP server (mail.yourdomain.net)
	$params ["port"] = "25"; // Leave as is - todo: note this is default smtp mail port
	$params ["auth"] = false; // Leave as is
	$params ["username"] = "user"; // Username of from account
	$params ["password"] = "password"; // Password of the above
	                                  
	// Create the mail object using the Mail::factory method
	$mail_object = & Mail::factory ( "smtp", $params ); // todo - establish an understanding of this method and compare to contemporary best practive
	
	$mail_object->send ( $recipients, $hdrs, $body ); // Send the email using the Mail PEAR Class
		                                               // echo "send to: $recipients,<BR>headers: $hdrs,<BR>body: $body<BR>"; todo: note this html output is disabled; there is no confirmation in this code
}
?>
