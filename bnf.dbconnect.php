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

/////////////////////////////////////////////////////////// Main Code

   	$mysqli_link = mysqli_connect("$localhost", "$username", "$password") or 
		die ("<p><b>$localhost</b> could not connect to the database using <b>$username</b> and <b>$password</b>. Oops, did I say that out loud?");
   	mysqli_select_db($mysqli_link, "$database") or 
		die ("<p>Aw, crap, I was unable to select the database. I'm not sure what the problem is either but I'm sure you'll work it out!");
	
?>