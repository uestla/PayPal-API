<?php

namespace PayPal\API\Requests;


interface IApiRequest
{
	/** @return array */
	function getRequestData();
}
