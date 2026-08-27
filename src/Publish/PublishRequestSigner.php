<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;




















final readonly class PublishRequestSigner
{
    public function __construct(
        private string $appKey,
        private string $appSecret,
    ) {
    }

    



    public function sign(string $method, string $path, string $body): SignedPublicationRequest
    {
        $params = ['auth_key' => $this->appKey];

        if ($body !== '') {
            $params['body_md5'] = \md5($body);
        }

        \ksort($params);

        $canonical = \implode("\n", [\strtoupper($method), $path, self::formatQueryParameters($params)]);
        $signature = \hash_hmac('sha256', $canonical, $this->appSecret);

        $query = \http_build_query(['auth_key' => $this->appKey, 'auth_signature' => $signature]);

        return new SignedPublicationRequest(\strtoupper($method), $path, $query, $body);
    }

    


    private static function formatQueryParameters(array $params): string
    {
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = "{$key}={$value}";
        }

        return \implode('&', $pairs);
    }
}
