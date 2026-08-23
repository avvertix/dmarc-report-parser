<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * Reasons that may affect DMARC disposition or execution thereof.
 *
 * @see PolicyOverrideType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
enum PolicyOverrideType: string
{
    /**
     * The message was relayed via a known forwarder, or local
     * heuristics identified the message as likely having been forwarded.
     * There is no expectation that authentication would pass.
     *
     * Removed by RFC 9990. Retained because RFC 7489-era reports still use it.
     */
    case FORWARDED = 'forwarded';

    /**
     * The message was exempted from application of policy by
     * the "pct" setting in the DMARC policy record.
     *
     * Removed by RFC 9990 along with the "pct" tag. Retained because
     * RFC 7489-era reports still use it.
     */
    case SAMPLED_OUT = 'sampled_out';

    /**
     * The message was exempted from application of policy by the testing
     * mode ("t" tag) in the DMARC Policy Record.
     *
     * @see RFC 9990, Section 3.1.6
     */
    case POLICY_TEST_MODE = 'policy_test_mode';

    /**
     * Message authentication failure was anticipated by
     * other evidence linking the message to a locally maintained list of
     * known and trusted forwarders.
     */
    case TRUSTED_FORWARDER = 'trusted_forwarder';

    /**
     * Local heuristics determined that the message arrived
     * via a mailing list, and thus authentication of the original
     * message was not expected to succeed.
     */
    case MAILING_LIST = 'mailing_list';

    /**
     * The Mail Receiver's local policy exempted the message
     * from being subjected to the Domain Owner's requested policy
     * action.
     */
    case LOCAL_POLICY = 'local_policy';

    /**
     * Some policy exception not covered by the other entries in
     * this list occurred.  Additional detail can be found in the
     * PolicyOverrideReason's "comment" field.
     */
    case OTHER = 'other';

    /**
     * A type this parser does not recognise.
     *
     * Not defined by any RFC. The list of override types changed between
     * RFC 7489 and RFC 9990 and may change again, so an unrecognised value is
     * normalised here instead of failing the report.
     */
    case UNKNOWN = 'unknown';

    /**
     * Resolve a reported override type, falling back to UNKNOWN.
     */
    public static function fromReport(string $type): self
    {
        return self::tryFrom($type) ?? self::UNKNOWN;
    }
}
