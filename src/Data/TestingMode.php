<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * Whether testing mode was declared in the DMARC Record, the value of the "t" tag.
 *
 * Introduced by RFC 9990, absent from RFC 7489-era reports. An absent value is
 * not the same as NO: it means the report says nothing about the tag.
 *
 * @see TestingType RFC 9990, Appendix A
 * @see policy_published RFC 9990, Section 3.1.1.5
 */
enum TestingMode: string
{
    case YES = 'y';

    case NO = 'n';

    /**
     * A value this parser does not recognise.
     *
     * Not defined by any RFC, kept so an unexpected value does not fail the report.
     */
    case UNKNOWN = 'unknown';

    /**
     * Resolve a reported testing mode, falling back to UNKNOWN.
     */
    public static function fromReport(string $mode): self
    {
        return self::tryFrom($mode) ?? self::UNKNOWN;
    }
}
