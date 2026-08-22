<?php

namespace Avvertix\DmarcReportParser\Tests\Support;

use RuntimeException;
use ZipArchive;

/**
 * Generates compressed fixtures on the fly.
 *
 * The point of these archives is the compression ratio, so they are written at
 * test time instead of being committed: a few gigabytes of repeated bytes cost
 * nothing to generate and would be an oddity in the repository.
 */
final class Archives
{
    /**
     * Write a gzip archive whose expansion is $expandedBytes long
     */
    public static function gzip(string $name, int $expandedBytes): string
    {
        $path = self::path($name);

        $handle = gzopen($path, 'wb9');

        if ($handle === false) {
            throw new RuntimeException("Unable to write the gzip fixture [{$path}].");
        }

        foreach (self::chunks($expandedBytes) as $chunk) {
            gzwrite($handle, $chunk);
        }

        gzclose($handle);

        return $path;
    }

    /**
     * Write a zip archive holding a single entry that expands to $expandedBytes
     */
    public static function zip(string $name, int $expandedBytes, string $entryName = 'report.xml'): string
    {
        $path = self::path($name);
        $entry = self::path($name.'.entry');

        $handle = fopen($entry, 'wb');

        if ($handle === false) {
            throw new RuntimeException("Unable to write the zip entry [{$entry}].");
        }

        foreach (self::chunks($expandedBytes) as $chunk) {
            fwrite($handle, $chunk);
        }

        fclose($handle);

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to write the zip fixture [{$path}].");
        }

        $zip->addFile($entry, $entryName);
        $zip->setCompressionName($entryName, ZipArchive::CM_DEFLATE, 9);
        $zip->close();

        unlink($entry);

        return $path;
    }

    /**
     * Write a gzip archive whose ISIZE trailer lies about how far it expands.
     *
     * ISIZE is the last four bytes of the file and whoever builds the archive
     * writes them, so a small claim there says nothing about the real expansion.
     */
    public static function gzipWithFalsifiedTrailer(string $name, int $expandedBytes, int $claimedBytes): string
    {
        $path = self::gzip($name, $expandedBytes);

        $handle = fopen($path, 'r+b');

        if ($handle === false) {
            throw new RuntimeException("Unable to falsify the gzip trailer of [{$path}].");
        }

        fseek($handle, -4, SEEK_END);
        fwrite($handle, pack('V', $claimedBytes));
        fclose($handle);

        return $path;
    }

    /**
     * Write a zip archive holding $report first and $additionalEntries of filler after it
     */
    public static function multiEntryZip(string $name, string $report, int $additionalEntries): string
    {
        $path = self::path($name);

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to write the zip fixture [{$path}].");
        }

        $zip->addFile($report, basename($report));

        for ($entry = 0; $entry < $additionalEntries; $entry++) {
            $zip->addFromString("filler-{$entry}.xml", str_repeat('a', 1024));
        }

        $zip->close();

        return $path;
    }

    /**
     * Write a zip archive whose declared uncompressed size lies about how far it expands.
     *
     * The size ZipArchive::statIndex() reports comes from the central directory,
     * which is part of the archive: it is a claim by whoever built the file, and
     * this rewrites it to a small one while leaving the entry untouched.
     */
    public static function zipWithFalsifiedSize(string $name, int $expandedBytes, int $claimedBytes): string
    {
        $path = self::zip($name, $expandedBytes);

        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new RuntimeException("Unable to falsify the declared size of [{$path}].");
        }

        $localHeader = strpos($raw, "PK\x03\x04");
        $centralHeader = strpos($raw, "PK\x01\x02");

        if ($localHeader === false || $centralHeader === false) {
            throw new RuntimeException("Unable to locate the headers of [{$path}].");
        }

        $claim = pack('V', $claimedBytes);

        $raw = substr_replace($raw, $claim, $localHeader + 22, 4);
        $raw = substr_replace($raw, $claim, $centralHeader + 24, 4);

        file_put_contents($path, $raw);

        return $path;
    }

    /**
     * A one megabyte chunk of highly compressible filler, repeated until $bytes are written
     *
     * @return iterable<int, string>
     */
    private static function chunks(int $bytes): iterable
    {
        $chunkSize = 1024 * 1024;
        $chunk = str_repeat('a', $chunkSize);

        for ($written = 0; $written < $bytes; $written += $chunkSize) {
            yield substr($chunk, 0, min($chunkSize, $bytes - $written));
        }
    }

    private static function path(string $name): string
    {
        $directory = sys_get_temp_dir().'/dmarc-report-parser-fixtures';

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory.'/'.$name;
    }
}
