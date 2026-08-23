<?php

use Avvertix\DmarcReportParser\Data\DiscoveryMethod;
use Avvertix\DmarcReportParser\Data\DispositionType;
use Avvertix\DmarcReportParser\Data\DkimAuthResult;
use Avvertix\DmarcReportParser\Data\DmarcReport;
use Avvertix\DmarcReportParser\Data\PolicyOverrideType;
use Avvertix\DmarcReportParser\Data\TestingMode;
use Avvertix\DmarcReportParser\DmarcReportParser;

/**
 * A minimal RFC 9990 report carrying extension elements at either level.
 *
 * File level sits inside an <extension> wrapper after policy_published; record
 * level is appended bare after auth_results, per RFC 9990 Section 5.
 */
function reportWithExtensions(string $fileLevel, string $recordLevel): string
{
    $xml = reportWithAuthResults('<spf><domain>example.com</domain><result>fail</result></spf>');

    $xml = str_replace('</auth_results>', '</auth_results>'.$recordLevel, $xml);

    $xml = str_replace(
        '<feedback xmlns="urn:ietf:params:xml:ns:dmarc-2.0">',
        '<feedback xmlns="urn:ietf:params:xml:ns:dmarc-2.0" xmlns:ext="https://example.com/arc-ext">',
        $xml,
    );

    return str_replace('</policy_published>', '</policy_published>'.$fileLevel, $xml);
}

function reportWithReason(string $reason): string
{
    return str_replace(
        '<spf>fail</spf>',
        "<spf>fail</spf>{$reason}",
        reportWithAuthResults('<spf><domain>example.com</domain><result>fail</result></spf>'),
    );
}

/**
 * A minimal RFC 9990 report, with $authResults spliced in, so a test can vary
 * one part of the document without restating the whole thing.
 */
function reportWithAuthResults(string $authResults): string
{
    return <<<XML
    <?xml version="1.0"?>
    <feedback xmlns="urn:ietf:params:xml:ns:dmarc-2.0">
      <version>1.0</version>
      <report_metadata>
        <org_name>Sample Reporter</org_name>
        <email>report_sender@example-reporter.com</email>
        <report_id>3v98abbp8ya9n3va8yr8oa3ya</report_id>
        <date_range><begin>302832000</begin><end>302918399</end></date_range>
      </report_metadata>
      <policy_published>
        <domain>example.com</domain>
        <p>quarantine</p>
      </policy_published>
      <record>
        <row>
          <source_ip>192.0.2.123</source_ip>
          <count>123</count>
          <policy_evaluated>
            <disposition>pass</disposition>
            <dkim>pass</dkim>
            <spf>fail</spf>
          </policy_evaluated>
        </row>
        <identifiers><header_from>example.com</header_from></identifiers>
        <auth_results>{$authResults}</auth_results>
      </record>
    </feedback>
    XML;
}

it('parses the RFC 9990 sample report', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/rfc9990-sample.xml');

    expect($report)
        ->toBeInstanceOf(DmarcReport::class)
        ->version->toEqual('1.0')
        ->org_name->toEqual('Sample Reporter')
        ->email->toEqual('report_sender@example-reporter.com')
        ->report_id->toEqual('3v98abbp8ya9n3va8yr8oa3ya')
        ->records->toHaveCount(1);
});

it('reads the policy discovery method', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/rfc9990-sample.xml');

    expect($report->publishedPolicy->discovery_method)
        ->toBe(DiscoveryMethod::TREEWALK);
});

it('leaves the discovery method null for a report that predates it', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.xml');

    expect($report->publishedPolicy->discovery_method)
        ->toBeNull();
});

it('reads the policy testing mode', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/rfc9990-sample.xml');

    expect($report->publishedPolicy->testing)
        ->toBe(TestingMode::NO);
});

it('distinguishes an absent testing mode from a declared no', function () {
    // Absent is not the same as "n": a 7489-era report says nothing at all
    // about the t tag, which is why this is nullable rather than a bool.
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.xml');

    expect($report->publishedPolicy->testing)
        ->toBeNull();
});

