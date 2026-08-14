<?php

/**
 * Integration tests for public comment submission.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Model;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Site\Model\CwmsermonModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The comment form is the one place a stranger writes to the database, and
 * storecomment() took the moderation state straight from the request:
 *
 *     'published' => $input->getInt('published', 1)
 *
 * The template posted it as a hidden field, but a visitor decides what a visitor
 * posts. Anyone could approve their own comment on a site configured to hold
 * them — and the default was 1, so omitting the field published too. Verified
 * against a live site before the fix: a POST carrying published=1 stored a
 * visible comment while the template said comment_publish=0.
 *
 * The state now comes from the template and defaults to 0, matching the field's
 * own default in template.xml, so an unreadable setting holds rather than
 * publishes.
 *
 * ⚠️ These tests delete their rows by hand rather than rolling back:
 * Table::store() commits.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmsermonModel::class)]
class CwmsermonCommentSubmissionTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * Comment rows created by a test.
     *
     * @var  int[]
     */
    private array $comments = [];

    /**
     * @var  object|null
     */
    private ?object $savedTemplate = null;

    /**
     * @var  bool
     */
    private bool $hadTemplate = false;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);

        // Cwmparams caches the template in a public static, so a test can seed it
        // and must put it back.
        $this->hadTemplate = isset(Cwmparams::$templateTable);

        if ($this->hadTemplate) {
            $this->savedTemplate = Cwmparams::$templateTable;
        }
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null && $this->comments !== []) {
            foreach ($this->comments as $id) {
                $this->db->setQuery(
                    'DELETE FROM ' . $this->db->quoteName('#__bsms_comments')
                    . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
                )->execute();
            }
        }

        if ($this->hadTemplate && $this->savedTemplate !== null) {
            Cwmparams::$templateTable = $this->savedTemplate;
        }

        parent::tearDown();
    }

    /**
     * The exploit, as a test: the request asks to be published, the template
     * says hold, and the template wins.
     */
    #[TestDox('a comment claiming published=1 is still held when the template holds comments')]
    public function testRequestCannotSelfApprove(): void
    {
        $this->seedTemplate(0);
        $this->postComment(['published' => 1]);

        self::assertSame(0, $this->storedState(), 'A visitor must not be able to approve their own comment.');
    }

    #[TestDox('omitting published does not publish')]
    public function testOmittingPublishedDoesNotPublish(): void
    {
        $this->seedTemplate(0);
        $this->postComment([]);

        self::assertSame(0, $this->storedState(), 'The old default of 1 published whenever the field was absent.');
    }

    #[TestDox('a template that auto-approves still auto-approves')]
    public function testAutoApproveIsPreserved(): void
    {
        $this->seedTemplate(1);
        $this->postComment(['published' => 0]);

        self::assertSame(1, $this->storedState(), 'comment_publish=1 means publish, whatever the request says.');
    }

    /**
     * Fail closed: an unreadable or unset setting must hold, not publish.
     */
    #[TestDox('an unset comment_publish holds the comment')]
    public function testUnsetSettingHolds(): void
    {
        $this->seedTemplate(null);
        $this->postComment(['published' => 1]);

        self::assertSame(0, $this->storedState(), 'An absent setting must hold rather than publish.');
    }

    /**
     * The date is recorded by the server, not asked for.
     */
    #[TestDox('comment_date is set server-side, not taken from the request')]
    public function testCommentDateIsNotTakenFromTheRequest(): void
    {
        $this->seedTemplate(0);
        $this->postComment(['comment_date' => '1999-01-01 00:00:00']);

        $stored = (string) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('comment_date')
            . ' FROM ' . $this->db->quoteName('#__bsms_comments')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . end($this->comments)
        )->loadResult();

        self::assertStringStartsNotWith('1999', $stored, 'A forged date must not be stored.');
    }

    /**
     * @param   int|null  $publish  comment_publish value, or null to leave it unset
     *
     * @return  void
     */
    private function seedTemplate(?int $publish): void
    {
        $params = new Registry();

        if ($publish !== null) {
            $params->set('comment_publish', (string) $publish);
        }

        $template         = new \stdClass();
        $template->id     = 1;
        $template->params = $params;

        Cwmparams::$templateTable = $template;
        Cwmparams::$templateId    = 1;

        // getTemplateparams() resolves the id from input when it has no pk.
        Factory::getApplication()->getInput()->set('t', 1);
    }

    /**
     * @param   array  $extra  Request values a visitor could control
     *
     * @return  void
     */
    private function postComment(array $extra): void
    {
        $input = Factory::getApplication()->getInput();

        $input->set('study_id', 1);
        $input->set('full_name', 'Integration Probe');
        $input->set('user_email', 'probe@example.test');
        $input->set('comment_text', 'submitted by a test');
        $input->set('published', null);
        $input->set('comment_date', null);

        foreach ($extra as $key => $value) {
            $input->set($key, $value);
        }

        self::assertTrue($this->createModel()->storecomment(), 'The comment should store.');

        $this->comments[] = (int) $this->db->setQuery(
            'SELECT MAX(' . $this->db->quoteName('id') . ') FROM ' . $this->db->quoteName('#__bsms_comments')
        )->loadResult();
    }

    /**
     * @return  int  The published state of the comment this test just stored
     */
    private function storedState(): int
    {
        return (int) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('published')
            . ' FROM ' . $this->db->quoteName('#__bsms_comments')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . end($this->comments)
        )->loadResult();
    }

    /**
     * @return  CwmsermonModel
     */
    private function createModel(): CwmsermonModel
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwmsermonModel $model */
        $model = $factory->createModel('Cwmsermon', 'Site', ['ignore_request' => true]);

        $this->assertInstanceOf(CwmsermonModel::class, $model);

        return $model;
    }
}
