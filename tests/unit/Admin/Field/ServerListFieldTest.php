<?php

/**
 * Unit test for ServerListField's data attributes
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\ServerListField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test: getInput() rendered through parent::getInput() (ListField's
 * own layout-based <select>) and then regex-edited the already-rendered HTML
 * string to splice in data-server-types/data-server-info -- core fields never
 * do this; FormField already has dataAttributes support for exactly this.
 *
 * getOptions() must run before collectLayoutData() captures the field's
 * render data (the same ordering hazard as LocationListField/IconTypeField),
 * since dataAttributes is what collectLayoutData() snapshots into the
 * rendered `dataAttribute` string.
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerListFieldTest extends ProclaimTestCase
{
    public function testGetInputUsesDataAttributesNotRegex(): void
    {
        $ref   = new \ReflectionMethod(ServerListField::class, 'getInput');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringNotContainsString(
            'preg_replace',
            $body,
            'getInput() must not regex-edit its own rendered HTML -- use $this->dataAttributes instead'
        );
        $this->assertStringContainsString(
            "dataAttributes['data-server-types']",
            $body
        );

        $getOptionsAt  = strpos($body, '$this->getOptions()');
        $dataAttrAt    = strpos($body, "dataAttributes['data-server-types']");
        $collectDataAt = strpos($body, '$this->collectLayoutData()');

        $this->assertNotFalse($getOptionsAt);
        $this->assertNotFalse($dataAttrAt);
        $this->assertNotFalse($collectDataAt);
        $this->assertLessThan(
            $collectDataAt,
            $getOptionsAt,
            'getOptions() must run before collectLayoutData() snapshots dataAttributes into the render data'
        );
        $this->assertLessThan(
            $collectDataAt,
            $dataAttrAt,
            'dataAttributes must be populated before collectLayoutData() snapshots it'
        );
    }
}