it('reads the policy for non-existent subdomains', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/rfc9990-sample.xml');

    expect($report->publishedPolicy)
        ->np->toBe(DispositionType::NONE)
        ->sp->toBe(DispositionType::NONE)
        ->p->toBe(DispositionType::QUARANTINE);
});

it('reads the report generator', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/rfc9990-sample.xml');

    expect($report->generator)
        ->toEqual('Example DMARC Aggregate Reporter v1.2');
});

it('leaves generator and np null for a report that predates them', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.xml');

    expect($report->generator)->toBeNull();
    expect($report->publishedPolicy->np)->toBeNull();
});

it('preserves a file level extension', function () {
    // RFC 9990 Section 5: extensions exist so future revisions can add data.
    // Dropping them silently defeats the mechanism.
    $xml = reportWithExtensions(
        fileLevel: '<extension><ext:arc-override>never</ext:arc-override></extension>',
        recordLevel: '',
    );

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->extensions)
        ->toHaveCount(1)
        ->and($report->extensions[0])
        ->name->toEqual('arc-override')
        ->namespace->toEqual('https://example.com/arc-ext')
        ->content->toEqual('never');
});

it('preserves a record level extension', function () {
    $xml = reportWithExtensions(
        fileLevel: '',
        recordLevel: '<ext:arc-results>chain-ok</ext:arc-results>',
    );

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records[0]->extensions)
        ->toHaveCount(1)
        ->and($report->records[0]->extensions[0])
        ->name->toEqual('arc-results')
        ->namespace->toEqual('https://example.com/arc-ext')
        ->content->toEqual('chain-ok');
});

it('parses a report carrying an unrecognised extension without complaint', function () {
    // Section 5: a processor unable to handle an extension should ignore it and
    // continue. It is never a parse error and never a warning.
    $xml = reportWithExtensions(
        fileLevel: '<extension><ext:invented>1</ext:invented></extension>',
        recordLevel: '<ext:also-invented><ext:nested>deep</ext:nested></ext:also-invented>',
    );

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records)->toHaveCount(1);
    expect($report->extensions[0])->name->toEqual('invented');
    expect($report->records[0]->extensions[0])
        ->name->toEqual('also-invented')
        ->content->toBeArray();
});

it('keeps a per-signature selector for every dkim result', function () {
    // Selectors are discoverable only from report data, so nothing may drop or
    // flatten them while normalising. RFC 9990 makes selector mandatory; RFC
    // 7489 did not, and real reports from that era omit it.
    $xml = reportWithAuthResults(
        '<dkim><domain>example.com</domain><result>pass</result><selector>selector1</selector></dkim>'.
        '<dkim><domain>example.com</domain><result>fail</result></dkim>'.
        '<dkim><domain>other.example</domain><result>pass</result><selector>selector2</selector></dkim>',
    );

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records[0]->auth_results->dkim)->toHaveCount(3);

    expect(array_map(fn ($dkim) => $dkim->selector, $report->records[0]->auth_results->dkim))
        ->toBe(['selector1', null, 'selector2']);
});

it('accepts a report_id that does not match the ABNF', function (string $reportId, string $expected) {
    // RFC 9990 Section 3.5.1 gives report_id an ABNF, but says the format "is
    // not very strict, as the key goal is uniqueness". Small reporters send
    // malformed identifiers routinely and the consuming app dedupes on this
    // value, so whatever arrived has to reach the caller intact.
    $xml = str_replace(
        '<report_id>3v98abbp8ya9n3va8yr8oa3ya</report_id>',
        "<report_id>{$reportId}</report_id>",
        reportWithAuthResults('<spf><domain>example.com</domain><result>fail</result></spf>'),
    );

    $dmarc = new DmarcReportParser;

    expect($dmarc->fromString($xml))
        ->report_id->toEqual($expected);
})->with([
    'conforming' => ['1721054318-example.com@example.org', '1721054318-example.com@example.org'],
    'angle bracketed' => ['&lt;sample-ridtxt@example.com&gt;', '<sample-ridtxt@example.com>'],
    'spaces' => ['report 42', 'report 42'],
    'two at signs' => ['a@b@c', 'a@b@c'],
    'numeric' => ['1735694883.674529', '1735694883.674529'],
]);

