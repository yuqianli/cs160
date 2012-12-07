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
    	 
    	$rides[] = array(
    			'link' => $link,
    			'price' => $price,
    			'image' => $image,
    			'name' => $name,
    			'originCity' => trim($city1),
    			'destinationCity' => trim($city2),
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
    	
    	$rides[] = array(    
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
    /*
    $response = $arr;
    $fp = fopen('home.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);*/
    $zim->clear();
    $joy->clear();

    //write to page
    //$homepage = file_get_contents('./home.html', false);
	//echo $homepage;  
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>CarJackers!</title>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css"/>
        <link href="static/css/style.css" rel="stylesheet" type="text/css">
        <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
        <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
        <script src="static/js/slides.min.jquery.js"></script>
        <script src="static/js/jquery.tools.min.js"></script>
        <script src="static/js/scripts.js"></script>
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<!-- For Social sharing -->
		<script type="text/javascript">var switchTo5x=true;</script>
		<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
		<script type="text/javascript">stLight.options({publisher: "7ff40cca-f7f1-4de4-b3af-ef52c7cd6080"});</script>
		<!-- Social sharing -->
        <script type="text/javascript">
            $(function() {
                $('#datepicker').datepicker({ minDate: 0 });
            });
        $("document").ready(function() {
                $('.st_facebook').click(function(e) {
            	    e.stopPropagation();
                });
                $('.st_googleplus').click(function(e) {
            	    e.stopPropagation();
                });
                $('.st_twitter').click(function(e) {
            	    e.stopPropagation();
                });
                $('.st_linkedin').click(function(e) {
            	    e.stopPropagation();
                });
                $('.st_email').click(function(e) {
            	    e.stopPropagation();
                });
                for(var i=1; i<<?=count($rides)?>;i++){
                    $('#searchresult-inner'+i).click(function() {
                 	    window.open(this.children[0].children[0].children[0].children[0].innerText);
                    });
                    $("#trigger"+i).click(function(e) {
                    	$(".glass_overlay").overlay().load();
                	    e.stopPropagation();
                    	var directionsService = new google.maps.DirectionsService();
                        var directionsDisplay = new google.maps.DirectionsRenderer();

                        var map = new google.maps.Map(document.getElementById('map'), {
                          zoom:7,
                          mapTypeId: google.maps.MapTypeId.ROADMAP
                        });
                        directionsDisplay.setMap(map);
                        var request = {
                        	       origin: this.firstChild.innerHTML,//+', CA',  // parse the origin from JSON file
                        	       destination: this.childNodes.item(1).innerHTML,//+', CA',	//parse the destination from JSON file
                        	       travelMode: google.maps.DirectionsTravelMode.DRIVING
                        	     };
                        
                        directionsService.route(request, function(response, status) {
                            if (status == google.maps.DirectionsStatus.OK) {
                              directionsDisplay.setDirections(response);
                            }
                          });
                    });
                }
                $('#slides').slides({
                    preload: false,
                    play: 5000,
                    pause: 2500,
                    animationStart: function(current){
                        $('.caption').animate({
                            bottom:-35
                        },100);
                    },
                    animationComplete: function(current){
                        $('.caption').animate({
                            bottom:0
                        },200);
                    },
                    slidesLoaded: function() {
                        $('.caption').animate({
                            bottom:0
                        },200);
                    }
                });
        });
        </script>
        <style>

.trigger{
text-align:center;
vertical-align:middle;
}
/* the overlayed element */
.glass_overlay {

    margin-left:200px;

    /* must be initially hidden */
    display:none;

    /* place overlay on top of other elements */
    z-index:10000;

    /* styling */
    background-color:#333;

    width:750px;
    min-height:200px;
    border:1px solid #666;

    /* CSS3 styling for latest browsers */
    -moz-box-shadow:0 0 90px 5px #000;
    -webkit-box-shadow: 0 0 90px #000;
}

/* close button positioned on upper right corner */
.glass_overlay .close {
    background-image:url(static/img/close.png);
    position:absolute;
    right:-15px;
    top:-15px;
    cursor:pointer;
    height:35px;
    width:35px;
}


    /* styling for elements inside overlay */
  .details {
  position:absolute;
  top:15px;
  right:15px;
  font-size:11px;
  color:#fff;
  width:150px;
  }

  .details h3 {
  color:#aba;
  font-size:15px;
  }
  </style>
    </head>
    <body>
    <div class="glass_overlay">
    <div id="map" style="width: 350px; height: 400px; float: left;"></div> 
    <h3 class="details">Let's figure out the content!</h3>
    </div>
    <div id="container">
    <div class="logo">
        <h1><a href="./"><strong>CAR</strong>JACKERS</a></h1>
    </div>

    <div id="searchbars">
        <div class="padder-mini">
            <form method="post" action="./results.php" name="searchbars" onsubmit="return validate();">
                <table>
                    <tr>
                        <td>I am a</td>
                        <td>
                            <table>
                                <tr>
                                    <td><input name="type" id="fr1" type="radio" value="passenger">Passenger</td>
                                    <td><input name="type" id="fr1" type="radio" value="driver">Driver</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>From</td>
                        <td><input name="from" id="fr1" type="text" value="San Francisco, CA"></td>
                    </tr>
                    <tr>
                        <td>To</td>
                        <td><input name="to" id="fr1" type="text" value="Los Angeles, CA"></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td><input name="date" type="text" id="datepicker" placeholder="Choose a date"/></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input id="sub" type="submit" value="Search" style="width: 100%;"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <div class="splitter">
        <h2 style="margin-top:30px;">Freshly jacked rides</h2>
    </div>
    <div class="padder">
        <div id="results">
            <div id="slides">
                <table>
                    <tr>
                        <td><a href="#" class="prev"><img src="static/img/arrow-prev.png" width="24" height="43" /></a></td>
                        <td><span class="slides_container">
            <?php
            $counter = 1;
            foreach ($rides as $ride){
            	?>
            	<div class='slide'><table class='searchresult-inner' id='searchresult-inner<?=$counter ?>'>
                            <tr>
                                <td style='vertical-align:middle;width:100px;'><div style="display:none;"><?=$ride['link'] ?></div><img src='<?=$ride['image'] ?>'/></td>
                                <td style='vertical-align:middle;'>
                                    <table style='width:100%;'>
                                        <tr>
                                            <td><span class='popout'><?=$ride['price'] ?></span></td>
                                            <td style='text-align:right;'><span class='popout'></span></td>
                                        </tr>
                                        <tr>
                                            <td><?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?></td>
                                            <td style='text-align:right;'><?=$ride['date'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><i><?=$ride['driver'] ?></i></td>
                                            <td style='text-align:right;'>                                            
            	<span class='st_facebook' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_googleplus' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_twitter' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_linkedin' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_email' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class='trigger' id='trigger<?=$counter ?>'><div style='display:none;' id='originCity<?=$counter ?>'><?=$ride['originCity'] ?></div><div style='display:none;' id='destinationCity<?=$counter++ ?>'><?=$ride['destinationCity'] ?></div><img src='static/img/glass.png'></td>
                            </tr>
                        </table></div>
            	
            	
            	<?php 
            }
            ?>
                        
                        </span></td>
                        <td><a href="#" class="next"><img src="static/img/arrow-next.png" width="24" height="43" /></a></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    </div>
    </body>
</html>

