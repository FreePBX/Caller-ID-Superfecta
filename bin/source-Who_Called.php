<?php
//this file is designed to be used as an include that is part of a loop.
//If a valid match is found, it should give $caller_id a value
//available variables for use are: $thenumber
//retreive website contents using get_url_contents($url);

//configuration / display parameters
//The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
$source_desc = "http://whocalled.us - These listings are provided by other users of the whocalled service. This service requires authentication - which you configure in Who Called Extended Information, below.";
$source_param = array();
$source_param['Username']['desc'] = 'Your user account Login on the whocalled.us web site.';
$source_param['Username']['type'] = 'text';
$source_param['Password']['desc'] = 'Your user account Password on the whocalled.us web site.';
$source_param['Password']['type'] = 'password';
$source_param['Get_Caller_ID_Name']['desc'] = 'Use whocalled.us for caller id name lookup.';
$source_param['Get_Caller_ID_Name']['type'] = 'checkbox';
// $source_param['Report_Back']['desc'] = 'If a the caller has been flagged as SPAM by the successful CID scheme, provide it back to Who Called for their database. All submissions back to this source are considered SPAMers.';
// $source_param['Report_Back']['type'] = 'checkbox';
$source_param['Get_SPAM_Score']['desc'] = 'Use whocalled.us for spam scoring.';
$source_param['Get_SPAM_Score']['type'] = 'checkbox';
$source_param['SPAM_Threshold']['desc'] = 'Specify the listings required to mark a call as spam.';
$source_param['SPAM_Threshold']['type'] = 'number';
$source_param['SPAM_Threshold']['default'] = 10;

