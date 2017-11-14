<?php

/////////////////////////////////////////////////////////// Source
//
//
//	BnF Catalog Tool
//	Digital Humanities Research Group
//  School of Humanities and Communication Arts
//  University of Western Sydney
//
//	Procedural Scripting: PHP | MySQL | JQuery
//
//	FOR ALL ENQUIRIES ABOUT CODE
//
//	Who:	Dr Katie McDonough
//	Email: 	k.mcdonough@westernsydney.edu.au
//
//  VERSION 0.1
//	4 April 2017
//
//
/////////////////////////////////////////////////////////// Prevent Direct Access

	if(!defined('MyConstInclude')) {
   		die('Direct access not permitted');
	}	

/////////////////////////////////////////////////////////// Sanitise functions

	function cleanInput($input) {
  		$search = array(
			'@<script[^>]*?>.*?</script>@si',
			'@<[\/\!]*?[^<>]*?>@si',
			'@<style[^>]*?>.*?</style>@siU',
			'@<![\s\S]*?--[ \t\n\r]*>@'
  		);
    	$output = preg_replace($search, '', $input);
    	return $output;
  	}
	
	function sanitize($input) {
		if (is_array($input)) {
			foreach($input as $var=>$val) {
				$output[$var] = sanitize($val);
			}
		}
		else {
			if (get_magic_quotes_gpc()) {
				$input = stripslashes($input);
			}
			$input  = cleanInput($input);
			$output = mysql_real_escape_string($input);
		}
		return $output;
	}	
	
/////////////////////////////////////////////////////////// Session Handler Coming Soon	
	
	$_SESSION["credential_loginName"] = "jensor";

/////////////////////////////////////////////////////////// Main DB configuration

	$localhost = "localhost";
	$username = "####";
	$password = "####";
	$database = "BnF_records";

/////////////////////////////////////////////////////////// Detaint all vars

	$dbc = @mysqli_connect($localhost, $username, $password);

	foreach($_POST as $key => $value) {
		$newVal = trim($value);
//		$newVal = sanitize($newVal);
    	$newVal = mysqli_real_escape_string($dbc,$newVal);
		$_POST[$key] = $newVal;
	}

	foreach($_GET as $key => $value) {
		$newVal = trim($value);
//		$newVal = sanitize($newVal);
    	$newVal = mysqli_real_escape_string($dbc,$newVal);
		$_GET[$key] = $newVal;
	}

/////////////////////////////////////////////////////////// Close
	
?>