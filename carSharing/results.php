<?php
    // Include the library
    include('simple_html_dom.php');
    $origin = $_POST['from'];
    $destination = $_POST['to'];
	$startDate = $_POST['date'];
	
	
	
	//error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	//a = month
	//b = day
	//c = year
	$startDate = array( 'a' => substr($startDate,0,2),
						 'b' => substr($startDate,3,2),
						 'c' => substr($startDate,6,4)
						 );
	
	//extract city info
	$locationcontents = explode(" ", $origin);
	
	//city has two cases.  If the size of the exploded array is greater than 2,
	//it means that the city has more than one word, so handle that accordingly..
	$originCity = "";
	$originState = "";
	
	if ( count($locationcontents) > 2)
	{
		$originCity = $locationcontents[0] . "+" . substr($locationcontents[1], 0, (strlen($locationcontents[1]) - 1));
		$originState = $locationcontents[2];
	}
	else
	{
		$originCity = $locationcontents[0];
		$originState = $locationcontents[1];
	}
	
	
	$destinationContents = explode(" ", $destination);
	
	$destinationCity = "";
	$destinationState = "";
	
	if ( count($locationcontents) > 2)
	{
		$destinationCity = $destinationContents[0] . "+" . substr($destinationContents[1], 0, (strlen($destinationContents[1]) - 1));
		$destinationState = $destinationContents[2];
	}
	else
	{
		$destinationCity = $destinationContents[0];
		$destinationState = $destinationContents[1];
	}
	
	//how to convert name of month to number of month
	
	//get latitudes/longitudes via google geocode
	$originurl = "http://maps.googleapis.com/maps/api/geocode/json?address=$originCity,+$originState&sensor=true";
	$desturl="http://maps.googleapis.com/maps/api/geocode/json?address=$destinationCity,+$destinationState&sensor=true";
			
	$originjson = file_get_contents($originurl);
	$destinationjson = file_get_contents($desturl);
	
	$originj = json_decode($originjson, true);
	$destj = json_decode($destinationjson, true);
	
	$originLat = $originj['results'][0]['geometry']['location']['lat'];
	$originLon = $originj['results'][0]['geometry']['location']['lng'];
	$destinationLat = $destj['results'][0]['geometry']['location']['lat'];
	$destinationLon = $destj['results'][0]['geometry']['location']['lng'];
	$type="";
	if(isset($_POST['type'])){
		if($_POST['type']=="passenger"){
			$type="ride_request";
		}elseif ($_POST['type']=="driver"){
			$type="ride_offer";
		}
	}
	
    //query site
    $siteQuery = "http://ridejoy.com/rides/search?utf8=%E2%9C%93&type=$type&origin=$originCity%2C+$originState%2C+USA&origin_latitude=$originLat&origin_longitude=$originLon&destination=$destinationCity%2C+$destinationState%2C+USA&destination_latitude=$destinationLat&destination_longitude=$destinationLon&date=";

    // Retrieve the DOM from a given URL
    
    
    $ridejoy = file_get_html($siteQuery);
    
    $joy = $ridejoy->find('div.rides_search_container div.date');
    //foreach ($ridejoy->find('div.rides_search_container div.date') as $f) {
    $jindex = 0;
    $jindex2 = 0;
    
    
    
    	
    //$usertype = $_POST['type'];
    $from = explode(", ", $_POST['from']);
    $to = explode(", ", $_POST['to']);
    
    $fromCity = str_replace(' ', '+', $from[0]);
    $fromState = $from[1];
    $toCity = str_replace(' ', '+', $to[0]);
    $toState = $to[1];
    //no need to check, zimride defaults blank dates to current date
    //$date = $_POST['date'];
    $url_date = str_replace('/', '%2F', empty($_POST['date'])?$_POST['date']:"");
    
    // Retrieve the DOM from a given URL
    $page_url = "http://www.zimride.com/search?s=$fromCity%2C+$fromState&e=$toCity%2C+$toState&date=$url_date&s_name=$toCity%2C+$toState&s_full_text=$fromCity%2C+$fromState%2C+USA&s_error_code=&s_address=$fromCity%2C+$fromState%2C+USA&s_city=$fromCity&s_state=$fromState&s_zip=&s_country=US&s_lat=$originLat&s_lng=$originLon&s_location_key=&s_user_lat=&s_user_lng=&s_user_country=&e_name=$toCity%2C+$toState&e_full_text=$toCity%2C+$toState%2C+USA&e_error_code=&e_address=$toCity%2C+$toState%2C+USA&e_city=$toCity&e_state=$toState&e_zip=&e_country=US&e_lat=$destinationLat&e_lng=$destinationLon&e_location_key=&e_user_lat=&e_user_lng=&e_user_country=";
    $zimride = file_get_html($page_url);
    //echo $page_url;
    //echo $folder;
    
    
    
    

    
    
    
    
    // Find all TD tags with "align=center"
    $i=0;
    $cur=0;
    $zim = $zimride->find('div.ride_list a') ;
    //foreach ($zimride->find('div.ride_list a') as $e) {
    $zindex=0;
    
    
    $j=0;
    
    
    
    
    
    while(!empty($joy[$jindex]) || !empty($zim[$zindex])){
    	
    
    if(!empty($joy[$jindex])){
        //$perDay = $joy[$jindex]->find('div.rides_search_result_container'); //$joy[$jindex]
    	$name = "";//$f->find('div.username',0)->plaintext;
    	$link = $joy[$jindex]->childNodes(1)->childNodes($j)->find('a', 0)->href;
    	$image = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.photo img',0)->src;
    	$tmp = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.seat_count',0);
    	$price = $tmp?$tmp->plaintext:"free";
    	//$main5 = $f->find('div.inner_content span.inner',0)->innertext;
    	$originCity = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.origin',0)->plaintext;
    	$destinationCity = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.destination',0)->plaintext;
    	$type = $tmp?"driver":"passenger";
    
    	$departure = $joy[$jindex]->find('div.date_header',0)->plaintext;
    	//date has to be fixed, the algorithm uses $i to compare childNodes
    	//but it has not been implemented yet.
    
    	$arr[] = array(    'link' => $link,
    			'price' => $price,
    			'image' => $image,
    			'name' => $name,
    			'originCity' => $originCity,
    			'destinationCity' => $destinationCity,
    			'driver' => $type,
    			'date' => trim($departure)
    	);
    	$j++;
    	if(!$joy[$jindex]->childNodes(1)->childNodes($j)){
    	  $jindex++;
    	  $j=0;
    	}
    	//echo "\n\t".$main5."\n";
    }
    
    
    if(!empty($zim[$zindex])){
        $name = $zim[$zindex]->find('div.username',0);
        //ignore last two links in page that are not people
        if ($name) {
        	$name = $name->plaintext;
            $link = $zim[$zindex]->getAttribute('href');
            $image = $zim[$zindex]->find('img',0)->src;
            $price = $zim[$zindex]->find('div[class=price_box]',0)?$zim[$zindex]->find('div[class=price_box]',0)->childNodes(1)->childNodes(0)->plaintext:"free";
            $main5 = $zim[$zindex]->find('div.inner_content span.inner',0)->innertext;
            $city1 = explode("<span", $main5);
            $city2 = explode("</span>", $main5);
            $type = $zim[$zindex]->find('div.userpic span.driver',0)?"driver":"passenger";

            $departure = $zimride->find('div.ride_list',0)->childNodes($cur)->find('span',0)->plaintext;
            //date has to be fixed, the algorithm uses $i to compare childNodes
            //but it has not been implemented yet. 

            $arr[] = array(    'link' => $link,
                               'price' => $price,
                               'image' => $image,
                               'name' => $name,
                               'originCity' => $city1[0],
                               'destinationCity' => $city2[1],
                               'driver' => $type,
                               'date' => $departure
                         );
            //echo "\n\t".$main5."\n";
            $i = $i+1;
        	if($zimride->find('div.ride_list',0)->childNodes($i)->find('em',0))
        		$cur = $i;
        }
        $zindex++;
    }
    
    
    
    
    }
    
    
    //print_r($arr);
    $response = $arr;
    
    $fp = fopen('results.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);
    $ridejoy->clear();

    $zimride->clear();
    $homepage = file_get_contents('./results.html', false);
	echo $homepage;       
				       
?>
