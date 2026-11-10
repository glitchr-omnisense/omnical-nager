<?php

namespace Omnical\Nager\Tests;

use Omnical\Declarative\DeclarativeFactory;
use Omnical\Model\Holiday;
use Omnical\Model\HolidayQuery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Nager.Date itself, two requests one after the other with a User-Agent
 * naming the application and a contact: France's public holidays of 2027,
 * Germany's in Saxony. `docker compose run --rm omnical live`; never in the
 * default run.
 */
#[Group('live')]
final class LiveTest extends TestCase
{
    private const UA = 'omnical-tests/1.0 (+https://github.com/glitchr-omnisense/omnical; marco.meyer@glitchr.io)';

    public function testFranceAndSaxony(): void
    {
        $provider = DeclarativeFactory::fromFile(NagerTest::DEFINITION, HttpClient::create(['timeout' => 30]))->create(['user_agent' => self::UA]);
        self::assertInstanceOf(\Omnical\HolidaysInterface::class, $provider);

        $names = array_map(static fn (Holiday $h) => $h->name, $provider->holidays(HolidayQuery::year('FR', 2027)));
        self::assertContains('Bastille Day', $names);

        $saxony = array_map(static fn (Holiday $h) => $h->name, $provider->holidays(HolidayQuery::year('DE', 2027, 'DE-SN')));
        self::assertContains('Repentance and Prayer Day', $saxony);
    }
}
