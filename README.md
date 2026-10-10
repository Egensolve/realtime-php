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

## Calls control channels

Use the provider-neutral `CallChannel` for a product-authorized Calls session's control feed:

```php
use Egensolve\Realtime\Contracts\Channel\CallChannel;
use Egensolve\Realtime\Contracts\Channel\ChannelParser;

$call = CallChannel::for('example', 'call_demo');
$name = $call->toString(); // private-v2.tenants.example.calls.call_demo
$parsed = ChannelParser::parse($name);
```

Tenant identifiers allow 1–64 ASCII letters, digits, `_` and `-`; call identifiers allow 1–128
of the same characters. Case is preserved. The complete channel must still fit 164 characters.
Only the Calls shape uses `private-v2`; existing v1 channels are unchanged. The envelope remains
version 1. Existing `Auth\ChannelAuthSigner` signs this private channel without presence data,
after the backend has authorized access. Parsing a channel is not an authorization decision.
This API carries control events, not audio/video, and does not supply a media client.

## Media and Artifacts (trusted backends only)

Use `Media\MediaClient` for upload intents, interrupted-upload capability renewal, multipart part
intents, completion, asset metadata, download grants and deletion. Use
`Artifacts\ArtifactsClient` for artifact creation, revision
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

`renewUploadCapability()` reissues an expired provider PUT instruction for the same active upload.
Pass `upload_id` and `upload_token`; include a positive `part_number` only for multipart renewal.
Version 1 keeps both opaque upload values stable. Older provider capabilities remain valid only
until their own expiry, and no renewed capability extends the asset retention deadline.

## License

MIT. See [LICENSE](LICENSE).

## Media retention and tiering

Trusted product backends can use `MediaClient.policy()` to discover effective retention limits and permitted tiering profile IDs. `updateRetention` accepts an explicit ISO-8601 expiry or authorized null expiry and an idempotency key. Upload intents may select a permitted `tiering_profile`. These endpoints require a server supporting configurable retention and the appropriate machine-credential abilities; null expiry does not extend upload or download capabilities.
