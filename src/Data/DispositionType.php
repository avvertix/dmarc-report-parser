<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * The policy actions specified by p, sp and np in the DMARC record,
 * and the action applied to the messages in a record.
 *
 * RFC 9990 splits this in two: DispositionType covers the published policy
 * (none, quarantine, reject) while ActionDispositionType covers
 * policy_evaluated/disposition and adds "pass". Both positions share this enum
 * so that adding "pass" stays backward compatible.
 *
 * @see DispositionType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 * @see DispositionType RFC 9990, Appendix A and Section 3.1.1.5
 * @see ActionDispositionType RFC 9990, Appendix A and Section 3.1.1.9
 */
enum DispositionType: string
{
    case NONE = 'none';
    case QUARANTINE = 'quarantine';
    case REJECT = 'reject';

    /**
     * No action taken, the message passed DMARC with an enforcing policy.
     *
     * Only reported in policy_evaluated/disposition, never in a published policy.
     *
     * @see ActionDispositionType RFC 9990, Section 3.1.1.9
     */
    case PASS = 'pass';
}
