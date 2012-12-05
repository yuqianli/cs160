<?php 
    // Include the library
    include('simple_html_dom.php');
    
    //scrape ridejoy
    $joy_url = "http://http://www.ridejoy.com/";
    $joy = file_get_html($zim_url);
    $ridejoy = $joy->find('div.ridesets', 0);
    $i=0;
   /* while (!empty($ridejoy->childNodes($i))) {
    	$image = $ridejoy->childNodes($i)->find('div.photo img',0)->src;
    	//$main5 = $ridejoy->childNodes($i)->find('a',0)->childNodes(2)->innertext;
    	//$main5 = str_replace('"', ' ', $main5);
    	$city1 = $ridejoy->childNodes($i)->find('div.origin',0)->plaintext;
    	$city2 = $ridejoy->childNodes($i)->find('div.destination',0)->plaintext;
    	$link = $ridejoy->childNodes($i)->childNodes(0)->childNodes(0)->childNodes(0)->data-href;
        $name = "";
        $price = $ridejoy->childNodes($i)->find('div.price_box h1',0)->innertext;
        $type = $ridejoy->childNodes($i)->find('a span.driver',0)?"driver":"passenger";
        $date = $ridejoy->childNodes($i)->find('div.day',0)? ($ridejoy->childNodes($i)->find('div.day',0)->plaintext . $ridejoy->childNodes($i)->find('div.month',0)->plaintext) : ($ridejoy->childNodes($i)->find('div.date',0)->childNodes(0)->plaintext . " - " . $ridejoy->childNodes($i)->find('div.date',0)->childNodes(1)->plaintext);
    	
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
    	$i++;
        //print_r($arr);
    }
    */
    //scrape zimride
    $zim_url = "http://www.zimride.com";
    $zim = file_get_html($zim_url);
    foreach ($zim->find('div.rides ul li') as $e) {
    	$image = $e->find('img',0)->src;
    	$main5 = $e->find('a',0)->childNodes(2)->innertext;
    	$main5 = str_replace('"', ' ', $main5);
    	$city1 = explode("<span", $main5);
    	$city2 = explode("</span>", $main5);
    	$link = 'http://zimride.com/' . $e->find('a',0)->href;
        $name = $e->find('a',0)->childNodes(3)->plaintext;
        $price = $e->find('div.price_box h1',0)->innertext;
        $type = $e->find('a span.driver',0)?"driver":"passenger";
    	
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
        //print_r($arr);
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
