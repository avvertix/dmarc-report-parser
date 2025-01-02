<?php

namespace Avvertix\DmarcReportParser;

use Avvertix\DmarcReportParser\Data\DateRange;
use Avvertix\DmarcReportParser\Data\DmarcReport;
use Avvertix\DmarcReportParser\Data\Policy;
use Avvertix\DmarcReportParser\Data\Record;
use Avvertix\DmarcReportParser\Exception\UnsupportedFormatException;
use InvalidArgumentException;
use RuntimeException;
use Saloon\XmlWrangler\XmlReader;
use Symfony\Component\Mime\MimeTypes;
use ZipArchive;

final class DmarcReportParser
{
    /**
     * Supported mime types for file reading
     *
     * @var array
     */
    private const SUPPORTED_MIME_TYPES = [
        'text/xml',
        'application/gzip',
        'application/zip',
    ];

    /**
     * Parse a DMARC report from file.
     * File must be readable in xml format or compressed archives (zip, gz) containing only one xml file
     */
    public function fromFile(string $path): DmarcReport
    {
        $mimeTypes = new MimeTypes;
        $mimeType = $mimeTypes->guessMimeType($path);

        if (! in_array($mimeType, self::SUPPORTED_MIME_TYPES)) {
            throw new UnsupportedFormatException($mimeType, basename($path));
        }

        if ($mimeType === 'application/zip') {
            $zip = new ZipArchive;
            if ($zip->open($path) === true) {
                $content = $zip->getFromIndex(0);
                $zip->close();

                return $this->fromString($content);
            }
            throw new RuntimeException('Error reading zip file', 1);
        }

        if ($mimeType === 'application/gzip') {

            ob_start();
            $bytes = readgzfile($path);
            $content = ob_get_contents();
            ob_end_clean();

            if ($bytes === false) {
                throw new RuntimeException('Error reading gzip file', 1);
            }

            return $this->fromString($content);

        }

        $reader = XmlReader::fromFile($path);

        return $this->parseXmlReport($reader);
    }

    /**
     * Parse a DMARC report from a XML string representing the report content
     */
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
