<?php

/**
 * Super-simple, minimum abstraction MailChimp API v2 wrapper
 *
 * Class Give_MailChimp_API
 */
class Give_MailChimp_API {

	/**
	 * @var MailChimp
	 */
	private $api_key;

	/**
	 * @var mixed|string
	 */
	private $api_endpoint = 'https://<dc>.api.mailchimp.com/2.0/';

	/**
	 * API v3 endpoint.
	 *
	 * @since 1.4
	 *
	 * @var string
	 */
	private $api_endpoint_v3 = 'https://<dc>.api.mailchimp.com/3.0';

	/**
	 * @var bool
	 */
	private $verify_ssl = false;

	/**
	 * Give_MailChimp_API constructor.
	 *
	 * @param string $api_key MailChimp API key
	 * @param array  $options
	 *
	 * Create a new instance
	 */
	function __construct( $api_key, $options = array() ) {
		$this->api_key = $api_key;

		if ( strpos( $this->api_key, '-' ) === false ) {
			echo '<div class="updated settings-error notice"><p>' . sprintf( __( '<strong>Settings updated.</strong> An invalid MailChimp API key `%s` provided. Please enter a valid API key.', 'give-mailchimp' ), $api_key ) . '</p></div>';
			give_delete_option( 'give_mailchimp_api' );
			exit;
		}

		list( , $datacentre ) = explode( '-', $this->api_key );
		$this->api_endpoint = str_replace( '<dc>', $datacentre, $this->api_endpoint );

		// Update v3 endpoint.
		$this->api_endpoint_v3 = str_replace( '<dc>', $datacentre, $this->api_endpoint_v3 );
	}

	/**
	 * Call an API method. Every request needs the API key, so that is added automatically -- you don't need to pass it in.
	 *
	 * @param  string $method         The API method to call, e.g. 'lists/list'
	 * @param  array  $args           An array of arguments to pass to the method. Will be json-encoded for you.
	 * @param string  $ver            API version.
	 * @param string  $request_method API method.
	 *
	 * @return array | object          Associative array of json decoded API response.
	 */
	public function call( $method, $args = array(), $ver = '2.0', $request_method = 'POST' ) {

		if ( '2.0' === $ver ) {
			return $this->_raw_request( $method, $args );
		}

		/**
		 * Call API v3
		 *
		 * @since 1.4
		 * @see   https://developer.mailchimp.com/
		 */
		return $this->_v3_call( $method, $args, $request_method );
	}

	/**
	 * Performs the underlying HTTP request. Not very exciting
	 *
	 * @param  string $method The API method to be called
	 * @param  array  $args   Assoc array of parameters to be passed
	 *
	 * @return array          Assoc array of decoded result
	 */
	private function _raw_request( $method, $args = array() ) {
		$args['apikey'] = $this->api_key;

		$url = $this->api_endpoint . '/' . $method . '.json';

		$request_args = array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'sslverify'   => false,
			'headers'     => array(
				'content-type' => 'application/json'
			),
			'body'        => json_encode( $args ),
		);

		$request = wp_remote_post( $url, $request_args );

		return is_wp_error( $request ) ? false : json_decode( wp_remote_retrieve_body( $request ) );
	}

	/**
	 * Mail Chimp API v3 Call.
	 *
	 * @since 1.4
	 *
	 * @param string $endpoint Endpoint URL
	 * @param array  $args     Request array.
	 * @param string $method   Request method.
	 *
	 * @return \WP_Error | array
	 */
	public function _v3_call( $endpoint, $args = array(), $method = 'POST' ) {

		// Make MailChimp API endpoint URL.
		$endpoint_url = $this->api_endpoint_v3 . '/' . $endpoint;
		$req_arg      = array(
			'method'  => $method,
			'headers' => array(
				'Authorization' => "apikey {$this->api_key}",
			),
		);

		if ( ! empty( $args ) ) {
			$req_arg['body'] = json_encode( $args );
		}

		$request = wp_remote_post( $endpoint_url, $req_arg );

		return is_wp_error( $request ) ? false : json_decode( wp_remote_retrieve_body( $request ) );
	}

	/**
	 * Get or create Mail Chimp field.
	 *
	 * @since 1.4
	 *
	 * @param string $list_id LIST ID.
	 * @param array  $args    request arguments.
	 * @param string $method  METHOD.
	 *
	 * @return array|object|\WP_Error
	 */
	public function call_merge_field_request( $list_id, $args = array(), $method = 'GET' ) {
		return $this->call( "lists/{$list_id}/merge-fields", $args, '3.0', $method );
	}
}
