<?php 
    // Include the library
    include('simple_html_dom.php');
    $com = explode(".com", $_POST['url']);
    $http = explode("http://", $com[0]);
    $site = explode("www.", $http[1]);
    if( (count($site)==1 && $site[0]=="zimride") || (count($site)==2 && $site[1]=="zimride") ){
    	$zimride = file_get_html($_POST['url']);
    	$name = $zimride->find('span.name a.requires_login', 0)->plaintext;
    }else{
    	$ridejoy = file_get_html($_POST['url']);
    	$name = $ridejoy->find('div.details', 0)->childNodes(0)->childNodes(1)->plaintext;
    }
  echo trim($name);  
?>