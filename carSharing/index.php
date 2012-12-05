<?php 
    // Include the library
    include('simple_html_dom.php');
    
    
    
    
    
    //scrape ridejoy
    $joy_url = "http://www.ridejoy.com/";
    $joy = file_get_html($joy_url);
    //echo $joy;
    $ridejoy = $joy->find('div.rides_search_container div.ridesets', 0);
    //echo $ridejoy;
    $rindex=0;
    
    //scrape zimride
    $zim_url = "http://www.zimride.com";
    $zim = file_get_html($zim_url);
    $zimride = $zim->find('div.rides ul li');
    $zindex = 0;
    
    while($ridejoy->childNodes($rindex) || !empty($zimride[$zindex])){
    
    //while ( $ridejoy->childNodes($i) ) {
    if($ridejoy->childNodes($rindex)){
    	//echo $ridejoy->childNodes($i);
    	$image = $ridejoy->childNodes($rindex)->find('div.photo img',0)->src;
    	//$main5 = $ridejoy->childNodes($i)->find('a',0)->childNodes(2)->innertext;
    	//$main5 = str_replace('"', ' ', $main5);
    	$city1 = $ridejoy->childNodes($rindex)->find('div.origin',0)->plaintext;
    	$city2 = $ridejoy->childNodes($rindex)->find('div.destination',0)->plaintext;
    	$link = $ridejoy->childNodes($rindex)->childNodes(0)->childNodes(0)->childNodes(0)->getAttribute('data-href');
    	$name = "";
    	$price = $ridejoy->childNodes($rindex)->find('div.seat_count',0)?$ridejoy->childNodes($rindex)->find('div.seat_count',0)->plaintext:"free";
    	$type = $ridejoy->childNodes($rindex)->find('a span.driver',0)?"driver":"passenger";
    	$date = $ridejoy->childNodes($rindex)->find('div.day',0)? ($ridejoy->childNodes($rindex)->find('div.day',0)->plaintext . $ridejoy->childNodes($rindex)->find('div.month',0)->plaintext) : ($ridejoy->childNodes($rindex)->find('div.date',0)->childNodes(0)->plaintext . " - " . $ridejoy->childNodes($rindex)->find('div.date',0)->childNodes(1)->plaintext);
    	 
    	$arr[] = array(
    			'link' => $link,
    			'price' => $price,
    			'image' => $image,
    			'name' => $name,
    			'originCity' => $city1,
    			'destinationCity' => $city2,
    			'driver' => $type,
    			'date' => $date
    	);
    	$rindex++;
    	//print_r($arr);
    }
    //foreach ($zim->find('div.rides ul li') as $e) {
    if(!empty($zimride[$zindex])){
    	$image = $zimride[$zindex]->find('img',0)->src;
    	$main5 = $zimride[$zindex]->find('a',0)->childNodes(2)->innertext;
    	$main5 = str_replace('"', ' ', $main5);
    	$city1 = explode("<span", $main5);
    	$city2 = explode("</span>", $main5);
    	$link = 'http://zimride.com/' . $zimride[$zindex]->find('a',0)->href;
        $name = $zimride[$zindex]->find('a',0)->childNodes(3)->plaintext;
        $price = $zimride[$zindex]->find('div.price_box h1',0)->innertext;
        $type = $zimride[$zindex]->find('a span.driver',0)?"driver":"passenger";
    	
    	$arr[] = array(    
    			'link' => $link,
    			'price' => $price,
    			'image' => $image,
    			'name' => $name,
    			'originCity' => trim($city1[0]),
    			'destinationCity' => trim($city2[1]),
    			'driver' => $type,
    			'date' => "today"
    	);
    	$zindex++;
        //print_r($arr);
    }
    }

    //scrape ridejoy
    //todo

    //write to json
    $response = $arr;
    $fp = fopen('home.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);
    $zim->clear();
    $joy->clear();

    //write to page
    $homepage = file_get_contents('./home.html', false);
	echo $homepage;  
?>
