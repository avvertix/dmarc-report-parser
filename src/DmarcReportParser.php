<?php

namespace Avvertix\DmarcReportParser;

use Avvertix\DmarcReportParser\Data\DateRange;
use Avvertix\DmarcReportParser\Data\DmarcReport;
use Avvertix\DmarcReportParser\Data\Policy;
use Avvertix\DmarcReportParser\Data\Record;
use InvalidArgumentException;
use Saloon\XmlWrangler\XmlReader;

final class DmarcReportParser
{
    /**
     * Parse a DMARC xml report from file
     */
    public function fromFile(string $path): DmarcReport
    {
        $reader = XmlReader::fromFile($path);

        return $this->parseXmlReport($reader);
    }

    public function fromString(string $xml): DmarcReport
    {
        $reader = XmlReader::fromString($xml);

        return $this->parseXmlReport($reader);
    }

    private function parseXmlReport(XmlReader $reader): DmarcReport
    {
        $version = $reader->value('feedback.version')->sole();

        if (version_compare($version, '1.0', '!=')) {
            throw new InvalidArgumentException("Unexpected version identifier found. Expected 1.0, found [{$version}]");
        }

        $metadata = $reader->value('feedback.report_metadata')->sole();

        $records = array_map(fn ($item) => Record::fromArray($item), $reader->value('feedback.record')->get());

        return new DmarcReport(
            version: $version,

            org_name: $metadata['org_name'],
            email: $metadata['email'],
            report_id: $metadata['report_id'],
            date_range: DateRange::fromArray($metadata['date_range'] ?? []),

            publishedPolicy: Policy::fromArray($reader->value('feedback.policy_published')->sole()),

            records: $records,

            extra_contact_info: $metadata['extra_contact_info'] ?? null,
            error: $metadata['error'] ?? null,

        );
    }
}
