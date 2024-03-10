# Walnut Lang HTTP Adapter
A small adapter for performing HTTP calls to Walnut language code using PSR HTTP.

## Installation

To install the latest version, use the following command:

```bash
$ composer require kapitancho/walnut-lang-psr-http-adapter
```

## Usage

```walnut-lang
module demo-psr-http:

handleRequest = ^HttpRequest => HttpResponse :: [
   statusCode: 200,
   protocolVersion: HttpProtocolVersion.HTTP11,
   headers: [:],
   body: 'Hello world!'
];
```
