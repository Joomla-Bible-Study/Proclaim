<?php

/**
 * Unit tests for Modal/ServerTypeField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\Modal\ServerTypeField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Form\Form;

/**
 * Regression test for #1480: ServerTypeField hand-rolled ~250 lines of manual
 * modal HTML/JS via a fully overridden getInput() extending plain FormField —
 * the same pattern that produced the XSS in this exact file (#1457). It now
 * extends Joomla core's ModalSelectField, matching Modal/SeriesField.php,
 * Modal/StudyField.php and Modal/TeacherDisplayField.php (#1465/#1479).
 *
 * The old #1457 regression test (asserting getInput() escaped its
 * interpolated values) no longer applies: getInput() isn't overridden at
 * all anymore, so there is no hand-rolled HTML left to escape.
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerTypeFieldTest extends ProclaimTestCase
{
    /**
     * Build a field instance with a bound Form supplying id=5 for
     * $this->form->getValue('id'), without going through the constructor
     * (which needs a full Form already attached).
     */
    private function makeField(mixed $value): ServerTypeField
    {
        $field = (new \ReflectionClass(ServerTypeField::class))->newInstanceWithoutConstructor();

        $form = new Form('test');
        $form->load('<form><fields><field name="id" type="text" /></fields></form>');
        $form->bind(['id' => 5]);

        (new \ReflectionProperty($field, 'form'))->setValue($field, $form);
        (new \ReflectionProperty($field, 'fieldname'))->setValue($field, 'test');

        $xml = new \SimpleXMLElement('<field name="test" />');
        (new \ReflectionMethod(ServerTypeField::class, 'setup'))->invoke($field, $xml, $value);

        return $field;
    }

    /**
     * @return void
     */
    public function testFieldExtendsModalSelectFieldAndBuildsCorrectUrls(): void
    {
        $this->assertTrue(
            is_subclass_of(ServerTypeField::class, ModalSelectField::class),
            'ServerTypeField must extend ModalSelectField — see #1480'
        );

        $field = $this->makeField('YouTube');

        $urls = (new \ReflectionProperty($field, 'urls'))->getValue($field);

        $this->assertStringContainsString('view=cwmservers', $urls['select'], 'Select URL must target cwmservers');
        $this->assertStringContainsString('layout=types', $urls['select'], 'Select URL must use the types layout');
        $this->assertStringContainsString('recordId=5', $urls['select'], 'Select URL must carry the current record id');

        $this->assertStringContainsString('view=cwmserver', $urls['edit'], 'Edit URL must target cwmserver');
        $this->assertStringContainsString('task=cwmserver.edit', $urls['edit'], 'Edit URL must use task=cwmserver.edit — not cwmmessage, see #1480');
        $this->assertStringContainsString('task=cwmserver.add', $urls['new'], 'New URL must use task=cwmserver.add — not cwmmessage, see #1480');
    }

    /**
     * @return void
     */
    public function testValueIsNormalizedToLowercase(): void
    {
        $field = $this->makeField('YouTube');

        $value = (new \ReflectionProperty($field, 'value'))->getValue($field);

        $this->assertSame('youtube', $value, 'ServerTypeField must lowercase its type-key value — see #1480');
    }

    /**
     * @return void
     */
    public function testGetValueTitleUsesReverseLookupNotSqlQuery(): void
    {
        $field = $this->makeField('');

        $getValueTitle = new \ReflectionMethod(ServerTypeField::class, 'getValueTitle');

        // Empty value must short-circuit to '' without touching the reverse-lookup model.
        $this->assertSame('', $getValueTitle->invoke($field));

        // The non-empty path can't be invoked here (it boots the component's
        // MVC factory), so pin its shape: it must resolve titles through the
        // model's reverse-lookup map, not a per-render SQL query -- the exact
        // regression this test's name guards, which the empty-value
        // short-circuit assertion alone never exercised.
        $lines = file($getValueTitle->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $getValueTitle->getStartLine() - 1, $getValueTitle->getEndLine() - $getValueTitle->getStartLine() + 1)
        );

        $this->assertStringContainsString('getTypeReverseLookup()', $body, 'Titles must come from the reverse-lookup map');
        $this->assertStringNotContainsString('setQuery', $body, 'getValueTitle() must not run its own SQL query');
        $this->assertStringNotContainsString('createQuery', $body, 'getValueTitle() must not build its own SQL query');
    }
}
