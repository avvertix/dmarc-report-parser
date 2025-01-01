<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * The policy actions specified by p and sp in the DMARC record.
 *
 * @see DmarcResultType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
enum DmarcResultType: string
{
    case PASS = 'pass';
    case FAIL = 'fail';
}
