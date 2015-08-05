<?php
//@ini_set("display_errors",1);
//@ini_set("log_errors", 1);
include_once('classes/connection.php');
	
	if(isset($_GET['case'])){
		$case = $_GET['case'];
	}
	/////////////////
	if($case == 'all_select'){
		$all_result = array();
		$id = $_GET['id'];
		$table = array("daily_justeat_orders","daily_phone_orders","daily_lazymeal_orders","daily_prescheduled_orders","daily_request_delivery","grabbit_orders");
		$retField= array('*');
		foreach($table as $tb){
			//$res = select($retField, $tb, "agent_id=$id and status != 5 and status != 7",'',"order_time DESC");
			$res = select($retField, $tb, "agent_id=$id",'',"order_time DESC");
			if(empty($res)) continue;
			$ret_res = data_fill_array($tb,$res);
			$all_result[$tb] = $ret_res;
		}
		if($all_result){
			echo '{"result":' .json_encode($all_result).'}';
			mysql_close();
		}else{
			$all_result['error'] = "No order found";
			echo '{"result":' .json_encode($all_result).'}';
			mysql_close();
		}
	}
	////////////////
		/////////////////
	if($case == 'all_select_andriod'){
		$all_result = array();
		$id = $_GET['id'];
		$table = array("daily_justeat_orders","daily_phone_orders","daily_lazymeal_orders","daily_prescheduled_orders","daily_request_delivery","grabbit_orders");
		$retField= array('*');
		foreach($table as $tb){
			//$res = select($retField, $tb, "agent_id=$id and status != 5 and status != 7",'',"order_time DESC");
			$res = select($retField, $tb, "agent_id=$id",'',"order_time DESC");
			if(empty($res)) continue;
			$ret_res = data_fill_array_andriod($tb,$res);
			$all_result[$tb] = $ret_res;
		}
		if($all_result){
			echo '{"result":' .json_encode($all_result).'}';
			mysql_close();
		}else{
			$all_result['error'] = "No order found";
			echo '{"result":' .json_encode($all_result).'}';
			mysql_close();
		}
	}
	////////////////
	if($case == 'history'){
		$all_result = array();
		$id = $_GET['id'];
		$table = array("daily_justeat_orders","daily_phone_orders","daily_lazymeal_orders","daily_prescheduled_orders","daily_request_delivery","grabbit_orders");
		$retField= array('*');
		foreach($table as $tb){
			$res = select($retField, $tb, "agent_id=$id and (status = 5 or status = 7)",'',"order_time DESC");
			if(empty($res)) continue;
			$ret_res = data_fill_array($tb,$res);
			$all_result[$tb] = $ret_res;
		}
		if($all_result){
			echo '{"result":' .json_encode($all_result).'}';
			mysql_close();
		}else{
			$all_result[] = "No order found";
			echo '{"result":' .json_encode($all_result).'}';
			mysql_close();
		}
	}
	///////////////
	
	if($case == 'update_location'){
		$table_name = $_GET['table'];
		$langitude = $_GET['langitude'];
		$latitude = $_GET['latitude'];
		$id = $_GET['id'];
		if($table_name == 'daily_profile_drivers'){
			$query = "UPDATE `daily_profile_drivers` SET `langitude`=$langitude,`latitude`=$latitude WHERE `id_profile_driver` = $id";
			$result = mysql_query($query);
			$json_output[] = $result;
			echo '{"success":' .json_encode($json_output).'}';
		}
	}
    
    if($case == 'update'){
        $table_name = $_GET['table'];
        $status = $_GET['status'];
        $row_id = $_GET['row_id'];
       // $order_id = $_GET['order_id'];
		//$time = date('H:i:s');
		$time = date('H:i:s',strtotime($_GET['datet']));
		if($status == 4){
			$field = ",pickup_time='".$time."'";
		}elseif($status == 5){
			$field = ",drop_off_time='".$time."'";
		}
        if($table_name == 'daily_justeat_orders'){
            $query = "update `daily_justeat_orders` set status=$status $field where id = $row_id";
            $result = mysql_query($query);
            $json_output[] = $result;
           // echo '{"success":' .json_encode($json_output).'}';
        }elseif($table_name == 'grabbit_orders'){
			$query = "update `grabbit_orders` set status=$status $field where order_id = $row_id";
            $result = mysql_query($query);
            $json_output[] = $result;
           // echo '{"success":' .json_encode($json_output).'}';
		}elseif($table_name == 'daily_request_delivery'){
			$query = "update `daily_request_delivery` set status=$status $field where id = $row_id";
            $result = mysql_query($query);
            $json_output[] = $result;
           // echo '{"success":' .json_encode($json_output).'}';
		}elseif($table_name == 'daily_prescheduled_orders'){
			if($status == 4){
				$field = ",pickup_time='".$time."'";
			}elseif($status == 5){
				$field = ",delivery_time='".$time."'";
			}
			$query = "update `daily_prescheduled_orders` set status=$status $field where id = $row_id";
            $result = mysql_query($query);
            $json_output[] = $result;
           // echo '{"success":' .json_encode($json_output).'}';
		}elseif($table_name == 'daily_phone_orders'){
			$query = "update `daily_phone_orders` set status=$status $field where id = $row_id";
            $result = mysql_query($query);
            $json_output[] = $result;
            //echo '{"success":' .json_encode($json_output).'}';
		}elseif($table_name == 'daily_lazymeal_orders'){
			$query = "update `daily_lazymeal_orders` set status=$status $field where id = $row_id";
            $result = mysql_query($query);
            $json_output[] = $result;
            //echo '{"success":' .json_encode($json_output).'}';
		}
		if($result == true){
			logfile($_GET['datet'],$_GET['order_type'],$_GET['driver_id'],$row_id,$status,$_GET['reason']);
		}else{
            echo '{"error":' .json_encode($json_output).'}';
		}
		
    }
	
	if($case == 'login'){
		$new_weldone_array = array();
		 if(isset($_GET['table'])){
			$table_name = $_GET['table'];
			$email = $_GET['email'];
			$pass = md5($_GET['pass']);
			$query = "select * from $table_name where email='$email' AND password = '$pass'" ;
			$q=mysql_query($query);
			$row=mysql_fetch_assoc($q);
			if(isset($row['id_profile_driver'])){
				$vehicle_profile = get_vehicle_profile($row['id_profile_driver']);
				//print_r($vehicle_profile); exit;
				$profile = array_merge($row,$vehicle_profile);
				if($vehicle_profile['id_transportation'] != 0){
					$transportaition_name = get_transportaion($vehicle_profile['id_transportation']);
					$welldone_array = array_merge($profile,$transportaition_name);
					foreach($welldone_array as $key=>$val){
						if(empty($val)) $val = 0;
						$new_weldone_array[$key] = $val;
					}
					$json_output[]=$new_weldone_array;
				}else{
					foreach($profile as $key=>$val){
						if(empty($val)) $val = 0;
						$new_weldone_array[$key] = $val;
					}
					$json_output[]=$new_weldone_array;
				}
				driver_onoffduty($row['id_profile_driver'],0,1);
			}else{
				$json_output[]="Enter Vaild Username or Password";
			}
			echo '{"result":' .json_encode($json_output).'}'; 
			mysql_close();
		} 
	}
	
	if($case == 'logout'){
		driver_onoffduty($_GET['id'],0,0);
		$json_output[]="Now You Are Offduty.";
		echo '{"result":' .json_encode($json_output).'}'; 
	}
	
	if($case == 'offduty'){
		driver_onoffduty($_GET['id'],0,1);
		$json_output[]="Now You Are Offduty.";
		echo '{"result":' .json_encode($json_output).'}'; 
	}
	
	if($case == 'onduty'){
		driver_onoffduty($_GET['id'],1,1);
		$json_output[]="Now You Are Onduty.";
		echo '{"result":' .json_encode($json_output).'}'; 
	}
	
	/* if($case == 'logfile'){
		 if(isset($_GET['table'])){
			$table_name = $_GET['table'];
			$driver_id = $_GET['driver_id'];
			$order_id = $_GET['order_id'];
			$status = $_GET['status'];
			$date = $_GET['datetime'];
			if(empty($date)) $date = date('Y-m-d H:i:s');
			$order_type = $_GET['order_type'];
			$q = mysql_query("SELECT `name`,`last_name` FROM `daily_drivers` WHERE `id` = $driver_id");
			$user_name = mysql_fetch_assoc($q);
			 if($status == 2){
				$message =$user_name['name'].' '.$user_name['last_name'].' is Assigned to '.$order_type.' Order '.$order_id.' on '.$date;
			}elseif($status == 4){
				$field = ",pickup_time='".$date."'";
				$message =$user_name['name'].' '.$user_name['last_name'].' is Picked up to '.$order_type.' Order '.$order_id.' on '.$date;
			}elseif($status == 8){
				$message =$user_name['name'].' '.$user_name['last_name'].' has Confirmed to '.$order_type.' Order '.$order_id.' on '.$date;
			}elseif($status == 5){
				$field = ",drop_off_time='".$date."'";
				$message =$user_name['name'].' '.$user_name['last_name'].' has Completed to '.$order_type.' Order '.$order_id.' on '.$date;
			}elseif($status == 7){
				$reason = $_GET['reason'];
				$message =$user_name['name'].' '.$user_name['last_name'].' has Cancelled to '.$order_type.' Order '.$order_id.' on '.$date.' for the reason of '.$reason;
			}elseif($status == 11){
				$reason = $_GET['reason'];
				$message =$user_name['name'].' '.$user_name['last_name'].' has Cancelled to '.$order_type.' Order '.$order_id.' on '.$date.' for the reason of '.$reason;
			}
			update_order_status($order_id,$status,$field);
			$query_insert = mysql_query("INSERT INTO $table_name SET driver_id='$driver_id', order_number=$order_id, status=$status,message='$message',addDate='$date',updated='$date',sender_id=$driver_id");
			$json_output[]=$query_insert; 
			echo '{"result":' .json_encode($json_output).'}'; 
			mysql_close(); 
		} 
	} */
	
	function logfile($date,$order_type,$driver_id,$order_id,$status,$reason){
		if(empty($date)) $date = date('Y-m-d H:i:s');
		$q = mysql_query("SELECT `first_name`,`last_name` FROM `daily_profile_drivers` WHERE `id_profile_driver` = $driver_id");
		$user_name = mysql_fetch_assoc($q);
		 if($status == 2){
			$message =$user_name['first_name'].' '.$user_name['last_name'].' is Assigned to '.$order_type.' Order '.$order_id.' on '.$date;
		}elseif($status == 4){
			$field = ",pickup_time='".$date."'";
			$message =$user_name['first_name'].' '.$user_name['last_name'].' is Picked up to '.$order_type.' Order '.$order_id.' on '.$date;
		}elseif($status == 8){
			$message =$user_name['first_name'].' '.$user_name['last_name'].' has Confirmed to '.$order_type.' Order '.$order_id.' on '.$date;
		}elseif($status == 5){
			$field = ",drop_off_time='".$date."'";
			$message =$user_name['first_name'].' '.$user_name['last_name'].' has Completed to '.$order_type.' Order '.$order_id.' on '.$date;
		}elseif($status == 7){
			$message =$user_name['first_name'].' '.$user_name['last_name'].' has Cancelled to '.$order_type.' Order '.$order_id.' on '.$date.' for the reason of '.$reason;
		}elseif($status == 11){
			$message =$user_name['first_name'].' '.$user_name['last_name'].' has Rejected to '.$order_type.' Order '.$order_id.' on '.$date.' for the reason of '.$reason;
		}
		//$query_insert = mysql_query("INSERT INTO daily_order_logs SET driver_id='$driver_id', order_number=$order_id, status=$status,message='$message',addDate='$date',updated='$date',sender_id=$driver_id");
		$sql = "INSERT INTO daily_order_logs SET driver_id='$driver_id', order_number=$order_id, status=$status,message='$message',addDate='$date',updated='$date',sender_id=$driver_id";
		//file_put_contents("ip.txt", date('H:i:s').'----------'.$sql, FILE_APPEND);
		$query_insert = mysql_query($sql);
		$json_output[]=$query_insert; 
		echo '{"result":' .json_encode($json_output).'}'; 
		mysql_close(); 
	}
	
	function driver_onoffduty($id,$status,$in_off){
		$query = "update `daily_profile_drivers` set status=$status,in_off=$in_off where id_profile_driver = $id";
        $result = mysql_query($query);
	}
	
	function update_order_status($order_id,$status, $field = ''){
		$query = "update `daily_justeat_orders` set status=$status $field where order_number=$order_id";
        $result = mysql_query($query);
	}
	function get_transportaion($transportaion_id){
		return mysql_fetch_assoc(mysql_query("select name_transportation from daily_transportation where id_transportation=$transportaion_id"));
	}
	
	function get_vehicle_profile($id_profile_driver){
		return mysql_fetch_assoc(mysql_query("select * from daily_profile_drivers_transportation where id_profile_driver=$id_profile_driver"));
	}
	
	function select($retField, $table, $where="", $groupby="", $orderby="", $limit="") {
		//echo "";
		$fields = implode(",", $retField);
		if ($where!="") {
			$q = "select $fields from $table WHERE $where";
		} else {
			$q = "select $fields from $table";
		}
		if ($groupby!="") {
			$q .= " GROUP BY $groupby";
		}
		if ($orderby!="") {
			$q .= " ORDER BY $orderby";
		}
		if ($limit!="") {
			$q .= " LIMIT $limit";
		}
		//echo "$q";
		//$this->sql = $q;
		//$this->log();
		$r = mysql_query($q);
			if($r){
			$num=mysql_num_rows($r);
			//$this->num=mysql_num_rows($r);
			$i=1;
			while ($row=mysql_fetch_object($r)) {
				$cont[$i] = $row;
				$i++;
			}
			if (mysql_num_rows($r)>0) {
				
			//	echo print_r($cont);
			//	exit;
				
				return $cont;
			}
		}else{
			return;
		}
		
	}
	function data_fill_array($tb,$res){
		$return = array();
		if($tb == 'daily_justeat_orders' || $tb == 'daily_lazymeal_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_time;
				}else{
					$new_array['Order time'] = 'ASAP';
				}
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = 0;
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->merchant_reference){
					$merchant_profile = get_merchant_profile($r->merchant_reference);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
					$new_array['Merchant Rate'] = $merchant_profile->use_drive_rate;
				}else{
					$new_array['Restaurant Name'] = $r->restaurant_name;
					$new_array['Restaurant Address'] = $r->restaurant_address;
					$new_array['Merchant Rate'] = 0;
				}
				if($r->customer_id){
					$customer_profile = get_customer_profile($r->customer_id);
					$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
					$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
					$new_array['Customer Phone'] = $customer_profile->phone;
					$new_array['Customer Notes'] = $customer_profile->note;
					$new_array['Customer Unit'] = $customer_profile->unit;
					$new_array['Customer Buzzer'] = $customer_profile->buzzer;
				}else{
					$new_array['Customer Name'] = $r->customer_name;
					$str = $r->delivery_address;
					if(strstr($str, 'UNIT')){
						 $aspk =  explode('UNIT',$str);
						 $new_array['Customer Address'] = $aspk[0];
					}else{
						$new_array['Customer Address'] = $r->delivery_address;
					}
					$new_array['Customer Phone'] = $r->phone_number;
					if($tb == 'daily_lazymeal_orders'){
						$new_array['Customer Unit'] = $r->customer_unit;
						$new_array['Customer Buzzer'] = $r->customer_buzzer;
					}
				}
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = $val;
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'grabbit_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->order_id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_time;
				}else{
					$new_array['Order time'] = $r->order_type;
				}
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = 0;
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->merchant_reference){
					$merchant_profile = get_merchant_profile($r->merchant_reference);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
					$new_array['Merchant Rate'] = $merchant_profile->use_drive_rate;
				}else{
					$new_array['Restaurant Name'] = $r->restaurant_name;
					$new_array['Restaurant Address'] = $r->pickup_location;
					$new_array['Merchant Rate'] = 0;
				}
				if($r->user_id){
					$customer_profile = get_customer_profile($r->user_id);
					$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
					$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
					$new_array['Customer Phone'] = $customer_profile->phone;
					$new_array['Customer Notes'] = $customer_profile->note;
					$new_array['Customer Unit'] = $customer_profile->unit;
					$new_array['Customer Buzzer'] = $customer_profile->buzzer;
				}else{
					$new_array['Customer Name'] = $r->customer_name;
					$new_array['Customer Address'] = $r->delivery_address;
					$new_array['Customer Phone'] = $r->phone_number;
				}
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = $val;
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'daily_prescheduled_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Order time'] = $r->delivery_date.' '.$r->delivery_time;
				$new_array['Merchant Rate'] = $r->payout;
				$new_array['Paid'] = $r->paid;
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = 0;
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->merchant_reference){
					$merchant_profile = get_merchant_profile($r->merchant_reference);
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
				}else{
					$new_array['Restaurant Name'] = $r->store_name;
					$new_array['Restaurant Address'] = $r->restaurant_address;
				}
				if($r->user_id){
					$customer_profile = get_customer_profile($r->user_id);
					$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
					$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
					$new_array['Customer Phone'] = $customer_profile->phone;
					$new_array['Customer Notes'] = $customer_profile->note;
					$new_array['Customer Unit'] = $customer_profile->unit;
					$new_array['Customer Buzzer'] = $customer_profile->buzzer;
				}else{
					$new_array['Customer Name'] = $r->customer_name;
					$new_array['Customer Address'] = $r->customer_address;
					$new_array['Customer Phone'] = $r->phone_number;
				}
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = $val;
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'daily_phone_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Merchant Rate'] = $r->delivery_fee;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_date.' '.$r->delivery_time;
				}else{
					$new_array['Order time'] = $r->order_type;
				}
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = 0;
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->account){
					$merchant_profile = get_merchant_profile($r->account);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
				}else{
					$new_array['Restaurant Name'] = $r->restaurant_name;
					$new_array['Restaurant Address'] = $r->restaurant_address;
				}
				$new_array['Customer Info'] = 'NA';
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = $val;
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'daily_request_delivery'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Merchant Rate'] = $r->delivery_fee;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_time;
				}else{
					$new_array['Order time'] = 'ASAP';
				}
				$new_array['Order Price'] = $r->grand_total;
				$new_array['Paid'] = $r->paid;
				$new_array['Food Cost'] = 0;
				$new_array['Restaurant Address'] = $r->pickup_address;
				$new_array['Restaurant Phone'] = $r->pickup_phone;
				$new_array['Customer Name'] = $r->name;
				$new_array['Customer Address'] = $r->drop_location.' '.$r->drop_postal_code;
				$new_array['Customer Phone'] = $r->phone;
				$new_array['Customer Unit'] = $r->	drop_unit;
				$new_array['Customer Buzzer'] = $r->drop_buzzer;
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = $val;
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}
		return $return;
	}
	
	function data_fill_array_andriod($tb,$res){
		$return = array();
		if($tb == 'daily_justeat_orders' || $tb == 'daily_lazymeal_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_time;
				}else{
					$new_array['Order time'] = 'ASAP';
				}
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = '';
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->merchant_reference){
					$merchant_profile = get_merchant_profile($r->merchant_reference);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
					$new_array['Merchant Rate'] = $merchant_profile->use_drive_rate;
				}else{
					$new_array['Merchant Name'] = $r->restaurant_name;
					$new_array['Merchant Address'] = $r->restaurant_address;
					$new_array['Merchant Phone'] = '';
					$new_array['Merchant Notes'] = '';
					$new_array['Merchant Rate'] = '';
				}
				if($r->customer_id){
					$customer_profile = get_customer_profile($r->customer_id);
					$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
					$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
					$new_array['Customer Phone'] = $customer_profile->phone;
					$new_array['Customer Notes'] = $customer_profile->note;
					$new_array['Customer Unit'] = $customer_profile->unit;
					$new_array['Customer Buzzer'] = $customer_profile->buzzer;
				}else{
					$new_array['Customer Name'] = $r->customer_name;
					$str = $r->delivery_address;
					if(strstr($str, 'UNIT')){
						 $aspk =  explode('UNIT',$str);
						 $new_array['Customer Address'] = $aspk[0];
					}else{
						$new_array['Customer Address'] = $r->delivery_address;
					}
					$new_array['Customer Phone'] = $r->phone_number;
					if($tb == 'daily_lazymeal_orders'){
						$new_array['Customer Unit'] = $r->customer_unit;
						$new_array['Customer Buzzer'] = $r->customer_buzzer;
					}
				}
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = "$val";
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'grabbit_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->order_id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_time;
				}else{
					$new_array['Order time'] = $r->order_type;
				}
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = '';
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->merchant_reference){
					$merchant_profile = get_merchant_profile($r->merchant_reference);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
					$new_array['Merchant Rate'] = $merchant_profile->use_drive_rate;
				}else{
					$new_array['Merchant Name'] = $r->restaurant_name;
					$new_array['Merchant Address'] = $r->pickup_location;
					$new_array['Merchant Phone'] = '';
					$new_array['Merchant Notes'] = '';
					$new_array['Merchant Rate'] = '';
				}
				if($r->user_id){
					$customer_profile = get_customer_profile($r->user_id);
					$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
					$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
					$new_array['Customer Phone'] = $customer_profile->phone;
					$new_array['Customer Notes'] = $customer_profile->note;
					$new_array['Customer Unit'] = $customer_profile->unit;
					$new_array['Customer Buzzer'] = $customer_profile->buzzer;
				}else{
					$new_array['Customer Name'] = $r->customer_name;
					$new_array['Customer Address'] = $r->delivery_address;
					$new_array['Customer Phone'] = $r->phone_number;
				}
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = "$val";
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'daily_prescheduled_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Order time'] = $r->delivery_date.' '.$r->delivery_time;
				$new_array['Merchant Rate'] = $r->payout;
				$new_array['Paid'] = $r->paid;
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = '';
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->merchant_reference){
					$merchant_profile = get_merchant_profile($r->merchant_reference);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
				}else{
					$new_array['Merchant Name'] = $r->store_name;
					$new_array['Merchant Address'] = $r->restaurant_address;
					$new_array['Merchant Phone'] = '';
					$new_array['Merchant Notes'] = '';
				}
				if($r->user_id){
					$customer_profile = get_customer_profile($r->user_id);
					$new_array['Customer Name'] = $customer_profile->firstname.' '.$customer_profile->lastname;
					$new_array['Customer Address'] = $customer_profile->address.' '.$customer_profile->postal_code;
					$new_array['Customer Phone'] = $customer_profile->phone;
					$new_array['Customer Notes'] = $customer_profile->note;
					$new_array['Customer Unit'] = $customer_profile->unit;
					$new_array['Customer Buzzer'] = $customer_profile->buzzer;
				}else{
					$new_array['Customer Name'] = $r->customer_name;
					$new_array['Customer Address'] = $r->customer_address;
					$new_array['Customer Phone'] = $r->phone_number;
				}
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = "$val";
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'daily_phone_orders'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Merchant Rate'] = $r->delivery_fee;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_date.' '.$r->delivery_time;
				}else{
					$new_array['Order time'] = $r->order_type;
				}
				if($r->paid=='YES'){
					$new_array['Order Price'] = $r->total_price;
					$new_array['Food Cost'] = '';
				}else{
					$new_array['Food Cost'] = $r->food_cost;
					$new_array['Order Price'] = $r->total_price;
				}
				if($r->account){
					$merchant_profile = get_merchant_profile($r->account);
					//$new_array['Merchant Name'] = $merchant_profile->name.' '.$merchant_profile->company;
					$new_array['Merchant Name'] = $merchant_profile->company;
					$new_array['Merchant Address'] = $merchant_profile->address;
					$new_array['Merchant Phone'] = $merchant_profile->phone;
					$new_array['Merchant Notes'] = $merchant_profile->note;
				}else{
					$new_array['Merchant Name'] = $r->restaurant_name;
					$new_array['Merchant Address'] = $r->restaurant_address;
					$new_array['Merchant Phone'] = '';
					$new_array['Merchant Notes'] = '';
				}
				$new_array['Customer Info'] = 'NA';
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = "$val";
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}elseif($tb == 'daily_request_delivery'){
			$x = 0;
			foreach($res as $r){
				$new_array = array();
				$new_array['Order id'] = $r->id;
				$new_array['Order no'] = $r->order_number;
				$new_array['Order status'] = $r->status;
				$new_array['Merchant Rate'] = $r->delivery_fee;
				$new_array['Paid'] = $r->paid;
				if($r->order_type=='GIVEN_TIMEFRAME'){
					$new_array['Order time'] = $r->delivery_time;
				}else{
					$new_array['Order time'] = 'ASAP';
				}
				$new_array['Order Price'] = $r->grand_total;
				$new_array['Food Cost'] = '';
				$new_array['Merchant Address'] = $r->pickup_address;
				$new_array['Merchant Phone'] = $r->pickup_phone;
				$new_array['Customer Name'] = $r->name;
				$new_array['Customer Address'] = $r->drop_location.' '.$r->drop_postal_code;
				$new_array['Customer Phone'] = $r->phone;
				$new_array['Customer Unit'] = $r->	drop_unit;
				$new_array['Customer Buzzer'] = $r->drop_buzzer;
				$new_array['Table Name'] = $tb;
				$new_weldone_array = array();
				foreach($new_array as $key=>$val){
					if(empty($val)) $val=0;
					$new_weldone_array[$key] = "$val";
				}
				$return[$x] = $new_weldone_array;
				$x++;
			}
		}
		return $return;
	}
	
	function get_merchant_profile($merchant_id){
		$r = mysql_query("select name,address,phone,note,company from daily_merchants where id=$merchant_id");
		return mysql_fetch_object($r);
	}
	
	function get_customer_profile($customer_id){
		$r = mysql_query("select firstname,lastname,phone,unit,buzzer,address,postal_code,note from daily_customers_nonpreorder where id=$customer_id");
		return mysql_fetch_object($r);
	}

?>