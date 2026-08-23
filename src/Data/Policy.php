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

        /**
         * The method used to discover the DMARC Policy Record used during evaluation.
         *
         * Null for RFC 7489-era reports, which do not carry this element.
         *
         * @see RFC 9990, Section 3.1.1.5
         */
        public readonly ?DiscoveryMethod $discovery_method = null,

        /**
         * Whether testing mode was declared in the DMARC Record, the "t" tag.
         *
         * Null for RFC 7489-era reports, which do not carry this element. That
         * is not the same as TestingMode::NO.
         *
         * @see RFC 9990, Section 3.1.1.5
         */
        public readonly ?TestingMode $testing = null,

        /**
         * The policy to apply to messages from non-existent subdomains.
         *
         * Null for RFC 7489-era reports, which do not carry this element.
         *
         * @see RFC 9990, Section 3.1.1.5
         */
        public readonly ?DispositionType $np = null,
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
            discovery_method: is_null($policy['discovery_method'] ?? null) ? null : DiscoveryMethod::fromReport($policy['discovery_method']),
            testing: is_null($policy['testing'] ?? null) ? null : TestingMode::fromReport($policy['testing']),
            np: empty($policy['np'] ?? null) ? null : DispositionType::from($policy['np']),
        );
    }
}
