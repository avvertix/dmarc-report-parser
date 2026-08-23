<?php

namespace Avvertix\DmarcReportParser;

use Avvertix\DmarcReportParser\Data\DateRange;
use DateRangeError;

/**
 * The standardized filename of an aggregate report attachment.
 *
 * Reading it yields the policy domain and the reporting window without
 * decompressing or parsing anything, which makes it a cheap routing and
 * duplicate-detection key on an ingestion path.
 *
 * Many reporters do not follow the convention and never will, so a name that
 * does not match is routine rather than exceptional and parse() reports it by
 * returning null.
 *
 * @see Email RFC 9990, Section 3.5.2
 */
final class ReportFilename
{
    /**
     * The filename grammar of RFC 9990 Section 3.5.2:
     *
     * filename = receiver "!" policy-domain "!" begin-timestamp
     *            "!" end-timestamp [ "!" unique-id ] "." extension
     *
     * unique-id is 1*(ALPHA / DIGIT) in the RFC. It is matched more loosely
     * here, since the same section suggests UUIDs as a source and a hyphenated
     * UUID does not fit the published grammar.
     */
    private const string PATTERN = '/^(?<receiver>[^!]+)!(?<policy_domain>[^!]+)!(?<begin>\d+)!(?<end>\d+)(?:!(?<unique_id>[A-Za-z0-9-]+))?\.(?<extension>xml\.gz|xml)$/';

    public function __construct(
        /**
         * The Mail Receiver that generated the report.
         */
        public readonly string $receiver,

        /**
         * The DMARC Policy Domain the report is about.
         */
        public readonly string $policy_domain,

        /**
         * The reporting period.
         */
        public readonly DateRange $date_range,

        /**
         * Whether the attachment is gzip compressed, as told by the extension.
         */
        public readonly bool $compressed,

        /**
         * An optional identifier distinguishing reports generated simultaneously.
         */
        public readonly ?string $unique_id = null,
    ) {}

    /**
     * Read a report filename, or null when it yields no usable information.
     *
     * Null covers both a name that does not follow the convention and one that
     * follows it while claiming an impossible reporting window: either way the
     * caller has nothing to route on, and neither is worth an exception on an
     * ingestion path where non-conforming names are routine.
     *
     * A path is accepted as well as a bare name, so a caller can hand over
     * whatever it received without trimming first.
     */
    public static function parse(string $filename): ?self
    {
        if (preg_match(self::PATTERN, basename($filename), $parts) !== 1) {
            return null;
        }

        try {
            $dateRange = DateRange::fromArray([
                'begin' => $parts['begin'],
                'end' => $parts['end'],
            ]);
        } catch (DateRangeError) {
            return null;
        }

        return new self(
            receiver: $parts['receiver'],
            policy_domain: $parts['policy_domain'],
            date_range: $dateRange,
            compressed: $parts['extension'] === 'xml.gz',
            unique_id: ($parts['unique_id'] ?? '') === '' ? null : $parts['unique_id'], // @phpstan-ignore nullCoalesce.offset
        );
    }
}
