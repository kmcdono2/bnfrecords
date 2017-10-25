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
	
	$query = "TRUNCATE Libraire_records_content;";
	$mysqli_result = mysqli_query($mysqli_link, $query);

/////////////////////////////////////////////////////////// Run Routines
	
	$query = "SELECT * FROM Libraire_records ORDER BY ID ASC";
	$mysqli_result = mysqli_query($mysqli_link, $query);
	while($row = mysqli_fetch_row($mysqli_result)) {
		$dups = array();
		$data = "";
		$url = $row[4];
		$html = str_get_html($url);	
		foreach($html->find('.etiquetteMarc') as $element) {
			$intermarcField = trim("$element");
			$dups[] = strip_tags(trim($element->parent()));
		}
		$dups = array_unique($dups, SORT_REGULAR);
		foreach($dups as $d){
			$data .= $d."|";
		}
		$UUID = $row[1];
		$queryA = "INSERT INTO Libraire_records_content ";
		$queryA .="VALUES (\"0\", \"$UUID\", \"$data\");";
		$mysqli_resultA = mysqli_query($mysqli_link, $queryA);
		$x++;
	}
	
	echo "$x done";
	die;





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