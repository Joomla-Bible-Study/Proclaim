<?php

/**
 * Unit tests for the guided tour startTour links
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmguidedtourHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Filter\InputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Every startTour link Proclaim ships must name a tour Proclaim ships (#2362).
 *
 * The Getting Started post-install message linked to startTour=getting_started
 * while the control panel read the value as an integer, so the link never
 * started anything. These tests read the shipped definitions, so a message
 * whose link names no tour fails here rather than on a site.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmguidedtourHelper::class)]
class CwmguidedtourHelperStartTourTest extends ProclaimTestCase
{
    /**
     * A helper holding the shipped definitions, built without a database.
     *
     * @return  CwmguidedtourHelper
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    private function helper(): CwmguidedtourHelper
    {
        return (new \ReflectionClass(CwmguidedtourHelper::class))->newInstanceWithoutConstructor();
    }

    /**
     * Every post-install message linking to startTour names a shipped tour, read the
     * way the control panel reads it, and at least one message has such a link.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('Every post-install startTour link names a shipped tour')]
    public function testEveryPostInstallStartTourLinkNamesATour(): void
    {
        $helper   = $this->helper();
        $messages = (new \ReflectionProperty(CwmguidedtourHelper::class, 'postInstallMessages'))->getValue($helper);
        $uids     = array_column((new \ReflectionProperty(CwmguidedtourHelper::class, 'tours'))->getValue($helper), 'uid');
        $filter   = new InputFilter();
        $checked  = 0;

        foreach ($messages as $key => $message) {
            parse_str((string) parse_url($message['action'] ?? '', PHP_URL_QUERY), $query);

            if (!isset($query['startTour'])) {
                continue;
            }

            // The control panel reads the value with getCmd(), so test what it sees.
            $value = $filter->clean((string) $query['startTour'], 'CMD');
            $checked++;

            $this->assertContains(
                $helper->startTourUid($value),
                $uids,
                \sprintf("Post-install message '%s' links to startTour=%s, which starts no tour — see #2362", $key, $value)
            );
        }

        $this->assertGreaterThan(0, $checked, 'No post-install message links to a tour, so this test checked nothing');
    }

    /**
     * startTourUid() maps the legacy value 1 to the What's New tour.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('startTourUid() keeps the legacy "1" pointing at the What\'s New tour')]
    public function testLegacyOneMeansWhatsNew(): void
    {
        $this->assertSame(
            'com_proclaim_whats_new_10_1',
            $this->helper()->startTourUid('1'),
            'What\'s New messages already stored on sites link with startTour=1'
        );
    }

    /**
     * startTourUid() returns null for an empty string, 0 or an unknown value.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('startTourUid() names nothing for an empty or unknown value')]
    public function testUnknownValuesNameNothing(): void
    {
        $helper = $this->helper();

        $this->assertNull($helper->startTourUid(''));
        $this->assertNull($helper->startTourUid('0'));
        $this->assertNull($helper->startTourUid('bogus'));
    }
}
