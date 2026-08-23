<?php

use Avvertix\DmarcReportParser\Data\DateRange;
use Avvertix\DmarcReportParser\ReportFilename;

it('reads the RFC 9990 example filename', function () {
    // The example from RFC 9990 Section 3.5.2, verbatim.
    $filename = ReportFilename::parse('mail.receiver.example!example.com!1013662812!1013749130.xml.gz');

    expect($filename)
        ->toBeInstanceOf(ReportFilename::class)
        ->receiver->toEqual('mail.receiver.example')
        ->policy_domain->toEqual('example.com')
        ->unique_id->toBeNull()
        ->compressed->toBeTrue();

    expect($filename->date_range)
        ->toBeInstanceOf(DateRange::class)
        ->begin->toEqual(new DateTimeImmutable('@1013662812'))
        ->end->toEqual(new DateTimeImmutable('@1013749130'));
});

it('reads a filename carrying a unique id', function () {
    $filename = ReportFilename::parse('mail.receiver.example!example.com!1013662812!1013749130!abc123.xml');

    expect($filename)
        ->unique_id->toEqual('abc123')
        ->compressed->toBeFalse();
});

it('reads a filename given as a path', function () {
    $filename = ReportFilename::parse('/var/spool/dmarc/mail.receiver.example!example.com!1013662812!1013749130.xml.gz');

    expect($filename)->receiver->toEqual('mail.receiver.example');
});

it('returns null for a filename that does not follow the convention', function (string $filename) {
    expect(ReportFilename::parse($filename))->toBeNull();
})->with([
    'no separators' => 'report.xml',
    'too few fields' => 'mail.receiver.example!example.com!1013662812.xml',
    'non-numeric timestamps' => 'mail.receiver.example!example.com!yesterday!today.xml',
    'unsupported extension' => 'mail.receiver.example!example.com!1013662812!1013749130.zip',
    'no extension' => 'mail.receiver.example!example.com!1013662812!1013749130',
    'empty' => '',
]);

it('reads a hyphenated uuid as the unique id', function () {
    // RFC 9990 Section 3.5.2 suggests UUIDs for unique-id while its own grammar
    // allows only ALPHA and DIGIT, so a hyphenated UUID contradicts the ABNF.
    // Reporters will use them anyway, and the point of this helper is to salvage
    // information rather than to police it.
    $filename = ReportFilename::parse('mail.receiver.example!example.com!1013662812!1013749130!1a0d5f3e-9c2b-4d7a-8e1f-2b3c4d5e6f70.xml.gz');

    expect($filename)->unique_id->toEqual('1a0d5f3e-9c2b-4d7a-8e1f-2b3c4d5e6f70');
});

it('returns null for a range whose end precedes its begin', function () {
    // The name fits the grammar but the window is nonsense, so there is still no
    // usable information. A caller on the ingestion path should not have to
    // catch an exception to find that out.
    expect(ReportFilename::parse('mail.receiver.example!example.com!1013749130!1013662812.xml'))
        ->toBeNull();
});
