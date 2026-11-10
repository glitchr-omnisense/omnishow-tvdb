<?php

namespace Omnishow\Tvdb\Tests;

use Omnisense\Contracts\Policy\InvalidPolicyException;
use Omnisense\Contracts\Policy\Permission;
use Omnishow\Exception\OmnishowException;
use Omnishow\Exception\UsageLimitReachedException;
use Omnishow\Model\WorkRef;
use Omnishow\Registry;
use Omnishow\Testing\RecordedHttpClient;
use Omnishow\Tvdb\TvdbFactory;
use Omnishow\Tvdb\TvdbProvider;
use PHPUnit\Framework\TestCase;

/**
 * TvdbProvider's usage policy (policy.json) through omnishow's registry:
 * as declared, a site's limit counted with the call over it never made,
 * a 429 and its Retry-After holding every next call, a looser setting
 * refused. No network: recorded answers only.
 */
final class PolicyTest extends TestCase
{
    private const OPTIONS = ['api_key' => 'not-a-real-tvdb-key'];

    public function testThePolicyIsTheOneDeclared(): void
    {
        $policy = (new Registry([new TvdbFactory(RecordedHttpClient::routes([]))], ['films' => ['factory' => 'tvdb', 'options' => self::OPTIONS]]))->policy('films');

        self::assertSame('tvdb', $policy->provider);
        self::assertSame('2026-11-10', $policy->provenance->verifiedAt);
        self::assertFileExists(TvdbProvider::POLICY);
        self::assertNull($policy->rateLimits, 'TheTVDB publishes no figure');
        self::assertSame(Permission::Contract, $policy->rights->commercial);
        self::assertSame('https://thetvdb.com', $policy->rights->attribution->url);
    }

    public function testASitesLimitIsCountedAndTheCallOverItIsNeverMade(): void
    {
        $http = RecordedHttpClient::routes([]);
        $registry = new Registry([new TvdbFactory($http)], ['films' => ['factory' => 'tvdb', 'options' => self::OPTIONS, 'policy' => ['rateLimits' => [['max' => 1, 'window' => 'day', 'operations' => ['work']]]]]]);

        try {
            $registry->lookup('films')->work(WorkRef::tvdb(81189));
        } catch (UsageLimitReachedException $e) {
            throw $e;
        } catch (OmnishowException) {
            // Nothing recorded: the provider's own answer to that, counted all the same.
        }
        $made = \count($http->requests);
        self::assertGreaterThan(0, $made);
        try {
            $registry->lookup('films')->work(WorkRef::tvdb(81189));
            self::fail('The call over the site\'s limit was made.');
        } catch (UsageLimitReachedException $e) {
            self::assertInstanceOf(OmnishowException::class, $e, 'the family\'s catch holds it');
            self::assertSame('1/day/application/work', $e->limit?->id());
        }
        self::assertCount($made, $http->requests, 'the refused call reached nothing');
    }

    public function testA429AndItsRetryAfterHoldEveryCallUntilTheWaitIsOver(): void
    {
        $http = RecordedHttpClient::routes(['~.~' => [429, '{"error":"too many requests"}', ['retry-after' => ['120']]]]);
        $registry = new Registry([new TvdbFactory($http)], ['films' => ['factory' => 'tvdb', 'options' => self::OPTIONS]]);

        try {
            $registry->lookup('films')->work(WorkRef::tvdb(81189));
            self::fail('A 429 was read as an answer.');
        } catch (UsageLimitReachedException $e) {
            self::assertSame(120, $e->retryAfter);
            self::assertSame('tvdb answered 429 Too Many Requests: try again in 120 s.', $e->getMessage());
        }
        try {
            $registry->lookup('films')->work(WorkRef::tvdb(81189));
            self::fail('A call went out while the provider asked to wait.');
        } catch (UsageLimitReachedException $e) {
            self::assertGreaterThanOrEqual(119, $e->retryAfter);
        }
        self::assertCount(1, $http->requests, 'the wait held the next request back');
    }

    public function testASiteCannotLoosenThePolicy(): void
    {
        $registry = new Registry([new TvdbFactory(RecordedHttpClient::routes([]))], ['films' => ['factory' => 'tvdb', 'options' => self::OPTIONS, 'policy' => ['rights' => ['commercial' => 'allowed']]]]);

        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('commercial');
        $registry->lookup('films');
    }
}
