<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * A DMARC Report
 *
 * @see DMARC XML Schema https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 * @see ReportMetadataType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class DmarcReport
{
    public function __construct(
        /**
         * The DMARC report specification version
         */
        public readonly string $version,

        /**
         * Name of the organization/ISP that produced the report
         *
         * @see Report generator metadata
         */
        public readonly string $org_name,

        /**
         * Email or the organization that produced the report
         *
         * @see Report generator metadata
         */
        public readonly string $email,

        /**
         * Identifier of the report
         *
         * @see Report generator metadata
         */
        public readonly string $report_id,

        /**
         * The time range in UTC covered by messages in this report, specified in seconds since epoch
         *
         * @see Report generator metadata
         */
        public readonly DateRange $date_range,

        /**
         * The DMARC policy that applied to the messages in this report
         */
        public readonly Policy $publishedPolicy,

        /**
         * The evaluated records
         *
         * @var List<Record>
         */
        public readonly array $records,

        /**
         * Additional contact details of the organization generating the report
         *
         * @see Report generator metadata
         */
        public readonly ?string $extra_contact_info = null,

        /**
         * Errors reported if DMARC record lead to unexpected failures
         *
         * @see Report generator metadata
         */
        public readonly ?string $error = null,

        /**
         * The name and version of the software that generated the report.
         *
         * Null for RFC 7489-era reports, which do not carry this element.
         *
         * @see RFC 9990, Section 3.1.1.3
         */
        public readonly ?string $generator = null,

        /**
         * File level extension elements, empty when the report carries none.
         *
         * @see RFC 9990, Section 3.1.1.6
         *
         * @var list<Extension>
         */
        public readonly array $extensions = [],

    ) {}
}
