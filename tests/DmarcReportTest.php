<?php

use Avvertix\DmarcReportParser\Data\DateRange;
use Avvertix\DmarcReportParser\Data\DmarcReport;
use Avvertix\DmarcReportParser\Data\Policy;
use Avvertix\DmarcReportParser\Data\Record;
use Avvertix\DmarcReportParser\DmarcReportParser;
use Avvertix\DmarcReportParser\Exception\UnsupportedFormatException;

it('can parse xml file', function () {

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.xml');

    expect($report)
        ->toBeInstanceOf(DmarcReport::class);

    expect($report->version)
        ->toEqual('1.0');

    expect($report->org_name)
        ->toEqual('Enterprise Outlook');

    expect($report->email)
        ->toEqual('dmarcreport@microsoft.com');

    expect($report->report_id)
        ->toEqual('17265e8a413a4989bc4eef2c46ef08d0');

    expect($report->date_range)
        ->toBeInstanceOf(DateRange::class)
        ->begin->toEqual(new DateTimeImmutable('2024-11-25 0:00:00', new DateTimeZone('UTC')))
        ->end->toEqual(new DateTimeImmutable('2024-11-26 0:00:00', new DateTimeZone('UTC')));

    expect($report->publishedPolicy)
        ->toBeInstanceOf(Policy::class)
        ->domain->toEqual('a-domain.localhost');

    expect($report->extra_contact_info)
        ->toBeEmpty();

    expect($report->error)
        ->toBeEmpty();

    expect($report->records)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(Record::class);
});


it('can parse zip file', function () {

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.zip');

    expect($report)
        ->toBeInstanceOf(DmarcReport::class);

    expect($report->version)
        ->toEqual('1.0');

    expect($report->org_name)
        ->toEqual('Enterprise Outlook');

    expect($report->email)
        ->toEqual('dmarcreport@microsoft.com');

    expect($report->report_id)
        ->toEqual('17265e8a413a4989bc4eef2c46ef08d0');

    expect($report->date_range)
        ->toBeInstanceOf(DateRange::class)
        ->begin->toEqual(new DateTimeImmutable('2024-11-25 0:00:00', new DateTimeZone('UTC')))
        ->end->toEqual(new DateTimeImmutable('2024-11-26 0:00:00', new DateTimeZone('UTC')));

    expect($report->publishedPolicy)
        ->toBeInstanceOf(Policy::class)
        ->domain->toEqual('a-domain.localhost');

    expect($report->extra_contact_info)
        ->toBeEmpty();

    expect($report->error)
        ->toBeEmpty();

    expect($report->records)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(Record::class);
});

it('can parse gzip file', function () {

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc.xml.gz');

    expect($report)
        ->toBeInstanceOf(DmarcReport::class);

    expect($report->version)
        ->toEqual('1.0');

    expect($report->org_name)
        ->toEqual('Enterprise Outlook');

    expect($report->email)
        ->toEqual('dmarcreport@microsoft.com');

    expect($report->report_id)
        ->toEqual('17265e8a413a4989bc4eef2c46ef08d0');

    expect($report->date_range)
        ->toBeInstanceOf(DateRange::class)
        ->begin->toEqual(new DateTimeImmutable('2024-11-25 0:00:00', new DateTimeZone('UTC')))
        ->end->toEqual(new DateTimeImmutable('2024-11-26 0:00:00', new DateTimeZone('UTC')));

    expect($report->publishedPolicy)
        ->toBeInstanceOf(Policy::class)
        ->domain->toEqual('a-domain.localhost');

    expect($report->extra_contact_info)
        ->toBeEmpty();

    expect($report->error)
        ->toBeEmpty();

    expect($report->records)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(Record::class);
});

it('can parse a xml file with txt extension', function () {

    $dmarc = new DmarcReportParser;

    $report = $dmarc->fromFile('./tests/fixtures/dmarc-as-txt.txt');

    expect($report)
        ->toBeInstanceOf(DmarcReport::class);

    expect($report->version)
        ->toEqual('1.0');

    expect($report->org_name)
        ->toEqual('Enterprise Outlook');

    expect($report->email)
        ->toEqual('dmarcreport@microsoft.com');

    expect($report->report_id)
        ->toEqual('17265e8a413a4989bc4eef2c46ef08d0');

    expect($report->date_range)
        ->toBeInstanceOf(DateRange::class)
        ->begin->toEqual(new DateTimeImmutable('2024-11-25 0:00:00', new DateTimeZone('UTC')))
        ->end->toEqual(new DateTimeImmutable('2024-11-26 0:00:00', new DateTimeZone('UTC')));

    expect($report->publishedPolicy)
        ->toBeInstanceOf(Policy::class)
        ->domain->toEqual('a-domain.localhost');

    expect($report->extra_contact_info)
        ->toBeEmpty();

    expect($report->error)
        ->toBeEmpty();

    expect($report->records)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(Record::class);
});

