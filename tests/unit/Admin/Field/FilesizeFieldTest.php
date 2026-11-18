<?php

/**
 * Unit test for FilesizeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\FilesizeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Form\Field\TextField;

/**
 * Regression test: getInput() hand-concatenated the <input> attribute string,
 * which produced invalid, duplicated markup -- `size="35"class="form-control"`
 * -- on every render of the field's own live XML usage (no space between the
 * two attributes), because the variable holding the non-readonly branch's
 * value was itself a second, unprefixed `class="form-control"` literal.
 *
 * Rebased on TextField, like the file's own PlaylistPickerField sibling, so
 * the input renders through core's attribute pipeline instead.
 *
 * Verified safe for the KB/MB/GB size-converter modal's transfer button
 * (build/media_source/js/cwmcore-admin.es6.js:166, `transferFileSize()`):
 * it writes the converted value by plain `document.getElementById(
 * 'jform_params_size').value = ss`, never touching the field's internal
 * markup shape. `$this->id` is computed by FormField::setup() from the
 * form group/field name regardless of base class, and core's own
 * layouts/joomla/form/field/text.php unconditionally renders
 * `id="<?php echo $id; ?>"`, so the id the JS selects by is unchanged.
 *
 * @since  __DEPLOY_VERSION__
 */
class FilesizeFieldTest extends ProclaimTestCase
{
    public function testExtendsTextFieldInsteadOfHandBuildingTheInput(): void
    {
        $this->assertTrue(
            is_subclass_of(FilesizeField::class, TextField::class),
            'FilesizeField must extend TextField so the <input> renders through core\'s layout, not '
                . 'hand-concatenated attribute strings'
        );
    }

    public function testGetInputDelegatesToParentRatherThanConcatenatingAttributes(): void
    {
        $ref   = new \ReflectionMethod(FilesizeField::class, 'getInput');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString('parent::getInput()', $body);
        $this->assertStringNotContainsString(
            'readonly="readonly"',
            $body,
            'Hand-built readonly/class attribute strings were the source of the duplicate-class bug'
        );
    }
}