it('parses a report exercising every element RFC 9990 introduces', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/rfc9990-full.xml');

    expect($report)
        ->generator->toEqual('Example DMARC Aggregate Reporter v1.2')
        ->error->toEqual('Multiple DMARC Policy Records found')
        ->records->toHaveCount(2);

    expect($report->publishedPolicy)
        ->discovery_method->toBe(DiscoveryMethod::TREEWALK)
        ->testing->toBe(TestingMode::YES)
        ->np->toBe(DispositionType::REJECT)
        ->sp->toBe(DispositionType::QUARANTINE)
        ->p->toBe(DispositionType::REJECT)
        ->pct->toBeNull(); // removed by RFC 9990

    expect($report->extensions[0])
        ->name->toEqual('arc-override')
        ->namespace->toEqual('https://example.com/arc-ext');

    expect($report->records[0]->extensions[0])
        ->name->toEqual('arc-results')
        ->namespace->toEqual('https://example.com/arc-ext');

    expect($report->records[0]->row->policy_evaluated)
        ->disposition->toBe(DispositionType::PASS)
        ->reasons->toHaveCount(1);

    expect($report->records[0]->row->policy_evaluated->reasons[0])
        ->type->toBe(PolicyOverrideType::POLICY_TEST_MODE);

    expect(array_map(fn ($dkim) => $dkim->selector, $report->records[0]->auth_results->dkim))
        ->toBe(['selector1', 'selector2']);

    // Second record carries spf but no dkim, the mirror of the first.
    expect($report->records[1]->auth_results)
        ->dkim->toBe([])
        ->spf->toHaveCount(1);

    expect($report->records[1]->extensions)->toBe([]);
});

it('reports no extensions when a report carries none', function () {
    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.xml');

    expect($report->extensions)->toBe([]);
    expect($report->records[0]->extensions)->toBe([]);
});

it('parses a record whose auth_results carry no spf result', function () {
    // RFC 9990 Section 3.1.1.11 makes spf optional; RFC 7489 required exactly one.
    $xml = reportWithAuthResults('<dkim><domain>example.com</domain><result>pass</result><selector>abc123</selector></dkim>');

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records[0]->auth_results)
        ->spf->toBe([])
        ->dkim->toHaveCount(1)
        ->dkim->toContainOnlyInstancesOf(DkimAuthResult::class);
});

it('parses the policy_test_mode override reason', function () {
    // New override type in RFC 9990 Section 3.1.6, corresponding to the "t" tag.
    $xml = reportWithReason('<reason><type>policy_test_mode</type><comment>in testing</comment></reason>');

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records[0]->row->policy_evaluated->reasons[0])
        ->type->toBe(PolicyOverrideType::POLICY_TEST_MODE)
        ->comment->toEqual('in testing');
});

it('keeps parsing when an override reason type is not recognised', function () {
    // The override type list has already changed twice across RFCs, so a value
    // from a future revision must not take the whole report down with it.
    $xml = reportWithReason('<reason><type>invented_in_a_later_rfc</type></reason>');

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records[0]->row->policy_evaluated->reasons[0])
        ->type->toBe(PolicyOverrideType::UNKNOWN);
});

it('still parses override reasons that RFC 9990 removed', function (string $type, PolicyOverrideType $expected) {
    // forwarded and sampled_out are gone from RFC 9990, but a 2019 report using
    // them is still a valid RFC 7489 report.
    $xml = reportWithReason("<reason><type>{$type}</type></reason>");

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromString($xml);

    expect($report->records[0]->row->policy_evaluated->reasons[0])
        ->type->toBe($expected);
})->with([
    ['forwarded', PolicyOverrideType::FORWARDED],
    ['sampled_out', PolicyOverrideType::SAMPLED_OUT],
]);
