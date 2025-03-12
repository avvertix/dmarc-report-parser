<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see IdentifierType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class Identifier
{
    public function __construct(

        /**
         * The RFC5321.MailFrom domain.
         */
        public readonly ?string $envelope_from,

        /**
         * The RFC5322.From domain.
         */
        public readonly string $header_from,

        /**
         * The envelope recipient domain.
         */
        public readonly ?string $envelope_to,

    ) {}

    public static function fromArray(array $identifiers): self
    {
        return new self(
            header_from: $identifiers['header_from'],
            envelope_from: $identifiers['envelope_from'] ?? null,
            envelope_to: $identifiers['envelope_to'] ?? null,
        );
    }
}
