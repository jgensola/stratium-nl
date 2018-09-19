<?php
class iPay88 {

	public $request_url,
		$merchant_key,
		$merchant_code,
		$ipay88_data,
		$payment_data,
		$payment,
		$give_settings;

	public function f_give_payment_gateways( $gateways ) {

	    $gateways['ipay88'] = array(
	        'admin_label'    => esc_attr__( 'iPay88', 'give-ipay88' ),
	        'checkout_label' => esc_attr__( 'iPay88', 'give-ipay88' )
	    );

	    return $gateways;

	}

	public function f_give_gateway_ipay88( $purchase_data ) {

	    $this->request_url = give_is_test_mode() ? 'https://sandbox.ipay88.com.ph/epayment/entry.asp' : 'https://payment.ipay88.com.ph/epayment/entry.asp';
	    $this->merchant_key = give_is_test_mode() ? give_get_option('ipay88_test_merchant_key') : give_get_option('ipay88_live_merchant_key');
	    $this->merchant_code = give_is_test_mode() ? give_get_option('ipay88_test_merchant_code') : give_get_option('ipay88_live_merchant_code');

	    $this->ipay88_data['MerchantKey'] = $this->merchant_key;
	    $this->ipay88_data['MerchantCode'] = $this->merchant_code;
	    $this->ipay88_data['PaymentId'] = 1;
	    $this->ipay88_data['RefNo'] = rand(00000000, 99999999);
	    $this->ipay88_data['Amount'] = number_format($purchase_data['price'], 2);
	    $this->ipay88_data['Currency'] = give_get_currency();
	    $this->ipay88_data['ProdDesc'] = isset($purchase_data['post_data']['purpose']) ? (is_array($purchase_data['post_data']['purpose']) ? $purchase_data['post_data']['purpose'][0] : $purchase_data['post_data']['purpose']) : $purchase_data['post_data']['give-form-title'];
	    $this->ipay88_data['UserName'] = $purchase_data['user_info']['first_name'] . ' ' . $purchase_data['user_info']['last_name'];
	    $this->ipay88_data['UserEmail'] = $purchase_data['user_email'];
	    $this->ipay88_data['UserContact'] = isset($purchase_data['post_data']['give-contact']) ? $purchase_data['post_data']['give-contact'] : '';
	    $this->ipay88_data['Remark'] = isset($purchase_data['post_data']['location']) ? (is_array($purchase_data['post_data']['location']) ? $purchase_data['post_data']['location'][0] : $purchase_data['post_data']['location']) : '';
	    $this->ipay88_data['Lang'] = 'UTF-8';
	    $this->ipay88_data['Signature'] = $this->iPay88_signature($this->ipay88_data['MerchantKey'] . $this->ipay88_data['MerchantCode'] . $this->ipay88_data['RefNo'] . preg_replace('/[,.s]/', '', $this->ipay88_data['Amount']) . $this->ipay88_data['Currency']);
	    $this->ipay88_data['ResponseURL'] = home_url('donation-confirmation');
	    array_shift($this->ipay88_data);

	    $this->payment_data = array(
	        'price'           => $this->ipay88_data['Amount'],
	        'give_form_title' => $this->ipay88_data['ProdDesc'],
	        'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
	        'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
	        'date'            => $purchase_data['date'],
	        'user_email'      => $purchase_data['user_email'],
	        'purchase_key'    => $purchase_data['purchase_key'],
	        'currency'        => $this->ipay88_data['Currency'],
	        'user_info'       => $purchase_data['user_info'],
	        'status'          => 'pending',
	        'gateway'         => 'ipay88'
	    );

	    $this->payment = give_insert_payment( $this->payment_data );
	     if ( $this->payment ) {
	        $_SESSION['current_transaction'] = $this->payment;
	    } else {
	        give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	    } 

	    echo '<form action="' . $this->request_url . '" id="ePayment" method="post">';
	    foreach($this->ipay88_data as $key => $val) {
	        echo '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
	    }
	    echo '</form>';
	    echo '<script>document.getElementById("ePayment").submit()</script>';

	}

	public function give_add_ipay88_settings( $settings ) {
		$this->give_settings = array(
		    array(
		        'name' => '<strong>' . __( 'iPay88', 'give-ipay88' ) . '</strong>',
		        'desc' => '<hr>',
		        'type' => 'give_title',
		        'id'   => 'give_title_ipay88',
		    ),
		    array(
		        'id'   => 'ipay88_live_merchant_key',
		        'name' => esc_html__( 'Live Merchant Key', 'give-ipay88' ),
		        'desc' => esc_html__( 'Please enter your iPay88 live merchant key.', 'give-ipay88' ),
		        'type' => 'text'
		    ),
		    array(
		        'id'   => 'ipay88_live_merchant_code',
		        'name' => esc_html__( 'Live Merchant Code', 'give-ipay88' ),
		        'desc' => esc_html__( 'Please enter your iPay88 live merchant code.', 'give-ipay88' ),
		        'type' => 'text'
		    ),
		    array(
		        'id'   => 'ipay88_test_merchant_key',
		        'name' => esc_html__( 'Test Merchant Key', 'give-ipay88' ),
		        'desc' => esc_html__( 'Please enter your iPay88 test merchant key.', 'give-ipay88' ),
		        'type' => 'text'
		    ),
		    array(
		        'id'   => 'ipay88_test_merchant_code',
		        'name' => esc_html__( 'Test Merchant Code', 'give-ipay88' ),
		        'desc' => esc_html__( 'Please enter your iPay88 test merchant code.', 'give-ipay88' ),
		        'type' => 'text'
		    )
		);

		return array_merge( $settings, $this->give_settings );
	}

	public function iPay88_signature($source) {

		return base64_encode($this->hex2bins(sha1($source)));

	}

	public function hex2bins($hexSource) {

		$bin = "";

		for ($i=0; $i < strlen($hexSource); $i = $i + 2) {
			$bin .= chr(hexdec(substr($hexSource, $i, 2)));
		}

		return $bin;
		
	}

}