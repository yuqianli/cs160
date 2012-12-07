<?php
    // Include the library
    include('simple_html_dom.php');
	//error_reporting(E_ERROR | E_WARNING | E_PARSE); //error reporting strictness

    //POST search terms
    $origin = $_POST['from'];
    $destination = $_POST['to'];
	$startDate = $_POST['date'];
	
    // a/b/c = month/day/year
	$startDate = array(
        'a' => substr($startDate,0,2),
        'b' => substr($startDate,3,2),
        'c' => substr($startDate,6,4)
     );
	
    //rebuild origin location for url validity
	$locationcontents = explode(" ", $origin);
	$originCity = "";
	$originState = "";
    //if city name has a space in it
	if ( count($locationcontents) > 2) {
		$originCity = $locationcontents[0] . "+" . substr($locationcontents[1], 0, (strlen($locationcontents[1]) - 1));
		$originState = $locationcontents[2];
	} else {
		$originCity = $locationcontents[0];
		$originState = $locationcontents[1];
	}
	
    //rebuild destination location for url validity
	$destinationContents = explode(" ", $destination);
	$destinationCity = "";
	$destinationState = "";
    //if city name has a space in it
	if ( count($locationcontents) > 2) {
		$destinationCity = $destinationContents[0] . "+" . substr($destinationContents[1], 0, (strlen($destinationContents[1]) - 1));
		$destinationState = $destinationContents[2];
	} else {
		$destinationCity = $destinationContents[0];
		$destinationState = $destinationContents[1];
	}
	
	
    //get origin latitude/longitude via google geocode
	$originurl = "http://maps.googleapis.com/maps/api/geocode/json?address=$originCity,+$originState&sensor=true";
	$originjson = file_get_contents($originurl);
	$originj = json_decode($originjson, true);
	$originLat = $originj['results'][0]['geometry']['location']['lat'];
	$originLon = $originj['results'][0]['geometry']['location']['lng'];

    //get destination latitude/longitude via google geocode
	$desturl="http://maps.googleapis.com/maps/api/geocode/json?address=$destinationCity,+$destinationState&sensor=true";
	$destinationjson = file_get_contents($desturl);
	$destj = json_decode($destinationjson, true);
	$destinationLat = $destj['results'][0]['geometry']['location']['lat'];
	$destinationLon = $destj['results'][0]['geometry']['location']['lng'];
	
    
	$type="";
	if(isset($_POST['type'])){
		if($_POST['type']=="passenger"){
			$type="ride_offer";
		}elseif ($_POST['type']=="driver"){
			$type="ride_request";
		}
	}
    
	
    //retrieve DOM from ridejoy
    $ridejoy_url = "http://ridejoy.com/rides/search?utf8=%E2%9C%93&type=$type&origin=$originCity%2C+$originState%2C+USA&origin_latitude=$originLat&origin_longitude=$originLon&destination=$destinationCity%2C+$destinationState%2C+USA&destination_latitude=$destinationLat&destination_longitude=$destinationLon&date=";
    $ridejoy = file_get_html($ridejoy_url);
    $joy = $ridejoy->find('div.rides_search_container div.date');
    
    	
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
    $type="";
    if(isset($_POST['type'])){
    	if($_POST['type']=="passenger"){
    		$type="need";
    	}elseif ($_POST['type']=="driver"){
    		$type="offer";
    	}
    }
    /* FIX: should use the same POSTed values as ridejoy 
    $fromCity = str_replace(' ', '+', $origin[0]);
    $fromState = $origin[1];
    $toCity = str_replace(' ', '+', $destination[0]);
    $toState = $destination[1];
    $url_date = str_replace('/', '%2F', empty($_POST['date'])?$_POST['date']:"");
    end FIX */
    
    //retrieve DOM from zimride
    $zim_url = "http://www.zimride.com/search?s=$fromCity%2C+$fromState&e=$toCity%2C+$toState&date=$url_date&filter_type=$type&s_name=$toCity%2C+$toState&s_full_text=$fromCity%2C+$fromState%2C+USA&s_error_code=&s_address=$fromCity%2C+$fromState%2C+USA&s_city=$fromCity&s_state=$fromState&s_zip=&s_country=US&s_lat=$originLat&s_lng=$originLon&s_location_key=&s_user_lat=&s_user_lng=&s_user_country=&e_name=$toCity%2C+$toState&e_full_text=$toCity%2C+$toState%2C+USA&e_error_code=&e_address=$toCity%2C+$toState%2C+USA&e_city=$toCity&e_state=$toState&e_zip=&e_country=US&e_lat=$destinationLat&e_lng=$destinationLon&e_location_key=&e_user_lat=&e_user_lng=&e_user_country=";
    $zimride = file_get_html($zim_url);
    //find all TD tags with "align=center"
    $zim = $zimride->find('div.ride_list a') ;
    
    //scrape from ridejoy and zimride
    $jindex = 0;
    $zindex=0;
    //scrape from ridejoy
    $j=0;
    while (!empty($joy[$jindex]) || !empty($zim[$zindex])) {
        if(!empty($joy[$jindex])){
            $name = "";
            $link = $joy[$jindex]->childNodes(1)->childNodes($j)->find('a', 0)->href;
            $image = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.photo img',0)->src;
            $tmp = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.seat_count',0);
            $price = $tmp?$tmp->plaintext:"free";
            $originCity = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.origin',0)->plaintext;
            $destinationCity = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.destination',0)->plaintext;
            $type = $tmp?"driver":"passenger";
            $departure = $joy[$jindex]->find('div.date_header',0)->plaintext;
            //add commas
            $departure = str_replace('day ', 'day, ', $departure);

            //echo "#".trim($price)."#";
            $rides[] = array(
                'link' => $link,
                'price' => trim($price),
                'image' => $image,
                'name' => $name,
                'originCity' => trim($originCity),
                'destinationCity' => trim($destinationCity),
                'driver' => $type,
                'date' => $departure
            );
            $j++;
            if(!$joy[$jindex]->childNodes(1)->childNodes($j)) {
                $jindex++;
                $j=0;
            }
        }
        
        //scrape from zimride
        $cur=0;
        $i=0;
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
                //remove dash
                $departure = str_replace('— ', '', $departure);
//echo "#$price#";
                $rides[] = array(
                    'link' => $link,
                    'price' => $price,
                    'image' => $image,
                    'name' => $name,
                    'originCity' => trim($city1[0]),
                    'destinationCity' => trim($city2[1]),
                    'driver' => $type,
                    'date' => $departure
                );
                $i++;
                if ($zimride->find('div.ride_list',0)->childNodes($i)->find('em',0)) {
                    $cur = $i;
                }
            }
            $zindex++;
        }
    }

    session_start();
    $_SESSION['rides'] = $rides;
    
    //write scraped data to json file
   /* $response = $rides;
    $fp = fopen('results.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);*/

    //finish up
    $ridejoy->clear();
    $zimride->clear();
    //$homepage = file_get_contents('./results.html', false);
	//echo $homepage;    

	include('results.inc');
				       
?>
