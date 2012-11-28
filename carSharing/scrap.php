<?php
    // Include the library
    include('simple_html_dom.php');
    
    $usertype = $_POST['type'];
    $from = explode(", ", $_POST['from']);
    $to = explode(", ", $_POST['to']);
    
    $fromCity = str_replace(' ', '+', $from[0]);
    $fromState = $from[1];
    $toCity = str_replace(' ', '+', $to[0]);
    $toState = $to[1];
    //no need to check, zimride defaults blank dates to current date
    $date = $_POST['date'];
    $url_date = str_replace('/', '%2F', $_POST['date']);
    
    // Retrieve the DOM from a given URL
    $page_url = "http://www.zimride.com/search?s=$fromCity%2C+$fromState&e=$toCity%2C+$toState&date=$url_date&s_name=$toCity%2C+$toState&s_full_text=$fromCity%2C+$fromState%2C+USA&s_error_code=&s_address=$fromCity%2C+$fromState%2C+USA&s_city=$fromCity&s_state=$fromState&s_zip=&s_country=US&s_lat=37.7749295&s_lng=-122.41941550000001&s_location_key=&s_user_lat=&s_user_lng=&s_user_country=&e_name=$toCity%2C+$toState&e_full_text=$toCity%2C+$toState%2C+USA&e_error_code=&e_address=$toCity%2C+$toState%2C+USA&e_city=$toCity&e_state=$toState&e_zip=&e_country=US&e_lat=34.0522342&e_lng=-118.2436849&e_location_key=&e_user_lat=&e_user_lng=&e_user_country=";
    $folder = file_get_html($page_url);
    //echo $page_url;
    //echo $folder;
    
    
    // Find all TD tags with "align=center"
    $i=0;
    $cur=0;
    //$e = $folder->find('div.class=ride_list a') ;
    //print_r($e);
    foreach ($folder->find('div.ride_list a') as $e){
        $name = $e->find('div.username',0)->plaintext;
        //ignore last two links in page that are not people
        if (isset($name)) {
            $link = $e->getAttribute('href');
            $image = $e->find('img',0)->src;
            $price = $e->find('div[class=price_box]',0)?$e->find('div[class=price_box]',0)->childNodes(1)->childNodes(0)->plaintext:"free";
            $main5 = $e->find('div.inner_content span.inner',0)->innertext;
            $city1 = explode("<span", $main5);
            $city2 = explode("</span>", $main5);
            $type = $e->find('div.userpic span.driver',0)?"driver":"passenger";

            $departure = $folder->find('div.ride_list',0)->childNodes($cur)->find('span',0)->plaintext;
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
        	if($folder->find('div.ride_list',0)->childNodes($i)->find('em',0))
        		$cur = $i;
        }
    }
    //print_r($arr);
    $response = $arr;
    
    $fp = fopen('results.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);

    $folder->clear();
    $homepage = file_get_contents('./output.html', false);
	echo $homepage;       
				       
?>
