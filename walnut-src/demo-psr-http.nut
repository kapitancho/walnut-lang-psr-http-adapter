module demo-psr-http %% http-core:

handleRequest = ^HttpRequest => HttpResponse :: [
   statusCode: 200,
   protocolVersion: HttpProtocolVersion.HTTP11,
   headers: [:],
   body: 'Hello world!'
];