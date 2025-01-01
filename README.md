# DMARC Report Parser

[![Latest Version on Packagist](https://img.shields.io/packagist/v/avvertix/dmarc-report-parser.svg?style=flat-square)](https://packagist.org/packages/avvertix/dmarc-report-parser)
[![Tests](https://img.shields.io/github/actions/workflow/status/avvertix/dmarc-report-parser/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/avvertix/dmarc-report-parser/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/avvertix/dmarc-report-parser.svg?style=flat-square)](https://packagist.org/packages/avvertix/dmarc-report-parser)

DMARC Report Parser is designed to simplify the analysis of [DMARC](https://dmarc.org/) (Domain-based Message Authentication, Reporting & Conformance) reports:

- Parse the XML-based report into fully typed classes
- Read reports from GZip files without decompressing first (coming soon)

## Installation

You can install the package via composer:

```bash
composer require avvertix/dmarc-report-parser
```

Require PHP 8.3 with `xsl` and `sodium` extensions.

## Usage

It is possible to parse reports from XML files or strings. The output is fully typed instance of `DmarcReport`.


**from file**

```php
$dmarc = new Avvertix\DmarcReportParser\DmarcReportParser();

/**
 * @var Avvertix\DmarcReportParser\Data\DmarcReport
 */
$report = $dmarc->fromFile('path/to/report.xml');

```

**from string**

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
```

**DmarcReport class**

The [`DmarcReport`](./src/Data/DmarcReport.php) class represent the report in a fully typed manner.

A few differences with respect to the spec:

- Report generator metadata are directly accessible from the `DmarcReport` class and not encapsulated in an object
- Records are exposed using the `records` (array) property
- In AuthResult both dkim and spf properties are represented as arrays
- In general when the spec report an element to be available multiple times we represent it as an array property


## Testing

DMARC Report Parser is covered in unit test. The [PestPHP](https://pestphp.com/) framework is used. To run the whole test suite execute the `test` script. 

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alessio Vertemati](https://github.com/avvertix)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
