<?php
$mapkey = "AIzaSyA3i3b3N5yI70O-JR-HlqeeeYkPZU1PuTk";
//$mapkey = "AIzaSyCKBuiS8793x9Bc8hD5-XjsLUOHQuW2w8g";
$all_rec=array("*");
$table = PREFIX."profile_drivers";
$res_all_cat = $db->select($all_rec,$table);
$date = date("Y-m-d");
if(isset($_POST['assign'])){
	require_once("assign-save.php");
	//print_r($_POST); exit;
}
$table = array("justeat_orders","phone_orders","lazymeal_orders","prescheduled_orders","request_delivery","grabbit_orders");
 if(!empty($res_all_cat)){
	$color = array();
	$js_arr = array();
	$x = 0;
		foreach($table as $tb){
			if($tb == 'grabbit_orders'){
				$chk_assign_orders = $db->select(array('*'),$tb,"order_date = '$date'  and (status = 0 or status = 1 or status = 2 or status = 4)"); 
				//if(!empty($chk_assign_orders)) break;
			}else{
				$chk_assign_orders = $db->select(array('*'),PREFIX.$tb,"order_date = '$date' and (status = 0 or status = 1 or status = 2 or status = 4)"); 
				$tb = PREFIX.$tb;
				//if(!empty($chk_assign_orders)) break;
			}
			//print_r($chk_assign_orders); exit;
			if(empty($chk_assign_orders)) continue;
			foreach($chk_assign_orders as $ord){
				$array_coordinate = array();
				$ord->table = $tb;
				if($tb == 'grabbit_orders'){
					$ord->id = $ord->order_id;
				}
				//print_r($ord);
				$addressesall = get_pick_drop_address($ord);
				//print_r($addressesall); exit;
				$addressdrop1 = $addressesall['Restaurant Address'];
				$addressdropname = $addressesall['Restaurant Name'];
				$addressdrop = str_ireplace(" ","+",$addressdrop1);
				$addresspick1 = $addressesall['Customer Address'];
				$addresspickname = $addressesall['Customer Name'];
				$addresspick = str_ireplace(" ","+",$addresspick1);
				$res_driver = $db->select(array('*'),PREFIX."profile_drivers","id_profile_driver=$ord->agent_id");
				if(!empty($res_driver)){
					foreach($res_driver as $d){
						$driver_name = $d->first_name.' '.$d->last_name;
						$driver_ph = $d->phone;
						break;
					}
				}
				// get pickup coordinate
				if($addresspick1 != 'N/A'){
					$url = "https://maps.google.com/maps/api/geocode/json?address=$addresspick&sensor=false&key=$mapkey";
					//echo $url; exit;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$response = curl_exec($ch);
					curl_close($ch);
					$response_a = json_decode($response);
					$lat0 = $response_a->results[0]->geometry->location->lat;
					$long0 = $response_a->results[0]->geometry->location->lng;
					$array_coordinate[0]->latitude = $lat0;
					$array_coordinate[0]->langitude = $long0;
					$array_coordinate[0]->name = $addresspickname;
					$array_coordinate[0]->tbl = $tb;
					$array_coordinate[0]->status = $ord->status;
					$array_coordinate[0]->id = $ord->id;
					$array_coordinate[0]->address = $addresspick1;
					$array_coordinate[0]->rphone = $addressesall['Merchant Phone'];
					$array_coordinate[0]->orderno = $addressesall['Order No'];
					$array_coordinate[0]->otime = $addressesall['Order Time'];
					$array_coordinate[0]->dname = $driver_name;
					$array_coordinate[0]->dph = $driver_ph;
				}
				if($addressdrop1 != 'N/A'){
					$url = "https://maps.google.com/maps/api/geocode/json?address=$addressdrop&sensor=false&key=$mapkey";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$response = curl_exec($ch);
					curl_close($ch);
					$response_a = json_decode($response);
					$lat1 = $response_a->results[0]->geometry->location->lat;
					$long1 = $response_a->results[0]->geometry->location->lng;
					$array_coordinate[1]->latitude = $lat1;
					$array_coordinate[1]->langitude = $long1;
					$array_coordinate[1]->name = $addressdropname;
					$array_coordinate[1]->id = $ord->id;
					$array_coordinate[1]->tbl = $tb;
					$array_coordinate[1]->address = $addressdrop1;
					$array_coordinate[1]->rphone = $addressesall['Customer Phone'];
					$array_coordinate[1]->orderno = $addressesall['Order No'];
					$array_coordinate[1]->otime = $addressesall['Order Time'];
					$array_coordinate[1]->dname = $driver_name;
					$array_coordinate[1]->dph = $driver_ph;
					$array_coordinate[1]->status = 5;
				}
				
				/* if(!empty($res_driver)){
					foreach($res_driver as $d){
						if($d->status == 1){
							$array_coordinate[$d->id_profile_driver]->latitude = $d->latitude;
							$array_coordinate[$d->id_profile_driver]->langitude = $d->langitude;
							$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$d->latitude,$d->langitude&sensor=true&key=$mapkey";
							$json = file_get_contents($url); // this WILL do an http request for you
							$data = json_decode($json);
							//print_r($data);
							if(empty($data->results)) continue;
							$address = $data->results[0]->address_components[2]->long_name.','.$data->results[0]->formatted_address;
							$array_coordinate[$d->id_profile_driver]->name = $d->first_name.' '.$d->last_name;
							$array_coordinate[$d->id_profile_driver]->address = $address;
							$array_coordinate[$d->id_profile_driver]->dph = $driver_ph;
							$array_coordinate[$d->id_profile_driver]->orderno = $addressesall['Order No'];
							$array_coordinate[$d->id_profile_driver]->otime = $addressesall['Order Time'];
							$array_coordinate[$d->id_profile_driver]->status = 111;
							break;
						}
					}
				} */
				$new_array[$x] = $array_coordinate;
				$x++;
					
			}
		}
}
//echo '<pre>'; 
//print_r($new_array); exit;
$x = 0;
foreach($new_array as $ac){
	foreach($ac as $co){
		if($co->status == 0) $co->status = 1;
		$profile_status = $db->selectSRow(array('background_hex'),PREFIX."order_status","id=$co->status");
		if(empty($profile_status)) $profile_status['background_hex'] = '0';
		$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$co->latitude,$co->langitude&sensor=true&key=$mapkey";
		$json = file_get_contents($url); // this WILL do an http request for you
		$data = json_decode($json);
		//print_r($data);
		if(empty($data->results)) continue;
		$address = $data->results[0]->address_components[2]->long_name.','.$data->results[0]->formatted_address;
		//$js_arr[$x] =  '["'.$address.'"!'.$co->latitude.'!'.$co->langitude.']';
		$js_arr[$x] =  '"'.$address.'"!'.$co->latitude.'!'.$co->langitude;
		$color[$x] = $profile_status['background_hex'].'zee'.$co->tbl.$co->id;
		$class = "class='info_content'";
		$infoaddr[$x] = '<div><h4>Order: '.$co->orderno.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Time: '.$co->otime.'</h4><p>'.$co->name.'(ph: '.$co->rphone.')</br>'.$co->address.'</p><p>Driver: '.$co->dname.' (Ph: '.$co->dph.')</p></div>';
		$x++;
	}
}

