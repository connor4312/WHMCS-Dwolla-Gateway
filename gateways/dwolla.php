<?php

function dwolla_config() {
	return array(
		'FriendlyName' => array(
			'Type' => 'System',
			'Value' => 'Dwolla'
		),
		'key' => array(
			'FriendlyName' => 'Consumer key',
			'Type' => 'text',
			'Description' => 'Consumer key for the application.'
		),
		'consumerSecret' => array(
			'FriendlyName' => 'Consumer Secret key',
			'Type' => 'text',
			'Description' => 'Shh, it\'s a secret!'
		),
		'destinationId' => array(
			'FriendlyName' => 'Dwolla ID',
			'Type' => 'text',
			'Size' => '30',
			'Description' => 'Dwolla ID of the Dwolla account receiving the funds. Format will always match "812-xxx-xxxx".'
		),		
		'allowFundingSources' => array(
			'FriendlyName' => 'Allow Fund Sources',
			'Type' => 'yesno',
			'Description' => 'Flag to allow guest checkout and bank-funded payments.'
		),
		'callback' => array(
			'FriendlyName' => 'Callback URL',
			'Type' => 'text',
			'Description' => 'Something like /whmcs/modules/gateways/callback/dwolla.php. If empty, will be generated automatically.'
		)
	);
}

function dwolla_link($params) {



	$data = array(

		/***********************************************************************
		|	WHMCS' own information. Feel free to change. Sadly, shipping and tax
		|	info are not provided to the gateway.
		***********************************************************************/

		'orderId' => $params['invoiceid'],
		'description' => $params['description'],
		'amount' => $params['amount'],
		'shipping' => '0.00',
		'tax' => '0.00',
		'name' => 'Invoice' . $params['invoiceid'],

		/***********************************************************************
		|	Credentials for Dwolla, as set above...
		***********************************************************************/

		'key' => $params['key'],
		'signature' => hash_hmac('sha1', $key . '&' . $timestamp . '&' . $orderID, $params['consumerSecret']),
		'destinationId' => $params['destinationId'],
		'allowFundingSources' => $params['allowFundingSources'],
		'callback' => empty($params['callback']) ? $params['systemurl'] . '/modules/gateways/callback/dwolla.php' : $params['callback']
	);


	/***************************************************************************
	|	Turn this big array into a form, and return it.
	***************************************************************************/

	$out = '<form method="POST" action="https://www.dwolla.com/payment/pay">';
	foreach ($data as $key => $value) {
		$out .= '<input type="hidden" name="' . $value . '" value ="' . $value .'" />';
	}
	$out .= '<input type="submit" value="Pay Now" class="btn btn-primary"/></form>';

	return $out;
}
?>