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
     */
    case FORWARDED = 'forwarded';

    /**
     * The message was exempted from application of policy by
     * the "pct" setting in the DMARC policy record.
     */
    case SAMPLED_OUT = 'sampled_out';

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

}