$str = implode("=",$js_arr);
$st_color = implode(",",$color);
$infowinadd = implode("!",$infoaddr);
//echo $str.'----------'.$infowinadd; exit;
if(empty($js_arr)){
	$str = '"Central,1440 West Pender Street, Vancouver, BC V6G 2S3, Canada"!49.289966!-123.128867';
	$st_color = '11';
	$infowinadd = '<div></div>';
}else{
	$str = '"Central,1440 West Pender Street, Vancouver, BC V6G 2S3, Canada"!49.289966!-123.128867='.$str;
	$st_color = '11,'.$st_color;
	$infowinadd = '<div></div>!'.$infowinadd;
}
//echo $str; exit;
//echo $str.'----'.$st_color; exit;
?>
 <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
 <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=geometry"></script>
 <script type="text/javascript" src="assets/js/session.js"></script>
	<script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js" type="text/javascript"></script>
<script type="text/javascript">
//var arr_dist = {};
var old_order_count = 0;
var order_arr_str;
var str_color_str;
var new_assign_order = {};
var color = [];
var new_marker;
var m1 = '<?php echo $str; ?>';
var php_color_str = "<?php echo $st_color; ?>";
var php_infoWindow = "<?php echo $infowinadd; ?>";
var infoWindowContent = [];  
var m2;
var dlist;
var dinfowind;
var dcolor;
var olist = '<?php echo $str; ?>';
var oinfowind = "<?php echo $infowinadd; ?>";
var ocolor = "<?php echo $st_color; ?>";
var merge_color;
var m21;
var merge_color1;
var a1 = {};
var data231 = {};
var markers = [];
var arr;
var arr1;
var merge_info_window;
var merge_info_window1;
var color_arr;
var color_arr1;
var info_window_arr;
var info_window_arr1;
var temp_arr = [];
var map;
var bounds;
var script = document.createElement('script');
var del_postion = [];
script.src = "http://maps.googleapis.com/maps/api/js?sensor=false&callback=map_onload";
document.body.appendChild(script);
setInterval(function(){
	jQuery(function(jQuery) {
		// Asynchronously Load the map API 
		initialize();
	});
	jQuery.ajax({
	  url: 'ajax-follow.php',
	  type: 'post',
	  data: {'action': 'loc_dr', 'userid': '11239528343'},
	  success: function(data23, status) {
		if(data23 != 'error'){
			obj = JSON.parse(data23);
			dlist = obj.mark;
			dcolor = obj.color;
			dinfowind = obj.info_window;
		}else{
			dlist = '"Central,1440 West Pender Street, Vancouver, BC V6G 2S3, Canada"!49.289966!-123.128867';
			dcolor = '11';
			dinfowind = '<div></div>';
			//setAllMap(null);
		}
		
	  }
	}); // end ajax call
}, 5000);

function map_onload(){
	bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap',
		panControl: false,
		streetViewControl: false,
		scaleControl: false,
		center: new google.maps.LatLng(49.289966,-123.128867),
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		scrollwheel: false
    };
	map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
	map.setTilt(45);
}

function removeAllMarkers(map,markersArray){// removes all markers from map
    /* for( var i = 0; i < markersArray.length; i++ ){
		markersArray.pop().setMap(null)
    }   markersArray
    markersArray.length = 0;
	del_postion = []; */
	while(markersArray.length){
         markersArray.pop().setMap(null);
    }
	//markersArray.pop().setAllMap(null);
};

