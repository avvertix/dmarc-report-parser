<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * The DMARC policy that applied to the messages.
 *
 * @see PolicyPublishedType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class Policy
{
    public function __construct(
        /**
         * The domain at which the DMARC record was found.
         */
        public readonly string $domain,

        /**
         * The DKIM alignment mode.
         */
        public readonly ?AlignmentMode $adkim,

        /**
         * The SPF alignment mode.
         */
        public readonly ?AlignmentMode $aspf,

        /**
         * The policy to apply to messages from the domain.
         */
        public readonly DispositionType $p,

        /**
         * The policy to apply to messages from subdomains.
         */
        public readonly ?DispositionType $sp,

        /**
         * The percent of messages to which policy applies.
         */
        public readonly ?int $pct,

        /**
         * Failure reporting options in effect.
         */
        public readonly ?string $fo,
    ) {}

    public static function fromArray(array $policy): self
    {
        return new self(
            domain: $policy['domain'],
            p: DispositionType::from($policy['p']),
            fo: $policy['fo'] ?? null,
            pct: is_null($policy['pct'] ?? null) ? null : intval($policy['pct'], 10),
            sp: empty($policy['sp'] ?? null) ? null : DispositionType::from($policy['sp']),
            adkim: is_null($policy['adkim'] ?? null) ? null : AlignmentMode::from($policy['adkim']),
            aspf: is_null($policy['aspf'] ?? null) ? null : AlignmentMode::from($policy['aspf']),
        );
    }
}
