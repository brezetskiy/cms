<?php

/*
 * SimpleModal Contact Form
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2009 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: contact-dist.php 254 2010-07-23 05:14:44Z emartin24 $
 *
 */


// User settings
$to = 'test@domain.ru';
$subject = "Вопрос из сайта";

// Include extra form fields and/or submitter data?
// false = do not include
$extra = array(
	"form_subject"	=> false,
	"form_cc"		=> false,
	"ip"			=> false,
	"user_agent"	=> false
);

// Process

$action = isset($_POST["action"]) ? $_POST["action"] : "";

if (empty($action)) {
	$array_form['title']= 'Вопрос специалисту' ;//iconv("WINDOWS-1251", "UTF-8", 'Вопрос специалисту'); 
	$array_form['name']= 'Ваше имя*'; //iconv("WINDOWS-1251", "UTF-8", 'Ваше имя*'); 
	$array_form['message']= 'Вопрос*'; //iconv("WINDOWS-1251", "UTF-8", 'Вопрос'); 
	$array_form['send']= 'Отправить вопрос'; //iconv("WINDOWS-1251", "UTF-8", 'Отправить вопрос'); 
	$array_form['cansel']= 'Отменить'; //iconv("WINDOWS-1251", "UTF-8", 'Отменить'); 
	
	// Send back the contact form HTML
	$output = "<div style='display:none'>
	<div class='contact-top'></div>
	<div class='contact-content'>
		<h1 class='contact-title'>".$array_form['title']."</h1>
		<div class='contact-loading' style='display:none'></div>
		<div class='contact-message' style='display:none'></div>
		<form action='#' style='display:none'>
			<table><tr><td>
			<label for='contact-name'>".$array_form['name'].":</label><br/>
			<input type='text' id='contact-name' class='contact-input' name='name' tabindex='1001' /><br/>
			<label for='contact-email'>Email*:</label><br/>
			<input type='text' id='contact-email' class='contact-input' name='email' tabindex='1002' /><br/></td>";
	if (isset($_POST['id'])) $output .= "<label>".$_POST['id']."</label>";
	if ($extra["form_subject"]) {
		$output .= "
			<label for='contact-subject'>Subject:</label>
			<input type='text' id='contact-subject' class='contact-input' name='subject' value='' tabindex='1003' />";
	}

	$output .= "<td>
			<label for='contact-message'>".$array_form['message'].":</label><br/>
			<textarea id='contact-message' class='contact-input' name='message' cols='40' rows='4' tabindex='1004'></textarea>
			</td></tr></table>";

	if ($extra["form_cc"]) {
		$output .= "
			<label>&nbsp;</label>
			<input type='checkbox' id='contact-cc' name='cc' value='1' tabindex='1005' /> <span class='contact-cc'>Send me a copy</span>
			<br/>";
	}

	$output .= "
			<label>&nbsp;</label><div id='button-right'>
			<button type='submit' class='contact-send contact-button' tabindex='1006'>".$array_form['send']."</button>			
			</div>
			<input type='hidden' name='id'  id='id_trener' value=''/>
			<input type='hidden' name='token'  value='" . smcf_token($to) . "'/>
		</form>
	</div>	
</div>";
//<button type='submit' class='contact-cancel contact-button simplemodal-close' tabindex='1007'>".$array_form['cansel']."</button>
	echo $output;
}
else if ($action == "send") {
	// Send the email
	$name = isset($_POST["name"]) ? mime_header_encode($_POST["name"])  : "";	
	$email = isset($_POST["email"]) ? $_POST["email"] : "";
	$subject = isset($_POST["subject"]) ? $_POST["subject"] : $subject;
	$message = isset($_POST["message"]) ? $_POST["message"] : "";

	 /* Соединяемся, выбираем базу данных */
	 
    $link = mysql_connect("cformat.mysql.ukraine.com.ua", "cformat_anoco", "n96gnczp")
        or die("Could not connect : " . mysql_error());  
    mysql_select_db("cformat_anoco") or die("Could not select database");	
	mysql_set_charset("utf-8");
	
	$id = isset($_POST["id"]) ? $_POST["id"] : ""; 
	$query = "SELECT * FROM treners WHERE id='$id'";
	$result = mysql_query($query);
	$user = mysql_fetch_assoc($result); 
	
	$cc = isset($_POST["cc"]) ? $_POST["cc"] : "";
	$token = isset($_POST["token"]) ? $_POST["token"] : "";

	// make sure the token matches
	if ($token === smcf_token($to)) {
		$to = $user['email'];
		$message = mime_header_encode($user['name'], 'windows-1251').", ".mime_header_encode($message);
		smcf_send($name, $email, $subject, $message, $cc);
		echo "Ваш вопрос успешно отправлен.";
	}
	else {
		echo "Unfortunately, your message could not be verified.";
	}
}

