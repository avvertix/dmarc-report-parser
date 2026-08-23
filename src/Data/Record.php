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

        /**
         * Record level extension elements, empty when the record carries none.
         *
         * Unlike the file level, these are not wrapped: RFC 9990 appends them
         * directly after auth_results.
         *
         * @see RFC 9990, Section 3.1.1.7
         *
         * @var list<Extension>
         */
        public readonly array $extensions = [],

    ) {}

    /**
     * @param  array<string, string>  $namespaces  the report's namespace declarations, used to resolve extension prefixes
     */
    public static function fromArray(array $record, array $namespaces = []): self
    {
        return new self(
            row: Row::fromArray($record['row']),
            identifiers: Identifier::fromArray($record['identifiers']),
            auth_results: AuthResult::fromArray($record['auth_results']),
            extensions: Extension::listFromArray($record, $namespaces),
        );
    }
}
