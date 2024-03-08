<?php

namespace Walnut\Lang\NativeConnector\PsrHttp\Implementation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use Walnut\Lang\Blueprint\Execution\Program;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\PropertyNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\NativeCode\NativeCodeContext;
use Walnut\Lang\Blueprint\Value\DictValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\NativeConnector\PsrHttp\Blueprint\PsrHttpEntryPoint;

final readonly class PsrHttpAdapter implements PsrHttpEntryPoint {
	public function __construct(
		private Program $program,
        private NativeCodeContext $nativeCodeContext,
		private ResponseFactoryInterface $responseFactory,
		private StreamFactoryInterface $streamFactory,
		private string $entryPointName = 'handleRequest'
	) {}

	private function buildRequest(RequestInterface $request): Value {
		return $this->nativeCodeContext->valueRegistry->dict([
			'protocolVersion' => $this->nativeCodeContext->valueRegistry->enumerationValue(
				new TypeNameIdentifier('HttpProtocolVersion'),
				new EnumValueIdentifier(
					match($request->getProtocolVersion()) {
						'1.0' => 'HTTP1',
						'2.0' => 'HTTP2',
						'3.0' => 'HTTP3',
						/*'1.1',*/ default => 'HTTP11'
					}
				)
			),
			'method' => $this->nativeCodeContext->valueRegistry->enumerationValue(
				new TypeNameIdentifier('HttpRequestMethod'),
				new EnumValueIdentifier(strtoupper($request->getMethod()))
			),
			//'uri' => $this->valueRegistry->string($request->getUri()->__toString()),
			'requestTarget' => $this->nativeCodeContext->valueRegistry->string($request->getRequestTarget()),

			'headers' => $this->nativeCodeContext->valueRegistry->dict($this->getRequestHeaders($request->getHeaders())),
			'body' => $this->nativeCodeContext->valueRegistry->string($request->getBody()->__toString()),
		]);
	}

	private function convertResponse(DictValue $response): ResponseInterface {
		$result = $this->responseFactory->createResponse(
			$response->valueOf(new PropertyNameIdentifier('statusCode'))->literalValue()
		)->withProtocolVersion(
			match($response->valueOf(new PropertyNameIdentifier('protocolVersion'))->name()) {
				'HTTP1' => '1.0',
				'HTTP2' => '2.0',
				'HTTP3' => '3.0',
				/*'HTTP11',*/ default => '1.1'
			}
		);
		$body = $response->valueOf(new PropertyNameIdentifier('body'));
		if ($body instanceof StringValue) {
			$result = $result->withBody($this->streamFactory->createStream($body->literalValue()));
		}
		$headers = $response->valueOf(new PropertyNameIdentifier('headers'));
		foreach($headers->values() as $key => $value) {
			foreach($value->values() as $headerValue) {
				$result = $result->withAddedHeader(
					$key,
					$headerValue->literalValue()
				);
			}
		}
		return $result;
	}

	public function execute(RequestInterface $request): ResponseInterface {
		$response = $this->program->callFunction(
			new VariableNameIdentifier($this->entryPointName),
			$this->nativeCodeContext->typeRegistry->withName(
				new TypeNameIdentifier('HttpRequest')
			),
			$responseType = $this->nativeCodeContext->typeRegistry->withName(
				new TypeNameIdentifier('HttpResponse')
			),
			$this->buildRequest($request)
		);
		return $response instanceof DictValue && $response->type()->isSubtypeOf($responseType) ?
			$this->convertResponse($response) : throw new RuntimeException(
				sprintf("Invalid result type: '%s'. HttpResponse expected", $response::class)
			);
	}

	private function getRequestHeaders(array $headers): array {
		$result = [];
		foreach($headers as $headerName => $headerValues) {
			$values = [];
			foreach($headerValues as $headerValue) {
				$values[] = $this->nativeCodeContext->valueRegistry->string($headerValue);
			}
			$result[$headerName] = $this->nativeCodeContext->valueRegistry->list($values);
		}
		return $result;
	}

}