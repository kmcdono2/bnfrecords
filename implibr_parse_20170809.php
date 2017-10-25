<?php

/////////////////////////////////////////////////////////// XML Layout
//
//	srw_searchRetrieveResponse > srw_records > srw_record > srw_recordIdentifier
//	srw_searchRetrieveResponse > srw_records > srw_record > srw_recordData > mxc_record > [tag] mxc_controlfield, [value], [attributes > tag]
//
/////////////////////////////////////////////////////////// Functions

	function xml2assoc($xml) { 
		$tree = null; 
		while($xml->read()) {
			switch ($xml->nodeType) { 
				case XMLReader::END_ELEMENT: return $tree; 
				case XMLReader::ELEMENT: 
					$node = array('tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : xml2assoc($xml)); 
					if($xml->hasAttributes) {
						while($xml->moveToNextAttribute()) {
							$node['attributes'][$xml->name] = $xml->value; 
						}
					}
					$tree[] = $node; 
				break; 
				case XMLReader::TEXT: 
				case XMLReader::CDATA: 
					$tree .= $xml->value; 
			} 
		}
		return $tree; 
	} 

/////////////////////////////////////////////////////////// Prevent Direct Access and Collect session data

	define('MyConstInclude', TRUE);
	$MerdUser = session_id();
	if(empty($MerdUser)) { session_start(); }
	$SIDmerd = session_id(); 
	
/////////////////////////////////////////////////////////// Add Includes Plus CharSet

	include("./bnf.config.php");
	include("./bnf.dbconnect.php");
	mb_internal_encoding("UTF-8");
	if (!mysqli_set_charset($mysqli_link, "utf8")) {
		echo "PROBLEM WITH CHARSET!";
		die;		
	}

/////////////////////////////////////////////////////////// Other Includes

	include("./index_functions.php");
	include("./simple_html_dom.php");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	
/////////////////////////////////////////////////////////// Get and Set Vars
	
	$query = "TRUNCATE Imp_Libraire_XML;";
	$mysqli_result = mysqli_query($mysqli_link, $query);

/////////////////////////////////////////////////////////// Run Routines

	for($b=0; $b<10; $b++){
		
/////////////////////////////// Loop Vars		

		$max = 1000;
		
		if($b == 0){ $a = 1; }
		if($b == 1){ $a = 1000; }
		if($b == 2){ $a = 2000; }
		if($b == 3){ $a = 3000; }
		if($b == 4){ $a = 4000; }
		if($b == 5){ $a = 5000; }
		if($b == 6){ $a = 6000; }
		if($b == 7){ $a = 7000; }
		if($b == 8){ $a = 8000; }
		if($b == 9){ $a = 9000; }
		
/////////////////////////////// XML Source and Configuration		
		
//		$a = 1;
//		$max = 1000;
		$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$url = "http://catalogue.bnf.fr/api/SRU?version=1.2&operation=searchRetrieve&query=";
		$url .= "(aut.type%20all%20%22pep%22)";
		$url .= "%20and%20";
		$url .= "(aut.anywhere%20all%20%22imprimeur-libraire%22)";
		$url .= "%20and%20";
		$url .= "(aut.status=validated)";
		$url .= "&recordSchema=intermarcxchange&maximumRecords=$max";
		$url .= "&startRecord=$a";
		
/////////////////////////////// Get XML		
		
		$xml_source = file_get_contents($url, false, $context);
		$xml_source = preg_replace("/srw:/i", "srw_","$xml_source");
		$xml_source = preg_replace("/mxc:/i", "mxc_","$xml_source");	

/////////////////////////////// Parse XML via HTML DOM
			
		$xml = new XMLReader();
		$xml->XML($xml_source);
		$assoc = xml2assoc($xml); 
    	$xml->close();

/////////////////////////////// Extract Values

//		$queryA = "TRUNCATE Catlibr_XML";
//		$mysqli_result = mysqli_query($mysqli_link, $queryA);
		foreach($assoc as $assoc_i) {
			foreach($assoc_i as $srw_searchRetrieveResponse) {
				foreach($srw_searchRetrieveResponse as $srw_records_key => $srw_records_value) {
					if(is_array($srw_records_value)){
						if($srw_records_value["tag"] == "srw_records") {
							foreach($srw_records_value["value"] as $srw_record_key => $srw_record_value) {
								foreach($srw_record_value as $srw_recordDatas_key => $srw_recordDatas_value) {
									foreach($srw_recordDatas_value as $srw_recordData_key => $srw_recordData_value) {
										
									//	print Record position	
//										if(($srw_recordData_value["tag"] == "srw_recordPosition")) {
//											print "Position : ".$srw_recordData_value["value"]."\n";
//										}
										
										if(($srw_recordData_value["tag"] == "srw_recordData")) {
											foreach($srw_recordData_value as $mxc_records_key => $mxc_records_value) {
												foreach($mxc_records_value as $mxc_record){
													if($f > 0) {
														print "\n";
													}
												//	print "ID"." : ".$mxc_record["attributes"]["id"]."\n";
													$ID = "http://catalogue.bnf.fr/".$mxc_record["attributes"]["id"];
													$query = "SELECT ID, UUID FROM Imp_Libraire_XML_UUID WHERE ARK = \"$ID\"";
													$mysqli_result = mysqli_query($mysqli_link, $query);
													while($row = mysqli_fetch_row($mysqli_result)) {	
														$XML_ID = $row[0];
														$UUID = $row[1]; 
														
													}
													foreach($mxc_record["value"] as $tags) {
														if($tags["tag"] == "mxc_controlfield") {
													//		print "".$tags["attributes"]["tag"]." : ".$tags["value"]."\n";
															$queryA = "INSERT INTO Imp_Libraire_XML VALUES (";
															$queryA .= "\"0\", ";
															$queryA .= "\"$XML_ID\", ";
															$queryA .= "\"$UUID\", ";
															$queryA .= "\"$ID\", ";
															$queryA .= "\"".$tags["attributes"]["tag"]."\", ";
															$queryA .= "\"\", ";
															$queryA .= "\"".$tags["value"]."\"";
															$queryA .= ")\n";
															$mysqli_resultA = mysqli_query($mysqli_link, $queryA);
														}
														if($tags["tag"] == "mxc_datafield") {
															$intermarcID = $tags["attributes"]["tag"];
															foreach($tags["value"] as $tk => $tv) {
													//			print $intermarcID." ".$tv["attributes"]["code"]." : ".$tv["value"]."\n";
																$queryA = "INSERT INTO Imp_Libraire_XML VALUES (";
																$queryA .= "\"0\", ";
																$queryA .= "\"$XML_ID\", ";
																$queryA .= "\"$UUID\", ";
																$queryA .= "\"$ID\", ";
																$queryA .= "\"".$intermarcID."\", ";
																$queryA .= "\"".$tv["attributes"]["code"]."\", ";
																$queryA .= "\"".$tv["value"]."\"";
																$queryA .= ")\n";
																$mysqli_resultA = mysqli_query($mysqli_link, $queryA);
															}
														}
													}
													$f++;
													
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
/////////////////////////////////////////////////////////// Debug Die		
//		
//		die;
//		
/////////////////////////////////////////////////////////// MYSQL Routines Start		
//		
//		for($x=0;$x<1000;$x++) { 
//			$record = $xml_contents->srw_records->srw_record[$x]->srw_recordData->mxc_record;	
//			$query = "SELECT * FROM Catlibr_XML_UUID ORDER BY ID ASC";
//			$mysqli_result = mysqli_query($mysqli_link, $query);
//			while($row = mysqli_fetch_row($mysqli_result)) {	
//				$UUID = $row[1];
//				$queryA = "INSERT INTO Catlibr_XML VALUES ";
//				$queryA .="(\"0\", \"$UUID\", \"$ARK\", \"$FRBNF\", );";
//				$mysqli_resultA = mysqli_query($mysqli_link, $queryA);
//				$n++;
//			}
//		}	
//		
/////////////////////////////////////////////////////////// MYSQL Routines Finish		
		
	}

/////////////////////////////////////////////////////////// Close
	
	include("./bnf.dbdisconnect.php");
	echo "$n done";

/////////////////////////////////////////////////////////// Finish

?>