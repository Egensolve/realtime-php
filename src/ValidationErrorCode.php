<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;






enum ValidationErrorCode: string
{
    case EnvelopeInvalidJson = 'envelope.invalid_json';
    case EnvelopeTooLarge = 'envelope.too_large';
    case EnvelopeMissingField = 'envelope.missing_field';
    case EnvelopeUnsupportedVersion = 'envelope.unsupported_version';
    case EnvelopeInvalidKind = 'envelope.invalid_kind';
    case EnvelopeRecoveryRequired = 'envelope.recovery_required';
    case EnvelopeRecoveryNotAllowed = 'envelope.recovery_not_allowed';
    case EnvelopeExpiresAtRequired = 'envelope.expires_at_required';
    case EnvelopeExpiresAtNotAllowed = 'envelope.expires_at_not_allowed';
    case EnvelopeInvalidId = 'envelope.invalid_id';
    case EnvelopeInvalidDomain = 'envelope.invalid_domain';
    case EnvelopeInvalidType = 'envelope.invalid_type';
    case EnvelopeInvalidTenant = 'envelope.invalid_tenant';
    case EnvelopeInvalidTimestamp = 'envelope.invalid_timestamp';
    case EnvelopeInvalidSubject = 'envelope.invalid_subject';
    case EnvelopeInvalidRecovery = 'envelope.invalid_recovery';
    case EnvelopePayloadNotObject = 'envelope.payload_not_object';
    case EnvelopePayloadBinaryRejected = 'envelope.payload_binary_rejected';
    case ChannelInvalidFormat = 'channel.invalid_format';
    case ChannelInvalidSegment = 'channel.invalid_segment';
    case ChannelTooLong = 'channel.too_long';
    case AuthInvalidSocketId = 'auth.invalid_socket_id';
    case AuthPresenceDataRequired = 'auth.presence_data_required';
    case AuthPresenceDataNotAllowed = 'auth.presence_data_not_allowed';
    case AuthPresenceDataTooLarge = 'auth.presence_data_too_large';
}
