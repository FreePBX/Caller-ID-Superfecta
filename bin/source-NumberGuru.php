<?php
//this file is designed to be used as an include that is part of a loop.
//If a valid match is found, it should give $caller_id a value
//available variables for use are: $thenumber
//retreive website contents using get_url_contents($url);

//configuration / display parameters
//The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
$source_desc = "http://www.numberguru.com - 	US free CNAM lookup.<br><br>This data source requires Superfecta Module version 2.2.4 or higher.";
$source_param['Get_Caller_ID_Name']['desc'] = 'Use NumberGuru for caller id name lookup.';
$source_param['Get_Caller_ID_Name']['type'] = 'checkbox';
$source_param['Get_Caller_ID_Name']['default'] = 'on';
$source_param['Get_SPAM_Score']['desc'] = 'Use NumberGuru for spam scoring.';
$source_param['Get_SPAM_Score']['type'] = 'checkbox';
$source_param['Get_SPAM_Score']['default'] = 'on';
$source_param['Spam_Threshold']['desc'] = 'How sensitive of a spam score to use 0-100';
$source_param['Spam_Threshold']['type'] = 'number';
$source_param['Spam_Threshold']['default'] = '60';

//run this if the script is running in the "get caller id" usage mode.
//and only if either run mode is required
if(($usage_mode == 'get caller id') && (($run_param['Get_SPAM_Score'] == 'on') || ($run_param['Get_Caller_ID_Name'] == 'on')))
{
	$TFnpa = false;
	$validnpaUS = false;
	$validnpaCAN = false;
	$number_error = false;

	if($debug)
	{
		print "Searching NumberGuru ... ";
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
	
	if(!$number_error)
	{
		$thenumber = (substr($thenumber,0,1) == 1) ? substr($thenumber,1) : $thenumber;
		$npa = substr($thenumber,0,3);
		
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
	}
	
	if(!$validnpaUS && !$validnpaCAN)
	{
		$number_error = true;
	}
	
	if($number_error)
	{
		if($debug)
		{
			print "Skipping Source - Non US/CAN number: ".$thenumber."<br>\n";
		}
	}
	else
	{
		// www.numberguru.com needs a cookie
		$temp_cookie_file = tempnam(function_exists('sys_get_temp_dir') ? realpath(sys_get_temp_dir()) : NULL, "CURLCOOKIE");
		// Get the cookie set
		$value = get_url_contents("http://www.numberguru.com",false,"http://www.numberguru.com",$temp_cookie_file);
		// Search NumberGuru USA
		$url = "http://www.numberguru.com/s/{$thenumber}";
		// Perform lookup using cookie
		$value = get_url_contents($url,false,"http://www.numberguru.com",$temp_cookie_file);
		// Delete the temporary cookie
		@unlink($temp_cookie_file);

        // Check for Capture Code
        if(strstr($value, "Please verify your humanity, and forgive the inconvenience"))
        {
			die("NumberGuru has blocked searches from this IP.  Please visit the site to solve the captcha first.<br>");
        }
        
		// Check for CNAM lookup
		if(isset($run_param['Get_Caller_ID_Name']) && $run_param['Get_Caller_ID_Name'] == 'on')
		{
			// By default, the found name is empty
			$name = "";
		
			// Grab the first result that matches our phone number
			$pattern = "/<div class=\"callout_details_left caller_id\">Owner's Name:<\/div>\s*<div class=\"callout_details_right caller_id\">(.*)<\/div>/";
			preg_match($pattern, $value, $match);
			if(isset($match[1]) && strlen($match[1])){
				$name = trim(strip_tags($match[1]));
			}
			
			// If we found a match, return it
			if(strlen($name) > 1 && $name<>"UNAVAILABLE")
			{
				$caller_id = $name;
			}
			else if($debug)
			{
				print "not found<br>\n";
			}
		}
		
		// Check for SPAM score
		if(isset($run_param['Get_SPAM_Score']) && $run_param['Get_SPAM_Score'] == 'on')
		{
			$score=0;
			$pattern = "/<div class=\"stat_number\">(.*)%<\/div>\s*<div class=\"stat_title\">Spammer Score<\/div>/";
			
			preg_match($pattern, $value, $match);
			if(isset($match[1]) && strlen($match[1]))
			{
				$score = $match[1];
				if(isset($run_param['Spam_Threshold']) && $score>=$run_param['Spam_Threshold'])
				{
					$spam = true;	// Flag as SPAM
					if($debug)
					{
						print "SPAM caller<br>\n";
					}
				}
			}
		}
		
	}
}
?>
