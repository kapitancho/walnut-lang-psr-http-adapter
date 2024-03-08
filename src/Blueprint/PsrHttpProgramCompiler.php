<?php

namespace Walnut\Lang\NativeConnector\PsrHttp\Blueprint;

use Walnut\Lang\Blueprint\Compilation\Source;

interface PsrHttpProgramCompiler {
	public function compileHttpProgram(
        Source $source,
        string $entryPointName = 'handleRequest'
    ): PsrHttpEntryPoint;
}