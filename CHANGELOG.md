# Changelog

## 0.2.2

- Discover effective Media retention and permitted tiering profiles.
- Update per-asset retention with explicit finite expiry or authorized null expiry.
- Support the retention PATCH request while preserving metadata-only transfers.

## 0.2.1

- Add interrupted direct and multipart Media upload-capability renewal.
- Validate opaque upload identities and multipart part numbers before transport.
- Preserve idempotency replay evidence, structured errors, and retryability.

## 0.2.0

- License this public package under MIT.
- Add trusted-backend Media and Artifacts clients and bounded HTTP support.
- Preserve server-returned identifiers in their original case.
- Keep protocol/publication use free of framework and Composer runtime dependencies.

## 0.1.0

- Provide public envelope, channel, authorization-signing, and durable-publication APIs.
- Require an application-provided publication HTTP transport.
- Exclude private administrative, asset-management, and framework-integration code.
