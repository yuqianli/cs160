<?php 
    //sorting function
    function cmp($a, $b)
    {
        return strcmp($a["price"], $b["price"]);
    }

    // Read the json  results  and parse the string into a data structure 
    $str_data = file_get_contents("results.json"); 
    if (!$str_data) {
        die("Error opening json");
    }
    $data = json_decode($str_data,true);   

    //sort the data and write to json file
    $arr = $data;
    usort($arr,"cmp");
    $fh = fopen("results.json", 'w');
	fwrite($fh, json_encode($arr,JSON_UNESCAPED_UNICODE)); 

    //finish up
    fclose($fh); 
    $sortpage = file_get_contents('./output.html', false);
    echo $sortpage;   
?>
