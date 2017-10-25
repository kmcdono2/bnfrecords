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
	
	$query = "TRUNCATE Libraire_records;";
	$mysqli_result = mysqli_query($mysqli_link, $query);

/////////////////////////////////////////////////////////// Run Routines

	$baseurl = "http://catalogue.bnf.fr";
	$url_pre = "http://catalogue.bnf.fr/changerPageAdvAuto.do?mots0=FRM%3B-1%3B1%3Blibraire&mots1=FRM%3B1%3B2%3Bimprimeur+libraire&typeAuto=1_PEP%3B2_RAM_PE%3B2_RAM_TP%3B2_RAM_TU%3B2_RAM_GE%3B2_RAM_SC%3B1_ORG%3B2_RAM_CO%3B2_RAM_NC%3B1_RAM&statutAuto=C&listeAffinages=FacSiecleNaiss_17&nbResultParPage=100&afficheRegroup=false&affinageActif=true&pageEnCours=";
	$url_post = "&nbPage=11&trouveDansFiltre=NoticePUB&triResultParPage=0&pageRech=rat";	
	
	$myFiles = array();
	for($n=1; $n<12; $n++) { 	
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
		$query = "INSERT INTO Libraire_records ";
		$query .= "VALUES (\"0\", \"$UUID\", \"$x\", \"$mF\", \"\");";
		$mysqli_result = mysqli_query($mysqli_link, $query);
		echo "$x $mF<br />";
		$x++;
	}

	$query = "SELECT * FROM Libraire_records ORDER BY ID ASC";
	$mysqli_result = mysqli_query($mysqli_link, $query);
	while($row = mysqli_fetch_row($mysqli_result)) {
		$ID = $row[0];
		$url = $row[3];
		$contents = file_get_contents($url);
		$contents = preg_replace("/\"/","'","$contents");
		$q = "UPDATE Libraire_records SET contents = \"$contents\" WHERE ID = \"$ID\"; ";
		$mysqli_resultB = mysqli_query($mysqli_link, $q);
	}

	echo "<br />Finished";	
	die;	
	
?>