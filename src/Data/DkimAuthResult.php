<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see DKIMAuthResultType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class DkimAuthResult
{
    public function __construct(

        /**
         * The "d=" parameter in the signature.
         */
        public readonly string $domain,

        /**
         * The DKIM verification result.
         */
        public readonly DkimResult $result,

        /**
         * The "s=" parameter in the signature.
         */
        public readonly ?string $selector = null,

        /**
         * Any extra information (e.g., from Authentication-Results)
         */
        public readonly ?string $human_result = null,

    ) {}

    public static function fromArray(array $result): self
    {
        return new self(
            domain: $result['domain'],
            result: DkimResult::from($result['result']),
            selector: $result['selector'] ?? null,
            human_result: $result['human_result'] ?? null,
        );
    }
}
