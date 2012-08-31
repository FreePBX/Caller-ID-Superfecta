<?php
//this file is designed to be used as an include that is part of a loop.
//If a valid match is found, it will give $caller_id a value
//available variables for use are: $thenumber
//retreive website contents using get_url_contents($url);

/* BUG FIXES */
// v0.9.5  Fixed PHP/MYSQL closing problem 3/30/2009 jpeterman
// v0.9.3: Fixed contacts query to also lookup phone_home entry
// v0.9.2: Initial Release Version
// 27 October 2011 added MySQL query for searching leads


//configuration / display parameters
//The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
$source_desc = "Look up data in your SugarCRM DB, local or remote.<br>Fill in the appropriate SugarCRM iconfiguration information to make the connection to the SugarCRM database.";
$source_param = array();
$source_param['DB_Host']['desc'] = 'Host address of the SugarCRM database. (localhost if the database is on the same server as FreePBX)';
$source_param['DB_Host']['type'] = 'text';
$source_param['DB_Name']['desc'] = 'schema name of the SugarCRM database';
$source_param['DB_Name']['type'] = 'text';
$source_param['DB_User']['desc'] = 'Username used to connect to the SugarCRM database';
$source_param['DB_User']['type'] = 'text';
$source_param['DB_Password']['desc'] = 'Password used to connect to the SugarCRM database';
$source_param['DB_Password']['type'] = 'password';

$source_param['Search_Accounts']['desc'] = 'Include Accounts records in search';
$source_param['Search_Accounts']['type'] = 'checkbox';
$source_param['Search_Accounts']['default'] = "on";
$source_param['Search_Users']['desc'] = 'Include Users records in search';
$source_param['Search_Users']['type'] = 'checkbox';
$source_param['Search_Users']['default'] = "on";
$source_param['Search_Contacts']['desc'] = 'Include Contacts records in search';
$source_param['Search_Contacts']['type'] = 'checkbox';
$source_param['Search_Contacts']['default'] = "on";
$source_param['Search_Leads']['desc'] = 'Include Leads records in search';
$source_param['Search_Leads']['type'] = 'checkbox';
$source_param['Search_Leads']['default'] = "on";

$source_param['Filter_Length']['desc']='The number of rightmost digits to check for a match';
$source_param['Filter_Length']['type']='number';
$source_param['Filter_Length']['default']= 9;

//run this if the script is running in the "get caller id" usage mode.
if($usage_mode == 'get caller id')
{
	if($debug)
	{
		print "Searching SugarCRM ... ";
	}
	
	$wquery_input = "";
	$wquery_string = "";
	$wquery_result = "";
	$wresult_caller_name = "";
		
	if (strlen($wquery_input) > $run_param['Filter_Length']) $wquery_input = substr($wquery_input, -$run_param['Filter_Length']); // keep only the filter_length rightmost digits
	$wdb_handle = mysql_connect($run_param['DB_Host'], $run_param['DB_User'], $run_param['DB_Password']) or die("SugarCRM connection failed" . mysql_error());
	mysql_select_db($run_param['DB_Name']) or die("SugarCRM db open error: " . mysql_error());
	mysql_query("SET NAMES 'utf8'") or die("UTF8 set query  failed: " . mysql_error());
	
	// search accounts
        if ($run_param['Search_Accounts'] == "on")
	{
		$wquery_string = "SELECT * FROM accounts WHERE deleted = '0' AND (RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(accounts.phone_office,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(accounts.phone_alternate,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(accounts.phone_fax,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "') LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM accounts query failed" . mysql_error());
		if(mysql_num_rows($wquery_result) > 0)
		{
			$wquery_row = mysql_fetch_array($wquery_result);
			$wresult_caller_name = $wquery_row["name"];
		}
	}
	
	// search also users, if no result from accounts
	if($run_param['Search_Users'] == "on" && strlen($wresult_caller_name) == '')
	{
		$wquery_string = "SELECT * FROM users WHERE deleted = '0' AND (RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(users.phone_work,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(users.phone_mobile,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '". $wquery_input ."'  OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(users.phone_home,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '". $wquery_input ."' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(users.phone_other,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(users.phone_fax,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "') LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM users query failed" . mysql_error());
		if(mysql_num_rows($wquery_result)>0)
		{
			$wquery_row = mysql_fetch_array($wquery_result);
			$wresult_caller_name = $wquery_row["last_name"] . ' ' . $wquery_row["first_name"];
		}
	} 
	
	// search also contacts, if no results from previous searches
	if($run_param['Search_Contacts'] == "on" && strlen($wresult_caller_name) == '')
	{
		$wquery_string = "SELECT * FROM contacts WHERE deleted = '0' AND (RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contacts.phone_work,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contacts.phone_mobile,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '". $wquery_input ."'  OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contacts.phone_home,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contacts.phone_other,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contacts.phone_fax,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "') LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM contacts query failed" . mysql_error());
		if(mysql_num_rows($wquery_result)>0)
		{
			$wquery_row = mysql_fetch_array($wquery_result);
			$wresult_caller_name = $wquery_row["last_name"] . ' ' . $wquery_row["first_name"];
		}
	}

	// search also leads if no results from previous searches
	if($run_param['Search_Leads'] == "on" && strlen($wresult_caller_name) == '')
	{
		$wquery_string = "SELECT * FROM leads WHERE deleted = '0' AND (RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(leads.phone_work,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(leads.phone_mobile,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '". $wquery_input ."'  OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(leads.phone_home,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(leads.phone_other,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "' OR RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(leads.phone_fax,' ',''),'+',''),'-',''),'(',''),')','')," . $run_param['Filter_Length'] . ") LIKE '" . $wquery_input . "') LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM contacts query failed" . mysql_error());
		if(mysql_num_rows($wquery_result)>0)
		{
			$wquery_row = mysql_fetch_array($wquery_result);
			$wresult_caller_name = $wquery_row["last_name"] . ' ' . $wquery_row["first_name"];
		}
	}


	mysql_close($wdb_handle);
	
	if(strlen($wresult_caller_name) > 0)
	{
		$caller_id = $wresult_caller_name;
	}
	else if($debug)
	{
		print "not found<br>\n";
	}
}
