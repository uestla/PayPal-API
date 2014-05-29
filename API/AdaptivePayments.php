<?php

namespace PayPal\API;

use Nette;

require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/Requests/PayRequest.php';


class AdaptivePayments extends Nette\Object
{

	/** @var string */
	protected $appOwner;

	/** @var array */
	protected $headers;

	/** @var bool */
	protected $sandbox;

	/** @var string|NULL */
	protected $defaultCurrency;


	const SANDBOX_END_POINT = 'https://svcs.sandbox.paypal.com/AdaptivePayments/';
	const SANDBOX_WEBSCR_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	const SANDBOX_APP_ID = 'APP-80W284485P519543T';
	const LIVE_END_POINT = 'https://svcs.paypal.com/AdaptivePayments/';

	const LIVE_WEBSCR_URL = 'https://www.paypal.com/cgi-bin/webscr';

	const CMD_KEY = 'cmd';
	const APP_VALUE = '_ap-payment';
	const NTF_VALUE = '_notify-validate';

	const FORMAT_NAME_VALUE = 'NV';
	const FORMAT_JSON = 'JSON';

	const DOLLAR = 'USD';
	const CZECH_CRONE = 'CZK';


	function __construct($appOwner, $username, $password, $signature, $ipAddress, $appID = NULL, $sandbox = TRUE, $defaultCurrency = NULL)
	{
		$this->appOwner = $appOwner;

		$this->headers = array(
			'X-PAYPAL-SECURITY-USERID: '      . $username,
			'X-PAYPAL-SECURITY-PASSWORD: '    . $password,
			'X-PAYPAL-SECURITY-SIGNATURE: '   . $signature,
			'X-PAYPAL-REQUEST-DATA-FORMAT: '  . static::FORMAT_JSON,
			'X-PAYPAL-RESPONSE-DATA-FORMAT: ' . static::FORMAT_JSON,
			'X-PAYPAL-APPLICATION-ID: ' . ($sandbox ? 'APP-80W284485P519543T' : $appID),
			'X-PAYPAL-DEVICE-IPADDRESS: ' . $ipAddress,
		);

		$this->sandbox = (bool) $sandbox;
		$this->defaultCurrency = $defaultCurrency;
	}


	function getAppOwner()
	{
		return $this->appOwner;
	}


	function createPayRequest($actionType, array $receivers, $currencyCode, $cancelUrl, $returnUrl)
	{
		if ($currencyCode === NULL) {
			$currencyCode = $this->defaultCurrency;
		}

		return new Requests\PayRequest($actionType, $receivers, $currencyCode, $cancelUrl, $returnUrl);
	}


	function sendPayRequest(Requests\PayRequest $request)
	{
		return $this->get('Pay', $request->getRequestData());
	}


	function getPaymentDetails($payKey = NULL, $transactionID = NULL, $trackingID = NULL)
	{
		$data = array();
		if ($payKey !== NULL) {
			$data['payKey'] = $payKey;

		} elseif ($transactionID !== NULL) {
			$data['transactionId'] = $transactionID;

		} else {
			$data['trackingId'] = $trackingID;
		}

		$data['requestEnvelope'] = array(
			'errorLanguage' => 'en_US',
			'detailLevel' => 'ReturnAll',
		);

		return $this->get('PaymentDetails', $data);
	}


	function getLoginRedirectionUrl($payKey)
	{
		$url = new Nette\Http\Url($this->getWebScrUrl());
		$url->setQuery(array(
			static::CMD_KEY => static::APP_VALUE,
			'paykey' => $payKey,
		));

		return (string) $url;
	}


	function executePayment($payKey, $fundingPlanID = NULL)
	{
		$data = array();
		$data['payKey'] = $payKey;

		if ($fundingPlanID !== NULL) {
			$data['fundingPlanId'] = $fundingPlanID;
		}

		$data['requestEnvelope'] = array(
			'errorLanguage' => 'en_US',
			'detailLevel' => 'ReturnAll',
		);

		return $this->get('ExecutePayment', $data);
	}


	function verifyStatus(array $data, & $query = NULL)
	{
		$new = array();
		$new[ static::CMD_KEY ] = static::NTF_VALUE;

		foreach ($data as $key => $val) {
			$new[$key] = $val;
		}

		$query = http_build_query($new, NULL, '&');
		return $this->sendRequest($this->getWebScrUrl(), $query, FALSE);
	}


	// === INTERNAL METHODS ========================================================================


	/** @return string */
	protected function getWebScrUrl()
	{
		return $this->sandbox ? static::SANDBOX_WEBSCR_URL : static::LIVE_WEBSCR_URL;
	}


	protected function getEndPoint($operation)
	{
		return ($this->sandbox ? static::SANDBOX_END_POINT : static::LIVE_END_POINT) . $operation;
	}


	protected function get($operation, array $data)
	{
		$response = new Response((array) Nette\Utils\Json::decode($this->sendRequest($this->getEndPoint($operation), Nette\Utils\Json::encode($data))));

		if (!$response->isSuccessFull()) {
			throw new Exception($response);
		}

		return $response;
	}


	protected function sendRequest($url, $data, $sendHeaders = TRUE)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);

		if ($sendHeaders) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		}

		curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);

		$answer = curl_exec($curl);
		if (curl_errno($curl)) {
			echo (string) curl_errno($curl), " ", curl_error($curl); die();
			throw new RequestFailureException($answer);
		}

		list (, $body) = explode("\r\n\r\n", $answer);
		return $body;
	}

}
