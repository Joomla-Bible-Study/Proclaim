<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmsermonModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Captcha\CaptchaProviderInterface;
use Joomla\CMS\Captcha\CaptchaRegistry;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * The comment form's captcha: the form carries a captcha field, the plugin that
 * guards it follows Proclaim's Captcha option and then the site's default, and
 * that plugin judges the answer.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmsermonModelCommentCaptchaTest extends ProclaimTestCase
{
    /**
     * Name of the captcha provider these tests register.
     *
     * @var    string
     *
     * @since  __DEPLOY_VERSION__
     */
    private const string TEST_PLUGIN = 'proclaimcommenttest';

    /**
     * The application in place before a test replaced it.
     *
     * @var    mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    private mixed $savedApplication = null;

    /**
     * PluginHelper's enabled-plugin list before a test replaced it.
     *
     * @var    mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    private mixed $savedPlugins = null;

    /**
     * Save the application and the enabled-plugin list that the tests replace.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->savedApplication = Factory::$application;
        $this->savedPlugins     = (new \ReflectionProperty(PluginHelper::class, 'plugins'))->getValue();
    }

    /**
     * Restore the application and the enabled-plugin list.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Factory::$application = $this->savedApplication;
        (new \ReflectionProperty(PluginHelper::class, 'plugins'))->setValue(null, $this->savedPlugins);

        parent::tearDown();
    }

    /**
     * Proclaim's Captcha option names the plugin, when that plugin is enabled.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testTheCaptchaOptionNamesThePlugin(): void
    {
        $this->siteWith(self::TEST_PLUGIN, '0', [self::TEST_PLUGIN]);

        $this->assertSame(self::TEST_PLUGIN, $this->model()->getCommentCaptcha());
    }

    /**
     * "Use Global" stores an empty option, so the site's default captcha applies.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testUseGlobalFallsBackToTheSiteDefault(): void
    {
        $this->siteWith('', self::TEST_PLUGIN, [self::TEST_PLUGIN]);

        $this->assertSame(self::TEST_PLUGIN, $this->model()->getCommentCaptcha());
    }

    /**
     * "None" turns the captcha off, even when the site has a default captcha.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testNoneMeansNoCaptcha(): void
    {
        $this->siteWith('0', self::TEST_PLUGIN, [self::TEST_PLUGIN]);

        $this->assertSame('', $this->model()->getCommentCaptcha());
    }

    /**
     * A plugin that isn't enabled counts as none, so the form shows no captcha
     * and the controller asks for no answer.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testAPluginThatIsNotEnabledMeansNoCaptcha(): void
    {
        $this->siteWith(self::TEST_PLUGIN, '0', []);

        $this->assertSame('', $this->model()->getCommentCaptcha());
    }

    /**
     * The plugin judges the answer: only the right one passes.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    public function testThePluginJudgesTheAnswer(): void
    {
        Factory::getContainer()->get(CaptchaRegistry::class)->add($this->testProvider());
        $model = $this->model();

        $this->assertTrue($model->checkCommentCaptcha(self::TEST_PLUGIN, 'right'));
        $this->assertFalse($model->checkCommentCaptcha(self::TEST_PLUGIN, 'wrong'));
        $this->assertFalse($model->checkCommentCaptcha(self::TEST_PLUGIN, ''));
    }

    /**
     * A plugin Joomla can't load never accepts an answer: the check either fails
     * or throws, and the controller treats a throw as a failed answer.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    public function testAPluginThatCannotLoadNeverAcceptsAnAnswer(): void
    {
        (new \ReflectionProperty(PluginHelper::class, 'plugins'))->setValue(null, []);

        try {
            $passed = $this->model()->checkCommentCaptcha('proclaimnosuchcaptcha', 'right');
        } catch (\RuntimeException) {
            $passed = false;
        }

        $this->assertFalse($passed);
    }

    /**
     * The comment form carries the captcha field, in the fieldset the comment
     * template shows when a captcha is on.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTheCommentFormCarriesTheCaptchaField(): void
    {
        $form = simplexml_load_file(\dirname(__DIR__, 4) . '/site/forms/comment.xml');

        $this->assertCount(1, $form->xpath('//fieldset[@name="captcha"]/field[@name="captcha"][@type="captcha"]'));
    }

    /**
     * The captcha widget posts its answer as jform[captcha], the name comment()
     * reads the answer from.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTheCaptchaPostsUnderTheNameTheControllerReads(): void
    {
        Factory::getContainer()->get(CaptchaRegistry::class)->add($this->testProvider());

        $form = new Form('com_proclaim.comment', ['control' => 'jform']);
        $form->loadFile(\dirname(__DIR__, 4) . '/site/forms/comment.xml');
        $form->setFieldAttribute('captcha', 'plugin', self::TEST_PLUGIN);

        $this->assertStringContainsString('name="jform[captcha]"', $form->getField('captcha')->input);
    }

    /**
     * The single-message menu item has no captcha field, so saving it never asks
     * an administrator to solve one.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTheMenuItemHasNoCaptchaField(): void
    {
        $menuItem = simplexml_load_file(\dirname(__DIR__, 4) . '/site/tmpl/cwmsermon/default.xml');

        $this->assertSame([], $menuItem->xpath('//field[@type="captcha"]'));
    }

    /**
     * Make the application a site whose Proclaim Captcha option, site default
     * captcha and enabled captcha plugins are the ones given.
     *
     * @param   string    $option   Proclaim's Captcha option; an empty string is "Use Global".
     * @param   string    $default  The site's default captcha; '0' is none.
     * @param   string[]  $enabled  Names of the enabled captcha plugins.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function siteWith(string $option, string $default, array $enabled): void
    {
        $app = $this->createStub(SiteApplicationDouble::class);
        $app->method('isClient')->willReturnCallback(static fn (string $client): bool => $client === 'site');
        $app->method('getParams')->willReturn(new Registry(['captcha' => $option]));
        $app->method('get')->willReturnCallback(
            static fn (string $key, $fallback = null) => $key === 'captcha' ? $default : $fallback
        );
        Factory::$application = $app;

        $plugins = array_map(
            static fn (string $name): object => (object) ['type' => 'captcha', 'name' => $name, 'params' => '{}'],
            $enabled
        );
        (new \ReflectionProperty(PluginHelper::class, 'plugins'))->setValue(null, $plugins);
    }

    /**
     * A sermon model; the captcha methods use no model state, so it skips the constructor.
     *
     * @return  CwmsermonModel  An instance built without running its constructor
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    private function model(): CwmsermonModel
    {
        return (new \ReflectionClass(CwmsermonModel::class))->newInstanceWithoutConstructor();
    }

    /**
     * A captcha provider that accepts only the answer "right".
     *
     * @return  CaptchaProviderInterface  A provider named TEST_PLUGIN that accepts only the answer "right"
     *
     * @since   __DEPLOY_VERSION__
     */
    private function testProvider(): CaptchaProviderInterface
    {
        return new class (self::TEST_PLUGIN) implements CaptchaProviderInterface {
            /**
             * Remember the name the provider registers under.
             *
             * @param   string  $name  The provider's name.
             *
             * @since   __DEPLOY_VERSION__
             */
            public function __construct(private readonly string $name)
            {
            }

            /**
             * The name the provider registers under.
             *
             * @return  string  The name passed to the constructor
             *
             * @since   __DEPLOY_VERSION__
             */
            public function getName(): string
            {
                return $this->name;
            }

            /**
             * Show a plain input under the name the field gives, as a real widget posts.
             *
             * @param   string  $name        The field name.
             * @param   array   $attributes  The field attributes.
             *
             * @return  string  A hidden input whose name attribute is the escaped field name
             *
             * @since   __DEPLOY_VERSION__
             */
            public function display(string $name = '', array $attributes = []): string
            {
                return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES) . '">';
            }

            /**
             * Accept only the answer "right".
             *
             * @param   ?string  $code  The posted answer.
             *
             * @return  bool  Whether the posted answer is exactly "right"
             *
             * @since   __DEPLOY_VERSION__
             */
            public function checkAnswer(?string $code = null): bool
            {
                return $code === 'right';
            }

            /**
             * Leave the field as it is.
             *
             * @param   FormField          $field    The captcha field.
             * @param   \SimpleXMLElement  $element  The field's XML.
             *
             * @return  void
             *
             * @since   __DEPLOY_VERSION__
             */
            public function setupField(FormField $field, \SimpleXMLElement $element): void
            {
            }
        };
    }
}

/**
 * The part of Joomla's site application the comment captcha reads, so a test can
 * double it; SiteApplication itself is final.
 *
 * @since  __DEPLOY_VERSION__
 */
interface SiteApplicationDouble extends CMSApplicationInterface
{
    /**
     * The component's options merged with the active menu item's.
     *
     * @param   ?string  $option  The component, or null for the current one.
     *
     * @return  Registry  The merged options
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getParams(?string $option = null): Registry;
}
