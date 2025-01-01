<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * The DMARC report record that contains all the authentication results that
 * were evaluated by the receiving system for the given set of messages.
 *
 * @see RecordType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class Record
{
    public function __construct(

        public readonly Row $row,

        public readonly Identifier $identifiers,

        public readonly AuthResult $auth_results,

    ) {}

    public static function fromArray(array $record): self
    {
        return new self(
            row: Row::fromArray($record['row']),
            identifiers: Identifier::fromArray($record['identifiers']),
            auth_results: AuthResult::fromArray($record['auth_results']),
        );
    }
}
