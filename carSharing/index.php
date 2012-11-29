<?php 
    // Include the library
    include('simple_html_dom.php');
    
    //scrape zimride
    $zim_url = "http://www.zimride.com";
    $folder = file_get_html($zim_url);
    foreach ($folder->find('div.rides ul li') as $e) {
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
    $folder->clear();

    //write to page
    $homepage = file_get_contents('./home.html', false);
	echo $homepage;  
?>
