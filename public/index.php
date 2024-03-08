<?php

use Walnut\Lang\Blueprint\Compilation\Source;
use Walnut\Lang\Implementation\Registry\ProgramBuilderFactory;
use Walnut\Lang\NativeConnector\PsrHttp\Implementation\PsrHttpProgramCompilerAdapter;
use Walnut\Lang\Implementation\Compilation\ProgramCompilationContext;

require_once __DIR__ . '/../vendor/autoload.php';

$sourceRoot = __DIR__ . '/../walnut-src';

$streamFactory = null; //TODO: get a PSR-compatible Stream factory
$responseFactory = null; //TODO: get a PSR-compatible HTTP Response factory

$request = null; //TODO: get a PSR-compatible HTTP request

$source =  $request->getHeaderLine('x-source') ?: 'demo-psr-http';
$sources = [];
foreach(glob("$sourceRoot/*.nut") as $sourceFile) {
	$sources[] = str_replace('.nut', '', basename($sourceFile));
}
if (!in_array($source, $sources, true)) {
	$source = 'demo-web';
}

$content = (new PsrHttpProgramCompilerAdapter(
    new ProgramCompilationContext(
    	new ProgramBuilderFactory()
    ), $responseFactory, $streamFactory
))->compileHttpProgram(
	new Source($sourceRoot, $source)
)->execute($request);

//TODO: send the response stored in $content
