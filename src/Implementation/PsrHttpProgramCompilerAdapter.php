<?php

namespace Walnut\Lang\NativeConnector\PsrHttp\Implementation;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\ProgramCompilationContext;
use Walnut\Lang\Blueprint\Compilation\Source;
use Walnut\Lang\NativeConnector\PsrHttp\Blueprint\PsrHttpEntryPoint;
use Walnut\Lang\NativeConnector\PsrHttp\Blueprint\PsrHttpProgramCompiler;

final readonly class PsrHttpProgramCompilerAdapter implements PsrHttpProgramCompiler {
	public function __construct(
        private ProgramCompilationContext $programCompilationContext,
		private ResponseFactoryInterface $responseFactory,
		private StreamFactoryInterface $streamFactory
	) {}

	public function compileHttpProgram(Source $source, string $entryPointName = 'handleRequest'): PsrHttpEntryPoint {
		return new PsrHttpAdapter(
            $this->programCompilationContext->compileProgram($source),
            $this->programCompilationContext->nativeCodeContext(),
			$this->responseFactory,
			$this->streamFactory,
			$entryPointName
		);
	}
}