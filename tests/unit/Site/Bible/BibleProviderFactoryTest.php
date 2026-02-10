<?php

/**
 * Unit tests for BibleProviderFactory
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible;

use CWM\Component\Proclaim\Site\Bible\BibleProviderFactory;
use CWM\Component\Proclaim\Site\Bible\BibleProviderInterface;
use CWM\Component\Proclaim\Site\Bible\Provider\ApiBibleProvider;
use CWM\Component\Proclaim\Site\Bible\Provider\GetBibleProvider;
use CWM\Component\Proclaim\Site\Bible\Provider\LocalProvider;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;

/**
 * Test class for BibleProviderFactory
 *
 * @since  10.1.0
 */
class BibleProviderFactoryTest extends ProclaimTestCase
{
    /**
     * Test getProvider returns LocalProvider
     *
     * @return void
     */
    public function testGetLocalProvider(): void
    {
        BibleProviderFactory::reset();
        $provider = BibleProviderFactory::getProvider('local');

        $this->assertInstanceOf(BibleProviderInterface::class, $provider);
        $this->assertInstanceOf(LocalProvider::class, $provider);
        $this->assertSame('local', $provider->getName());
        $this->assertTrue($provider->returnsText());
        $this->assertTrue($provider->isOfflineCapable());
    }

    /**
     * Test getProvider returns GetBibleProvider
     *
     * @return void
     */
    public function testGetGetBibleProvider(): void
    {
        BibleProviderFactory::reset();
        $provider = BibleProviderFactory::getProvider('getbible');

        $this->assertInstanceOf(BibleProviderInterface::class, $provider);
        $this->assertInstanceOf(GetBibleProvider::class, $provider);
        $this->assertSame('getbible', $provider->getName());
        $this->assertTrue($provider->returnsText());
        $this->assertFalse($provider->isOfflineCapable());
    }

    /**
     * Test getProvider returns ApiBibleProvider
     *
     * @return void
     */
    public function testGetApiBibleProvider(): void
    {
        BibleProviderFactory::reset();
        $provider = BibleProviderFactory::getProvider('api_bible', 'test-key');

        $this->assertInstanceOf(BibleProviderInterface::class, $provider);
        $this->assertInstanceOf(ApiBibleProvider::class, $provider);
        $this->assertSame('api_bible', $provider->getName());
        $this->assertTrue($provider->returnsText());
        $this->assertFalse($provider->isOfflineCapable());
    }

    /**
     * Test getProvider throws for unknown provider
     *
     * @return void
     */
    public function testGetUnknownProviderThrows(): void
    {
        BibleProviderFactory::reset();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Bible provider: fakeprovider');
        BibleProviderFactory::getProvider('fakeprovider');
    }

    /**
     * Test getProvider caches instances
     *
     * @return void
     */
    public function testProviderInstanceCaching(): void
    {
        BibleProviderFactory::reset();
        $provider1 = BibleProviderFactory::getProvider('getbible');
        $provider2 = BibleProviderFactory::getProvider('getbible');

        $this->assertSame($provider1, $provider2);
    }

    /**
     * Test getProviderNames returns all provider names
     *
     * @return void
     */
    public function testGetProviderNames(): void
    {
        $names = BibleProviderFactory::getProviderNames();

        $this->assertContains('local', $names);
        $this->assertContains('getbible', $names);
        $this->assertContains('api_bible', $names);
        $this->assertCount(3, $names);
    }

    /**
     * Test reset clears cached instances
     *
     * @return void
     */
    public function testResetClearsCache(): void
    {
        $provider1 = BibleProviderFactory::getProvider('getbible');
        BibleProviderFactory::reset();
        $provider2 = BibleProviderFactory::getProvider('getbible');

        $this->assertNotSame($provider1, $provider2);
    }

    /**
     * Test getProviderForTranslation falls back to getbible when all providers enabled
     * but no DB available (unit test environment).
     *
     * @return void
     */
    public function testGetProviderForTranslationFallback(): void
    {
        BibleProviderFactory::reset();

        $params = new Registry([
            'provider_getbible' => 1,
        ]);

        // Without DB access, local/getbible checks fail and it falls to getbible
        $provider = BibleProviderFactory::getProviderForTranslation('kjv', $params);
        $this->assertInstanceOf(BibleProviderInterface::class, $provider);
    }

    /**
     * Test getProviderForTranslation with only getbible enabled
     *
     * @return void
     */
    public function testGetProviderForTranslationGetbibleOnly(): void
    {
        BibleProviderFactory::reset();

        $params = new Registry([
            'provider_getbible' => 1,
        ]);

        // Without DB, getbible check fails, but getbible is still the fallback
        $provider = BibleProviderFactory::getProviderForTranslation('kjv', $params);
        $this->assertInstanceOf(BibleProviderInterface::class, $provider);
    }

    /**
     * Test getProviderForTranslation with getbible disabled falls back to local
     *
     * @return void
     */
    public function testGetProviderForTranslationLocalFallback(): void
    {
        BibleProviderFactory::reset();

        $params = new Registry([
            'provider_getbible' => 0,
        ]);

        $provider = BibleProviderFactory::getProviderForTranslation('kjv', $params);
        $this->assertInstanceOf(LocalProvider::class, $provider);
    }

    /**
     * Test getProviderForTranslation with GDPR mode falls back to local
     *
     * @return void
     */
    public function testGetProviderForTranslationGdprMode(): void
    {
        BibleProviderFactory::reset();

        $params = new Registry([
            'gdpr_mode'          => 1,
            'provider_getbible'  => 1,
            'provider_api_bible' => 1,
            'api_bible_api_key'  => 'test-key',
        ]);

        // GDPR mode disables all external providers, falls back to local
        $provider = BibleProviderFactory::getProviderForTranslation('kjv', $params);
        $this->assertInstanceOf(LocalProvider::class, $provider);
    }
}
