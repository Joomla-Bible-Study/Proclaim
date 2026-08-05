<?php

/**
 * Unit tests for Cwmuploadscript and the addon upload() contract.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\Cwmuploadscript;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression coverage for #1564.
 *
 * 1. CwmmediafileController::xhr() dispatches `$addon->$handler($input)` with a
 *    Joomla\Input\Input object, but CWMAddon::upload() and all 16 overrides
 *    declared `?array $data`. `handler=upload` passed method_exists(), then PHP
 *    threw an uncaught TypeError before Cwmuploadscript::upload() was entered --
 *    every validation inside it (size limits, canUpload(), File::upload()) was
 *    dead code, and the endpoint returned a crash instead of the JSON xhr()
 *    promises.
 *
 * 2. The admin's "Legal Extensions" setting (`upload_extensions`) was never
 *    enforced: MediaHelper::canUpload() reads `restrict_uploads_extensions`,
 *    a key com_proclaim's config.xml does not define, so Registry::get()
 *    always fell through to Joomla's hardcoded default list.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmuploadscriptTest extends ProclaimTestCase
{
    // -------------------------------------------------------------------------
    // Finding 1 -- the upload() type contract
    // -------------------------------------------------------------------------

    /**
     * Every addon that ships an upload() override, plus the abstract base.
     *
     * @return array<string, array{0: class-string}>
     */
    public static function addonClassProvider(): array
    {
        $base    = \dirname(__DIR__, 4) . '/admin/src/Addons/Servers';
        $classes = ['CWMAddon (base)' => [CWMAddon::class]];

        foreach (glob($base . '/*/CWMAddon*.php') as $file) {
            $server = basename(\dirname($file));
            $short  = basename($file, '.php');

            $classes[$short] = [
                'CWM\\Component\\Proclaim\\Administrator\\Addons\\Servers\\' . $server . '\\' . $short,
            ];
        }

        return $classes;
    }

    /**
     * The dispatcher hands upload() an Input object. Any override drifting back
     * to `?array` (or any other non-Input type) reintroduces the fatal
     * TypeError, so pin the declared type on every implementation.
     *
     * @param   class-string  $class  Addon class under test
     */
    #[DataProvider('addonClassProvider')]
    public function testUploadAcceptsAnInputObject(string $class): void
    {
        $method = new \ReflectionMethod($class, 'upload');
        $params = $method->getParameters();

        $this->assertCount(1, $params, $class . '::upload() must take exactly one parameter');

        $type = $params[0]->getType();

        $this->assertNotNull($type, $class . '::upload() must declare its parameter type');
        $this->assertSame(
            Input::class,
            (string) $type,
            $class . '::upload() must accept the Input object xhr() actually passes — see #1564'
        );
        $this->assertFalse(
            $type->allowsNull(),
            $class . '::upload() is always passed an Input; a nullable type invites the old ?array shape back'
        );
    }

    /**
     * Cwmuploadscript is the shared implementation the three real handlers
     * delegate to, and its body only ever uses the Input API.
     */
    public function testCwmuploadscriptUploadAcceptsAnInputObject(): void
    {
        $method = new \ReflectionMethod(Cwmuploadscript::class, 'upload');
        $type   = $method->getParameters()[0]->getType();

        $this->assertNotNull($type, 'Cwmuploadscript::upload() must declare its parameter type');
        $this->assertSame(Input::class, (string) $type);
    }

    /**
     * xhr() reaches any *public* addon method by name, so the public upload()
     * surface must be exactly the three addons that genuinely accept uploads.
     * Everything else is a `return false;` stub that has no business being
     * callable from the generic dispatcher.
     */
    public function testOnlyRealUploadHandlersArePublic(): void
    {
        $public = [];

        foreach (self::addonClassProvider() as $row) {
            $class = $row[0];

            if ((new \ReflectionMethod($class, 'upload'))->isPublic()) {
                $public[] = (new \ReflectionClass($class))->getShortName();
            }
        }

        sort($public);

        $this->assertSame(
            ['CWMAddonDirect', 'CWMAddonLegacy', 'CWMAddonLocal'],
            $public,
            'Only the addons that actually perform uploads may expose upload() publicly — see #1564'
        );
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- the configured extension list is what gets enforced
    // -------------------------------------------------------------------------

    /**
     * Invoke the protected copy helper.
     *
     * @param   Registry  $params  Params to mutate
     *
     * @return  void
     */
    private function applyConfiguredExtensions(Registry $params): void
    {
        $method = new \ReflectionMethod(Cwmuploadscript::class, 'applyConfiguredExtensions');
        $method->invoke(null, $params);
    }

    /**
     * The admin's configured list must land on the key MediaHelper reads.
     */
    public function testConfiguredLegalExtensionsBecomeTheEnforcedList(): void
    {
        $params = new Registry(['upload_extensions' => 'vtt,mp3,mp4']);

        $this->assertNull(
            $params->get('restrict_uploads_extensions'),
            'Precondition: com_proclaim does not define the key MediaHelper reads'
        );

        $this->applyConfiguredExtensions($params);

        $this->assertSame(
            'vtt,mp3,mp4',
            $params->get('restrict_uploads_extensions'),
            "The admin's Legal Extensions must be the list canUpload() enforces — see #1564"
        );
    }

    /**
     * Surrounding whitespace must not defeat the non-empty guard, and must not
     * reach the allow-list as a leading empty element.
     */
    public function testWhitespaceOnlyValueIsTreatedAsUnset(): void
    {
        $params = new Registry(['upload_extensions' => '   ']);

        $this->applyConfiguredExtensions($params);

        $this->assertNull(
            $params->get('restrict_uploads_extensions'),
            'A whitespace-only list must fall back to Joomla defaults, not be copied through'
        );
    }

    /**
     * An empty/absent setting must leave the key unset so Joomla's default list
     * applies. Writing '' would make canUpload() reject every file — a worse
     * failure than the bug being fixed.
     */
    #[DataProvider('emptyExtensionProvider')]
    public function testEmptyConfigurationFallsBackToJoomlaDefaults(array $config): void
    {
        $params = new Registry($config);

        $this->applyConfiguredExtensions($params);

        $this->assertNull(
            $params->get('restrict_uploads_extensions'),
            'An empty Legal Extensions setting must not be copied through — it would reject every upload'
        );
    }

    /**
     * @return array<string, array{0: array}>
     */
    public static function emptyExtensionProvider(): array
    {
        return [
            'key absent'   => [[]],
            'empty string' => [['upload_extensions' => '']],
        ];
    }
}
