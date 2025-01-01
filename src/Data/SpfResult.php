<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * SPF verification result
 *
 * @see SPFResultType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
enum SpfResult: string
{
    case NONE = 'none';
    case NEUTRAL = 'neutral';
    case PASS = 'pass';
    case FAIL = 'fail';
    case SOFTFAIL = 'softfail';
    case TEMPERROR = 'temperror';
    case PERMERROR = 'permerror';

    /**
     * @see TEMPERROR
     */
    case UNKNOWN = 'unknown';

    /**
     * @see PERMERROR
     */
    case ERROR = 'error';
}