//run this if the script is running in the "get caller id" usage mode.
if($usage_mode == 'get caller id')
{
	if($debug)
	{
		print "Searching Who Called ... <br>\n";
	}

		//check for the correct 11 digits in US/CAN phone numbers in international format.
	// country code + number
	if (strlen($thenumber) == 11)
	{
		if (substr($thenumber,0,1) == 1)
		{
			$thenumber = substr($thenumber,1);
		}
		else
		{
			$number_error = true;
		}

	}
	// international dialing prefix + country code + number
	if (strlen($thenumber) > 11)
	{
		if (substr($thenumber,0,3) == '001')
		{
			$thenumber = substr($thenumber, 3);
		}
		else
		{
			if (substr($thenumber,0,4) == '0111')
			{
				$thenumber = substr($thenumber,4);
			}			
			else
			{
				$number_error = true;
			}
		}
	}	
	// number
      if(strlen($thenumber) < 10)
	{
		$number_error = true;
	}
else
	{
		$number_error = false;
	}
	if(!$number_error)
	{
		$npa = substr($thenumber,0,3);
		$nxx = substr($thenumber,3,3);
		$station = substr($thenumber,6,4);
		
		// Check for Toll-Free numbers
		$TFnpa = false;
		if($npa=='800'||$npa=='866'||$npa=='877'||$npa=='888')
		{
			$TFnpa = true;
		}
		
		// Check for valid US NPA
		$npalistUS = array(
			"201", "202", "203", "205", "206", "207", "208", "209", "210", "212",
			"213", "214", "215", "216", "217", "218", "219", "224", "225", "228",
			"229", "231", "234", "239", "240", "242", "246", "248", "251", "252",
			"253", "254", "256", "260", "262", "264", "267", "268", "269", "270",
			"276", "281", "284", "301", "302", "303", "304", "305", "307", "308",
			"309", "310", "312", "313", "314", "315", "316", "317", "318", "319",
			"320", "321", "323", "325", "330", "331", "334", "336", "337", "339",
			"340", "345", "347", "351", "352", "360", "361", "386", "401", "402",
			"404", "405", "406", "407", "408", "409", "410", "412", "413", "414",
			"415", "417", "419", "423", "424", "425", "430", "432", "434", "435",
			"440", "441", "443", "456", "469", "473", "478", "479", "480", "484",
			"500", "501", "502", "503", "504", "505", "507", "508", "509", "510",
			"512", "513", "515", "516", "517", "518", "520", "530", "540", "541",
			"551", "559", "561", "562", "563", "567", "570", "571", "573", "574",
			"575", "580", "585", "586", "600", "601", "602", "603", "605", "606",
			"607", "608", "609", "610", "612", "614", "615", "616", "617", "618",
			"619", "620", "623", "626", "630", "631", "636", "641", "646", "649",
			"650", "651", "660", "661", "662", "664", "670", "671", "678", "682",
			"684", "700", "701", "702", "703", "704", "706", "707", "708", "710",
			"712", "713", "714", "715", "716", "717", "718", "719", "720", "724",
			"727", "731", "732", "734", "740", "754", "757", "758", "760", "762",
			"763", "765", "767", "769", "770", "772", "773", "774", "775", "779",
			"781", "784", "785", "786", "787", "801", "802", "803", "804", "805",
			"806", "808", "809", "810", "812", "813", "814", "815", "816", "817",
			"818", "828", "829", "830", "831", "832", "843", "845", "847", "848",
			"850", "856", "857", "858", "859", "860", "862", "863", "864", "865",
			"868", "869", "870", "876", "878", "900", "901", "903", "904", "906",
			"907", "908", "909", "910", "912", "913", "914", "915", "916", "917",
			"918", "919", "920", "925", "928", "931", "936", "937", "939", "940",
			"941", "947", "949", "951", "952", "954", "956", "970", "971", "972",
			"973", "978", "979", "980", "985", "989",
			"800", "866", "877", "888"
		);
		
		$validnpaUS = false;
		if(in_array($npa, $npalistUS))
		{
			$validnpaUS = true;
		}
		
		// Check for valid CAN NPA
		$npalistCAN = array(
			"204", "226", "249", "250", "289", "306", "343", "365", "403", "416", "418", "438", "450",
			"506", "514", "519", "581", "587", "579", "604", "613", "647", "705", "709",
			"778", "780", "807", "819", "867", "873", "902", "905",
			"800", "866", "877", "888"
		  );
		
		$validnpaCAN = false;
		if(in_array($npa, $npalistCAN))
		{
			$validnpaCAN = true;
		}
	
		if(!$TFnpa && (!$validnpaUS && !$validnpaCAN))
		{
			$number_error = true;
		}
	
		if($number_error)
		{
			if($debug)
			{
				print "Skipping Source - Not a valid US/CAN number: ".$thenumber."<br>\n";
			}
		}

//		{
	
	
	if($run_param['Get_SPAM_Score'] == 'on')
	{
		if($debug)
		{
			print "Testing if spam ... ";
		}
		$url = "http://whocalled.us/do?action=getScore&name=".$run_param['Username']."&pass=".$run_param['Password']."&phoneNumber=$thenumber";
		$value = get_url_contents($url);
		$st_success = strstr($value, "success");
		$st_score = strstr($value, "score");
		$success = substr($st_success,8,1);
		$score = substr($st_score,6);
		if($success=='1')
		{
		  if($score > $run_param['SPAM_Threshold'])
		  {
				$spam = true;
				if($debug)
				{
					print " determined to be <b>SPAM</b> (score: ".$score.")<br>\n";
				}
		  }
		  else if($debug)
			{
				print "Not a SPAM caller (score: ".$score.")<br>\n";
			}
		}
		else if($debug)
		{
			print "Error in Lookup.<br>\n";
		}
	}
	
	if($run_param['Get_Caller_ID_Name'] == 'on')
	{
		if($debug)
		{
			print "Looking up CNAM ... ";
		}
		$url = "http://whocalled.us/do?action=getWho&name=".$run_param['Username']."&pass=".$run_param['Password']."&phoneNumber=$thenumber";
		$value = get_url_contents($url);
		$st_success = strstr($value, "success");
		$st_cid = strstr($value, "who");
		$success = substr($st_success,8,1);
		$cid = substr($st_cid,4);
		if($success=='1')
		{
		  if($cid != '')
		  {
				$caller_id = $cid;
		  }
		  else if($debug)
			{
				print "not found<br>\n";
			}
		}
		else if($debug)
		{
			print "Error in Lookup.<br>\n";
		}
	}
}
}
if($usage_mode == 'post processing')
{
/***** Disabling Report back 

//	return the value back to Who Called if the user has enabled it and the result didn't come from cache. This will truncate the string to 15 characters

//		if($debug)
//		{
//			print "Reporting value back winningsource ..:".$winning_source."<br>\n";
//			print "Reporting value back caller_id ......:".$cid."<br>\n";
//			print "Reporting value back spam ...........:".$spam."<br>\n";			
//			print "Reporting value back toggle .........:".$run_param['Report_Back']."<br>\n";			
//		}

	if((($winning_source != 'Who_Called') && ($first_caller_id != '') && ($spam == '1') && ($run_param['Report_Back'] == 'on')))
	{
	$reportbacknow = true;
	}	
	else
	{
	$reportbacknow = false;
	}	

	if ($reportbacknow) 
	{
	$url = "http://whocalled.us/do?action=report&name=".$run_param['Username']."&pass=".$run_param['Password']."&phoneNumber=$thenumber&date=".date('Y-m-d')."&callerID=".urlencode(substr($cid,0,15));
		$value = get_url_contents($url);
		if($debug)
		{
			$st_success = strstr($value, "success");
			$st_error = strstr($value, "errorMsg");
			$success = substr($st_success,8,1);
			$error = substr($st_error,9);
			if($success=='1')
			{
				print "Success. Posted SPAM information back to Who Called.<br>\n<br>\n";
			}
			else
			{
				print "Failed! Cant post back to Who Called. Reason: ".$error.".<br>\n<br>\n";
			}
		}
	}
**** end of report back *****/	
	
}
?> 