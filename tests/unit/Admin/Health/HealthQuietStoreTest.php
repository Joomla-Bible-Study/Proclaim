<?php

/**
 * Quietening a dashboard notice against the finding, not for good.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Health;

use CWM\Component\Proclaim\Administrator\Health\HealthQuietStore;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since 10.6.0
 */
class HealthQuietStoreTest extends ProclaimTestCase
{
    /**
     * The `#__bsms_admin` params as they were before the test wrote to them.
     *
     * @var    ?string
     * @since  10.6.0
     */
    private ?string $originalParams = null;

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'SELECT ' . $db->quoteName('params') . ' FROM ' . $db->quoteName('#__bsms_admin')
            . ' WHERE ' . $db->quoteName('id') . ' = 1'
        );

        $this->originalParams = $db->loadResult();
    }

    /**
     * The store writes with a plain UPDATE, so restoring the column by hand is
     * enough — and is not at the mercy of a Table::store() elsewhere
     * committing the transaction out from under the rollback.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[\Override]
    protected function tearDown(): void
    {
        if ($this->originalParams !== null) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery(
                'UPDATE ' . $db->quoteName('#__bsms_admin')
                . ' SET ' . $db->quoteName('params') . ' = ' . $db->quote($this->originalParams)
                . ' WHERE ' . $db->quoteName('id') . ' = 1'
            );
            $db->execute();
        }

        parent::tearDown();
    }

    /**
     * A passing check has nothing to silence. Storing a fingerprint for one
     * would mean a later failure could be hidden by a state that was fine.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('A result with no fingerprint is never quiet and cannot be quietened')]
    public function testPassingResultsAreNeverQuiet(): void
    {
        $passing = new HealthResult('test.passing', HealthStatus::Ok, 'All good');

        HealthQuietStore::quieten($passing);

        $this->assertFalse(HealthQuietStore::isQuiet($passing));
        $this->assertArrayNotHasKey('test.passing', HealthQuietStore::read());
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Quietening hides the finding it was cleared against')]
    public function testQuietenHidesTheSameFinding(): void
    {
        $finding = new HealthResult('test.finding', HealthStatus::Warning, '10 servers', '10:2008');

        $this->assertFalse(HealthQuietStore::isQuiet($finding), 'Nothing should be quiet before it is cleared.');

        HealthQuietStore::quieten($finding);

        $this->assertTrue(HealthQuietStore::isQuiet($finding));
    }

    /**
     * ⚠️ The whole reason quieting stores a fingerprint rather than a boolean.
     * 2,008 legacy media rows staying 2,008 should not nag every month; one
     * new legacy server has to say so immediately.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('A changed finding comes back on its own')]
    public function testChangedFindingIsNoLongerQuiet(): void
    {
        HealthQuietStore::quieten(new HealthResult('test.finding', HealthStatus::Warning, '10 servers', '10:2008'));

        $changed = new HealthResult('test.finding', HealthStatus::Warning, '11 servers', '11:2400');

        $this->assertFalse(
            HealthQuietStore::isQuiet($changed),
            'A finding that changed shape must resurface without waiting for anything to expire it.'
        );
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Restoring puts a cleared finding back on the dashboard')]
    public function testRestoreUndoesQuieten(): void
    {
        $finding = new HealthResult('test.finding', HealthStatus::Warning, '10 servers', '10:2008');

        HealthQuietStore::quieten($finding);
        HealthQuietStore::restore('test.finding');

        $this->assertFalse(HealthQuietStore::isQuiet($finding));
    }

    /**
     * ⚠️ A truncated write leaves a params column that still starts with `{`,
     * and Registry throws for exactly that -- the case Cwmparams::getAdmin()
     * and setCompParams() both already guard. This store is read on every
     * admin's dashboard, so an unguarded throw would turn a half-written row
     * into a dead screen.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Unreadable params report nothing quietened rather than throwing')]
    public function testCorruptParamsAreSurvivable(): void
    {
        $this->corruptParams();

        $this->assertSame([], HealthQuietStore::read());
        $this->assertFalse(
            HealthQuietStore::isQuiet(new HealthResult('test.finding', HealthStatus::Warning, 'x', '1:2'))
        );
    }

    /**
     * Writing means re-serialising the whole params column, so proceeding from
     * an empty fallback would trade every stored setting for a cleared banner.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Quietening refuses to write over a params column it could not read')]
    public function testCorruptParamsAreNotOverwritten(): void
    {
        $this->corruptParams();

        try {
            HealthQuietStore::quieten(new HealthResult('test.finding', HealthStatus::Warning, 'x', '1:2'));
            $this->fail('Quietening overwrote an unreadable params column instead of refusing.');
        } catch (\RuntimeException) {
            // Expected.
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'SELECT ' . $db->quoteName('params') . ' FROM ' . $db->quoteName('#__bsms_admin')
            . ' WHERE ' . $db->quoteName('id') . ' = 1'
        );

        $this->assertSame(
            '{"broken',
            $db->loadResult(),
            'The corrupt value was replaced, which is the data loss this refusal exists to prevent.'
        );
    }

    /**
     * Leave the admin row holding a value Registry cannot parse.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    private function corruptParams(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__bsms_admin')
            . ' SET ' . $db->quoteName('params') . ' = ' . $db->quote('{"broken')
            . ' WHERE ' . $db->quoteName('id') . ' = 1'
        );
        $db->execute();
    }

    /**
     * Check ids contain dots, which Registry reads as a path separator. Storing
     * the map as an encoded string is what keeps `content.legacy-servers` one
     * key rather than a `legacy-servers` node under `content`.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('A dotted check id survives a write and read unchanged')]
    public function testDottedIdsRoundTrip(): void
    {
        $finding = new HealthResult('content.legacy-servers', HealthStatus::Warning, '10 servers', '10:2008');

        HealthQuietStore::quieten($finding);

        $stored = HealthQuietStore::read();

        $this->assertSame('10:2008', $stored['content.legacy-servers'] ?? null);
        $this->assertArrayNotHasKey('content', $stored, 'The id was split on its dot into a nested node.');
    }
}