it('can parse xml string', function () {

    $dmarc = new DmarcReportParser;

    $xml = <<<'DMARC'
    <?xml version="1.0"?>
    <feedback xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <version>1.0</version>
    <report_metadata>
        <org_name>Enterprise Outlook</org_name>
        <email>dmarcreport@microsoft.com</email>
        <report_id>17265e8a413a4989bc4eef2c46ef08d0</report_id>
        <date_range>
        <begin>1732492800</begin>
        <end>1732579200</end>
        </date_range>
    </report_metadata>
    <policy_published>
        <domain>a-domain.localhost</domain>
        <adkim>s</adkim>
        <aspf>s</aspf>
        <p>reject</p>
        <sp>reject</sp>
        <pct>100</pct>
        <fo>0</fo>
    </policy_published>
    <record>
        <row>
        <source_ip>255.255.255.253</source_ip>
        <count>1</count>
        <policy_evaluated>
            <disposition>none</disposition>
            <dkim>pass</dkim>
            <spf>pass</spf>
        </policy_evaluated>
        </row>
        <identifiers>
        <envelope_to>other-domain.localhost</envelope_to>
        <envelope_from>a-domain.localhost</envelope_from>
        <header_from>a-domain.localhost</header_from>
        </identifiers>
        <auth_results>
        <dkim>
            <domain>a-domain.localhost</domain>
            <selector>dkim</selector>
            <result>pass</result>
        </dkim>
        <spf>
            <domain>a-domain.localhost</domain>
            <scope>mfrom</scope>
            <result>pass</result>
        </spf>
        </auth_results>
    </record>
    <record>
        <row>
        <source_ip>255.255.255.253</source_ip>
        <count>1</count>
        <policy_evaluated>
            <disposition>none</disposition>
            <dkim>pass</dkim>
            <spf>fail</spf>
        </policy_evaluated>
        </row>
        <identifiers>
        <envelope_to>other-domain.localhost</envelope_to>
        <envelope_from>a-domain.localhost</envelope_from>
        <header_from>a-domain.localhost</header_from>
        </identifiers>
        <auth_results>
        <dkim>
            <domain>a-domain.localhost</domain>
            <selector>dkim</selector>
            <result>pass</result>
        </dkim>
        <spf>
            <domain>a-domain.localhost</domain>
            <scope>mfrom</scope>
            <result>temperror</result>
        </spf>
        </auth_results>
    </record>
    </feedback>
    DMARC;

    $report = $dmarc->fromString($xml);

    expect($report)
        ->toBeInstanceOf(DmarcReport::class);

    expect($report->version)
        ->toEqual('1.0');

    expect($report->org_name)
        ->toEqual('Enterprise Outlook');

    expect($report->email)
        ->toEqual('dmarcreport@microsoft.com');

    expect($report->report_id)
        ->toEqual('17265e8a413a4989bc4eef2c46ef08d0');

    expect($report->date_range)
        ->toBeInstanceOf(DateRange::class)
        ->begin->toEqual(new DateTimeImmutable('2024-11-25 0:00:00', new DateTimeZone('UTC')))
        ->end->toEqual(new DateTimeImmutable('2024-11-26 0:00:00', new DateTimeZone('UTC')));

    expect($report->publishedPolicy)
        ->toBeInstanceOf(Policy::class)
        ->domain->toEqual('a-domain.localhost');

    expect($report->extra_contact_info)
        ->toBeEmpty();

    expect($report->error)
        ->toBeEmpty();

    expect($report->records)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(Record::class);
});

it('checks version', function () {

    $dmarc = new DmarcReportParser;

    $xml = <<<'DMARC'
    <?xml version="1.0"?>
    <feedback xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <version>2.0</version>
    <report_metadata>
        <org_name>Enterprise Outlook</org_name>
        <email>dmarcreport@microsoft.com</email>
        <report_id>17265e8a413a4989bc4eef2c46ef08d0</report_id>
        <date_range>
        <begin>1732492800</begin>
        <end>1732579200</end>
        </date_range>
    </report_metadata>
    <policy_published>
        <domain>a-domain.localhost</domain>
        <adkim>s</adkim>
        <aspf>s</aspf>
        <p>reject</p>
        <sp>reject</sp>
        <pct>100</pct>
        <fo>0</fo>
    </policy_published>
    </feedback>
    DMARC;

    $dmarc->fromString($xml);

})->throws(InvalidArgumentException::class, 'Unexpected version identifier found. Expected 1.0, found [2.0]');

it('refuse to parse a txt file', function () {

    $dmarc = new DmarcReportParser;

    $dmarc->fromFile('./tests/fixtures/plain.txt');

})->throws(UnsupportedFormatException::class, "Unsupported format [text/plain] for file [plain.txt]. Expecting xml, zip, gzip.");
