<?php

namespace Walnut\Lang\NativeConnector\PsrHttp\Blueprint;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface PsrHttpEntryPoint {
	public function execute(
        RequestInterface $request
    ): ResponseInterface;
}