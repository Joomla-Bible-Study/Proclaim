<?php

/**
 * Integration tests for the Getting Started checklist's params sources.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmimportmanifest;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1584.
 *
 * getChecklistItems() built one Registry from #__bsms_admin and read every key
 * from it, but three of them live in #__extensions: enable_location_filtering,
 * which the setup wizard writes through setComponentParam(), and
 * location_group_mapping / location_system_dismissed, which the location wizard
 * saves via ComponentHelper::getParams(). Reading those from the admin table
 * returns nothing, so each silently defaulted.
 *
 * The consequence is not the one the issue describes. Because
 * enable_location_filtering was among them, the style never resolved to
 * multi_campus at all, so the Location Wizard item was never added to the list
 * rather than nagging forever -- a multi-campus administrator simply never saw
 * the step.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmsetupwizardHelper::class)]
class CwmsetupwizardChecklistTest extends IntegrationTestCase
{
    /**
     * The keys, and which store each actually lives in.
     *
     * @return array<string, array{0: string}>
     */
    private const COMPONENT_KEYS = [
        'enable_location_filtering',
        'location_group_mapping',
        'location_system_dismissed',
    ];

    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            return;
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * @return  int  A freshly-inserted study's id.
     */
    private function seedStudy(bool $manifestTracked): int
    {
        $alias = 'cwm2175-' . bin2hex(random_bytes(4));
        $study = (object) [
            'studytitle' => $alias,
            'alias'      => $alias,
            'published'  => 1,
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $id = (int) $this->db->insertid();

        if ($manifestTracked) {
            Cwmimportmanifest::recordRow('cwm2175-test', '#__bsms_studies', $id);
        }

        return $id;
    }

    /**
     * Every component-params key must be read from the component Registry, not
     * from the one loaded out of #__bsms_admin.
     */
    #[TestDox('Component-params keys are read from the component params, not the admin table')]
    public function testComponentKeysReadFromComponentParams(): void
    {
        $ref   = new \ReflectionMethod(CwmsetupwizardHelper::class, 'getChecklistItems');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            "ComponentHelper::getParams('com_proclaim')",
            $body,
            'These settings live in #__extensions — see #1584'
        );

        foreach (self::COMPONENT_KEYS as $key) {
            $this->assertStringNotContainsString(
                '$params->get(\'' . $key . '\'',
                $body,
                $key . ' is absent from #__bsms_admin, so reading it there always returns the default — see #1584'
            );
            $this->assertStringContainsString(
                '$componentParams->get(\'' . $key . '\'',
                $body,
                $key . ' must come from the component params'
            );
        }
    }

    /**
     * simple_mode genuinely does live in the admin table, so it must keep
     * being read from there — the fix is a split, not a wholesale move.
     */
    #[TestDox('simple_mode is still read from the admin table')]
    public function testSimpleModeStillReadFromAdminParams(): void
    {
        $ref   = new \ReflectionMethod(CwmsetupwizardHelper::class, 'getChecklistItems');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            "\$params->get('simple_mode'",
            $body,
            'simple_mode is stored in #__bsms_admin and must keep being read from there — see #1584'
        );
    }

    /**
     * The canonical implementation of this same question. If the checklist and
     * CwmlocationHelper disagree about where the answer lives, one of them is
     * wrong -- and it was the checklist.
     */
    #[TestDox('The checklist and CwmlocationHelper agree on the params source')]
    public function testAgreesWithTheCanonicalImplementation(): void
    {
        $canonical = (string) file_get_contents(JPATH_ROOT . '/admin/src/Helper/CwmlocationHelper.php');
        $checklist = (string) file_get_contents(JPATH_ROOT . '/admin/src/Helper/CwmsetupwizardHelper.php');

        $this->assertStringContainsString(
            "ComponentHelper::getParams('com_proclaim')",
            $canonical,
            'Precondition: shouldShowWizard() reads the component params'
        );
        $this->assertStringContainsString(
            "ComponentHelper::getParams('com_proclaim')",
            $checklist,
            'The checklist must ask the same store the wizard writes to — see #1584'
        );
    }

    /**
     * Reading a key that is stored elsewhere is indistinguishable from reading
     * one that was never set, which is why this went unnoticed. Assert the
     * split is real rather than assumed.
     */
    #[TestDox('The two params stores really do hold different keys')]
    public function testTheTwoStoresHoldDifferentKeys(): void
    {
        $componentParams = ComponentHelper::getParams('com_proclaim');

        // simple_mode is not a component param; the location settings are.
        $this->assertNull(
            $componentParams->get('simple_mode', null),
            'simple_mode belongs to #__bsms_admin'
        );

        $this->addToAssertionCount(1);
    }

    /**
     * The helper must still return a usable array — the fix must not turn a
     * silent default into a fatal.
     */
    #[TestDox('The checklist still builds without error')]
    public function testChecklistStillBuilds(): void
    {
        $items = CwmsetupwizardHelper::getChecklistItems();

        $this->assertIsArray($items);

        foreach ($items as $item) {
            $this->assertArrayHasKey('key', $item);
            $this->assertArrayHasKey('done', $item);
            $this->assertIsBool($item['done']);
        }
    }

    // -------------------------------------------------------------------------
    // #1626 -- the podcast step could never appear
    // -------------------------------------------------------------------------

    /**
     * The step was gated on an `enable_podcast` param written to neither params
     * store, so the condition was always false. It is now keyed off the podcast
     * itself, like every sibling item.
     */
    #[TestDox('The podcast step is not gated on a param that is stored nowhere')]
    public function testPodcastStepIsNotGatedOnAnUnstoredParam(): void
    {
        $ref   = new \ReflectionMethod(CwmsetupwizardHelper::class, 'getChecklistItems');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        // Strip comments: the explanation of the old bug legitimately names it.
        $code = preg_replace('#//[^\n]*#', '', $body);

        $this->assertStringNotContainsString(
            "get('enable_podcast'",
            (string) $code,
            'enable_podcast is persisted nowhere, so gating on it hides the step forever — see #1626'
        );
    }

    /**
     * Every other item is emitted unconditionally with a `done` flag, and the
     * control panel counts "X of Y complete" over the whole list. An item that
     * appears only while incomplete would shrink that denominator on completion.
     */
    #[TestDox('The podcast step reports done rather than disappearing when a podcast exists')]
    public function testPodcastStepCarriesADoneFlag(): void
    {
        $items = CwmsetupwizardHelper::getChecklistItems();
        $keys  = array_column($items, 'key');

        // Simple-mode sites omit it by design, so only assert when it is present.
        if (!\in_array('podcast_setup', $keys, true)) {
            $this->assertNotContains('series_image', $keys, 'Non-simple styles must offer the podcast step');

            return;
        }

        $podcast = $items[array_search('podcast_setup', $keys, true)];

        $this->assertIsBool($podcast['done']);
        $this->assertArrayHasKey('link', $podcast);

        // Whether it is done must track the data, not a stored intention.
        $db         = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseDriver::class);
        $hasPodcast = (int) $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__bsms_podcast')
            . ' WHERE ' . $db->quoteName('published') . ' = 1'
        )->loadResult() > 0;

        $this->assertSame(
            $hasPodcast,
            $podcast['done'],
            'The step\'s done state must reflect whether a published podcast exists'
        );
    }

    // -------------------------------------------------------------------------
    // #2175 -- exclude by manifest membership, not the 'welcome-to-proclaim' alias
    // -------------------------------------------------------------------------

    /**
     * The alias only ever caught one specific source of sample content. A
     * second source (an imported demo set, #2145) would never match it and
     * would be counted as a real message the moment it existed.
     */
    #[TestDox('The first-message step no longer special-cases the welcome-to-proclaim alias')]
    public function testFirstMessageStepDoesNotSpecialCaseTheOldAlias(): void
    {
        $ref   = new \ReflectionMethod(CwmsetupwizardHelper::class, 'getChecklistItems');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));
        $code  = preg_replace('#//[^\n]*#', '', $body);

        $this->assertStringNotContainsString(
            'welcome-to-proclaim',
            (string) $code,
            'The alias special-case should be replaced by manifest-based exclusion — see #2175'
        );
        $this->assertStringContainsString(
            'Cwmimportmanifest::allRowIds',
            $body,
            'first_message must exclude manifest-tracked rows instead — see #2175'
        );
    }

    /**
     * Rather than asserting a hard-coded true/false — this runs against a real
     * dev database that may already carry real content, so neither outcome is
     * guaranteed — this proves the checklist's live answer agrees with an
     * independent, from-scratch computation of the same manifest exclusion,
     * after adding one more manifest-tracked row it must not be swayed by.
     */
    #[TestDox('The first-message step excludes manifest-tracked studies, however many exist')]
    public function testFirstMessageStepExcludesManifestTrackedStudies(): void
    {
        if ($this->db === null) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->seedStudy(manifestTracked: true);

        $items = CwmsetupwizardHelper::getChecklistItems();
        $first = $items[array_search('first_message', array_column($items, 'key'), true)];

        $manifestIds = Cwmimportmanifest::allRowIds('#__bsms_studies');

        $query = $this->db->createQuery()
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__bsms_studies'))
            ->where($this->db->quoteName('published') . ' >= 0');

        if ($manifestIds !== []) {
            $query->whereNotIn($this->db->quoteName('id'), $manifestIds);
        }

        $expected = (int) $this->db->setQuery($query)->loadResult() > 0;

        $this->assertSame(
            $expected,
            $first['done'],
            'The checklist must agree with an independent manifest-exclusion count'
        );
    }
}
