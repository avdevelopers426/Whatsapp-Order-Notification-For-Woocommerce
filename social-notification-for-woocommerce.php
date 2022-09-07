<?php 
/**
* Plugin Name: Whatsapp Order Notification For Woocommerce
* Description: social notification for woocommerce 
* Version: 1.0
* Copyright: 2020
* Text Domain: social-notification-for-woocommerce 
*/

/**
* menu sub page 
*/
add_action('admin_menu', 'WTSOD_createMenu');
function WTSOD_createMenu() {
	add_menu_page('WhatsApp', 'WhatsApp', 'manage_options', 'whatsapp_order', 'WTSOD_whatsappOrderContain');
}

/**
* page option 
*/

function WTSOD_whatsappOrderContain(){?>
	<div id="poststuff" bis_skin_checked="1">
	   	<div class="postbox">
	      	<div class="postbox-header">
	        	<h2><?php echo __( 'General Settings', 'whatapp-order-link' ); ?></h2>
	      	</div>
	      	<div class="inside">
	          	<form method="post">
	                <table>
		                <tr>
		                    <th>
		                      <?php echo __( 'Order Status', 'whatapp-order-link' ); ?>
		                    </th>
		                	<td>
		            			<?php 
		            		 		$ocpsw_disbl_atch_for_ord_status = get_option('ocpsw_disbl_atch_for_ord_status'); 
		            				$wc_get_order_statuses = wc_get_order_statuses(); ?>
		         				<select id="ocpsw_disbl_atch_for_ord_status" name="ocpsw_disbl_atch_for_ord_status"  style="width:99%;max-width:25em;">
		         					<option><?php echo __("-----select--------","social-notification-for-woocommerce");?></option>
		                      		<?php
		                      		
						                foreach( $wc_get_order_statuses as $key => $status ) {?>

						                	<option value="<?php echo esc_attr($key); ?>" <?php if($key == $ocpsw_disbl_atch_for_ord_status){echo __("selected","social-notification-for-woocommerce");} ?>><?php echo $status; ?></option>
						                	<?php 
						                  
						                }
						            
		                      		?>
		                      	</select>
		                	</td>
		                </tr>
		                <tr>
	                    	<th>
	                      		<?php echo __( 'Whatapp Token', 'social-notification-for-woocommerce' ); ?>
	                    	</th>
		                    <td>
		                    	<?php $whatapp_token = get_option('whatapp_token');  ?>
		                    	<input type="text" name="whatapp_token"  value="<?php echo esc_attr($whatapp_token); ?>">
		                    </td>
	                  	</tr>
	                </table>  
	        		<input type="hidden" name="action" value="ocpsw_save_option">
				    <input type="submit" value="Save changes" name="submit" class="button-primary" id="wfc-btn-space">
	           	</form>
	      	</div> 
        </div>
    </div>
	<?php 
}


/**
* page option save option
*/

function WTSOD_saveOptions() {
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'ocpsw_save_option') {
	 	update_option('ocpsw_disbl_atch_for_ord_status',sanitize_text_field($_REQUEST['ocpsw_disbl_atch_for_ord_status']), 'yes');
	  	update_option('whatapp_token', sanitize_text_field( $_REQUEST['whatapp_token'] ),'yes');
	  }
}
add_action( 'init', 'WTSOD_saveOptions');




