<?php

use Avvertix\DmarcReportParser\DmarcReportParser;
use Avvertix\DmarcReportParser\Tests\Support\Archives;

it('produces the same report from xml, gzip and zip', function (string $fixture) {
    // A report is the same report however it arrived. The compression is
    // transport, and must not show up in the parsed result.
    $dmarc = new DmarcReportParser;

    $fromXml = $dmarc->fromFile($fixture);
    $fromGzip = $dmarc->fromFile(Archives::gzipOf($fixture, basename($fixture).'.gz'));
    $fromZip = $dmarc->fromFile(Archives::zipOf($fixture, basename($fixture).'.zip'));

    expect($fromGzip)->toEqual($fromXml);
    expect($fromZip)->toEqual($fromXml);
})->with([
    './tests/fixtures/dmarc.xml',
    './tests/fixtures/rfc9990-sample.xml',
]);
