<?php
/*
Plugin Name: Woo Failed Payments Log
Plugin URI: https://woocommerce.com/
Description: Created failed payments logs into csv.
Author: Atul Rana
Author URI: https://woocommerce.com/
Text Domain: woocommerce-failed-payment-log
Domain Path: /languages/
Version: 1.0
*/


register_activation_hook( __FILE__, 'create_failed_payment' );
function create_failed_payment() {
 global $wpdb;
 $table_name = $wpdb->prefix . 'failed_payment_log';
	//die('here');
 $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  subscription_id int(11) NOT NULL,
  PRIMARY KEY  (id)
 );";
 
 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 dbDelta( $sql );
 }

add_action( 'woocommerce_new_order_note_data', 'woo_failed_order', 10,3);


function woo_failed_order($ordernote,$order){
	global $wpdb;
	global $woocommerce;
	require 'vendor/autoload.php';
	$table_name = $wpdb->prefix . 'failed_payment_log';
		if(isset($order['order_id'])){
			
			$order_id = $order['order_id'];
			
			$fail_log = $wpdb->get_row("select * from $table_name where order_id = $order_id");
			
			if($fail_log){
				
			
			$order = new WC_Order( $order_id );
			
			if( @$order->data['status'] == 'failed'){
				
				$subscription_id = $fail_log->subscription_id;
				
				$subscription =  new WC_Subscription( $subscription_id );
			
				$delete_record = $wpdb->delete( $table_name, array( 'ID' => $fail_log->id));
				
				
			$subscription_id = @$subscription->id;
			$order_id = @$order->id;
			$first_name = @$subscription->data['billing']['first_name'] ;
			$last_name = @$subscription->data['billing']['last_name'] ;
			$email = @$subscription->data['billing']['email'] ;
			$status = @$subscription->data['status'] ; 
			$statusorder = @$order->data['status'] ; 
			$payment_method = @$subscription->data['payment_method'] ;
			$reason = $ordernote['comment_content'];
			$date = date('Y-m-d H:i:s') ;
	
	 if(!file_exists(plugin_dir_path(__FILE__).'csv-log/failed_payments_log.csv')){
		 $fp = fopen(plugin_dir_path(__FILE__).'csv-log/failed_payments_log.csv', 'w');
		 fputcsv($fp, array('Subscription ID','Order ID','First Name','Last Name','Email','Subscription Status','Order Status','Payment Method','Fail Reason','Date'));
		fclose($fp);
	 }
	 $fp = fopen(plugin_dir_path(__FILE__).'csv-log/failed_payments_log.csv', 'a');
		 fputcsv($fp, array($subscription_id,$order_id,$first_name,$last_name,$email,$status,$statusorder,$payment_method,$reason,$date));
	fclose($fp);
	
	$app_key =  get_option('trello-app-key') ;
	$trello_app_token =  get_option('trello-app-token') ;
	$board_id = get_option('trello-borad-id');
	$list_id = get_option('trello-list-id');
	
	if($app_key && $trello_app_token && $board_id && $list_id){
		
			$client = new \Trello\Client($app_key);

			$client->setAccessToken($trello_app_token);
			$card = new \Trello\Model\Card($client);
			
			$card->name = 'Failed Subscription -'.$subscription_id;
			$card->desc = "New Failed Subscription
			
-	Subscription ID: **$subscription_id**
-	Order ID: **$order_id**
-	First Name: **$first_name**
-	Last Name: **$last_name**
-	Email: **$email**
-	Subscription Status: **$status**
-	Order Status: **$statusorder**
-	Payment Method: **$payment_method**
-	Fail Reason: **$reason**
-	Date: **$date**";
			$card->idList = $list_id;
			$card->pos = 'top';
			$card->save();
					} 
				}	
			$spreadsheet_name = get_option("spreadsheet-name");	
			include(plugin_dir_path(__FILE__).'/lib/index.php');
			if(isset($error)){
			 $fp = fopen(plugin_dir_path(__FILE__).'GS_log.txt', 'a');
			 fwrite($fp,$error.'\n');
			 fclose($fp);
			}
			
			}
			
		}
		
	return $ordernote;
}


 add_action('woocommerce_subscription_renewal_payment_failed','woo_failed_payments',10,2); 
 
 function woo_failed_payments($subscription=false,$order=false){
	 global $wpdb;
	  $table_name = $wpdb->prefix . 'failed_payment_log';
	 $wpdb->insert($table_name, array(
    'order_id' => @$order->id,
    'subscription_id' => @$subscription->id,
    ));
	
 }
 
  add_action('admin_menu','trellomenu'); 
 
 function trellomenu(){

add_menu_page('WFP Log', 'WFP Log', 'administrator','trello-settings', 'trello_settings');
add_submenu_page('trello-settings', 'Export', 'Export', 'administrator', 'export', 'export_failed_csv'); 
add_submenu_page('trello-settings', 'GExcel Settings', 'GExcel Settings', 'administrator', 'gexcel-settings', 'gexcel_settings'); 
}


