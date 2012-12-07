<?php
    // Include the library
    include('simple_html_dom.php');
	//error_reporting(E_ERROR | E_WARNING | E_PARSE); //error reporting strictness

    //POST search terms
    $origin = $_POST['from'];
    $destination = $_POST['to'];
	$startDate = $_POST['date'];
	
    // a/b/c = month/day/year
	$startDate = array(
        'a' => substr($startDate,0,2),
        'b' => substr($startDate,3,2),
        'c' => substr($startDate,6,4)
     );
	
    //rebuild origin location for url validity
	$locationcontents = explode(" ", $origin);
	$originCity = "";
	$originState = "";
    //if city name has a space in it
	if ( count($locationcontents) > 2) {
		$originCity = $locationcontents[0] . "+" . substr($locationcontents[1], 0, (strlen($locationcontents[1]) - 1));
		$originState = $locationcontents[2];
	} else {
		$originCity = $locationcontents[0];
		$originState = $locationcontents[1];
	}
	
    //rebuild destination location for url validity
	$destinationContents = explode(" ", $destination);
	$destinationCity = "";
	$destinationState = "";
    //if city name has a space in it
	if ( count($locationcontents) > 2) {
		$destinationCity = $destinationContents[0] . "+" . substr($destinationContents[1], 0, (strlen($destinationContents[1]) - 1));
		$destinationState = $destinationContents[2];
	} else {
		$destinationCity = $destinationContents[0];
		$destinationState = $destinationContents[1];
	}
	
	
    //get origin latitude/longitude via google geocode
	$originurl = "http://maps.googleapis.com/maps/api/geocode/json?address=$originCity,+$originState&sensor=true";
	$originjson = file_get_contents($originurl);
	$originj = json_decode($originjson, true);
	$originLat = $originj['results'][0]['geometry']['location']['lat'];
	$originLon = $originj['results'][0]['geometry']['location']['lng'];

    //get destination latitude/longitude via google geocode
	$desturl="http://maps.googleapis.com/maps/api/geocode/json?address=$destinationCity,+$destinationState&sensor=true";
	$destinationjson = file_get_contents($desturl);
	$destj = json_decode($destinationjson, true);
	$destinationLat = $destj['results'][0]['geometry']['location']['lat'];
	$destinationLon = $destj['results'][0]['geometry']['location']['lng'];
	
    
	$type="";
	if(isset($_POST['type'])){
		if($_POST['type']=="passenger"){
			$type="ride_offer";
		}elseif ($_POST['type']=="driver"){
			$type="ride_request";
		}
	}
    
	
    //retrieve DOM from ridejoy
    $ridejoy_url = "http://ridejoy.com/rides/search?utf8=%E2%9C%93&type=$type&origin=$originCity%2C+$originState%2C+USA&origin_latitude=$originLat&origin_longitude=$originLon&destination=$destinationCity%2C+$destinationState%2C+USA&destination_latitude=$destinationLat&destination_longitude=$destinationLon&date=";
    $ridejoy = file_get_html($ridejoy_url);
    $joy = $ridejoy->find('div.rides_search_container div.date');
    
    	
    //$usertype = $_POST['type'];
    $from = explode(", ", $_POST['from']);
    $to = explode(", ", $_POST['to']);
    
    $fromCity = str_replace(' ', '+', $from[0]);
    $fromState = $from[1];
    $toCity = str_replace(' ', '+', $to[0]);
    $toState = $to[1];
    //no need to check, zimride defaults blank dates to current date
    //$date = $_POST['date'];
    $url_date = str_replace('/', '%2F', empty($_POST['date'])?$_POST['date']:"");
    $type="";
    if(isset($_POST['type'])){
    	if($_POST['type']=="passenger"){
    		$type="need";
    	}elseif ($_POST['type']=="driver"){
    		$type="offer";
    	}
    }
    /* FIX: should use the same POSTed values as ridejoy 
    $fromCity = str_replace(' ', '+', $origin[0]);
    $fromState = $origin[1];
    $toCity = str_replace(' ', '+', $destination[0]);
    $toState = $destination[1];
    $url_date = str_replace('/', '%2F', empty($_POST['date'])?$_POST['date']:"");
    end FIX */
    
    //retrieve DOM from zimride
    $zim_url = "http://www.zimride.com/search?s=$fromCity%2C+$fromState&e=$toCity%2C+$toState&date=$url_date&filter_type=$type&s_name=$toCity%2C+$toState&s_full_text=$fromCity%2C+$fromState%2C+USA&s_error_code=&s_address=$fromCity%2C+$fromState%2C+USA&s_city=$fromCity&s_state=$fromState&s_zip=&s_country=US&s_lat=$originLat&s_lng=$originLon&s_location_key=&s_user_lat=&s_user_lng=&s_user_country=&e_name=$toCity%2C+$toState&e_full_text=$toCity%2C+$toState%2C+USA&e_error_code=&e_address=$toCity%2C+$toState%2C+USA&e_city=$toCity&e_state=$toState&e_zip=&e_country=US&e_lat=$destinationLat&e_lng=$destinationLon&e_location_key=&e_user_lat=&e_user_lng=&e_user_country=";
    $zimride = file_get_html($zim_url);
    //find all TD tags with "align=center"
    $zim = $zimride->find('div.ride_list a') ;
    
    //scrape from ridejoy and zimride
    $jindex = 0;
    $zindex=0;
    //scrape from ridejoy
    $j=0;
    while (!empty($joy[$jindex]) || !empty($zim[$zindex])) {
        if(!empty($joy[$jindex])){
            $name = "";
            $link = $joy[$jindex]->childNodes(1)->childNodes($j)->find('a', 0)->href;
            $image = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.photo img',0)->src;
            $tmp = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.seat_count',0);
            $price = $tmp?$tmp->plaintext:"free";
            $originCity = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.origin',0)->plaintext;
            $destinationCity = $joy[$jindex]->childNodes(1)->childNodes($j)->find('div.destination',0)->plaintext;
            $type = $tmp?"driver":"passenger";
            $departure = $joy[$jindex]->find('div.date_header',0)->plaintext;
            //add commas
            $departure = str_replace('day ', 'day, ', $departure);

            //echo "#".trim($price)."#";
            $rides[] = array(
                'link' => $link,
                'price' => trim($price),
                'image' => $image,
                'name' => $name,
                'originCity' => trim($originCity),
                'destinationCity' => trim($destinationCity),
                'driver' => $type,
                'date' => $departure
            );
            $j++;
            if(!$joy[$jindex]->childNodes(1)->childNodes($j)) {
                $jindex++;
                $j=0;
            }
        }
        
        //scrape from zimride
        $cur=0;
        $i=0;
        if(!empty($zim[$zindex])){
            $name = $zim[$zindex]->find('div.username',0);
            //ignore last two links in page that are not people
            if ($name) {
                $name = $name->plaintext;
                $link = $zim[$zindex]->getAttribute('href');
                $image = $zim[$zindex]->find('img',0)->src;
                $price = $zim[$zindex]->find('div[class=price_box]',0)?$zim[$zindex]->find('div[class=price_box]',0)->childNodes(1)->childNodes(0)->plaintext:"free";
                $main5 = $zim[$zindex]->find('div.inner_content span.inner',0)->innertext;
                $city1 = explode("<span", $main5);
                $city2 = explode("</span>", $main5);
                $type = $zim[$zindex]->find('div.userpic span.driver',0)?"driver":"passenger";
                $departure = $zimride->find('div.ride_list',0)->childNodes($cur)->find('span',0)->plaintext;
                //remove dash
                $departure = str_replace('— ', '', $departure);
//echo "#$price#";
                $rides[] = array(
                    'link' => $link,
                    'price' => $price,
                    'image' => $image,
                    'name' => $name,
                    'originCity' => trim($city1[0]),
                    'destinationCity' => trim($city2[1]),
                    'driver' => $type,
                    'date' => $departure
                );
                $i++;
                if ($zimride->find('div.ride_list',0)->childNodes($i)->find('em',0)) {
                    $cur = $i;
                }
            }
            $zindex++;
        }
    }

    session_start();
    $_SESSION['rides'] = $rides;
    
    //write scraped data to json file
   /* $response = $rides;
    $fp = fopen('results.json', 'w');
    fwrite($fp, json_encode($response));
    fclose($fp);*/

    //finish up
    $ridejoy->clear();
    $zimride->clear();
    //$homepage = file_get_contents('./results.html', false);
	//echo $homepage;    

	//include('results.html');
				       
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
                    <form method="post" action="./results.php">
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
                                <td><input name="from" id="fr1" type="text" placeholder="Origin"></td>
                            </tr>
                            <tr>
                                <td>To</td>
                                <td><input name="to" id="fr1" type="text" placeholder="Destination"></td>
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
            <div style="clear:both;">
                <form method="post" action="./sort.php">
                    <table style="width:100%;">
                        <tr>
                            <td><h2>Search Results</h2></td>
                            <td style="text-align:right;"><input type="submit" value="Sort by price"></td>
                        </tr>
                    </table>
                </form>
            </div>
            <div id="results">
            <div class='padder'>
            <?php
