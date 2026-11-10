<?php

namespace Omnical\Nager\Tests;

use Omnical\Declarative\DeclarativeFactory;
use Omnical\Declarative\DeclarativeProvider;
use Omnical\Model\Holiday;
use Omnical\Model\HolidayQuery;
use Omnical\Model\HolidayType;
use Omnical\Registry;
use Omnical\Testing\RecordedHttpClient;
use PHPUnit\Framework\TestCase;

/**
 * Nager.Date's answers recorded on 2026-11-10 at 04:21 UTC (GET
 * /api/v4/Holidays/FR/2027, DE/2027 and XX/2027, a 404; kept whole), read
 * through provider.json alone: no class of this package runs, there is none.
 */
final class NagerTest extends TestCase
{
    public const DEFINITION = __DIR__.'/../provider.json';
    private const UA = 'omnical-tests/1.0 (+https://github.com/glitchr-omnisense/omnical; tests@example.org)';

    public function testFrancesPublicHolidaysInEnglish(): void
    {
        $http = self::http();
        $holidays = self::registry($http)->holidays('nager')->holidays(HolidayQuery::year('FR', 2027, language: 'fr'));

        self::assertCount(11, $holidays);
        self::assertSame(['2027-07-14', 'Bastille Day', 'en', 'FR', true, [], [HolidayType::Public]], self::row($holidays[6]));
        self::assertSame('nager', $holidays[0]->source);
        self::assertSame('https://nagerholidays.com/api/v4/Holidays/FR/2027', $http->requests[0]['url']);
        self::assertArrayNotHasKey('query', $http->requests[0]['options'], 'v4 takes no language: names are English');
        self::assertSame([self::UA, 'application/json'], [$http->header('User-Agent'), $http->header('Accept')]);
    }

    public function testSubdivisionsAreIsoCodes(): void
    {
        $holidays = self::registry(self::http())->holidays('nager')->holidays(HolidayQuery::year('DE', 2027, 'DE-BY'));
        $names = array_map(static fn (Holiday $h) => $h->first->format('m-d').' '.$h->name, $holidays);

        self::assertContains('01-06 Epiphany', $names);
        self::assertContains('11-01 All Saints\' Day', $names);
        self::assertNotContains('10-31 Reformation Day', $names, 'not in Bavaria');
        $epiphany = array_values(array_filter($holidays, static fn (Holiday $h) => 'Epiphany' === $h->name))[0];
        self::assertSame([false, ['DE-BW', 'DE-BY', 'DE-ST']], [$epiphany->nationwide, $epiphany->regions]);
    }

    public function testEachYearIsACallAndAnUnknownCountryIsEmpty(): void
    {
        $http = self::http();
        $registry = self::registry($http);
        self::assertSame(['Christmas Day', 'New Year\'s Day'], array_map(static fn (Holiday $h) => $h->name, $registry->holidays('nager')->holidays(new HolidayQuery('FR', new \DateTimeImmutable('2026-12-20'), new \DateTimeImmutable('2027-01-05')))));
        self::assertSame(['https://nagerholidays.com/api/v4/Holidays/FR/2026', 'https://nagerholidays.com/api/v4/Holidays/FR/2027'], array_map(static fn (array $r) => $r['url'], $http->requests));
        self::assertSame([], $registry->holidays('nager')->holidays(HolidayQuery::year('XX', 2027)), 'a 404: no holiday');
    }

    public function testTheBaseUriPointsAtASelfHostedInstance(): void
    {
        $http = self::http();
        (new Registry([DeclarativeFactory::fromFile(self::DEFINITION, $http)], ['nager' => ['factory' => 'nager', 'options' => ['base_uri' => 'https://holidays.internal.example/api/v4']]]))->holidays('nager')->holidays(HolidayQuery::year('FR', 2027));
        self::assertSame('https://holidays.internal.example/api/v4/Holidays/FR/2027', $http->requests[0]['url']);
    }

    public function testThePackageIsFoundAmongTheInstalledOnes(): void
    {
        $factories = array_values(array_filter(DeclarativeFactory::installed(self::http()), static fn (DeclarativeFactory $f) => 'nager' === $f->getName()));
        if ([] === $factories) {
            self::markTestSkipped('omnical/nager is not installed by Composer here.');
        }
        self::assertInstanceOf(DeclarativeProvider::class, $factories[0]->create());
    }

    public static function http(): RecordedHttpClient
    {
        return RecordedHttpClient::routes([
            '~^GET https://[^/]+/api/v4/Holidays/FR/2027$~' => [200, '@'.__DIR__.'/Fixtures/holidays-fr-2027.json'],
            '~^GET https://[^/]+/api/v4/Holidays/FR/2026$~' => [200, '[{"date":"2026-12-25","name":"Christmas Day","countryCode":"FR","nationalHoliday":true,"subdivisionCodes":null,"holidayTypes":["Public"]}]'],
            '~^GET https://[^/]+/api/v4/Holidays/DE/2027$~' => [200, '@'.__DIR__.'/Fixtures/holidays-de-2027.json'],
            '~^GET https://[^/]+/api/v4/Holidays/XX/2027$~' => [404, '@'.__DIR__.'/Fixtures/holidays-xx-2027.json'],
        ]);
    }

    private static function registry(RecordedHttpClient $http): Registry
    {
        return new Registry([DeclarativeFactory::fromFile(self::DEFINITION, $http)], ['nager' => ['factory' => 'nager', 'options' => ['user_agent' => self::UA]]]);
    }

    /** @return array{0: string, 1: string, 2: ?string, 3: string, 4: bool, 5: list<string>, 6: list<HolidayType>} */
    private static function row(Holiday $holiday): array
    {
        return [$holiday->first->format('Y-m-d'), $holiday->name, $holiday->language, $holiday->country, $holiday->nationwide, $holiday->regions, $holiday->types];
    }
}
