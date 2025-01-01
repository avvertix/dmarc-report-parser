<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * Alignment mode (relaxed or strict) for DKIM and SPF.
 *
 * @see AlignmentType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
enum AlignmentMode: string
{
    case RELAXED = 'r';
    case STRICT = 's';
}