$counter = 1;
            foreach ($rides as $ride){
            	?>
            	<table class='searchresult-inner' id='searchresult-inner<?=$counter ?>'>
            	<tr>
            	<td style='vertical-align:middle;width:100px;'><div style="display:none;"><?=$ride['link'] ?></div><img src='<?=$ride['image'] ?>' height='60px' width='60px'/></td>
            	<td style='vertical-align:middle;'>
            	<table style='width:100%;'>
            	<tr>
            	<td><?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?></td>
            	<td style='text-align:right;'><span class='popout'><?=$ride['price'] ?></span></td>
            	</tr>
            	<tr>
            	<td><?=$ride['date'] ?></td>
            	</tr>
            	<tr>
            	<td><i><?=$ride['driver'] ?></i></td>
            	
            	<td style='text-align:right;'>
            	<span class='st_facebook' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_googleplus' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_twitter' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_linkedin' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	<span class='st_email' st_title='<?=$ride['driver'] ?> looking for <?=$ride['originCity'] ?> -> <?=$ride['destinationCity'] ?> for <?=$ride['price'] ?>' st_url='<?=$ride['link'] ?>'></span>
            	</td></tr>
            	</table>
            	</td>
            	<td class='trigger' id='trigger<?=$counter?>'><div style='display:none;' id='originCity<?=$counter?>'><?=$ride['originCity'] ?></div><div style='display:none;' id='destinationCity<?=$counter++ ?>'><?=$ride['destinationCity'] ?></div><img src='static/img/glass.png'></td>
            	</tr>
            	</table>
            	<?php 
            }
            ?>
            
            </div>
            </div>
    </div>
    
    <script type="text/javascript">
    $("document").ready(function() {
        
        // $('#results')[0].innerHTML = result.join('');
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
         for(var i=1; i<<?=$counter?>;i++){
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
         $(".glass_overlay").overlay();
     });      
    </script>
</body>
</html>
