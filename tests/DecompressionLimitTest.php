<?php

use Avvertix\DmarcReportParser\Data\DmarcReport;
use Avvertix\DmarcReportParser\DmarcReportParser;
use Avvertix\DmarcReportParser\Exception\DecompressionLimitException;
use Avvertix\DmarcReportParser\ParserConfiguration;
use Avvertix\DmarcReportParser\Tests\Support\Archives;
use Symfony\Component\Mime\MimeTypes;

/**
 * Symfony's mime type map allocates around ten megabytes the first time it is
 * loaded. It happens once per process and has nothing to do with decompression,
 * so warm it up before any test measures peak memory.
 */
beforeEach(function () {
    (new MimeTypes)->guessMimeType('./tests/fixtures/dmarc.xml.gz');
});

it('refuses a gzip file that expands beyond the cap', function () {
    $cap = 64 * 1024;
    $path = Archives::gzip('exceeds-cap.xml.gz', 32 * 1024 * 1024);

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: $cap));

    $parsingARealReport = peakMemoryDelta(fn () => $dmarc->fromFile('./tests/fixtures/dmarc.xml.gz'));

    $expandingTheBomb = peakMemoryDelta(function () use ($dmarc, $path) {
        expect(fn () => $dmarc->fromFile($path))
            ->toThrow(DecompressionLimitException::class);
    });

    // Thirty two megabytes of expansion must cost no more than a real report
    // plus the cap: a guard that buffers everything and measures afterwards
    // allocates the full expansion and fails here.
    expect($expandingTheBomb)
        ->toBeLessThan($parsingARealReport + $cap * 10);
});

it('refuses a gzip file whose trailer understates the expansion', function () {
    $cap = 64 * 1024;
    $path = Archives::gzipWithFalsifiedTrailer('lying-trailer.xml.gz', 32 * 1024 * 1024, claimedBytes: 1024);

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: $cap));

    $dmarc->fromFile($path);
})->throws(DecompressionLimitException::class);

it('refuses a zip file that expands beyond the cap', function () {
    $cap = 64 * 1024;
    $path = Archives::zip('exceeds-cap.zip', 32 * 1024 * 1024);

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: $cap));

    $parsingARealReport = peakMemoryDelta(fn () => $dmarc->fromFile('./tests/fixtures/dmarc.zip'));

    $expandingTheBomb = peakMemoryDelta(function () use ($dmarc, $path) {
        expect(fn () => $dmarc->fromFile($path))
            ->toThrow(DecompressionLimitException::class);
    });

    expect($expandingTheBomb)
        ->toBeLessThan($parsingARealReport + $cap * 10);
});

it('refuses a zip file whose declared size understates the expansion', function () {
    $cap = 64 * 1024;
    $path = Archives::zipWithFalsifiedSize('lying-size.zip', 32 * 1024 * 1024, claimedBytes: 1024);

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: $cap));

    $dmarc->fromFile($path);
})->throws(DecompressionLimitException::class);

it('refuses the committed decompression fixture', function () {
    // One kilobyte of zip holding a megabyte of filler, kept in the repository as
    // a small standing example of the ratio the cap exists to stop.
    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: 64 * 1024));

    $dmarc->fromFile('./tests/fixtures/dmarc-decompression.zip');
})->throws(DecompressionLimitException::class);

it('reads only the first entry of a multi entry zip file', function () {
    $path = Archives::multiEntryZip('many-entries.zip', './tests/fixtures/dmarc.xml', additionalEntries: 1000);

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile($path);

    // The remaining entries are ignored rather than rejected, so the cap applies
    // to one entry and the other thousand cost nothing.
    expect($report)
        ->toBeInstanceOf(DmarcReport::class)
        ->report_id->toEqual('17265e8a413a4989bc4eef2c46ef08d0');
});

it('parses a report that expands to exactly the cap', function (string $fixture) {
    $reportBytes = filesize('./tests/fixtures/dmarc.xml');

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: $reportBytes));

    expect($dmarc->fromFile($fixture))
        ->toBeInstanceOf(DmarcReport::class)
        ->report_id->toEqual('17265e8a413a4989bc4eef2c46ef08d0')
        ->records->toHaveCount(2);
})->with([
    './tests/fixtures/dmarc.xml.gz',
    './tests/fixtures/dmarc.zip',
]);

it('refuses a report that expands one byte past the cap', function (string $fixture) {
    $reportBytes = filesize('./tests/fixtures/dmarc.xml');

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: $reportBytes - 1));

    $dmarc->fromFile($fixture);
})->with([
    './tests/fixtures/dmarc.xml.gz',
    './tests/fixtures/dmarc.zip',
])->throws(DecompressionLimitException::class);

it('refuses a report larger than the default cap', function () {
    $path = Archives::gzip('past-default-cap.xml.gz', ParserConfiguration::DEFAULT_MAX_DECOMPRESSED_BYTES + 1);

    $dmarc = new DmarcReportParser;

    $dmarc->fromFile($path);
})->throws(DecompressionLimitException::class);

it('reports a corrupt gzip file as an error rather than as too large', function () {
    // Once zlib rejects the trailer every further read fails while end of file is
    // never reported, so the loop has to stop on the failed read itself.
    $path = Archives::gzipWithFalsifiedTrailer('corrupt.xml.gz', 1024 * 1024, claimedBytes: 1024);

    $dmarc = new DmarcReportParser(new ParserConfiguration(maxDecompressedBytes: 10 * 1024 * 1024));

    $startedAt = microtime(true);

    try {
        $dmarc->fromFile($path);

        $this->fail('Expected a corrupt gzip file to be rejected.');
    } catch (RuntimeException $exception) {
        expect($exception)->not->toBeInstanceOf(DecompressionLimitException::class);
    }

    expect(microtime(true) - $startedAt)->toBeLessThan(5);
});
