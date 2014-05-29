<?php

namespace PayPal\API\Requests;

use Nette;
use PayPal;

require_once __DIR__ . '/IApiRequest.php';


class PayRequest extends Nette\Object implements IApiRequest
{

	/** @var string */
	protected $actionType;

	/** @var string */
	protected $cancelUrl;

	/** @var string */
	protected $currencyCode;

	/** @var string */
	protected $feesPayer = NULL;

	/** @var string */
	protected $ipnNotificationUrl = NULL;

	/** @var array */
	protected $receiverList;

	/** @var string */
	protected $returnUrl;

	/** @var string */
	protected $senderEmail = NULL;

	/** @var array */
	protected $fundingConstraint = NULL;


	const ACTION_PAY = 'PAY';
	const ACTION_CREATE = 'CREATE';
	const ACTION_PAY_PRIMARY = 'PAY_PRIMARY';

	const FEES_PAYER_SENDER = 'SENDER';
	const FEES_PAYER_PRIMARY_RECEIVER = 'PRIMARYRECEIVER';
	const FEES_PAYER_EACH_RECEIVER = 'EACHRECEIVER';
	const FEES_PAYER_SECONDARY_ONLY = 'SECONDARYONLY';

	const FUNDING_TYPE_ECHECK = 'ECHECK';
	const FUNDING_TYPE_BALANCE = 'BALANCE';
	const FUNDING_TYPE_CREDITCARD = 'CREDITCARD';


	/**
	 * @param  string $actionType
	 * @param  array $receivers of receiver (each item is an array(email => $email, amount => $amount) )
	 * @param  string $currencyCode
	 * @param  string $cancelUrl
	 * @param  string $returnUrl
	 */
	function __construct($actionType, array $receivers, $currencyCode, $cancelUrl, $returnUrl)
	{
		$this->actionType = $actionType;
		$this->currencyCode = $currencyCode;
		$this->cancelUrl = $cancelUrl;
		$this->returnUrl = $returnUrl;

		$this->receiverList = array(
			'receiver' => $receivers,
		);
	}


	function setFeesPayer($feesPayer)
	{
		$this->feesPayer = $feesPayer;
		return $this;
	}


	function setIpnNotificationUrl($ipnNotificationUrl)
	{
		$this->ipnNotificationUrl = $ipnNotificationUrl;
		return $this;
	}


	function setSenderEmail($senderEmail)
	{
		$this->senderEmail = $senderEmail;
		return $this;
	}


	function addFundingType($type)
	{
		if ($this->fundingConstraint === NULL) {
			$this->fundingConstraint = array(
				'allowedFundingType' => array(),
			);
		}

		$this->fundingConstraint['allowedFundingType'][] = array(
			'fundingTypeInfo' => array(
				'fundingType' => $type,
			),
		);

		return $this;
	}


	function getRequestData()
	{
		$data = array(
			'actionType' => $this->actionType,
			'receiverList' => $this->receiverList,
			'currencyCode' => $this->currencyCode,
			'cancelUrl' => $this->cancelUrl,
			'returnUrl' => $this->returnUrl,
			'requestEnvelope' => array(
				'errorLanguage' => 'en_US',
				'detailLevel' => 'ReturnAll',
			),
		);

		if ($this->feesPayer !== NULL) {
			$data['feesPayer'] = $this->feesPayer;
		}

		if ($this->ipnNotificationUrl !== NULL) {
			$data['ipnNotificationUrl'] = $this->ipnNotificationUrl;
		}

		if ($this->senderEmail !== NULL) {
			$data['senderEmail'] = $this->senderEmail;
		}

		if ($this->fundingConstraint !== NULL) {
			$data['fundingConstraint'] = $this->fundingConstraint;
		}

		return $data;
	}

}
