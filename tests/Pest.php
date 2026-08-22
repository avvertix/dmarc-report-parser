<?php

/**
 * Peak memory allocated while running $work, in bytes.
 *
 * Used to prove decompression stays bounded: a guard that buffers the whole
 * expansion before measuring it shows up here even though it throws the right
 * exception.
 */
function peakMemoryDelta(callable $work): int
{
    memory_reset_peak_usage();
    $before = memory_get_usage();

    $work();

    return memory_get_peak_usage() - $before;
}
