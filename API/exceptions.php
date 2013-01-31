<?php

namespace PayPal\API;


class Exception extends \Exception
{
	const INVALID_EMAIL = 589039;
	const RESTRICTED_ACCOUNT = 520009;
	const DUPLICATE_RECEIVER = 579040;
	const SAME_SENDER_AND_RECEIVER = 579033;



	function __construct(Response $response)
	{
		$message = isset($response->error[0]->message) ? $response->error[0]->message : NULL;
		$code = isset($response->error[0]->errorId) ? $response->error[0]->errorId : NULL;

		parent::__construct($message, $code, NULL);
	}
}



class RequestFailureException extends \Exception
{
	/** @var array */
	protected $response;



	function __construct($response, $message = NULL, $code = NULL, $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
		$this->response = $response;
	}



	function getValues()
	{
		return $this->response;
	}
}