function gexcel_settings(){
	global $woocommerce;
	if(isset($_POST['gesubmit'])){
			file_put_contents(plugin_dir_path(__FILE__). '/lib/client_secret.json',trim(stripslashes($_POST["gjsonfile"])));
			update_option("spreadsheet-name",$_POST["spreadsheet-name"]);
		}
	 $gjsonfile = trim(file_get_contents(plugin_dir_path(__FILE__). '/lib/client_secret.json'));
	 $spreadsheetname = get_option("spreadsheet-name");
	include_once "inc/settings-ges.php";
}

function trello_settings()
{
	 global $wpdb;
	 
	 require 'vendor/autoload.php';
	
	 if(isset($_POST['trellsubmit'])){
		 
		
		  if(isset($_POST['app-key'])){
			 
			 update_option('trello-app-key',$_POST['app-key']);
		 } 
		 $app_key =  get_option('trello-app-key') ;
		 $trello_app_token =  get_option('trello-app-token') ;
		 
		 if(isset($_POST['board_id'])){
			 
			 try{
				 
			  $client = new \Trello\Client($app_key);

				$client->setAccessToken($trello_app_token);
				$board = $client->getboard($_POST['board_id']);
				$boarddata = json_decode($client->getRawResponse($board));
				update_option('trello-borad-id',$_POST['board_id']);
			}catch(Exception $e) {
				$board_error = $e->getMessage();
			}
			
		 }
		 if(isset($_POST['list_id'])){
			 
			 update_option('trello-list-id',$_POST['list_id']);	
		 }
	
		  
	 }
	
		$app_key =  get_option('trello-app-key') ;
		$trello_app_token =  get_option('trello-app-token') ;
		$board_id = get_option('trello-borad-id');
		$list_id = get_option('trello-list-id');
		
		/* 
	  if(!$board_id && $trello_app_token){
		 
		    $client = new \Trello\Client($app_key);

			$client->setAccessToken($trello_app_token);
			$board = new \Trello\Model\Board($client);
			$board->name = 'Woo Failed Payments';
			$board->save();
			$board = json_decode($client->getRawResponse($board));
			
			update_option('trello-borad-id',$board->id);
		
			if(isset($board->id)){
				
				$list =  new \Trello\Model\Lane($client);
				
				$list->name = "Failed Subscription Orders";
				
				$list->idBoard = $board->id;
				
				$list->pos = 'top';
				
				$list->save();
				
				$list = json_decode($client->getRawResponse($list));
				
				update_option('trello-list-id',$list->id);	
				
			}
	 } 
		$app_key =  get_option('trello-app-key') ;
		$trello_app_token =  get_option('trello-app-token') ;
		$board_id = get_option('trello-borad-id');
		$list_id = get_option('trello-list-id'); */
		include_once "inc/settings.php";
}


