<?php

namespace PayPal\API\Requests;



interface APIRequest
{
	/** @return array */
	function getRequestData();
}
