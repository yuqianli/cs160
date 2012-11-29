<?php 
// Read the file contents into a string variable, 
// and parse the string into a data structure 

$str_data = file_get_contents("results.json"); 
$data = json_decode($str_data,true);   
   
// Modify the value, and write the structure to a file "data_out.json" //

function cmp($a, $b)
{
    return strcmp($a["price"], $b["price"]);
}

$arr = $data;

usort($arr,"cmp");
 

$fh = fopen("data_out.json", 'w')       
	or die("Error opening output file"); 
	fwrite($fh, json_encode($arr,JSON_UNESCAPED_UNICODE)); 
	
fclose($fh); 

    $sortpage = file_get_contents('./sort.html', false);
	echo $sortpage;   