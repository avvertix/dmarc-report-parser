<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * The policy actions specified by p and sp in the DMARC record.
 *
 * @see DispositionType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
enum DispositionType: string
{
    case NONE = 'none';
    case QUARANTINE = 'quarantine';
    case REJECT = 'reject';
}
