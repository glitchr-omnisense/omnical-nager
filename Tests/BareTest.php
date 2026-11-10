<?php

namespace Omnical\Nager\Tests;

use Omnical\Testing\DeclaredBareTestCase;

/** omnical/nager in plain PHP: its definition loaded and asked, nothing loaded but omnisense/omnical, omnisense/contracts and the HTTP client contracts. */
final class BareTest extends DeclaredBareTestCase
{
    protected static function definition(): string
    {
        return NagerTest::DEFINITION;
    }

    protected static function routes(): array
    {
        return ['~/Holidays/DE/2027$~' => [200, '@'.__DIR__.'/Fixtures/holidays-de-2027.json']];
    }

    protected static function query(): array
    {
        return ['country' => 'DE', 'from' => '2027-10-01', 'to' => '2027-11-30', 'region' => 'DE-SN'];
    }

    public function testTheDefinitionAloneGivesHolidays(): void
    {
        $declared = self::declared();

        self::assertSame([['2027-10-03', 'German Unity Day'], ['2027-10-31', 'Reformation Day'], ['2027-11-17', 'Repentance and Prayer Day']], $declared['holidays']);
        self::assertSame(['GET https://nagerholidays.com/api/v4/Holidays/DE/2027'], $declared['requests']);
    }
}
