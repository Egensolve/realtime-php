# ERS PHP client

This framework-neutral package provides version 1 envelope validation, typed channel builders and
parsing, backend channel-authorization signing, and a durable-publication client.

The publication client accepts an application-provided HTTP transport. That keeps the package free
of framework and Composer runtime dependencies while allowing each application to use its existing
HTTP stack. Remote publication endpoints must use HTTPS; plaintext HTTP is accepted only for
loopback development hosts.

```php
use Egensolve\Realtime\Contracts\Channel\AccountChannel;
use Egensolve\Realtime\Contracts\EnvelopeValidator;

$envelope = EnvelopeValidator::parse($rawEnvelopeJson);
$channel = AccountChannel::for('example', 'user', '42')->toString();
```

Application backends remain responsible for authenticating their users and deciding which channels
they may access. Never place application secrets in browser or mobile clients.

## Media and Artifacts (trusted backends only)

Use `Media\MediaClient` for upload intents, multipart part intents, completion, asset metadata,
download grants and deletion. Use `Artifacts\ArtifactsClient` for artifact creation, revision
upload/completion, publishing a revision and withdrawal. Both accept an ERS base URL, a
server-held machine credential and an optional `Http\Transport`. The default `Http\CurlTransport`
requires PHP's curl extension; injecting a transport keeps the protocol client dependency-free.

These clients send version-1 JSON metadata, not file bytes. Authorize the product user on your
backend, request a short-lived upload/download instruction, then transfer bytes directly to its
provider URL. Never embed machine credentials in a browser, mobile app or widget, and never log
signed URLs or upload tokens. Preserve returned IDs exactly, including letter case.

Pass a stable idempotency key for retries of the same mutation; use a different key for a new
operation. Inspect `Http\ApiResponse::data` for provider instructions and readiness. A successful
completion request does not by itself mean an object is ready. Handle `Http\ApiException` status
and error code; retry transient failures with backoff. Artifacts publishing accepts only a ready
revision; republish an earlier ready revision to roll back, or withdraw to stop public delivery.

## License

MIT. See [LICENSE](LICENSE).
