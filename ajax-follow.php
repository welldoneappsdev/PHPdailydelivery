<?php require_once("../common/config.php");
	require_once("../common/class.phpmailer.php");

	require_once("../common/resize.php");
$date = date("Y-m-d");
$unassign_orders = array();
$table = array("justeat_orders","phone_orders","lazymeal_orders","prescheduled_orders","request_delivery","grabbit_orders");
$mapkey = "AIzaSyA3i3b3N5yI70O-JR-HlqeeeYkPZU1PuTk";
//$mapkey = "AIzaSyCKBuiS8793x9Bc8hD5-XjsLUOHQuW2w8g";
$all_rec=array("*");
$table1 = PREFIX."profile_drivers";
$res_all_cat = $db->select($all_rec,$table1);

if($_POST['action'] == "loc_dr"){
 	$ac = array(); 
	foreach($res_all_cat as $driver_data){
		if($driver_data->status != 1) continue;
		$address = $data->results[0]->address_components[2]->long_name.','.$data->results[0]->formatted_address;
		$ac[$driver_data->id_profile_driver]->latitude = $driver_data->latitude;
		$ac[$driver_data->id_profile_driver]->langitude = $driver_data->langitude;
		$ac[$driver_data->id_profile_driver]->name = $driver_data->first_name.' '.$driver_data->last_name;
		$ac[$driver_data->id_profile_driver]->address = '';
		$ac[$driver_data->id_profile_driver]->dph = $driver_data->phone;
	}
	$x = 0;
	$jr = array();
	$color_code = array();
	$driver_info = array();
	if(! empty($ac)){
		foreach($ac as $coa){
			$jr[$x] =  '"'.$coa->address.'"!'.$coa->latitude.'!'.$coa->langitude;
			//$jr[$x] =  $coa->address.'='.'0';
			$color_code[$x] = '0zeeshan'; 
			$div = '<div class="info_content"><h4>Name: '.$coa->name.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h4>(ph: '.$coa->dph.')<p>'.$coa->address.'</p></div>';
			$driver_info[$x] = $div;
			$x++;
		}
		$ret = array('mark' => implode("=",$jr),'color'=>implode(",",$color_code),'info_window'=>implode("!",$driver_info));
		echo json_encode($ret);
		exit;
		//echo implode("=",$jr); exit;
	}else{
		echo 'error'; exit;
	} 
}
if($_POST['action'] == "drivers_list") { 
ob_clean();
foreach($res_all_cat as $fd){ 
		if($fd->status == 0) continue;
		$x++; ?>
		<div style = "flaot:left;clear:left;width:100%">
			<form method = "post" action = "" >
				<div style = "float:left;width:5%;padding-top:5px;"><?php 
						if($fd->status == 1){
							?><img width="15" src = "images/online.png" /><?php
						} ?>
				</div>
				<div style = "float:left;width:70%" class="context-menu__link" data-action="View" >
					<div style = "width:50%;float:left; " ><?php if(!empty($fd->first_name)){echo $fd->first_name; }else{ echo $fd->last_name;}?></div>
					<div style = "width:50%; float:left; font-size:11px;" id="dis_<?php echo $fd->id_profile_driver; ?>" >(00.00 KM)</div>
				</div>
				<input type = "hidden" name = "wl_driver" value = "<?php echo $fd->id_profile_driver; ?>" />
				<input type = "hidden" name = "order_id" class = "id3" value = "" />
				<input type = "hidden" name = "order_tb" class = "tb3" value = "" />
				<div style = "float:left;width:20%;"><input type = "submit"  name = "assign" value = "Set" /></div>
			</form>
		</div><?php
	} 

	foreach($res_all_cat as $fd){ 
		if($fd->status == 1) continue;
		$x++; ?>
		<div style = "flaot:left;clear:left;width:100%">
			<form method = "post" action = "" >
				<div style = "float:left;width:5%;padding-top:1px;"><?php 
						if($fd->status == 0){
							?><img width="15" src = "images/offline.png" /><?php
						} ?>
				</div>
				<div style = "float:left;width:70%" class="context-menu__link" data-action="View" >
					<div style = "width:50%;float:left; " ><?php if(!empty($fd->first_name)){echo $fd->first_name; }else{ echo $fd->last_name;}?></div>
					<div style = "width:50%; float:left; font-size:11px;" id="dis_<?php echo $fd->id_profile_driver; ?>" >(00.00 KM)</div>
				</div>
				<input type = "hidden" name = "wl_driver" value = "<?php echo $fd->id_profile_driver; ?>" />
				<input type = "hidden" name = "order_id" class = "id3" value = "" />
				<input type = "hidden" name = "order_tb" class = "tb3" value = "" />
				<div style = "float:left;margin-right:10px;width:20%;"><input type = "submit"  name = "assign" value = "Set" /></div>
			</form>
		</div><?php
	}
	$htm = ob_get_clean();
	echo $htm; exit;	
	
 }
