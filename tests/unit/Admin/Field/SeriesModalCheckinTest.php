<?php

/**
 * Unit test for Modal/SeriesField's checkin URL
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\Modal\SeriesField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test: setup() built select/new/edit URLs but never urls['checkin'],
 * the URL core's own picker JS calls whenever the popup chrome is dismissed
 * (X / Escape) rather than the in-iframe Cancel/Save. Without it, opening Edit
 * on a series from message.xml and closing the popup that way left the
 * #__bsms_series row checked out indefinitely, recoverable only via a manual
 * Global Check-in.
 *
 * @since  __DEPLOY_VERSION__
 */
class SeriesModalCheckinTest extends ProclaimTestCase
{
    public function testSetupBuildsACheckinUrl(): void
    {
        $field = (new \ReflectionClass(SeriesField::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');

        $xml = new \SimpleXMLElement('<field name="test" />');

        (new \ReflectionMethod(SeriesField::class, 'setup'))->invoke($field, $xml, 0);

        $urls = (new \ReflectionProperty($field, 'urls'))->getValue($field);

        $this->assertArrayHasKey('checkin', $urls, 'setup() must build urls[\'checkin\'] or a dismissed modal leaves the row checked out');
        $this->assertStringContainsString('option=com_proclaim', $urls['checkin']);
        $this->assertStringContainsString('task=cwmseries.checkin', $urls['checkin'], 'CwmseriesController (AdminController) ships checkin() natively');
        $this->assertStringContainsString('format=json', $urls['checkin'], 'Matches core\'s own Modal*Field checkin URLs, e.g. com_contact\'s ContactField');
    }
}
