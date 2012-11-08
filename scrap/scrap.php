<?php
	
	/*
	Web scraper adapted for wheelz.com
	
	TODO: -Expand return results into make/model rather than just name
		  -include support for more sites, currently only works for wheelz.com
		  -write to database
	
	*/
    // Include the library
    include('simple_html_dom.php');
    $place = $_POST['place'];
	$startdate = $_POST['startdate'];
	$starttime = $_POST['starttime'];
	$enddate = $_POST['enddate'];
	$endtime = $_POST['endtime'];
	
	//error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	//a = year
	//b = month
	//c = day
	$startdate = array( 'a' => substr($startdate,0,4),
						 'b' => substr($startdate,5,2),
						 'c' => substr($startdate,8,2)
						 );
						 
	
	$enddate = array( 'a' => substr($enddate,0,4),
						 'b' => substr($enddate,5,2),
						 'c' => substr($enddate,8,2)
						 );
	
	//extract city info
	$locationcontents = explode(" ", $place);
	
	$city = $locationcontents[0] . "+" . substr($locationcontents[1], 0, (strlen($locationcontents[1]) - 1));
	$state = $locationcontents[2];
	

	//extract start/end hour and time
	$startHour = intval(substr($starttime, 0, 2));
	$startMinute = substr($starttime, 3, 2);
	
	
	$endHour = intval(substr($endtime, 0, 2));
	$endMinute = substr($endtime, 3, 2);
	
	//perform necessary hour conversion
	//from wheelz.com: HOUR = 8 + Hour_in_military_hours, then bounded to [0,24]
	
	$startHour = $startHour + 8;
	$endHour = $endHour + 8;
	
	//perform overflow correction if necessary.
	if ($startHour >= 24)
	{ $startHour = $startHour - 24; }
	
	if ($endHour >= 24)
	{ $endHour = $endHour - 24; }
	
	//format hours properly
	if ($endHour < 10)
	{ $endHour = '0' . $endHour; }
	
	if ($startHour < 10)
	{ $startHour = '0' . $startHour; }
	
	
						 
    $myFile = "File.json";
    
    //query site
    $siteQuery = "http://www.wheelz.com/vehicles/search?vehicle_search[address]=$city%2C+$state#lastSearch[address]=&lastSearch[start_time]=$startdate[a]-$startdate[b]-$startdate[c]T$startHour%3A$startMinute%3A00.000Z&lastSearch[end_time]=$enddate[a]-$enddate[b]-$enddate[c]T$endHour%3A$endMinute%3A00.000Z&lastSearch[anytime]=&lastSearch[zoom]=12&lastSearch[radius]=";
    
    // Retrieve the DOM from a given URL
    
    $folder = file_get_html($siteQuery);
    
    //set variables necessary during parse
    
    $image = "";
    $vehicleName = "";
    $vehicleYear = "";
    $hourlyRate = "";
    $link = "";
    
    
    //horrible, kludgey way of parsing, but... it works .. for now ;)
    foreach ($folder->find('div[class=vehicle]') as $test)
    {
    	//find image location
    	foreach ($test->find('div[class=featurePhoto]') as $photo)
    	{
    		foreach ($photo->find('img') as $ph)
    		{
    			$image = $ph->getAttribute('src');
    		}
    	}
    	
    	//find vehicle name
    	foreach ($test->find('div[class=details]') as $deets)
    	{
    		foreach ($deets->find('h3') as $header)
    		{
    			$vehicleName = $header->text();
    		}
    		
    		//find year
    		foreach ($deets->find('span[class=year]') as $year)
    		{
    			$vehicleYear = $year->text();
    		}
    		
    		//find hourly rate
    		foreach ($deets->find('div[class=rates]') as $rates)
    		{
    			foreach ($rates->find('span[class=price]') as $price)
    			{
    				$hourlyRate = $price->text();
    			}
    		}
    		
    		//find link
    		foreach ($deets->find('a') as $linky)
    		{
    			$link = $linky->getAttribute('href');
    			
    			$link = "www.wheelz.com" . $link;
    		}
    		
    	}
    	
    	//now save all of this into an array...
    	$arr[] = array( 'url' => $link,
    					'image' => $image,
    					'vehicleName' => $vehicleName,
    					'vehicleYear' => $vehicleYear,
    					'hourlyRate' => $hourlyRate
    				   );
    }
    
    //write json file
    $response = $arr;
    
    $fp = fopen('results.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);
    
    include('output.html');

        
    $folder->clear();				       
?>