/**
* send whatapp message new order formate.
*
* @param int $order_id Order ID.
* @return message formate
*/
function WTSOD_whatappMessageFormate($order_id ){
		$order = wc_get_order( $order_id );
		$order_data = $order->get_data();
		$item = $order->get_items();


        foreach( $order->get_items() as $item_id => $item ){
        	$product_name = $item->get_name();
        	$quantity = $item->get_quantity();
        	$total_sub = $item->get_subtotal();
        }
        $calling_code_data = WC()->countries->get_country_calling_code( $order_data['billing']['country'] );

			$calling_code = str_replace("+","",$calling_code_data);

			$messagesend = '``` 
			'.'New Order Received'.'
			-----------------------------'.'
			Order Number : '. esc_attr($order_data['id']) .'
			Date : '. esc_attr($order_data['date_created']->date('F d, Y')).'
			Email : '. esc_attr($order_data['billing']['email']).'
			Total Amount : '. esc_attr($order_data['total']).'
			Order Detail : '. esc_attr($product_name) .' x '. esc_attr($quantity) .' => '. esc_attr($total_sub) .' '. esc_attr($order_data['currency']).'
			-----------------------------'.'
			Sub Total : '. esc_attr($order->get_subtotal()).' '.esc_attr($order_data['currency']) .'
			Shipping : '. esc_attr($order_data['shipping_total']).' '.esc_attr($order_data['currency']).' '.esc_attr($order_data['payment_method_title']).'
			Tax : '.esc_attr($order_data['total_tax']).' '.esc_attr($order_data['currency']).'
			Total : '. esc_attr($order_data['total']).' '.esc_attr($order_data['currency']).'
			-----------------------------'.'
			Billing Address :
			'.
			esc_attr($order_data['billing']['first_name']).' '.esc_attr($order_data['billing']['last_name']).' '.esc_attr($order_data['billing']['company']).' '.$order_data['billing']['address_1'].' '.esc_attr($order_data['billing']['address_2']).' '.esc_attr($order_data['billing']['city']).','.esc_attr($order_data['billing']['state']).','.esc_attr($order_data['billing']['postcode']).' '.	esc_attr($order_data['billing']['country']).' '.esc_attr($order_data['billing']['email']).' '.esc_attr($order_data['billing']['phone']).'
			-----------------------------'.'
			Shipping Address :
			'.
			esc_attr($order_data['shipping']['first_name']).' '.esc_attr($order_data['shipping']['last_name']).' '.esc_attr($order_data['shipping']['company']).' '.esc_attr($order_data['shipping']['address_1']).' '.esc_attr($order_data['shipping']['address_2']).' '.esc_attr($order_data['shipping']['city']).','.esc_attr($order_data['shipping']['state']).','.esc_attr($order_data['shipping']['postcode']).' '.	esc_attr($order_data['shipping']['country']).' '.esc_attr($order_data['shipping']['phone']).'

			-----------------------------'.' ```';

		return $messagesend;

}



add_action('woocommerce_order_status_changed', 'WTSOD_whatappMessageSendNewOrder', 10, 3);
function WTSOD_whatappMessageSendNewOrder( $order_id) {

		$messagesend = WTSOD_whatappMessageFormate($order_id);
		$order = wc_get_order( $order_id );
		$order_data = $order->get_data();
		$item = $order->get_items();
        foreach( $order->get_items() as $item_id => $item ){
        	$product_name = $item->get_name();
        	$quantity = $item->get_quantity();
        	$total_sub = $item->get_subtotal();
        }
		$calling_code_data = WC()->countries->get_country_calling_code( 	$order_data['billing']['country'] );

		$calling_code = str_replace("+","",$calling_code_data);
		$phonenumber = $calling_code.$order_data['billing']['phone'];
		
		
		$ocpsw_disbl_atch_for_ord_status = get_option('ocpsw_disbl_atch_for_ord_status'); 

		$stusess = str_replace("wc-","",$ocpsw_disbl_atch_for_ord_status);

		if($stusess == $order_data['status']){
			WTSOD_whatappApiCallHere($messagesend,$phonenumber);
   		}
}


/**
* whatapp api calling here 
*
* @param var $messagesend var $phonenumber with countrycode.
* @return return message send suceesfully 
*/

function WTSOD_whatappApiCallHere($messagesend,$phonenumber ){

	$postData = [ "sender_number" => $phonenumber,
		    "message" => $messagesend,
	];

	$curl = curl_init();
			curl_setopt_array($curl, array(
		  	CURLOPT_URL => 'https://xeeshop.com/whatsapp/api/send_message',
		  	CURLOPT_RETURNTRANSFER => true,
		  	CURLOPT_ENCODING => '',
		  	CURLOPT_MAXREDIRS => 10,
		  	CURLOPT_TIMEOUT => 0,
		  	CURLOPT_FOLLOWLOCATION => true,
		  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  	CURLOPT_CUSTOMREQUEST => 'POST',
		  	CURLOPT_POSTFIELDS =>json_encode($postData),
			CURLOPT_HTTPHEADER => array(
			    'token: '.get_option('whatapp_token'),
			    'Content-Type: application/json'
			),
	));

	$response = curl_exec($curl);
	curl_close($curl);

}?>