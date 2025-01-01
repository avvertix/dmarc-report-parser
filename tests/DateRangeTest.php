<?php

use Avvertix\DmarcReportParser\Data\DateRange;

it('create from array', function () {

    $range = DateRange::fromArray([
        'begin' => '1732492800',
        'end' => '1732579200',
    ]);

    expect($range)
        ->toBeInstanceOf(DateRange::class);

    expect($range->begin)
        ->toBeInstanceOf(DateTimeImmutable::class)
        ->toEqual(new DateTimeImmutable('2024-11-25 0:00:00', new DateTimeZone('UTC')));

    expect($range->end)
        ->toBeInstanceOf(DateTimeImmutable::class)
        ->toEqual(new DateTimeImmutable('2024-11-26 0:00:00', new DateTimeZone('UTC')));
});

it('check for missing end', function () {

    DateRange::fromArray([
        'begin' => '1732492800',
        'end' => null,
    ]);

})->throws(InvalidArgumentException::class, 'Missing end timestamp.');

it('check for missing begin', function () {

    DateRange::fromArray([
        'begin' => null,
        'end' => '1732492800',
    ]);

})->throws(InvalidArgumentException::class, 'Missing begin timestamp.');

it('check for begin to be before end', function () {

    DateRange::fromArray([
        'begin' => '1732579200',
        'end' => '1732492800',
    ]);

})->throws(DateRangeError::class, 'Expecting end date to be after or equal begin date.');
