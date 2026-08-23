# DMARC Report Parser

[![Latest Version on Packagist](https://img.shields.io/packagist/v/avvertix/dmarc-report-parser.svg?style=flat-square)](https://packagist.org/packages/avvertix/dmarc-report-parser)
[![Tests](https://img.shields.io/github/actions/workflow/status/avvertix/dmarc-report-parser/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/avvertix/dmarc-report-parser/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/avvertix/dmarc-report-parser.svg?style=flat-square)](https://packagist.org/packages/avvertix/dmarc-report-parser)

DMARC Report Parser is designed to simplify the analysis of [DMARC](https://dmarc.org/) (Domain-based Message Authentication, Reporting & Conformance) reports:

- Parse the XML-based report into fully typed classes
- Read reports from GZip/Zip files without decompressing first
- Support both [RFC 7489](https://datatracker.ietf.org/doc/html/rfc7489) and [RFC 9990](https://datatracker.ietf.org/doc/html/rfc9990)

## Installation

You can install the package via composer:

```bash
composer require avvertix/dmarc-report-parser
```

Require PHP 8.3 with `xsl` and `sodium` extensions.

## Usage

It is possible to parse reports from XML files or strings. The output is fully typed instance of `DmarcReport`.


### from file

```php
$dmarc = new Avvertix\DmarcReportParser\DmarcReportParser();

/**
 * @var Avvertix\DmarcReportParser\Data\DmarcReport
 */
$report = $dmarc->fromFile('path/to/report.xml');

// Who generated the report, and for which reporting window
$report->org_name;                             // 'Enterprise Outlook'
$report->email;                                // 'dmarcreport@microsoft.com'
$report->report_id;                            // '1732492800-a-domain.localhost@example-reporter.com'
$report->date_range->begin;                    // DateTimeImmutable
$report->date_range->end->format('Y-m-d');     // '2024-11-26'

// The policy the receiver found for the domain
$report->publishedPolicy->domain;              // 'a-domain.localhost'
$report->publishedPolicy->p->value;            // 'reject'

// One record per sending IP
foreach ($report->records as $record) {
    $record->row->source_ip;                                  // '255.255.255.253'
    $record->row->count;                                      // 7
    $record->row->policy_evaluated->disposition->value;       // 'none', 'quarantine', 'reject' or 'pass'
    $record->identifiers->header_from;                        // 'a-domain.localhost'
}
```

See [the `DmarcReport` class](#dmarcreport-class) below for the full structure.

You can pass directly `zip` or `gzip` compressed reports. It is assumed that the XML report file is the first file in the compressed archive.

When working with compressed reports we cap expansion at 10MB by default. An archive that expands past the cap 
throws `Avvertix\DmarcReportParser\Exception\DecompressionLimitException`. If necessary you can adjust the 
expansion limit by providing a `ParserConfiguration`.

```php
use Avvertix\DmarcReportParser\DmarcReportParser;
use Avvertix\DmarcReportParser\ParserConfiguration;

$dmarc = new DmarcReportParser(new ParserConfiguration(
    maxDecompressedBytes: 2 * 1024 * 1024, // 2 MB
));
```

### from string

```php
$dmarc = new Avvertix\DmarcReportParser\DmarcReportParser();

$xml = <<<'DMARC'
<?xml version="1.0"?>
<feedback xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<version>1.0</version>
<!-- content omitted for brevity -->
</feedback>
DMARC;

/**
 * @var Avvertix\DmarcReportParser\Data\DmarcReport
 */
$report = $dmarc->fromString($xml);

// The parsed report is identical to one read from a file: the same XML as a
// plain string, gzip or zip always produces the same DmarcReport
$report->org_name;
$report->records[0]->row->count;
```

Reading the authentication results of every record, which is what most of a report is about:

```php
foreach ($report->records as $record) {
    // Zero or more DKIM signatures were evaluated for this group of messages
    foreach ($record->auth_results->dkim as $dkim) {
        $dkim->domain;              // 'a-domain.localhost'
        $dkim->selector;            // 'selector1', or null on RFC 7489-era reports
        $dkim->result->value;       // 'pass'
        $dkim->human_result;        // 'signature verified', or null
    }

    // Zero or one SPF result. The array is empty when the report carries none
    foreach ($record->auth_results->spf as $spf) {
        $spf->domain;               // 'a-domain.localhost'
        $spf->scope;                // 'mfrom', or null
        $spf->result->value;        // 'pass'
    }

    // Present when the receiver did not apply the published policy
    foreach ($record->row->policy_evaluated->reasons as $reason) {
        $reason->type->value;       // 'policy_test_mode'
        $reason->comment;           // 'policy in testing mode', or null
    }
}
```

### DmarcReport class

The [`DmarcReport`](./src/Data/DmarcReport.php) class represent the report in a fully typed manner.

A few differences with respect to the spec:

- Report generator metadata are directly accessible from the `DmarcReport` class and not encapsulated in an object
- Records are exposed using the `records` (array) property
- When the spec report an element to be available multiple times we represent it as an array property

```
DmarcReport
├── version              string              '1.0'
├── org_name             string              who generated the report
├── email                string              contact for the generator
├── report_id            string              unique per report, used to detect duplicates
├── date_range           DateRange           ├── begin  DateTimeImmutable
│                                            └── end    DateTimeImmutable
├── publishedPolicy      Policy              ├── domain            string
│                                            ├── p                 DispositionType
│                                            ├── sp                ?DispositionType
│                                            ├── np                ?DispositionType      RFC 9990
│                                            ├── adkim             ?AlignmentMode
│                                            ├── aspf              ?AlignmentMode
│                                            ├── pct               ?int                  RFC 7489 only
│                                            ├── fo                ?string
│                                            ├── discovery_method  ?DiscoveryMethod      RFC 9990
│                                            └── testing           ?TestingMode          RFC 9990
├── records              list<Record>        one per sending IP
├── extra_contact_info   ?string
├── error                ?string             problems the receiver hit reading the policy
├── generator            ?string             reporting software name and version, RFC 9990
└── extensions           list<Extension>     file level extensions, RFC 9990

Record
├── row                  Row                 ├── source_ip         string
│                                            ├── count             int
│                                            └── policy_evaluated  PolicyEvaluated
├── identifiers          Identifier          ├── header_from       string
│                                            ├── envelope_from     ?string
│                                            └── envelope_to       ?string
├── auth_results         AuthResult          ├── dkim  list<DkimAuthResult>
│                                            └── spf   list<SpfAuthResult>
└── extensions           list<Extension>     record level extensions, RFC 9990
```

Two things catch people out: the published policy is `publishedPolicy`, in camel case, while every
other property is snake case to match the XML; and the override reasons in `PolicyEvaluated` property is `reasons`, plural, because a record may carry more than one.


#### Common questions to ask a report

```php
use Avvertix\DmarcReportParser\Data\DispositionType;
use Avvertix\DmarcReportParser\Data\DmarcResultType;

// How many messages does this report cover?
$messages = array_sum(array_map(fn ($record) => $record->row->count, $report->records));

// Which messages failed DMARC on both mechanisms? These are the ones worth investigating
$failing = array_filter(
    $report->records,
    fn ($record) => $record->row->policy_evaluated->dkim === DmarcResultType::FAIL
        && $record->row->policy_evaluated->spf === DmarcResultType::FAIL
);

// Which messages were actually quarantined or rejected?
$enforced = array_filter(
    $report->records,
    fn ($record) => in_array(
        $record->row->policy_evaluated->disposition,
        [DispositionType::QUARANTINE, DispositionType::REJECT],
        true
    )
);

// Which DKIM selectors are in use? Reports are the only place to discover them,
// as DNS cannot be queried for every selector on a domain
$selectors = [];

foreach ($report->records as $record) {
    foreach ($record->auth_results->dkim as $dkim) {
        if ($dkim->selector !== null) {
            $selectors[$dkim->selector] = true;
        }
    }
}

$selectors = array_keys($selectors);

// Which sending IPs are responsible for the most messages?
$byVolume = [];

foreach ($report->records as $record) {
    $byVolume[$record->row->source_ip] ??= 0;
    $byVolume[$record->row->source_ip] += $record->row->count;
}

arsort($byVolume);
```

### Reading a report filename

RFC 9990 standardizes the attachment filename, which yields the policy domain and the reporting
window without decompressing or parsing anything:

```php
use Avvertix\DmarcReportParser\ReportFilename;

$parsedFilename = ReportFilename::parse('mail.receiver.example!example.com!1013662812!1013749130.xml.gz');

$parsedFilename->receiver;      // 'mail.receiver.example'
$parsedFilename->policy_domain; // 'example.com'
$parsedFilename->date_range;    // DateRange
$parsedFilename->unique_id;     // null
$parsedFilename->compressed;    // true
```

In case the attachment filename is not following the convention the `parse()` method returns `null`.



## RFC 7489 vs RFC 9990

DMARC was originally defined in [RFC 7489](https://datatracker.ietf.org/doc/html/rfc7489). 
[RFC 9990](https://datatracker.ietf.org/doc/html/rfc9990) revises it.
The `DmarcReport` supports both definitions without indicating if a report satisfy one or the other version
as it is possible that sender will take time to move to the new RFC.

Elements RFC 9990 adds are **nullable**, indicating that the report did not carry them:

| Property | Element | Type |
| --- | --- | --- |
| `DmarcReport::$generator` | `report_metadata/generator` | `?string` |
| `DmarcReport::$extensions` | `feedback/extension` | `list<Extension>`, empty when absent |
| `Policy::$discovery_method` | `policy_published/discovery_method` | `?DiscoveryMethod` — psl or treewalk |
| `Policy::$testing` | `policy_published/testing` | `?TestingMode` — the `t` tag, `y` or `n` |
| `Policy::$np` | `policy_published/np` | `?DispositionType` |
| `Record::$extensions` | namespaced elements after `auth_results` | `list<Extension>`, empty when absent |

RFC 9990 deprecate or remove few fields and values. Other behaviour worth knowing:

- `Policy::$pct` stays nullable. RFC 9990 removes the `pct` element, but RFC 7489-era reports carry it.
- `DkimAuthResult::$selector` stays nullable. RFC 9990 makes it mandatory; RFC 7489 did not, and real
  reports from that era omit it.
- `AuthResult::$spf` may be an empty array. RFC 9990 makes the `spf` element optional.
- `DispositionType` gained a `PASS` case. RFC 9990 allows `pass` in `policy_evaluated/disposition`,
  though never in a published policy.
- `PolicyOverrideType` keeps `FORWARDED` and `SAMPLED_OUT`, which RFC 9990 removed, and gained
  `POLICY_TEST_MODE`. An unrecognised value becomes `UNKNOWN` instead of throwing, as do unrecognised
  `DiscoveryMethod` and `TestingMode` values, so a later revision adding one does not break parsing.
- `report_id` is never rejected. RFC 9990 gives it an ABNF, but malformed identifiers are common and
  the raw value always reaches the caller.


## Testing

DMARC Report Parser is covered in unit test. The [PestPHP](https://pestphp.com/) framework is used. To run the whole test suite execute the `test` script. 

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alessio Vertemati](https://github.com/avvertix)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
