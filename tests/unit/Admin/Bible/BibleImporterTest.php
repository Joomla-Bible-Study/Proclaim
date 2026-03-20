<?php

/**
 * Unit tests for BibleImporter
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Bible;

use CWM\Library\Scripture\Importer\BibleImporter;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for BibleImporter
 *
 * @since  10.1.0
 */
class BibleImporterTest extends ProclaimTestCase
{
    /**
     * Test importFromJson returns -1 for invalid JSON
     *
     * @return void
     */
    public function testImportFromJsonReturnsNegativeForInvalidJson(): void
    {
        // importFromJson calls Factory::getContainer() which isn't available in unit tests
        // but we can verify it returns -1 for truly invalid JSON (before DB access)
        $result = BibleImporter::importFromJson('not valid json', 'test');
        $this->assertSame(-1, $result);
    }
}
