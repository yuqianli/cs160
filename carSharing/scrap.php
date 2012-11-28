<?php
    // Include the library
    include('simple_html_dom.php');
    
    $from = explode(", ", $_POST['from']);
    $to = explode(", ", $_POST['to']);
    //$date = ;
    
    $fromCity = str_replace(' ', '+', $from[0]);
    $fromState = $from[1];
    $toCity = str_replace(' ', '+', $to[0]);
    $toState = $to[1];
    $date = (isset($_POST['date']) && trim($_POST['date'])!='' ) ? substr($_POST['date'],0,2) . "%2F" . substr($_POST['date'],3,2) . "%2F20" . substr($_POST['date'],6,2):'';
    $myFile = "File.json";
    
    // Retrieve the DOM from a given URL
    $folder = file_get_html("http://www.zimride.com/search?s=$fromCity%2C+$fromState&e=$toCity%2C+$toState&date=$date&s_name=$toCity%2C+$toState&s_full_text=$fromCity%2C+$fromState%2C+USA&s_error_code=&s_address=$fromCity%2C+$fromState%2C+USA&s_city=$fromCity&s_state=$fromState&s_zip=&s_country=US&s_lat=37.7749295&s_lng=-122.41941550000001&s_location_key=&s_user_lat=&s_user_lng=&s_user_country=&e_name=$toCity%2C+$toState&e_full_text=$toCity%2C+$toState%2C+USA&e_error_code=&e_address=$toCity%2C+$toState%2C+USA&e_city=$toCity&e_state=$toState&e_zip=&e_country=US&e_lat=34.0522342&e_lng=-118.2436849&e_location_key=&e_user_lat=&e_user_lng=&e_user_country=");
    //echo "http://www.zimride.com/search?s=$fromCity%2C+$fromState&e=$toCity%2C+$toState&date=$date&s_name=$toCity%2C+$toState&s_full_text=$fromCity%2C+$fromState%2C+USA&s_error_code=&s_address=$fromCity%2C+$fromState%2C+USA&s_city=$fromCity&s_state=$fromState&s_zip=&s_country=US&s_lat=37.7749295&s_lng=-122.41941550000001&s_location_key=&s_user_lat=&s_user_lng=&s_user_country=&e_name=$toCity%2C+$toState&e_full_text=$toCity%2C+$toState%2C+USA&e_error_code=&e_address=$toCity%2C+$toState%2C+USA&e_city=$toCity&e_state=$toState&e_zip=&e_country=US&e_lat=34.0522342&e_lng=-118.2436849&e_location_key=&e_user_lat=&e_user_lng=&e_user_country=";
    //echo $folder;
    
    
    // Find all TD tags with "align=center"
    $i=0;
    //$e = $folder->find('div.class=ride_list a') ;
    //print_r($e);
    foreach ($folder->find('div.ride_list a') as $e){
        $main1=$e->find('div[class=username]',0)->plaintext;
        $main2=$e->getAttribute('href');
        $main3=$e->find('img',0)->src;
        $main4=$e->find('div[class=price_box]',0)?$e->find('div[class=price_box]',0)->childNodes(1)->childNodes(0)->plaintext:"free";
        $main5=$e->find('div.inner_content span.inner',0)->plaintext;
		$main6=$e->find('div.userpic span.driver',0)?"driver":"passenger";
		$main7=$e->find('div.userpic span.driver',0)?"driver":"passenger";
        //city will split in originCity and destinationCity
        //date has to be fixed, the algorithm uses $i to compare childNodes
        //but it has not been implemented yet. 
        $arr[] = array(    'link' => $main2,
                           'price' => $main4,
                           'image' => $main3,
                           'name' => $main1,
                           'city' => $main5,
						   'driver' => $main6,
						   'date' => $_POST['date']
                     );
       // echo "\n\t".$main5."\n";
      // $i = $i+1;
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