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
	
	$query = "TRUNCATE Imp_Libraire_XML_UUID;";
	$mysqli_result = mysqli_query($mysqli_link, $query);

/////////////////////////////////////////////////////////// Run Routines

	for($b=0; $b<10; $b++){
		if($b==0){
			$a=1;
		}
		if($b==1){
			$a=1000;
		}
		if($b==2){
			$a=2000;
		}
		if($b==3){
			$a=3000;
		}
		if($b==4){
			$a=4000;
		}
		if($b==5){
			$a=5000;
		}
		if($b==6){
			$a=6000;
		}
		if($b==7){
			$a=7000;
		}
		if($b==8){
			$a=8000;
		} 
		if($b==9){
			$a=9000;
		}
		$url = "http://catalogue.bnf.fr/api/SRU?version=1.2&operation=searchRetrieve&query=(aut.type%20all%20%22pep%22)%20and%20(aut.anywhere%20all%20%22imprimeur-libraire%22)%20and%20(aut.status=validated)&recordSchema=intermarcxchange&maximumRecords=1000&startRecord=$a";
		$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$xml = file_get_contents($url, false, $context);
		$xml = preg_replace("/srw:/i", "srw_","$xml");
		$xml = preg_replace("/mxc:/i", "mxc_","$xml");
		$xml = preg_replace("/\@attributes/i", "attributes","$xml");
		$xml_contents = simplexml_load_string($xml);
		echo $xml_contents->srw_numberOfRecords;
		for($X=0; $X<1000; $X++) { 
			$n++;
			$ID1 = $xml_contents->srw_records->srw_record[$X]->srw_recordData->mxc_record->mxc_controlfield[0];
			$ID2 = $xml_contents->srw_records->srw_record[$X]->srw_recordData->mxc_record->mxc_controlfield[1];
			echo "<br />$n | $ID1 | $ID2";
			
			/// database loop
			
			$UUID = guidv4();
			$query = "INSERT INTO Imp_Libraire_XML_UUID ";
			$query .= "VALUES (\"0\", \"$UUID\", \"$ID1\", \"$ID2\");";
			$mysqli_result = mysqli_query($mysqli_link, $query);
			
		}	
	}
	die;

?>