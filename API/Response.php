<?php

namespace PayPal\API;

use Nette;


class Response extends Nette\ArrayHash
{

	const STATUS_CREATED = 'CREATED';
	const STATUS_COMPLETED = 'COMPLETED';
	const STATUS_INCOMPLETE = 'INCOMPLETE';
	const STATUS_ERROR = 'ERROR';
	const STATUS_REVERSALERROR = 'REVERSALERROR';
	const STATUS_PROCESSING = 'PROCESSING';
	const STATUS_PENDING = 'PENDING';
	const STATUS_EXPIRED = 'EXPIRED';


	/** @param  array|\stdClass|\Traversable $values */
	function __construct($values = NULL)
	{
		foreach ((array) $values as $key => $value) {
			if (is_array($value)) {
				$this->$key = static::from($value, TRUE);
			} else {
				$this->$key = $value;
			}
		}
	}


	/** @return bool */
	function isSuccessFull()
	{
		return isset($this->responseEnvelope->ack) && strcasecmp($this->responseEnvelope->ack, 'success') === 0;
	}


	/** @return string|NULL */
	function getPayKey()
	{
		return isset($this->payKey) ? $this->payKey : NULL;
	}


	/** @return string|NULL */
	function getCorrelationID()
	{
		return isset($this->responseEnvelope->correlationId) ? $this->responseEnvelope->correlationId : NULL;
	}


	/** @return string|NULL */
	function getStatus()
	{
		return isset($this->status) ? $this->status : NULL;
	}

}
