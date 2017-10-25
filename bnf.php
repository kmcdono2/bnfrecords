<?php

/////////////////////////////////////////////////////////// Clean post and get	

/////////////////////////////////////////////////////////// Prevent Direct Access of Included Files

	define('MyConstInclude', TRUE);

/////////////////////////////////////////////////////////// Collect session data

	$MerdUser = session_id();
	if(empty($MerdUser)) { session_start(); }
	$SIDmerd = session_id(); 
	
/////////////////////////////////////////////////////////// Clean post and get	
	
	include("./bnf.config.php");
	include("./bnf.dbconnect.php");
	include("./index_functions.php");
	include("./bnf.simple_html_dom.php");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	mb_internal_encoding("UTF-8");
	if (!mysqli_set_charset($mysqli_link, "utf8")) {
		echo "PROBLEM WITH CHARSET!";
		die;		
	}
	
/////////////////////////////////////////////////////////// Get and Set Vars
	
	$query = "TRUNCATE Catlibr_records;";
	$mysqli_result = mysqli_query($mysqli_link, $query);

/////////////////////////////////////////////////////////// Run Routines

	$baseurl = "http://catalogue.bnf.fr";
	$url_pre = "http://catalogue.bnf.fr/changerPageAdv.do?mots0=GES%3B-1%3B0%3BCatlibr&mots1=ALL%3B0%3B1%3B&listeAffinages=FacDat_1700.0%211799.9&nbResultParPage=100&afficheRegroup=false&affinageActif=true&pageEnCours=";
	$url_post = "&nbPage=24&trouveDansFiltre=NoticePUB&triResultParPage=0&pageRech=rav";	
	
	$myFiles = array();
	for($n=1; $n<25; $n++) { 	
		$url = $url_pre.$n.$url_post;
		$html = file_get_html($url);	
		foreach($html->find('a') as $element){
			$recordlinkPart = $element->href;
			$recordlink = $baseurl.$recordlinkPart.'.intermarc';
			if(preg_match("/ark\:\/12148\//i","$recordlink")){
				$myFiles[] = "$recordlink";
			}	
		}
	}

	$x = 1;
	$myFiles = array_unique($myFiles, SORT_REGULAR);
	foreach($myFiles as $mF){
		$UUID = guidv4();
		$query = "INSERT INTO Catlibr_records ";
		$query .= "VALUES (\"0\", \"$UUID\", \"$x\", \"$mF\", \"\");";
		$mysqli_result = mysqli_query($mysqli_link, $query);
		echo "$x $mF<br />";
		$x++;
	}

	$query = "SELECT * FROM Catlibr_records ORDER BY ID ASC";
	$mysqli_result = mysqli_query($mysqli_link, $query);
	while($row = mysqli_fetch_row($mysqli_result)) {
		$ID = $row[0];
		$url = $row[3];
		$contents = file_get_contents($url);
		$contents = preg_replace("/\"/","'","$contents");
		$q = "UPDATE Catlibr_records SET content = \"$contents\" WHERE ID = \"$ID\"; ";
		$mysqli_resultB = mysqli_query($mysqli_link, $q);
	}

	echo "<br />Finished";	
	die;	
	
?>