function export_failed_csv(){
	
	 if(file_exists(plugin_dir_path(__FILE__).'csv-log/failed_payments_log.csv')){
		 
		 $file = plugin_dir_path(__FILE__).'csv-log/failed_payments_log.csv';
		 header('Content-Description: File Transfer');
		 header('Content-Type: application/octet-stream');
		 header('Content-Disposition: attachment; filename='.basename($file));
		 header('Content-Transfer-Encoding: binary');
		 header('Expires: 0');
		 header('Cache-Control: must-revalidate');
		 header('Pragma: public');
		 header('Content-Length: ' . filesize($file));
		 ob_clean();
		 flush();
		 readfile($file);
		 exit;
   
	 }
	
}

add_action('wp_ajax_update_token_value', 'update_token_value'); 
add_action( 'wp_ajax_nopriv_update_token_value', 'update_token_value' );

function update_token_value(){
	 global $wpdb;
	update_option('trello-app-token',$_POST['token']);
	
	echo  'success';
	die;
}


add_action('wp_ajax_delete_stored_token', 'delete_stored_token'); 
add_action( 'wp_ajax_nopriv_delete_stored_token', 'delete_stored_token' );

function delete_stored_token(){
	
	 global $wpdb;
			$app_key =  delete_option('trello-app-key') ;
			$trello_app_token =  delete_option('trello-app-token') ;
			$board_id = delete_option('trello-borad-id');
			$list_id = delete_option('trello-list-id');
	
	echo  'success';
	die;
	
}

add_filter( 'woocommerce_payment_complete_order_status', 'bryce_wc_autocomplete_paid_orders',10,2 );
function bryce_wc_autocomplete_paid_orders( $order_status, $order_id ) {
	
	global $wpdb;
	global $woocommerce;
	
	$order = new WC_Order( $order_id );

	$payment_gateway = $order->get_payment_method();
	if ( $order_status == 'processing') {
		return 'completed';
	}
	
	return $order_status;
}
 
add_filter('woocommerce_email_subject_new_order', 'change_admin_email_subject', 1, 2);
 
add_filter('woocommerce_email_subject_failed_order', 'change_admin_email_subject', 1, 2);
 
function change_admin_email_subject( $subject, $order ) {
    global $woocommerce;
	//echo $subject;
	//echo '<pre>' ; print_r($order) ; die;
  //  $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	$items = $order->get_items();
	foreach ( $items as $item ) {
		$product_name = $item['name'];
		$product_id = $item['product_id'];
		$product_variation_id = $item['variation_id'];
	}
	
	 $product = new WC_Product( $product_id );
	
	$subject = str_replace(array('{SKU}','{billing_first_name}','{billing_last_name}','{billing_company}'),array($product->get_sku(),$order->get_billing_first_name(),$order->get_billing_last_name(),$order->get_billing_company()),$subject);
    // Change subject according to your requirement
   // $subject = sprintf( '[%s] New Customer Order (# %s) from Name %s %s', $blogname, $order->id, $order->billing_first_name, $order->billing_last_name );
 
    return $subject;
} 

add_action('woocommerce_email_subject_cancelled_subscription','change_subscription_subject',10,2);

function change_subscription_subject($subject,$subscription){
	$subscription = new WC_Subscription(266);
	 
	$related_orders = $subscription->get_related_orders();
	 
 	foreach($related_orders as $order){
		
		$order_id = $order ;
		
		break;
	}
	 $order = new WC_Order($order_id);
	 $order_items = $order->get_items();
		foreach($order_items as $order_item){
			
			$product_id = $order_item->get_product_id();
			
		}
		 $product = new WC_Product( $product_id );
	$product = new WC_Product( $product_id );
	$subject = str_replace(array('{subscription_id}','{order_number}','{SKU}','{billing_first_name}','{billing_last_name}','{billing_company}'),array($subscription->get_id(),$order_id,$product->get_sku(),$subscription->get_billing_first_name(),$subscription->get_billing_last_name(),$subscription->get_billing_company()),$subject);
	
	
	return $subject;
}

?>