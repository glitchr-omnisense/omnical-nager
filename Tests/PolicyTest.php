<?php

namespace Omnical\Nager\Tests;

use Omnical\Declarative\DeclarativeFactory;
use Omnical\Model\HolidayQuery;
use Omnical\Registry;
use Omnisense\Contracts\Policy\Permission;
use Omnisense\Contracts\Policy\PolicyExport;
use Omnisense\Contracts\Policy\UsageNotPermitted;
use PHPUnit\Framework\TestCase;

/** Nager.Date's usage policy: free for non-commercial use, a sponsorship for commercial use, no holiday portal of one's own. */
final class PolicyTest extends TestCase
{
    public function testThePolicyIsTheOneNagerPublishes(): void
    {
        $policy = (new Registry([DeclarativeFactory::fromFile(NagerTest::DEFINITION, NagerTest::http())], ['n' => ['factory' => 'nager']]))->policy('n');

        self::assertSame('nager', $policy->provider);
        self::assertSame([], $policy->rateLimits, '"No Rate Limits": none, not unknown');
        self::assertSame(Permission::Contract, $policy->rights->commercial);
        self::assertSame(Permission::Forbidden, $policy->rights->redistribution);
        self::assertFalse($policy->rights->attribution->required);
        self::assertSame('https://nagerholidays.com/legal/termsofservice', $policy->provenance->terms);
        self::assertSame('2026-11-10', $policy->provenance->verifiedAt);
        $entries = (new PolicyExport())->folders(__DIR__.'/..')->entries();
        self::assertSame('omnical/nager', $entries[0]['package'] ?? null);
    }

    public function testACommercialSiteNeedsItsSponsorship(): void
    {
        $sponsor = new Registry([DeclarativeFactory::fromFile(NagerTest::DEFINITION, NagerTest::http())], ['n' => ['factory' => 'nager', 'usage' => ['commercial' => true, 'contract' => true]]]);
        self::assertCount(11, $sponsor->holidays('n')->holidays(HolidayQuery::year('FR', 2027)));

        $registry = new Registry([DeclarativeFactory::fromFile(NagerTest::DEFINITION, NagerTest::http())], ['n' => ['factory' => 'nager', 'usage' => ['commercial' => true]]]);
        $this->expectException(UsageNotPermitted::class);
        $this->expectExceptionMessage('commercial use needs a contract with the provider');
        $registry->holidays('n');
    }
}
