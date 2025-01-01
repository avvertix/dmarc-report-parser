<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * DKIM verification result, according to RFC 7001 Section 2.6.1.
 *
 * @see DKIMResultType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
enum DkimResult: string
{
    case NONE = 'none';
    case PASS = 'pass';
    case FAIL = 'fail';
    case POLICY = 'policy';
    case NEUTRAL = 'neutral';
    case TEMPERROR = 'temperror';
    case PERMERROR = 'permerror';
}