if($_POST['action'] == "drivers_list_build") {
	require_once("assign.php");
}
if($_POST['action'] == "new_create_marker") {
	$old_order_array = explode(',',$_POST['old_str']); 
	$new_order_array = explode(',',$_POST['new_str']); 
	$old_order_number_arr  = array();
	$new_order_number_arr  = array();
	$new_tbl_arr  = array();
	foreach($old_order_array as $ooa){
		$explod_arr = explode('!',$ooa);
		$old_order_number_arr[$explod_arr[0]] = $explod_arr[0];
	}
	$x = 0;
	foreach($new_order_array as $noa){
		$explod_arr = explode('!',$noa);
		$new_order_number_arr[$x] = $explod_arr[0];
		$new_tbl_arr[$x] = $explod_arr[1];
		$x++;
	}
	foreach($new_order_number_arr as $key=> $nn){
		if (! in_array($nn, $old_order_number_arr)) {
			$all_rec=array("*");
			if($new_tbl_arr[$key] == 'grabbit_orders'){
				$where = 'order_id ='.$nn;
			}else{
				$where = 'id ='.$nn;
			}
			$where = 'id ='.$nn;
			$chk_assign_orders = $db->select($all_rec,$new_tbl_arr[$key],$where);
			foreach($chk_assign_orders as $ord){
				$array_coordinate = array();
				$ord->table = $new_tbl_arr[$key];
				if($new_tbl_arr[$key] == 'grabbit_orders'){
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
					$array_coordinate[0]->status = $ord->status;
					$array_coordinate[0]->address = $addresspick1;
					$array_coordinate[0]->rphone = $addressesall['Merchant Phone'];
					$array_coordinate[0]->orderno = $addressesall['Order No'];
					$array_coordinate[0]->otime = $addressesall['Order Time'];
					$array_coordinate[0]->dname = $driver_name;
					$array_coordinate[0]->dph = $driver_ph;
					$array_coordinate[0]->id = $ord->id;
					$array_coordinate[0]->tbl = $new_tbl_arr[$key];
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
					$array_coordinate[1]->address = $addressdrop1;
					$array_coordinate[1]->rphone = $addressesall['Customer Phone'];
					$array_coordinate[1]->orderno = $addressesall['Order No'];
					$array_coordinate[1]->otime = $addressesall['Order Time'];
					$array_coordinate[1]->dname = $driver_name;
					$array_coordinate[1]->dph = $driver_ph;
					$array_coordinate[1]->status = 5;
					$array_coordinate[1]->id = $ord->id;
					$array_coordinate[1]->tbl = $new_tbl_arr[$key];
				}
				$new_array[$x] = $array_coordinate;
				$x++;
					
			}
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
		}
	}
	$str = implode("=",$js_arr);
	$st_color = implode(",",$color);
	$infowinadd = implode("!",$infoaddr);
	$ret = array('mark' => $str,'color'=>$st_color,'info_window'=>$infowinadd);
	echo json_encode($ret);
	exit;
}
if($_POST['action'] == "orders") {
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
		//print_r($unassign_orders); exit;
		if(empty($unassign_orders)){
			ob_clean(); ?>
			<div style = "float:left; clear:left;height: 215px;width:100%;Padding:21px;">No Found Orders</div><?php 
			$htm = ob_get_clean();
			$ret = array('html'=>$htm,'ocnt'=>0,'order_arr'=>'');
			echo json_encode($ret);
			exit;
		}
		ob_clean();
		$order_count = count($unassign_orders);
		$order_array = array();
		foreach($unassign_orders as $uno){
			$addresses = get_pick_drop_address($uno);
			if(empty($addresses['Restaurant Address']) && empty($addresses['Restaurant Name'])) continue;
			if(empty($addresses['Customer Address']) && empty($addresses['Customer Name'])) continue;
			$user_name = driver_name($uno->agent_id);
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
			if($uno->status == 1 || $uno->status == 0){ if($user_name) continue; ?>
					<ul class="tasks">
						<li class="task" id = "d_<?php echo $uno->id; ?>" order-add = "<?php echo $addresses['Restaurant Address']; ?>" data-id="3" order-id = "<?php echo $uno->id; ?>" order-tb = "<?php echo $uno->table; ?>" >
							<div style = "float:left; clear:left;width:100%;" class="task__content">
								<div style = "float:left;width:18%;padding-top: 8px;">
											<img id = "e_<?php echo $uno->id; ?>" width= "15" src = "<?php echo $src; ?>" />
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
									<img id = "e_<?php echo $uno->id; ?>" width= "15" src = "<?php echo $src; ?>" />
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
	$htm = ob_get_clean();
	$ret = array('html'=>$htm,'ocnt'=>$order_count,'order_arr' => implode(",",$order_array));
	echo json_encode($ret);
	exit;	
}
	
if($_POST['action'] == "drivers") {
	$online_drivers = array();
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
							$chk_assign_orders = $db->select(array('*'),$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and  (status = 2 or status = 4) "); 
						}else{
							$chk_assign_orders = $db->select(array('*'),PREFIX.$tb,"order_date = '$date' and agent_id =$fd->id_profile_driver and  (status = 2 or status = 4)"); 
						}
						foreach($chk_assign_orders as $cao){
							//if(!empty($cao->order_number)) $a[$cao->order_number] = $cao->order_number;
							if($cao->status == 2){
								$sat = 'Assigned';
								$online_drivers[$fd->id_profile_driver] = 'oissigned';
							}elseif($cao->status == 4){
								$sat = 'Picked';
								$online_drivers[$fd->id_profile_driver] = 'ogicked';
							}
							if(!empty($cao->order_number)) $a[$cao->order_number] = $cao->order_number.' - '.$sat;
						}
						$main_arr[$fd->id_profile_driver] = $a;
					}
					if(empty($chk_assign_orders)) continue; 
				}
				//echo implode(" - ",$main_arr[$fd->id_profile_driver]); exit; 
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
				////////// show free offline drivers end
				ob_clean();
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
					if(!empty($main_arr[$fd->id_profile_driver]) && $online_drivers[$fd->id_profile_driver] == 'oissigned'){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/online.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo implode(" - ",$main_arr[$fd->id_profile_driver]); ?></div>
							</div>
						</div> <?php
					}
				}
				foreach($res_all_cat as $fd){
					if($online_drivers[$fd->id_profile_driver] == 'ogicked'){ ?>
						<div style = "float:left; clear:left;width:100%;">
							<div style = "float:left;width:18%;padding: 10px;"><img width="15" src = "images/online.png" /></div>
							<div style = "float:left;width:80%;">
								<div style = "float:left;clear:left;color:white;"><?php echo $fd->first_name.' '.$fd->last_name; ?></div>
								<div style = "float:left;clear:left;color:#b4bcc0;font-size:11px;font-whieght:500;"><?php echo implode(" - ",$main_arr[$fd->id_profile_driver]); ?></div>
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
				}
				$htm = ob_get_clean();
				echo $htm; exit;
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
	function get_merchant_profile($merchant_id){
		$sql = "select name,address,phone,note,company from daily_merchants where id=$merchant_id";
		$r = mysql_query($sql);
		return mysql_fetch_object($r);
	}