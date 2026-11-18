<?php

/**
 * Unit test for Modal/TeacherDisplayField's cross-component renderer pinning
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\Modal\TeacherDisplayField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test: this field is reused from site/tmpl/cwmteacher/default.xml
 * (the "Single Teacher" menu-item type), which the Menu Manager renders under
 * option=com_menus. FileLayout's default 'auto' component resolution would
 * search com_menus's own layout override paths instead of com_proclaim's --
 * core's own dual-use Modal*Field fields (e.g. com_contact's ContactField,
 * reused the same way from its own menu-item-type form) avoid this with a
 * getRenderer() override that pins the component explicitly.
 *
 * @since  __DEPLOY_VERSION__
 */
class TeacherDisplayFieldRendererTest extends ProclaimTestCase
{
    public function testGetRendererPinsComProclaim(): void
    {
        $this->assertTrue(
            method_exists(TeacherDisplayField::class, 'getRenderer'),
            'TeacherDisplayField must override getRenderer() to pin the layout search path'
        );

        $ref   = new \ReflectionMethod(TeacherDisplayField::class, 'getRenderer');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString("setComponent('com_proclaim')", $body);
        $this->assertStringContainsString('setClient(1)', $body);
        $this->assertStringContainsString('parent::getRenderer(', $body);
    }
}