function initialize() {
	var backup_merge = m2;
	var backup_color = merge_color;
	var backup_info = merge_info_window;
    markers = [];                
    color = []; 
    infoWindowContent = [];          
	//if(typeof m2 == 'undefined'){
		if(typeof dlist == 'undefined'){
			m2 = olist;
			merge_color = ocolor;
			merge_info_window = oinfowind;
		}else if(typeof olist == 'undefined'){
			m2 = dlist;
			merge_color = dcolor;
			merge_info_window = dinfowind;
		}else{
			m2 = dlist+'='+olist;
			merge_color = dcolor+','+ocolor;
			merge_info_window = dinfowind+'!'+oinfowind;
		}
	//}
	//alert(dlist);
	arr = m2.split('=');
	//m2 = '';
	color_arr = merge_color.split(',');
	//merge_color = '';
	info_window_arr = merge_info_window.split('!');
	//merge_info_window = '';
	jQuery.each(arr, function( index, value ) {
		var arr2 = value.split('!');
		markers.push(arr2);
	}); 
	jQuery.each(color_arr, function( index1, value1 ) {
		var new_build_color_array = value1.split('zee');
		color.push(new_build_color_array[0]);
	});
	jQuery.each(info_window_arr, function( index2, value2 ) {
		temp_arr = [];
		temp_arr.push(value2)
		infoWindowContent.push(temp_arr);
	}); 
	if(markers){
		removeAllMarkers(map,del_postion);
	}		
		del_postion = [];
    // Display multiple markers on a map
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    
    // Loop through our array of markers & place each one on the map  
    for( i = 0; i < markers.length; i++ ) {
		if(color[i] != 11){
		if(color[i] == '#FFFFFF'){
				var scolor = 'colors/blank001.png'
			}else if(color[i] == '#FCB322'){
				var scolor = 'colors/blank002.png'
			}else if(color[i] == '#00BFFF'){
				var scolor = 'colors/blank003.png'
			}else if(color[i] == '#a9d86e'){
				var scolor = 'colors/blank004.png'
			}else if(color[i] == '#8C8C8C'){
				var scolor = 'colors/blank005.png'
			}else if(color[i] == '#FF6C60'){
				var scolor = 'colors/blank006.png'
			}else if(color[i] == '#90E0DC'){
				var scolor = 'colors/blank007.png'
			}else if(color[i] == '0'){
				var scolor = 'images/online.png'
			}else if(color[i] == '11'){
				var scolor = 'colors/transparent.png';
			}
			console.log(markers[i]);
			console.log(scolor);
			console.log(color[i]);
			console.log(infoWindowContent[i]);
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
       // bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0],
			icon: scolor
			
        });
		//scolor = 'colors/transparent.png';
		del_postion.push(marker);
        
        // Allow each marker to have an info window    
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
			//placeMarker(marker);
            return function() {
                infoWindow.setContent(infoWindowContent[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));
		}
        // Automatically center the map fitting all markers on the screen
        //map.fitBounds(bounds);
    }

    // Override our map zoom level once our fitBounds function runs (Make sure it only runs once)
      /* var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
        this.setZoom(20);
        google.maps.event.removeListener(boundsListener);
    }) */;  
    
}
function calcDistance(p1, p2){
  return (google.maps.geometry.spherical.computeDistanceBetween(p1, p2) / 1000).toFixed(2);
}
jQuery( document ).ready(function() {
	setInterval(function(){
		jQuery.ajax({
		  url: 'ajax-follow.php',
		  type: 'post',
		  data: {'action': 'orders', 'userid': '11239528343'},
		  success: function(datazee, status) {
			obj1 = JSON.parse(datazee);
			jQuery('#hideshow1').html(obj1.html);
			if(obj1.ocnt > old_order_count){
				//order_arr_str = obj.order_arr;
				old_order_count = obj1.ocnt;
				jQuery.ajax({
					url: 'ajax-follow.php',
					type: 'post',
					data: {'action': 'new_create_marker', 'old_str': order_arr_str,'new_str': obj1.order_arr},
					success: function(data, status) {
						obj = JSON.parse(data);
						olist = olist+'='+obj.mark;
						ocolor = ocolor+','+obj.color;
						oinfowind = oinfowind+'!'+obj.info_window;
						order_arr_str = obj1.order_arr;
					}
				});
			}else if(obj1.ocnt < old_order_count){
				//old_order_count = obj.ocnt;
				jQuery.ajax({
					url: 'ajax-follow.php',
					type: 'post',
					data: {'action': 'new_create_marker', 'old_str': obj1.order_arr,'new_str': order_arr_str},
					success: function(data, status) {
						obj = JSON.parse(data);
						//var str_index =  olist.indexOf(obj.mark);
						//alert(str_index);
						olist = olist.replace('='+obj.mark, ' ');
						var search_arr = ocolor.split(',');
						var search_str = obj.color.split(',');
						var search_str11 = search_str[0].split('zee');
						//alert(search_str11[1]);
						jQuery.each(search_arr, function( se, value ) {
							var find_str = value.search( search_str11[1] );
							console.log(find_str);
							if(find_str > 0){
								ocolor = ocolor.replace(','+value, ' ');
/* 								if(typeof str_color_str == 'undefined'){
									str_color_str = value;
								}else{
									str_color_str = str_color_str+','+value;
								} */
							}
						});
						str_color_str = '';
						oinfowind = oinfowind.replace('!'+obj.info_window, ' '); 
						old_order_count = obj1.ocnt;
						order_arr_str = obj1.order_arr;
					}
				}); 
				//alert(obj.ocnt);
			}
			//alert(obj.ocnt);
			//}
		  }
		}); // end ajax call
	}, 5000);
	setInterval(function(){
		jQuery.ajax({
		  url: 'ajax-follow.php',
		  type: 'post',
		  data: {'action': 'drivers', 'userid': '11239528343'},
		  success: function(data1, status) {
			//if(data == "ok") {
			 jQuery('#hideshow2').html(data1);
			//alert(data1);
			//}
		  }
		}); // end ajax call
	}, 5000);
	/* setInterval(function(){
		jQuery.ajax({
		  url: 'ajax-follow.php',
		  type: 'post',
		  data: {'action': 'drivers_list', 'userid': '11239528343'},
		  success: function(data2, status) {
			//if(data == "ok") {
			 jQuery('#dlist').html(data2);
			//alert(data2);
			//}
		  },
		  error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		  }
		}); // end ajax call
	}, 60000); */
	jQuery('#top-menu').hide();
	jQuery('#main-content').css('background-color','#59616b');
	jQuery('#main-content-top-right').hide();
	jQuery('#chat').hide();
	jQuery('#weldone').css('min-height','510px');
	jQuery('#weldone_body').css('height','100%');
	jQuery('#body-wrapper').css('height','100%');
	jQuery('#weldone_body').css('width','100%');
	jQuery('#main-content').css('width','100%');
	jQuery('#main-content').css('height','100%');
	jQuery('#footer').hide();
	
	jQuery("#map_canvas").mouseleave(function () {
		jQuery('#map_canvas').addClass('scrolloff'); // set the pointer events to none when mouse leaves the map area
	});
	

(function() {
  
  "use strict";

  //////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////////////
  //
  // H E L P E R    F U N C T I O N S
  //
  //////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////////////

  /**
   * Function to check if we clicked inside an element with a particular class
   * name.
   * 
   * @param {Object} e The event
   * @param {String} className The class name to check against
   * @return {Boolean}
   */
  function clickInsideElement( e, className ) {
    var el = e.srcElement || e.target;
    
    if ( el.classList.contains(className) ) {
      return el;
    } else {
      while ( el = el.parentNode ) {
        if ( el.classList && el.classList.contains(className) ) {
          return el;
        }
      }
    }

    return false;
  }

  /**
   * Get's exact position of event.
   * 
   * @param {Object} e The event passed in
   * @return {Object} Returns the x and y position
   */
  function getPosition(e) {
    var posx = 0;
    var posy = 0;

    if (!e) var e = window.event;
    
    if (e.pageX || e.pageY) {
      posx = e.pageX;
      posy = e.pageY;
    } else if (e.clientX || e.clientY) {
      posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
      posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    return {
      x: posx,
      y: posy
    }
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////////////
  //
  // C O R E    F U N C T I O N S
  //
  //////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////////////
  
  /**
   * Variables.
   */
  var contextMenuClassName = "context-menu";
  var contextMenuItemClassName = "context-menu__item";
  var contextMenuLinkClassName = "context-menu__link";
  var contextMenuActive = "context-menu--active";
  var taskItemClassName = "task";
  var taskItemInContext;

  var clickCoords;
  var clickCoordsX;
  var clickCoordsY;

  var menu = document.querySelector("#context-menu1");
  var menuItems = menu.querySelectorAll(".context-menu__item");
  var menuState = 0;
  var menuWidth;
  var menuHeight;
  var menuPosition;
  var menuPositionX;
  var menuPositionY;

  var windowWidth;
  var windowHeight;

  /**
   * Initialise our application's code.
   */
  function init() {
    contextListener();
    clickListener();
    keyupListener();
    resizeListener();
  }

  /**
   * Listens for contextmenu events.
   */
  function contextListener() {
    document.addEventListener( "contextmenu", function(e) {
      taskItemInContext = clickInsideElement( e, taskItemClassName );
	  jQuery('.id'+taskItemInContext.getAttribute("data-id")).val(taskItemInContext.getAttribute("order-id"));
	  jQuery('.tb'+taskItemInContext.getAttribute("data-id")).val(taskItemInContext.getAttribute("order-tb"));
	  jQuery.ajax({
		  url: 'ajax-follow.php', 
		  type: 'post',
		  data: {'action': 'drivers_list_build', 'oadd': taskItemInContext.getAttribute("order-add"), 'otb': taskItemInContext.getAttribute("order-tb"),'oid': taskItemInContext.getAttribute("order-id")},
		  success: function(response, status) {
			jQuery('.context-menu__item').html(response);
			//alert(response);
		  }
	  });
	  /* jQuery.each(arr_dir, function( index, value ) {
		if (typeof arr_dist[taskItemInContext.getAttribute("order-id")+'-'+index] === "undefined") {
			jQuery("#dis_"+value).html('0 KM');
		}else{
			jQuery("#dis_"+value).html('('+arr_dist[taskItemInContext.getAttribute("order-id")+'-'+index]+' KM)');
		} 
		//alert();
	}); */
	 //var idgenrate = "context-menu_"+taskItemInContext.getAttribute("order-id")+taskItemInContext.getAttribute("order-tb");
		//alert(taskItemInContext.getAttribute(idgenrate));
      if ( taskItemInContext ) {
        e.preventDefault();
        toggleMenuOn();
        positionMenu(e);
      } else {
        taskItemInContext = null;
        toggleMenuOff();
      }
    });
  }

  /**
   * Listens for click events.
   */
  function clickListener() {
	//alert('asdf');
    document.addEventListener( "click", function(e) {
      var clickeElIsLink = clickInsideElement( e, contextMenuLinkClassName );
      if ( clickeElIsLink ) {
        e.preventDefault();
        menuItemListener( clickeElIsLink );
      } else {
        var button = e.which || e.button;
        if ( button === 1 ) {
          toggleMenuOff();
        }
      }
    });
  }

  /**
   * Listens for keyup events.
   */
  function keyupListener() {
    window.onkeyup = function(e) {
      if ( e.keyCode === 27 ) {
        toggleMenuOff();
      }
    }
  }

  /**
   * Window resize event listener
   */
  function resizeListener() {
    window.onresize = function(e) {
      toggleMenuOff();
    };
  }

  /**
   * Turns the custom context menu on.
   */
  function toggleMenuOn() {
    if ( menuState !== 1 ) {
      menuState = 1;
      menu.classList.add( contextMenuActive );
    }
  }

  /**
   * Turns the custom context menu off.
   */
  function toggleMenuOff() {
    if ( menuState !== 0 ) {
      menuState = 0;
      menu.classList.remove( contextMenuActive );
    }
  }

  /**
   * Positions the menu properly.
   * 
   * @param {Object} e The event
   */
  function positionMenu(e) {
    clickCoords = getPosition(e);
    clickCoordsX = clickCoords.x;
    clickCoordsY = clickCoords.y;

    menuWidth = menu.offsetWidth + 4;
    menuHeight = menu.offsetHeight + 4;

    windowWidth = window.innerWidth;
    windowHeight = window.innerHeight;

    if ( (windowWidth - clickCoordsX) < menuWidth ) {
      menu.style.left = windowWidth - menuWidth + "px";
    } else {
      menu.style.left = clickCoordsX + "px";
    }

    if ( (windowHeight - clickCoordsY) < menuHeight ) {
      menu.style.top = windowHeight - menuHeight + "px";
    } else {
      menu.style.top = clickCoordsY + "px";
    }
  }

  /**
   * Dummy action function that logs an action when a menu item link is clicked
   * 
   * @param {HTMLElement} link The link that was clicked
   */
  function menuItemListener( link ) {
    console.log( "Task ID - " + taskItemInContext.getAttribute("data-id") + ", Task action - " + link.getAttribute("data-action"));
    toggleMenuOff();
  }

  /**
   * Run the app.
   */
  init();

})();
//////////zeeshan context menu
});
function hideshow(id,act,logic){
	if(act == 'hide'){
		jQuery('#'+id).hide();
		if(logic == '1'){
			jQuery('#hide1').hide();
			jQuery('#show1').show();
		}else if(logic == '2'){
			jQuery('#hide2').hide();
			jQuery('#show2').show();
		}
	}else if(act == 'show'){
		jQuery('#'+id).show();
		if(logic == '1'){
			jQuery('#hide1').show();
			jQuery('#show1').hide();
		}else if(logic == '2'){
			jQuery('#hide2').show();
			jQuery('#show2').hide();
		}
	}
}
</script>
<?php 
	$unassign_orders = array();
	//$unassign_orders = $db->select(array('*'),PREFIX."justeat_orders","order_date='$date' and status = 1");
	$table = array("justeat_orders","phone_orders","lazymeal_orders","prescheduled_orders","request_delivery","grabbit_orders");
	$xby = 1;
	foreach($table as $tb){
		if($tb == 'grabbit_orders'){
			$result = $db->select(array('*'),$tb,"order_date = '$date' and (status = 1 or status = 2 or status = 0 or status IS NULL)",'',"status asc");
			if($result){
				foreach($result as $r){
					$r->table = $tb;
					$unassign_orders[$xby] = $r;
					$xby ++;
				}
			}
			
		}else{
			$result = $db->select(array('*'),PREFIX.$tb,"order_date = '$date' and (status = 1 or status = 2 or status = 4 or status = 0 or status IS NULL)",'',"status asc");
			if($result){
				foreach($result as $r){
					$r->table = PREFIX.$tb;
					$unassign_orders[$xby] = $r;
					$xby ++;
				}
			}
		}
	}
	//echo '<pre>';
	//print_r($unassign_orders); exit;
	//foreach($unassign_orders as $uno){ echo '<pre>'; print_r($uno); } exit;
?>
<script language="javascript" type="text/javascript">
<!--
function popitup(id,url) {
		
	/* newwindow=window.open(url,'Assign Orders','height=400,width=400,top=200,left=1000,toolbar=no');
	if (window.focus) {newwindow.focus()}
	return false; */
}

// -->
</script>
<style>
@import url(http://fonts.googleapis.com/css?family=Roboto:400,300);

*,
*::before,
*::after {
  box-sizing: border-box;
}



.task {
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: solid 1px #dfdfdf;
}

.task:last-child {
  border-bottom: none;
}

/* context menu */

.context-menu {
  display: none;
  position: absolute;
  z-index: 10;
  padding: 12px 0;
  width: 240px;
  background-color: #fff;
  border: solid 1px #dfdfdf;
  box-shadow: 1px 1px 2px #cfcfcf;
}

.context-menu--active {
  display: block;
}

.context-menu__items {
  list-style: none;
  margin: 0;
  padding: 0;
}

.context-menu__item {
  display: block;
  margin-bottom: 4px;
}

.context-menu__item:last-child {
  margin-bottom: 0;
}

.context-menu__link {
  display: block;
  padding: 4px 12px;
  text-decoration: none;
}

.context-menu__link:hover {
  color: #fff;
  background-color: #0066aa;
}
</style>
<?php //echo '<pre>'; print_r($unassign_orders); exit; ?>
	<div style = "float:left;clear:left; height:100%; width:100%">
		<div style = "float:left;width:80%;margin-left:-20px;margin-top:-10px;">
			<div id="map_canvas" style="height:42em;margin:0px;padding:0px;"></div>
		</div>
		<div style = "float:left;width:20%;">
			<div style = "width:100%;min-width:175px;" >
				<div style = "float:left;margin-left:8%;width: 82%;margin-bottom:10px;">Orders</div>
				<div style = "float:left;width:10%" id = "hide1"><input onclick = "hideshow('hideshow1','hide','1')" style = "font-size: 11px; background-color: #59616b; border: medium none;" type = "button" value = "Hide" /></div>
				<div style = "float:left;display:none;width:10%" id = "show1"><input onclick = "hideshow('hideshow1','show','1')" style = "font-size: 9px; background-color: #59616b; border: medium none;" type = "button" value = "Show" /></div>
			</div>
			<div id = "hideshow1" style = "max-height: 245px;overflow-y: scroll;float:left; clear:left;Padding:5px;width:100%;min-width:175px;"><?php
			if($unassign_orders){
				$order_array = array();
				$old_order_count = count($unassign_orders); ?>
				<script> old_order_count = <?php echo $old_order_count; ?></script><?php
				foreach($unassign_orders as $uno){ 
						$addresses = get_pick_drop_address($uno);
						//print_r($addresses);
						if(empty($addresses['Restaurant Address']) && empty($addresses['Restaurant Name'])) continue;
						if(empty($addresses['Customer Address']) && empty($addresses['Customer Name'])) continue;
						$user_name = driver_name($uno->agent_id);
						//if(empty ($user_name)) continue;
						if($uno->status == 1 || $uno->status == 0){
							$src = 'colors/blank001.png';
						}elseif($uno->status == 2){
							$src = 'colors/blank002.png';
						}elseif($uno->status == 4){
							$src = 'colors/blank007.png';
						} 
						if($uno->table == 'grabbit_orders'){
							$uno->id = $uno->order_id;
						}
						$order_array[$uno->id] = $uno->id.'!'.$uno->table;
						//print_r($order_array); 
						if($uno->status == 1 || $uno->status == 0){ if($user_name) continue; ?>
						
							<ul class="tasks">
								<li class="task" id = "d_<?php echo $uno->id; ?>" data-id="3" order-add = "<?php echo $addresses['Restaurant Address']; ?>" order-id = "<?php echo $uno->id; ?>" order-tb = "<?php echo $uno->table; ?>" >
									<div style = "float:left; clear:left;width:100%;" class="task__content">
										<div style = "float:left;width:18%;padding-top: 8px;">
													<img width= "15" src = "<?php echo $src; ?>" />
													 <div class="task__actions">
													<i class="fa fa-eye"></i>
													<i class="fa fa-edit"></i>
													<i class="fa fa-times"></i>
												  </div>
												
										</div>
										<div style = "float:left;width:80%;border-bottom: 3px solid white;margin-bottom:10px;">
											<div style = "float:left;color:white;width:100%;"><?php echo 'Order # '.$uno->order_number; ?></div>
											<div style = "float:left;color:white;width:100%;"><?php echo $addresses['Restaurant Name']; ?></div>
											<div style = "float:left;color:#b4bcc0;font-size:11px;font-whieght:500;width:100%;"><?php echo $addresses['Restaurant Address']; ?></div>
											<div style = "float:left;color:white;width:100%;"><?php echo $addresses['Customer Name']; ?></div>
											<div style = "float:left;color:#b4bcc0;font-size:11px;font-whieght:500;width:100%;"><?php echo $addresses['Customer Address']; ?></div>
											<div style = "float:left;color:#b4bcc0;font-size:11px;font-whieght:500;width:100%;"><?php echo $user_name['first_name'].' '.$user_name['last_name']; ?></div>
											<!--<div style = "float:left;width:100%;border-bottom: 3px solid white;">&nbsp;</div>-->
										</div>
									</div>
								</li>
							</ul><?php 
						}else{ if(empty ($user_name)) continue; ?>
							<ul class="tasks">
								<li class="task" id = "d_<?php echo $uno->id; ?>" data-id="3" order-add = "<?php echo $addresses['Restaurant Address']; ?>" order-id = "<?php echo $uno->id; ?>" order-tb = "<?php echo $uno->table; ?>" >
									<div style = "float:left; clear:left;width:100%;margin-bottom:10px;" class="task__content">
										<div style = "float:left;width:18%;padding-top: 8px;">
											<img width= "15" src = "<?php echo $src; ?>" />
											 <div class="task__actions">
												<i class="fa fa-eye"></i>
												<i class="fa fa-edit"></i>
												<i class="fa fa-times"></i>
											  </div>
										</div>
										<div style = "float:left;width:80%;border-bottom: 3px solid white;">
											<div style = "float:left;color:white;width:100%;"><?php echo  'Order # '.$uno->order_number; ?></div>
											<div style = "float:left;color:white;width:100%;"><?php echo $addresses['Restaurant Name']; ?></div>
											<div style = "float:left;color:#b4bcc0;font-size:11px;font-whieght:500;width:100%;"><?php echo $addresses['Restaurant Address']; ?></div>
											<div style = "float:left;color:white;width:100%;"><?php echo $addresses['Customer Name']; ?></div>
											<div style = "float:left;color:#b4bcc0;font-size:11px;font-whieght:500;width:100%;"><?php echo $addresses['Customer Address']; ?></div>
											<div style = "float:left;color:#b4bcc0;font-size:11px;font-whieght:500;width:100%;margin-top:10px;"><?php echo $user_name['first_name'].' '.$user_name['last_name']; ?></div>
										</div>
									</div>
								</li>
							</ul><?php
						}
					}
					//print_r($order_array); exit;
				?><script> order_arr_str = '<?php echo implode(",",$order_array); ?>'</script><?php
			}else{
				?><div style = "float:left; clear:left;height: 215px;width:100%;Padding:21px;">No Found Orders</div><?php
			}  ?>
			</div>
			<div style = "width:100%;min-width:175px;">
				<div style = "float:left;margin-top:20px;margin-left:8%;width: 82%;margin-bottom:10px;clear:left;padding-left;:5px;">Drivers</div>
				<div style = "float:left;margin-top:20px;width: 10%;" id = "hide2"><input onclick = "hideshow('hideshow2','hide','2')" style = "font-size: 11px; background-color: #59616b; border: medium none;" type = "button" value = "Hide" /></div>
				<div style = "float:left;margin-top:20px;display:none;width: 10%;" id = "show2"><input onclick = "hideshow('hideshow2','show','2')" style = "font-size: 9px; background-color: #59616b; border: medium none;" type = "button" value = "Show" /></div>
			</div>
			<?php  
				$online_drivers = array();
				//echo '<pre>';
				////////// show free online drivers start
				foreach($res_all_cat as $fd){
					if($fd->status == 0) continue;
					$online_drivers[$fd->id_profile_driver] = 'online';
				}
				////////// show free online drivers end
				////////// show busy assign drivers start
				$main_arr = array();
				foreach($res_all_cat as $fd){
					if($fd->status == 0) continue;
					$date = date("Y-m-d");
					$a = array();
					foreach($table as $tb){
						if($tb == 'grabbit_orders'){
							$chk_assign_orders = $db->select(array('*'),$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and  status = 2 "); 
						}else{
							$chk_assign_orders = $db->select(array('*'),PREFIX.$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and  status = 2 "); 
						}
						foreach($chk_assign_orders as $cao){
							//if(!empty($cao->order_number)) $a[$cao->order_number] = $cao->order_number;
							if($cao->status == 2){
								$sat = 'Assigned';
							}elseif($cao->status == 4){
								$sat = 'Picked';
							}
							if(!empty($cao->order_number)) $a[$cao->order_number] = $cao->order_number.' - '.$sat;
						}
						$main_arr[$fd->id_profile_driver] = $a;
					}
					if(empty($chk_assign_orders)) continue;
					$online_drivers[$fd->id_profile_driver] = 'oissigned'; 
				}
				////////// show busy assign drivers end
				////////// show busy pickedup drivers start
				foreach($res_all_cat as $fd){
					if($fd->status == 0) continue;
					$date = date("Y-m-d");
					//$chk_assign_orders = $db->selectSRow(array('*'),PREFIX."justeat_orders","order_date='$date' and agent_id =$fd->id and (status = 2 or status = 4 or status = 5)");
					foreach($table as $tb){
						if($tb == 'grabbit_orders'){
							$chk_assign_orders = $db->selectSRow(array('*'),$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and (status = 4)");
						}else{
							$chk_assign_orders = $db->selectSRow(array('*'),PREFIX.$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and (status = 4 )");
						}
						if(!empty($chk_assign_orders)) break;
					}
					if(empty($chk_assign_orders)) continue;
					$online_drivers[$fd->id_profile_driver] = 'ogicked'; 
				}
				////////// show busy pickedup drivers end
				////////// show free offline drivers start
				foreach($res_all_cat as $fd){
					if($fd->status == 1) continue;
					$date = date("Y-m-d");
					//$chk_assign_orders = $db->selectSRow(array('*'),PREFIX."justeat_orders","order_date='$date' and agent_id =$fd->id and (status = 2 or status = 4 or status = 5)");
					foreach($table as $tb){
						if($tb == 'grabbit_orders'){
							$chk_assign_orders = $db->selectSRow(array('*'),$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and (status = 2 or status = 4 or status = 1)");
						}else{
							$chk_assign_orders = $db->selectSRow(array('*'),PREFIX.$tb,"order_date= '$date' and agent_id =$fd->id_profile_driver and (status = 2 or status = 4 or status = 1)");
						}
						if(!empty($chk_assign_orders)) break;
					}
					//if($chk_assign_orders) continue;
					$online_drivers[$fd->id_profile_driver] = 'off'; 
				}
				////////// show free offline drivers end ?>
		<!--</div></div>-->
		<?php 
		//rsort($online_drivers);
		////////////// show driver section 
			 if($res_all_cat){ ?>
				<div id = "hideshow2" style = "max-height: 245px;overflow-y: scroll;float:left; clear:left;padding:5px;width:100%;min-width:175px;"><?php
				foreach($res_all_cat as $fd){
					if($online_drivers[$fd->id_profile_driver] == 'online' && empty($main_arr[$fd->id_profile_driver])){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/online.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo "Online , No tasks"; ?></div>
							</div>
						</div> <?php
					}
				}
				foreach($res_all_cat as $fd){
					if(!empty($main_arr[$fd->id_profile_driver])){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/online.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo implode(" ssss- ",$main_arr[$fd->id_profile_driver]); ?></div>
							</div>
						</div> <?php
					}
				}
				foreach($res_all_cat as $fd){
					if($online_drivers[$fd->id_profile_driver] == 'ogicked' && empty($main_arr[$fd->id_profile_driver])){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/online.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo "PickedUp"; ?></div>
							</div>
						</div> <?php
					}
				}
				foreach($res_all_cat as $fd){
					if($online_drivers[$fd->id_profile_driver] == 'ohssigned'){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/online.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo "Unassigned , No tasks"; ?></div>
							</div>
						</div> <?php
					}
				}
				foreach($res_all_cat as $fd){
					if($online_drivers[$fd->id_profile_driver] == 'off'){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/offline.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo "Offline , No tasks"; ?></div>
							</div>
						</div> <?php
					}
				} ?>
				</div><?php
			}else{ ?>
				<div style = "float:left; clear:left;height: 210px;padding:5px;width:100%;">No Found Drivers</div><?php
			}    ?>
	</div>
	<?php
	////////////// show driver section
	/* $table = array("justeat_orders","phone_orders","lazymeal_orders","prescheduled_orders","request_delivery","grabbit_orders");
	$date = date('Y-m-d');
	$all_rec=array("*");
	$table1 = PREFIX."profile_drivers";
	$res_all_cat = $db->select($all_rec,$table1); 
	foreach($table as $tb){
		if($tb == 'grabbit_orders'){
			$chk_assign_orders = $db->select(array('*'),$tb,"order_date = '$date' and (status = 1 or status = 0 or status = 2 or status = 4)",'',"status asc");
		}else{
			$chk_assign_orders = $db->select(array('*'),PREFIX.$tb,"order_date = '$date' and (status = 1 or status = 0 or status = 2 or status = 4)",'',"status asc");;
			$tb = PREFIX.$tb;
		}
		foreach($chk_assign_orders as $abc_ord){
			//print_r($abc_ord);
			$abc_ord->table=$tb;
			foreach($res_all_cat as $fd){ 
				$addresses1 = get_pick_drop_address($abc_ord);
				if(!empty($addresses1['Restaurant Address'])){
					$address = $addresses1['Restaurant Address'];
					$address = str_ireplace(" ","+",$address);
					$url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=$mapkey";
					//echo $url;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$response = curl_exec($ch);
					curl_close($ch);
					//print_r($response);
					$response_a = json_decode($response);
					$lat = $response_a->results[0]->geometry->location->lat;
					$long = $response_a->results[0]->geometry->location->lng; 
					if(!empty($fd->latitude) && !empty($fd->langitude)){ 
					//echo $abc_ord->id.'dl'.$fd->latitude.'dlo'.$fd->langitude.'cl'.$lat.'clo'.$long.'oid';
						?><script>
							var p1 = new google.maps.LatLng(<?php echo $lat.', '.$long ?>);
							var p2 = new google.maps.LatLng(<?php echo $fd->latitude.','.$fd->langitude ?>);
							var distance = calcDistance(p1, p2);
							//alert(distance);
							arr_dist['<?php echo $abc_ord->id.'-'.$fd->id_profile_driver; ?>']= distance;
							arr_dir['<?php echo $fd->id_profile_driver;?>'] = <?php echo $fd->id_profile_driver;?>
						</script><?php
					}
				}  
			}
		}
	}  */
	?>
		<nav id="context-menu1" class="context-menu">
			<ul class="context-menu__items">
				<li class="context-menu__item">
					<?php //require_once("assign.php"); ?>
				</li>
			</ul>
		</nav>
	<?php
	function get_merchant_profile($merchant_id){
		$sql = "select name,address,phone,note,company from daily_merchants where id=$merchant_id";
		$r = mysql_query($sql);
		return mysql_fetch_object($r);
	}
	function driver_name($driver_id){
		$q = mysql_query("SELECT `first_name`,`last_name` FROM `daily_profile_drivers` WHERE `id_profile_driver` = $driver_id");
		return mysql_fetch_assoc($q);
	}
	function get_pick_drop_address($order_object){
		$new_array = array();
		if(($order_object->table == 'daily_justeat_orders') || ($order_object->table == 'daily_lazymeal_orders')){
			$new_array['Order Time'] = $order_object->order_time;
			$new_array['Order No'] = $order_object->order_number;
			if($order_object->merchant_reference){
				$merchant_profile = get_merchant_profile($order_object->merchant_reference);
				if($merchant_profile->company){
					$new_array['Restaurant Name'] = $merchant_profile->company;
				}else{
					$new_array['Restaurant Name'] = $merchant_profile->name;
				}
				$new_array['Restaurant Address'] = $merchant_profile->address;
				$new_array['Merchant Phone'] = $merchant_profile->phone;
			}else{
				$new_array['Restaurant Name'] = $order_object->restaurant_name;
				$new_array['Restaurant Address'] = $order_object->restaurant_address;
			}
			if($order_object->customer_id){
				$customer_profile = get_customer_profile($order_object->customer_id);
				$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
				$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
				$new_array['Customer Phone'] = $customer_profile->phone;
			}else{
				$new_array['Customer Name'] = $order_object->customer_name;
				$new_array['Customer Address'] = $order_object->delivery_address;
				$new_array['Customer Phone'] = $order_object->phone_number;
			}
		}elseif($order_object->table == 'grabbit_orders'){
			$new_array['Order Time'] = $order_object->order_time;
			$new_array['Order No'] = $order_object->order_number;
			if($order_object->merchant_reference){
				$merchant_profile = get_merchant_profile($order_object->merchant_reference);
				if($merchant_profile->company){
					$new_array['Restaurant Name'] = $merchant_profile->company;
				}else{
					$new_array['Restaurant Name'] = $merchant_profile->name;
				}
				$new_array['Restaurant Address'] = $merchant_profile->address;
				$new_array['Merchant Phone'] = $merchant_profile->phone;
			}else{
				$new_array['Restaurant Name'] = $order_object->restaurant_name;
				$new_array['Restaurant Address'] = $order_object->pickup_location;;
			}
			if($order_object->user_id){
				$customer_profile = get_customer_profile($order_object->user_id);
				$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
				$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
				$new_array['Customer Phone'] = $customer_profile->phone;
			}else{
				$new_array['Customer Name'] = $order_object->customer_name;
				$new_array['Customer Address'] = $order_object->delivery_address;
				$new_array['Customer Phone'] = $order_object->phone_number;
			}
		}elseif($order_object->table == 'daily_prescheduled_orders'){
			$new_array['Order Time'] = $order_object->order_time;
			$new_array['Order No'] = $order_object->order_number;
			if($order_object->merchant_reference){
				$merchant_profile = get_merchant_profile($order_object->merchant_reference);
				if($merchant_profile->company){
					$new_array['Restaurant Name'] = $merchant_profile->company;
				}else{
					$new_array['Restaurant Name'] = $merchant_profile->name;
				}
				$new_array['Restaurant Address'] = $merchant_profile->address;
				$new_array['Merchant Phone'] = $merchant_profile->phone;
			}else{
				$new_array['Restaurant Name'] = $order_object->store_name;
				$new_array['Restaurant Address'] = $order_object->restaurant_address;
			}
			if($order_object->customer_reference){
				$customer_profile = get_customer_profile($order_object->customer_reference);
				$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
				$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
				$new_array['Customer Phone'] = $customer_profile->phone;
			}else{
				$new_array['Customer Name'] = $order_object->customer_name;
				$new_array['Customer Address'] = $order_object->customer_address;
				$new_array['Customer Phone'] = $order_object->phone_number;
			}
		}elseif($order_object->table == 'daily_phone_orders'){
			$new_array['Order Time'] = $order_object->order_time;
			$new_array['Order No'] = $order_object->order_number;
			if($order_object->account){
				$merchant_profile = get_merchant_profile($order_object->account);
				if($merchant_profile->company){
					$new_array['Restaurant Name'] = $merchant_profile->company;
				}else{
					$new_array['Restaurant Name'] = $merchant_profile->name;
				}
				$new_array['Restaurant Address'] = $merchant_profile->address;
				$new_array['Merchant Phone'] = $merchant_profile->phone;
			}else{
				$new_array['Restaurant Name'] = $order_object->restaurant_name;
				$new_array['Restaurant Address'] = $order_object->restaurant_address;
			}
			$new_array['Customer Name'] = 'N/A';
			$new_array['Customer Address'] = 'N/A';
		}elseif($order_object->table == 'daily_request_delivery'){
			$new_array['Order Time'] = $order_object->order_time;
			$new_array['Order No'] = $order_object->order_number;
			$new_array['Restaurant Address'] = $order_object->pickup_address;
			$new_array['Restaurant Name'] = $order_object->pickup_location;
			$new_array['Restaurant Phone'] = $order_object->pickup_phone;
			$new_array['Customer Name'] = $order_object->name;
			$new_array['Customer Address'] = $order_object->drop_location.' '.$order_object->drop_postal_code;
			$new_array['Customer Phone'] = $order_object->phone;
		}
		return $new_array;
	}
	function get_customer_profile($customer_id){
		$r = mysql_query("select firstname,lastname,phone,unit,buzzer,address,postal_code,note from daily_customers_nonpreorder where id=$customer_id");
		return mysql_fetch_object($r);
	}
	
	
	