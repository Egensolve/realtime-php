<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Auth;

use Egensolve\Realtime\Contracts\Channel\ChannelGrammar;
use Egensolve\Realtime\Contracts\ValidationError;
use Egensolve\Realtime\Contracts\ValidationErrorCategory;
use Egensolve\Realtime\Contracts\ValidationErrorCode;







final class PresenceMemberData
{
    public const MAX_SERIALIZED_BYTES = 1024;

    private function __construct(
        public readonly string $userId,
         
        public readonly array $userInfo,
    ) {
    }

    




    public static function for(string $userId, array $userInfo = []): self
    {
        return new self(
            ChannelGrammar::requireOpaqueId($userId, 'userId'),
            $userInfo,
        );
    }

    






    public function toJson(): string
    {
        $json = json_encode(
            ['user_id' => $this->userId, 'user_info' => $this->userInfo],
            JSON_THROW_ON_ERROR,
        );

        if (\strlen($json) > self::MAX_SERIALIZED_BYTES) {
            throw ValidationError::make(
                ValidationErrorCode::AuthPresenceDataTooLarge,
                ValidationErrorCategory::SizeLimit,
                \sprintf('Serialized channel_data exceeds the %d byte limit.', self::MAX_SERIALIZED_BYTES),
            );
        }

        return $json;
    }
}
