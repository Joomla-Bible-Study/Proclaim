<?php

/**
 * Integration tests for guided tour registration.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmguidedtourHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1572.
 *
 * registerGuidedTours() ran on every Proclaim release, including no-op patch
 * bumps, and rewrote any existing tour from the hardcoded definition --
 * resetting published/access/language and deleting every step row before
 * reinserting the shipped ones. Admin edits and translated steps were lost
 * silently and repeatedly.
 *
 * Separately, core indexes #__guidedtours.uid with a plain KEY rather than a
 * UNIQUE one, and registration is check-then-act with no lock, so concurrent
 * requests could leave permanent duplicate tours. Lookups returned an
 * arbitrary one and uninstall removed only one, leaving the rest in core
 * tables forever.
 *
 * Start Tour is offered only while Joomla's System - Guided Tours plugin is
 * enabled, because that plugin loads the tour script and serves the tour
 * (#2361). Registration and removal must keep working with it disabled.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmguidedtourHelper::class)]
class CwmguidedtourHelperTest extends IntegrationTestCase
{
    private const string UID = 'com_proclaim_test_1572';

    private ?DatabaseDriver $db = null;

    private int $toursAtStart = 0;

    private ?int $pluginStateAtStart = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);

        if (!\in_array($this->db->getPrefix() . 'guidedtours', $this->db->getTableList(), true)) {
            $this->markTestSkipped('Core #__guidedtours table not present');
        }

        $this->db->transactionStart(true);
        $this->toursAtStart       = $this->countTours();
        $this->pluginStateAtStart = $this->guidedToursPluginState();
    }

    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);

                // These tests write to core Joomla tables, so a broken
                // isolation boundary damages more than Proclaim's own data.
                $after = $this->countTours();

                if ($after !== $this->toursAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__guidedtours went from %d to %d rows.',
                        $this->toursAtStart,
                        $after
                    );
                }

                // A plugin left disabled would switch tours off for the whole site.
                $pluginAfter = $this->guidedToursPluginState();

                if ($pluginAfter !== $this->pluginStateAtStart) {
                    $this->writeGuidedToursPluginState((int) $this->pluginStateAtStart);

                    $leaked = trim(($leaked ?? '') . \sprintf(
                        ' Test isolation broke: the Guided Tours plugin went from enabled=%d to %d; restored.',
                        (int) $this->pluginStateAtStart,
                        (int) $pluginAfter
                    ));
                }
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        $this->resetPluginCache();

        parent::tearDown();

        if ($leaked !== null) {
            $this->fail($leaked);
        }
    }

    /**
     * @return  int  Total rows in the core tours table.
     */
    private function countTours(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__guidedtours'))
        )->loadResult();
    }

    /**
     * Insert a bare tour row directly, bypassing the helper.
     *
     * @param   string  $uid        Tour uid
     * @param   string  $title      Title to store
     * @param   int     $published  Published state
     *
     * @return  int  Inserted id
     */
    private function insertRawTour(string $uid, string $title = 'Local title', int $published = 1): int
    {
        $row = (object) [
            'title'       => $title,
            'description' => 'local description',
            'extensions'  => json_encode(['com_proclaim']),
            'url'         => 'administrator/index.php?option=com_proclaim',
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'note'        => '',
            'uid'         => $uid,
            'created'     => (new \Joomla\CMS\Date\Date())->toSql(),
            'created_by'  => 0,
            'modified'    => (new \Joomla\CMS\Date\Date())->toSql(),
            'modified_by' => 0,
        ];

        $this->db->insertObject('#__guidedtours', $row, 'id');

        return (int) $this->db->insertid();
    }

    /**
     * Count a uid's rows straight from the table.
     *
     * The helper's own lookups share supportsGuidedTours() with the code under
     * test, so asserting through them would pass if that check stopped them both.
     *
     * @param   string  $uid  Tour uid
     *
     * @return  int
     */
    private function rowsForUid(string $uid): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__guidedtours'))
                ->where($this->db->quoteName('uid') . ' = ' . $this->db->quote($uid))
        )->loadResult();
    }

    /**
     * @return  int|null  The Guided Tours plugin's enabled flag, or null when it is not installed.
     */
    private function guidedToursPluginState(): ?int
    {
        $state = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('enabled'))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
                ->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('guidedtours'))
        )->loadResult();

        return $state === null ? null : (int) $state;
    }

    /**
     * Turn the Guided Tours plugin on or off inside the test's transaction, and
     * drop PluginHelper's cached list so the change is seen.
     *
     * @param   bool  $enabled  Wanted state
     *
     * @return  void
     */
    private function setGuidedToursPlugin(bool $enabled): void
    {
        if ($this->pluginStateAtStart === null) {
            $this->markTestSkipped('plg_system_guidedtours is not installed on the test database.');
        }

        $this->writeGuidedToursPluginState((int) $enabled);
        $this->resetPluginCache();
    }

    /**
     * @param   int  $enabled  Value to store in #__extensions.enabled
     *
     * @return  void
     */
    private function writeGuidedToursPluginState(int $enabled): void
    {
        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__extensions'))
                ->set($this->db->quoteName('enabled') . ' = ' . $enabled)
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
                ->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('system'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('guidedtours'))
        )->execute();
    }

    /**
     * @return  void
     */
    private function resetPluginCache(): void
    {
        (new \ReflectionProperty(PluginHelper::class, 'plugins'))->setValue(null, null);
    }


    /**
     * A helper whose tour list holds only a synthetic test tour.
     *
     * Registration iterates every shipped definition, and a real site already
     * carries those rows, so tests that asserted against the shipped uid were
     * really asserting against whatever the site happened to have. Replacing
     * the list keeps each test dependent only on its own fixture.
     *
     * @return  CwmguidedtourHelper
     */
    private function helper(): CwmguidedtourHelper
    {
        $helper = new CwmguidedtourHelper();

        $tours = new \ReflectionProperty(CwmguidedtourHelper::class, 'tours');
        $tours->setValue($helper, [
            'test_1572' => [
                'title'       => 'SHIPPED_TITLE',
                'description' => 'SHIPPED_DESC',
                'url'         => 'administrator/index.php?option=com_proclaim',
                'extensions'  => ['com_proclaim'],
                'published'   => 1,
                'access'      => 1,
                'language'    => '*',
                'note'        => '',
                'uid'         => self::UID,
                'autostart'   => false,
                'steps'       => [
                    [
                        'title'       => 'SHIPPED_STEP',
                        'description' => 'SHIPPED_STEP_DESC',
                        'position'    => 'center',
                        'target'      => '',
                        'type'        => 0,
                        'url'         => 'administrator/index.php?option=com_proclaim',
                    ],
                ],
            ],
        ]);

        return $helper;
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- an existing tour is not rewritten on update
    // -------------------------------------------------------------------------

    #[TestDox('Registering leaves an existing tour untouched')]
    public function testRegisterPreservesAnExistingTour(): void
    {
        $id = $this->insertRawTour(self::UID, 'Admin renamed this', 0);

        $this->helper()->registerGuidedTours();

        $row = $this->db->setQuery(
            $this->db->createQuery()
                ->select([$this->db->quoteName('title'), $this->db->quoteName('published')])
                ->from($this->db->quoteName('#__guidedtours'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadObject();

        $this->assertSame(
            'Admin renamed this',
            $row->title,
            'Every Proclaim update overwrote admin-edited tour text — see #1572'
        );
        $this->assertSame(
            '0',
            (string) $row->published,
            'An unpublished tour was silently re-published on each update — see #1572'
        );
    }

    #[TestDox('Registering does not delete the steps of an existing tour')]
    public function testRegisterPreservesExistingSteps(): void
    {
        $id = $this->insertRawTour(self::UID);

        $step = (object) [
            'tour_id'     => $id,
            'title'       => 'Admin authored step',
            'description' => 'translated content',
            'position'    => 'center',
            'target'      => '',
            'type'        => 0,
            'url'         => 'administrator/index.php',
            'published'   => 1,
            'language'    => 'de-DE',
            'ordering'    => 1,
            'note'        => '',
            'created'     => (new \Joomla\CMS\Date\Date())->toSql(),
            'created_by'  => 0,
            'modified'    => (new \Joomla\CMS\Date\Date())->toSql(),
            'modified_by' => 0,
        ];
        $this->db->insertObject('#__guidedtour_steps', $step, 'id');
        $stepId = (int) $this->db->insertid();

        $this->helper()->registerGuidedTours();

        $this->assertSame(
            1,
            (int) $this->db->setQuery(
                $this->db->createQuery()
                    ->select('COUNT(*)')
                    ->from($this->db->quoteName('#__guidedtour_steps'))
                    ->where($this->db->quoteName('id') . ' = ' . $stepId)
            )->loadResult(),
            'Steps were deleted and reinserted on every update, losing translated rows — see #1572'
        );
    }

    /**
     * The destructive refresh must still be reachable, just not by accident.
     */
    #[TestDox('An explicit reset does rewrite the tour')]
    public function testResetRewritesTheTour(): void
    {
        $id = $this->insertRawTour(self::UID, 'Admin renamed this', 0);

        $this->helper()->resetGuidedTours();

        $title = (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('title'))
                ->from($this->db->quoteName('#__guidedtours'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadResult();

        $this->assertNotSame('Admin renamed this', $title, 'An explicit reset is meant to restore the shipped text');
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- duplicate uids
    // -------------------------------------------------------------------------

    #[TestDox('Lookups return the earliest row when duplicates exist')]
    public function testLookupIsDeterministicAcrossDuplicates(): void
    {
        $first  = $this->insertRawTour(self::UID, 'first');
        $second = $this->insertRawTour(self::UID, 'second');

        $this->assertGreaterThan($first, $second, 'Precondition: two rows share the uid');

        $helper = $this->helper();

        $this->assertSame($first, $helper->getTourId(self::UID), 'Lookup must not depend on server row order — see #1572');
        $this->assertSame([$first, $second], $helper->getTourIds(self::UID));
    }

    /**
     * Without ORDER BY, MySQL happens to return rows in primary-key order, so
     * a runtime assertion here passes whether or not the clause is present.
     * Pin it in the source instead rather than claim coverage we do not have.
     */
    #[TestDox('The duplicate lookup orders explicitly rather than relying on storage order')]
    public function testLookupOrdersExplicitly(): void
    {
        $ref   = new \ReflectionMethod(CwmguidedtourHelper::class, 'getTourIds');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertMatchesRegularExpression(
            '/->order\(/',
            $body,
            'Callers must get the same row every time, not whichever the server returns — see #1572'
        );
    }

    #[TestDox('Uninstall removes every duplicate, not just one')]
    public function testRemoveAllToursDeletesEveryDuplicate(): void
    {
        $this->insertRawTour(self::UID, 'first');
        $this->insertRawTour(self::UID, 'second');
        $this->insertRawTour(self::UID, 'third');

        $this->helper()->removeAllTours();

        $this->assertSame(
            [],
            $this->helper()->getTourIds(self::UID),
            'Surplus rows survived uninstall in core tables forever — see #1572'
        );
    }

    /**
     * Registering into a database that already carries duplicates should leave
     * exactly one behind, so existing damage heals rather than persisting.
     */
    #[TestDox('Registration collapses pre-existing duplicates')]
    public function testRegistrationIsSelfHealingForExistingDuplicates(): void
    {
        $helper = $this->helper();

        // No tour yet -> the helper inserts one, then a racing request adds a
        // second. The next insert path prunes.
        $this->insertRawTour(self::UID, 'racer one');
        $this->insertRawTour(self::UID, 'racer two');

        $ref     = new \ReflectionMethod(CwmguidedtourHelper::class, 'pruneDuplicateTours');
        $removed = $ref->invoke($helper, self::UID);

        $this->assertSame(1, $removed, 'Exactly one surplus row should be removed');
        $this->assertCount(1, $helper->getTourIds(self::UID), 'One row must remain');
    }

    #[TestDox('Pruning a uid with no duplicates is a no-op')]
    public function testPruneIsANoOpWithoutDuplicates(): void
    {
        $this->insertRawTour(self::UID, 'only one');

        $ref = new \ReflectionMethod(CwmguidedtourHelper::class, 'pruneDuplicateTours');

        $this->assertSame(0, $ref->invoke($this->helper(), self::UID));
    }

    /**
     * The uid lookups are covered by the tour tests above; this pins the one
     * other bound query — the post-install message existence check by title_key.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("postInstallMessageExists() runs its bound title_key query")]
    public function testPostInstallMessageExistsBindExecutes(): void
    {
        $ref    = new \ReflectionMethod(CwmguidedtourHelper::class, 'postInstallMessageExists');
        $result = $ref->invoke($this->helper(), 'com_proclaim_no_such_title_key');

        // A broken :titleKey bind throws before it can answer; a clean run
        // returns a bool (false for this deliberately-absent key).
        $this->assertIsBool($result);
    }

    // -------------------------------------------------------------------------
    // Start Tour needs Joomla's Guided Tours plugin
    // -------------------------------------------------------------------------

    #[TestDox('canStartTours() follows the System - Guided Tours plugin')]
    public function testCanStartToursFollowsThePlugin(): void
    {
        $this->setGuidedToursPlugin(true);
        $this->assertTrue($this->helper()->canStartTours());

        $this->setGuidedToursPlugin(false);
        $this->assertFalse(
            $this->helper()->canStartTours(),
            'Start Tour was offered with the plugin disabled, where clicking it does nothing — see #2361'
        );
    }

    /**
     * Turning the plugin back on has to find the tours already in place, so
     * registration must not share the check that hides the button.
     */
    #[TestDox('Registering still writes a tour while the plugin is disabled')]
    public function testRegisterIgnoresThePlugin(): void
    {
        $this->setGuidedToursPlugin(false);

        $this->helper()->registerGuidedTours();

        $this->assertSame(
            1,
            $this->rowsForUid(self::UID),
            'Install and upgrade skipped the tours because the plugin was off — see #2361'
        );
    }

    #[TestDox('Uninstall still removes a tour while the plugin is disabled')]
    public function testRemoveAllToursIgnoresThePlugin(): void
    {
        $this->insertRawTour(self::UID);
        $this->assertSame(1, $this->rowsForUid(self::UID), 'Precondition: the tour row exists');
        $this->setGuidedToursPlugin(false);

        $this->helper()->removeAllTours();

        $this->assertSame(
            0,
            $this->rowsForUid(self::UID),
            'Uninstall left the tour orphaned in core tables because the plugin was off — see #2361'
        );
    }
}
