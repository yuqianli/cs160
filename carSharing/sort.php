<?php 
    //Read the json results and parse the string into a data structure 
    $str_data = file_get_contents("results.json"); 
    $data = json_decode($str_data,true);   

    //replace 'free' and '$' in prices
    for ($i = 0; $i < count($data); $i++) {
        $data[$i]['price'] = str_replace('free', '$0', $data[$i]['price']);
        $data[$i]['price'] = str_replace('$', '', $data[$i]['price']);
    }

    //sorting function, pads with 0 on left so that 0200 > 021
    function cmp($a, $b)
    {
        $a["price"] = str_pad($a["price"], 10, '0', STR_PAD_LEFT);
        $b["price"] = str_pad($b["price"], 10, '0', STR_PAD_LEFT);
        return strcasecmp($a["price"], $b["price"]);
    }

    //sort data
    $arr = $data;
    usort($arr,"cmp");

    //put '$' back in price
    for ($i = 0; $i < count($arr); $i++) {
        $arr[$i]['price'] = '$' . $arr[$i]['price'];
    }
     
    //write to results.json
    $fh = fopen("results.json", 'w');
    fwrite($fh, json_encode($arr)); 
    
    // put back free in $0
    //$file_contents = file_get_contents("results.json");
   // $fh = fopen("results.json", "w"); 
  //  $file_contents = str_replace('$0','free',$file_contents);
   // fwrite($fh, json_encode($arr)); 


    //finish up	
    fclose($fh); 
    $sortpage = file_get_contents('./results.html', false);
    echo $sortpage; 
?>
