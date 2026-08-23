<?php

namespace Avvertix\DmarcReportParser;

use Avvertix\DmarcReportParser\Data\DateRange;
use Avvertix\DmarcReportParser\Data\DmarcReport;
use Avvertix\DmarcReportParser\Data\Extension;
use Avvertix\DmarcReportParser\Data\Policy;
use Avvertix\DmarcReportParser\Data\Record;
use Avvertix\DmarcReportParser\Exception\DecompressionLimitException;
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
    private const array SUPPORTED_MIME_TYPES = [
        'text/xml',
        'application/gzip',
        'application/zip',
    ];

    /**
     * Size of a single read while decompressing
     */
    private const int READ_CHUNK_BYTES = 8192;

    private readonly ParserConfiguration $configuration;

    /**
     * Instantiate a DmarcReportParser
     */
    public function __construct(?ParserConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?? new ParserConfiguration;
    }

    /**
     * Parse a DMARC report from file.
     * File must be readable in xml format or compressed archives (zip, gz) containing only one xml file.
     *
     * A zip holding more than one entry is not rejected: the first entry is read and the rest ignored.
     * Decompression stops as soon as the expansion passes the configured cap.
     *
     * @throws DecompressionLimitException when the file expands beyond the configured cap
     * @throws UnsupportedFormatException when the file is not xml, zip or gzip
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
                $stream = $zip->getStreamIndex(0);

                if ($stream === false) {
                    $zip->close();

                    throw new RuntimeException('Error reading zip file', 1);
                }

                try {
                    $content = $this->readWithinCap($stream, basename($path));
                } finally {
                    fclose($stream);
                    $zip->close();
                }

                return $this->fromString($content);
            }

            throw new RuntimeException('Error reading zip file', 1);
        }

        if ($mimeType === 'application/gzip') {
            $handle = gzopen($path, 'rb');

            if ($handle === false) {
                throw new RuntimeException('Error reading gzip file', 1);
            }

            try {
                $content = $this->readWithinCap($handle, basename($path));
            } finally {
                gzclose($handle);
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

    /**
     * Read a decompression stream, stopping as soon as the expansion exceeds the cap.
     *
     * The count is kept while reading rather than taken from the archive: both gzip's
     * trailing ISIZE and the zip central directory are written by whoever built the
     * file and can claim anything.
     *
     * @param  resource  $stream
     *
     * @throws DecompressionLimitException
     */
    private function readWithinCap($stream, string $fileName): string
    {
        $maximum = $this->configuration->maxDecompressedBytes;

        $content = '';
        $read = 0;

        while (! feof($stream)) {
            $chunk = fread($stream, self::READ_CHUNK_BYTES);

            if ($chunk === false) {
                throw new RuntimeException("Error reading file [{$fileName}]", 1);
            }

            $read += strlen($chunk);

            if ($read > $maximum) {
                throw new DecompressionLimitException($fileName, $maximum);
            }

            $content .= $chunk;
        }

        return $content;
    }

    private function parseXmlReport(XmlReader $reader): DmarcReport
    {
        $version = $reader->value('feedback.version')->first() ?? '1.0'; // assuming version 1.0 if not specified

        if ($version !== '1' && version_compare($version, '1.0', '!=')) {
            throw new InvalidArgumentException("Unexpected version identifier found. Expected 1.0, found [{$version}]");
        }

        $metadata = $reader->value('feedback.report_metadata')->sole();

        $namespaces = $this->namespaceDeclarations($reader);

        $records = array_map(fn ($item) => Record::fromArray($item, $namespaces), $reader->value('feedback.record')->get());

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
            generator: $metadata['generator'] ?? null,
            extensions: Extension::listFromArray($reader->value('feedback.extension')->first() ?? [], $namespaces),

        );
    }

    /**
     * The namespace declarations in scope on the report, as attribute name to URI.
     *
     * Extension elements are namespaced and carry only their prefix in the
     * element name, so the declarations are needed to resolve a prefix back to
     * the URI identifying the extension.
     *
     * @see RFC 9990, Section 5
     *
     * @return array<string, string>
     */
    private function namespaceDeclarations(XmlReader $reader): array
    {
        $root = $reader->element('feedback')->first();

        return $root === null ? [] : array_filter(
            $root->getAttributes(),
            fn (string $name) => str_starts_with($name, 'xmlns'),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
