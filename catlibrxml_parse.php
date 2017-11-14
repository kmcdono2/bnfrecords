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
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	mb_internal_encoding("UTF-8");
	if (!mysqli_set_charset($mysqli_link, "utf8")) {
		echo "PROBLEM WITH CHARSET!";
		die;		
	}
	
/////////////////////////////////////////////////////////// Get and Set Vars
	
	$query = "TRUNCATE Catlibr_XML;";
	$mysqli_result = mysqli_query($mysqli_link, $query);

/////////////////////////////////////////////////////////// Run Routines

	for($b=0; $b<3; $b++){
		if($b==0){
			$a=1;
		}
		if($b==1){
			$a=1000;
		}
		if($b==2){
			$a=2000;
		}
	
		$url = "http://catalogue.bnf.fr/api/SRU?version=1.2&operation=searchRetrieve&query=(bib.anywhere%20all%20%22Catlibr%22)%20and%20(bib.publicationdate%20%3E=%201700)%20and%20(bib.publicationdate%20%3C=1799)&recordSchema=intermarcxchange&maximumRecords=1000&startRecord=$a";
		$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$xml = file_get_contents($url, false, $context);
		$xml = preg_replace("/srw:/i", "srw_","$xml");
		$xml = preg_replace("/mxc:/i", "mxc_","$xml");
//		$xml = preg_replace("/\@attributes/i", "attributes","$xml");
		
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $xml, $object, $index);
		print_r($object);
		die;
		
		$xml_contents = simplexml_load_string($xml);
		$xml_contents = json_encode($xml_contents, JSON_PRETTY_PRINT);
		echo $xml_contents;
		die;
		
		
		for($X=0; $X<1000; $X++) { 
			$n++;
//			$record = $xml_contents->srw_records->srw_record[$X]->srw_recordData->mxc_record->mxc_datafield[0]->mxc_subfield[0];
			
			$record = $xml_contents->srw_records->srw_record[$X]->srw_recordData->mxc_record;
			print_r($record);
			echo "<br />$n | $record ";
			die;
			
			// insert XML fields as rows & match UUID from Catlibr_XML_UUID
			$query = "SELECT * FROM Catlibr_XML_UUID ORDER BY ID ASC";
			$mysqli_result = mysqli_query($mysqli_link, $query);
			while($row = mysqli_fetch_row($mysqli_result)) {	
				$UUID = $row[1];
				$queryA = "INSERT INTO Catlibr_XML ";
				$queryA .="VALUES (\"0\", \"$UUID\", \"$record\");";
				$mysqli_resultA = mysqli_query($mysqli_link, $queryA);
				$x++;
				}
		}	
	}
	
	echo "$x done";
	die;	

?>