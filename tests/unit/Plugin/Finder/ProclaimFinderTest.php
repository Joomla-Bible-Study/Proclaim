<?php

/**
 * Unit tests for Proclaim Finder Plugin
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Plugin\Finder;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Plugin\Finder\Proclaim\Extension\Proclaim;

/**
 * Test class for Proclaim Finder Plugin
 *
 * @since  10.0.0
 */
class ProclaimFinderTest extends ProclaimTestCase
{
    /**
     * Test plugin class file exists
     *
     * @return void
     */
    public function testPluginClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test plugin has correct namespace
     *
     * @return void
     */
    public function testPluginHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Plugin\Finder\Proclaim\Extension;',
            $content
        );
    }

    /**
     * Test plugin extends Adapter
     *
     * @return void
     */
    public function testPluginExtendsAdapter(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('extends Adapter', $content);
        $this->assertStringContainsString(
            'use Joomla\Component\Finder\Administrator\Indexer\Adapter;',
            $content
        );
    }

    /**
     * Test plugin implements SubscriberInterface
     *
     * @return void
     */
    public function testPluginImplementsSubscriberInterface(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('implements SubscriberInterface', $content);
        $this->assertStringContainsString(
            'use Joomla\Event\SubscriberInterface;',
            $content
        );
    }

    /**
     * Test plugin defines context property
     *
     * @return void
     */
    public function testPluginDefinesContextProperty(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('$context = \'Proclaim\'', $content);
    }

    /**
     * Test plugin defines extension property
     *
     * @return void
     */
    public function testPluginDefinesExtensionProperty(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('$extension = \'com_proclaim\'', $content);
    }

    /**
     * Test plugin defines table property
     *
     * @return void
     */
    public function testPluginDefinesTableProperty(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('$table = \'#__bsms_studies\'', $content);
    }

    /**
     * Test plugin has getSubscribedEvents method
     *
     * @return void
     */
    public function testPluginHasGetSubscribedEventsMethod(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('public static function getSubscribedEvents()', $content);
    }

    /**
     * Test plugin has index method
     *
     * @return void
     */
    public function testPluginHasIndexMethod(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('protected function index(Result $item)', $content);
    }

    /**
     * Test plugin has getListQuery method
     *
     * @return void
     */
    public function testPluginHasGetListQueryMethod(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('protected function getListQuery(', $content);
    }

    /**
     * Test plugin has getStateQuery method
     *
     * @return void
     */
    public function testPluginHasGetStateQueryMethod(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('protected function getStateQuery()', $content);
    }

    /**
     * Test plugin subscribes to finder events
     *
     * @return void
     */
    public function testPluginSubscribesToFinderEvents(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('onFinderAfterDelete', $content);
        $this->assertStringContainsString('onFinderAfterSave', $content);
        $this->assertStringContainsString('onFinderBeforeSave', $content);
        $this->assertStringContainsString('onFinderChangeState', $content);
    }

    /**
     * Test plugin uses Cwmhelperroute for routing
     *
     * @return void
     */
    public function testPluginUsesCwmhelperroute(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('Cwmhelperroute::getArticleRoute', $content);
    }

    /**
     * Test plugin manifest file exists
     *
     * @return void
     */
    public function testPluginManifestFileExists(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/proclaim.xml';
        $this->assertFileExists($filePath);
    }

    /**
     * Test plugin has English language file
     *
     * @return void
     */
    public function testPluginHasEnglishLanguageFile(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/language/en-GB/en-GB.plg_finder_proclaim.sys.ini';
        $this->assertFileExists($filePath);
    }

    /**
     * Test plugin has German language file
     *
     * @return void
     */
    public function testPluginHasGermanLanguageFile(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/language/de-DE/de-DE.plg_finder_proclaim.sys.ini';
        $this->assertFileExists($filePath);
    }

    /**
     * Test plugin service provider exists
     *
     * @return void
     */
    public function testPluginServiceProviderExists(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/services/provider.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test plugin handles cwmmessage context for messages
     *
     * The model uses com_proclaim.cwmmessage as typeAlias, so the plugin
     * must handle this context for finder events.
     *
     * @return void
     */
    public function testPluginHandlesCwmmessageContext(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'com_proclaim.cwmmessage',
            $content,
            'Plugin should handle com_proclaim.cwmmessage context from CwmmessageModel'
        );
    }

    /**
     * Test plugin handles cwmserie context for series
     *
     * The model uses com_proclaim.cwmserie as typeAlias, so the plugin
     * must handle this context for finder events.
     *
     * @return void
     */
    public function testPluginHandlesCwmserieContext(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'com_proclaim.cwmserie',
            $content,
            'Plugin should handle com_proclaim.cwmserie context from CwmserieModel'
        );
    }

    /**
     * Test plugin handles series access changes
     *
     * When a series is updated, the plugin should update indexes for
     * all messages belonging to that series.
     *
     * @return void
     */
    public function testPluginHandlesSeriesAccessChanges(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('seriesAccessChange', $content);
        $this->assertStringContainsString('checkSeriesAccess', $content);
        $this->assertStringContainsString('old_seriesAccess', $content);
    }

    /**
     * Test plugin handles series state changes
     *
     * When a series publish state changes, the plugin should update
     * the state of all messages in that series.
     *
     * @return void
     */
    public function testPluginHandlesSeriesStateChanges(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('seriesStateChange', $content);
    }

    /**
     * Test plugin defines state_field as 'published'
     *
     * The #__bsms_studies table uses 'published' column instead of 'state'
     * for the published state. This must be explicitly set.
     *
     * @return void
     */
    public function testPluginDefinesStateFieldAsPublished(): void
    {
        $filePath = JPATH_ROOT . '/plugins/finder/proclaim/src/Extension/Proclaim.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            "\$state_field = 'published'",
            $content,
            'Plugin must define state_field as published since studies table uses published column'
        );
    }
}
