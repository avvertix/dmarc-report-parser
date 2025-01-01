<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see PolicyOverrideReason https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class PolicyOverrideReason
{
    public function __construct(

        public readonly PolicyOverrideType $type,

        public readonly ?string $comment = null,

    ) {}

    public static function fromArray(array $policy): self
    {
        return new self(
            type: PolicyOverrideType::from($policy['type']),
            comment: $policy['comment'] ?? null,
        );
    }
}
