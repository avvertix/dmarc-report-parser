<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see SPFAuthResultType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class SpfAuthResult
{
    public function __construct(

        public readonly string $domain,

        public readonly string $scope,

        public readonly SpfResult $result,

    ) {}

    public static function fromArray(array $result): self
    {
        return new self(
            domain: $result['domain'],
            scope: $result['scope'],
            result: SpfResult::from($result['result']),
        );
    }
}
