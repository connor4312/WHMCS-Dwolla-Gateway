<?php

/*******************************************************************************
|	Change this function if Dwolla edits their (wonderfully low) fees!
*******************************************************************************/

function fee($amount) {
	return $amount < 10 ? 0 : 0.25;
}

include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gateway = getGatewayVariables('Dwolla');
if (!$gateway["type"]) {
	throw new Exception("Module Not Activated");
}

$data = $_POST;

/*******************************************************************************
|	Dwolla returns a seekrit hash to make sure the transaction is valid. Would
|	probably be a good idea to check that, no?
*******************************************************************************/

function verifyGatewaySignature($proposedSignature, $checkoutId, $amount, $secret) {
	$amount = number_format($amount, 2);
	$signature = hash_hmac("sha1", "{$checkoutId}&{$amount}", $secret);

	return $signature == $proposedSignature;
}

if (!verifyGatewaySignature($data['signature'], $data['checkoutId'], $data['amount'], $gateway['consumerSecret'])) {
	throw new Exception('Invalid transaction');
}

/*******************************************************************************
|	Then all of WHMCS's standard, non-OO, non-namespaced, ugly jargon
*******************************************************************************/

$invoiceid = checkCbInvoiceID($data['CheckoutID'], $gateway["name"]);
checkCbTransID($data['TransactionId']);

if ($data['status'] == 'Completed') {
	addInvoicePayment($data['CheckoutID'], $data['TransactionId'], $data['Amount'], fee($data['Amount']), 'Dwolla');
	logTransaction($gateway["name"], $data, "Successful");
} else {
	logTransaction($gateway["name"], $data, "Unsuccessful");
}

?>