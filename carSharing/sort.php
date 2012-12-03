<?php 
// Read the json  results  and parse the string into a data structure 

$file_contents = file_get_contents("results.json");

$fh = fopen("results.json", "w");
$file_contents = str_replace('free','$0',$file_contents);
$file_contents = str_replace('"$','"',$file_contents);

fwrite($fh, $file_contents);
fclose($fh);
//"price": "$45",


$str_data = file_get_contents("results.json"); 
$data = json_decode($str_data,true);   
   

// Modify the value, and write the structure to a file "data_out.json" //

function cmp($a, $b)
{
	$a["price"] = str_pad($a["price"], 10, '0', STR_PAD_LEFT);
	$b["price"] = str_pad($b["price"], 10, '0', STR_PAD_LEFT);
    return strcasecmp($a["price"], $b["price"]);
}

$arr = $data;

usort($arr,"cmp");
 
// writing the results to results.json file 
$fh = fopen("results.json", 'w')       
	or die("Error opening output file"); 
	fwrite($fh, json_encode($arr,JSON_UNESCAPED_UNICODE)); 
	
	
fclose($fh); 

/*
need to be done
  $fh = fopen("results.json", "w");
 // trying to put back the dollar sign
 $file_contents = str_replace('"price": "','"price": "$',$file_contents);
 // trying to put back the word "free" in place of $0
 $file_contents = str_replace('"price": "$0','"price": "free',$file_contents);
 
$file_contents = str_replace('"price": "0','"price": "free',$file_contents);

fwrite($fh, $file_contents);
fclose($fh);

 */
    $sortpage = file_get_contents('./output.html', false);
	echo $sortpage;   
	
	
	
	
	
	
	