function smcf_token($s) {
	return md5("smcf-" . $s . date("WY"));
}

// Validate and send email
function smcf_send($name, $email, $subject, $message, $cc) {
	global $to, $extra;

	// Filter and validate fields
	$name = smcf_filter($name);
	$subject = smcf_filter($subject);
	$email = smcf_filter($email);
	if (!smcf_validate_email($email)) {
		$subject .= " - invalid email";
		$message .= "\n\nBad email: $email";
		$email = $to;
		$cc = 0; // do not CC "sender"
	}

	// Add additional info to the message
	if ($extra["ip"]) {
		$message .= "\n\nIP: " . $_SERVER["REMOTE_ADDR"];
	}
	if ($extra["user_agent"]) {
		$message .= "\n\nUSER AGENT: " . $_SERVER["HTTP_USER_AGENT"];
	}

	// Set and wordwrap message body
	
	$name = mime_header_encode("OT: ", 'windows-1251'). $name;
	$em = mime_header_encode("E-mail: ", 'windows-1251'). $email;
	$body = "$name\n";	
	$body .= "$em\n\n";	
	$body .= "$message";
	$body = wordwrap($body, 70);

	// Build header
	$headers = "From: $email\n";
	if ($cc == 1) {
		$headers .= "Cc: $email\n";
	}
	$headers .= "X-Mailer: PHP/SimpleModalContactForm";

	// UTF-8
	if (function_exists('mb_encode_mimeheader')) {
		$subject = mb_encode_mimeheader($subject, "KOI8-R", "B", "\n");
	}
	else {
		// you need to enable mb_encode_mimeheader or risk 
		// getting emails that are not UTF-8 encoded
	}
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/plain; charset=KOI8-R\n";
	$headers .= "Content-Transfer-Encoding: quoted-printable\n";

	// Send email
	$tousr = explode(',',$to);
	foreach ($tousr as $tomail) {
		$tomail = trim($tomail);
		@mail($tomail, $subject, $body, $headers) or 
			die("Unfortunately, a server issue prevented delivery of your message."); 
	}
}

function mime_header_encode($str, $data_charset='utf-8', $send_charset='KOI8-R') {
	  if($data_charset != $send_charset) {
	    $str = iconv($data_charset, $send_charset, $str);
	  }
	  return $str;
}
	
// Remove any un-safe values to prevent email injection
function smcf_filter($value) {
	$pattern = array("/\n/","/\r/","/content-type:/i","/to:/i", "/from:/i", "/cc:/i");
	$value = preg_replace($pattern, "", $value);
	return $value;
}

// Validate email address format in case client-side validation "fails"
function smcf_validate_email($email) {
	$at = strrpos($email, "@");

	// Make sure the at (@) sybmol exists and  
	// it is not the first or last character
	if ($at && ($at < 1 || ($at + 1) == strlen($email)))
		return false;

	// Make sure there aren't multiple periods together
	if (preg_match("/(\.{2,})/", $email))
		return false;

	// Break up the local and domain portions
	$local = substr($email, 0, $at);
	$domain = substr($email, $at + 1);


	// Check lengths
	$locLen = strlen($local);
	$domLen = strlen($domain);
	if ($locLen < 1 || $locLen > 64 || $domLen < 4 || $domLen > 255)
		return false;

	// Make sure local and domain don't start with or end with a period
	if (preg_match("/(^\.|\.$)/", $local) || preg_match("/(^\.|\.$)/", $domain))
		return false;

	// Check for quoted-string addresses
	// Since almost anything is allowed in a quoted-string address,
	// we're just going to let them go through
	if (!preg_match('/^"(.+)"$/', $local)) {
		// It's a dot-string address...check for valid characters
		if (!preg_match('/^[-a-zA-Z0-9!#$%*\/?|^{}`~&\'+=_\.]*$/', $local))
			return false;
	}

	// Make sure domain contains only valid characters and at least one period
	if (!preg_match("/^[-a-zA-Z0-9\.]*$/", $domain) || !strpos($domain, "."))
		return false;	

	return true;
}

exit;

?>