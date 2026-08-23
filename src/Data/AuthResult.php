<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see AuthResultType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class AuthResult
{
    public function __construct(

        /**
         * DKIM results
         */
        public readonly array $dkim,

        /**
         * SPF results.
         */
        public readonly array $spf,

    ) {}

    public static function fromArray(array $result): self
    {
        $dkimEntries = ! empty($result['dkim'] ?? null) ? (isset($result['dkim']['domain']) ? [$result['dkim']] : $result['dkim']) : [];

        // RFC 9990 Section 3.1.1.11 makes spf optional, where RFC 7489 required
        // exactly one, so auth_results may carry no spf element at all.
        $spfEntries = ! empty($result['spf'] ?? null) ? (isset($result['spf']['domain']) ? [$result['spf']] : $result['spf']) : [];

        return new self(
            dkim: array_map(fn ($item) => DkimAuthResult::fromArray($item), $dkimEntries),
            spf: array_map(fn ($item) => SpfAuthResult::fromArray($item), $spfEntries),
        );
    }
}
