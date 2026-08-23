<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * The method used to discover the DMARC Policy Record used during evaluation.
 *
 * Introduced by RFC 9990, absent from RFC 7489-era reports.
 *
 * @see DiscoveryType RFC 9990, Appendix A
 * @see policy_published RFC 9990, Section 3.1.1.5
 */
enum DiscoveryMethod: string
{
    /**
     * The Public Suffix List method, as described in RFC 7489.
     */
    case PSL = 'psl';

    /**
     * The Tree Walk method, as described in RFC 9989.
     */
    case TREEWALK = 'treewalk';

    /**
     * A method this parser does not recognise.
     *
     * Not defined by any RFC. Policy discovery is expected to keep evolving, so
     * an unrecognised value is normalised here instead of failing the report.
     */
    case UNKNOWN = 'unknown';

    /**
     * Resolve a reported discovery method, falling back to UNKNOWN.
     */
    public static function fromReport(string $method): self
    {
        return self::tryFrom($method) ?? self::UNKNOWN;
    }
}
