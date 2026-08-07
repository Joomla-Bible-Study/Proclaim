<?php

/**
 * Integration tests for teacher deletion.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmteacherModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1622.
 *
 * canDelete() queried for studies crediting the teacher, enqueued a "cannot
 * delete" warning, and then returned a plain permission check -- so the teacher
 * was deleted anyway, right after the administrator was told it could not be.
 * It also skipped the trash-first step every core list enforces.
 *
 * Deletion now follows the core shape: canDelete() is trashed-plus-asset-ACL as
 * in com_content's ArticleModel, and the referential refusal lives in an
 * overridden delete() that prunes the in-use teachers and reports why, as
 * com_users' LevelModel does for an access level in use.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmteacherModel::class)]
class CwmteacherDeleteTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * Rows this test created, as table => ids.
     *
     * @var  array<string, int[]>
     */
    private array $created = [];

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        // The harness Joomla root carries no com_proclaim language files, so
        // Text::sprintf() would hand back bare keys and the message assertions
        // could not see the names they are checking for. Load the repo's own.
        Factory::getApplication()->getLanguage()->load(
            'com_proclaim',
            \dirname(__DIR__, 4) . '/admin'
        );

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }

            try {
                $this->purgeCreatedRows();
            } catch (\Throwable) {
                // Best effort; the assertions have already run.
            }
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    #[TestDox('A teacher a study still credits is pruned from the batch, and the rest of the batch still deletes')]
    public function testInUseTeacherIsPrunedAndOthersProceed(): void
    {
        $inUse = $this->insertTeacher('In-use teacher', -2);
        $free  = $this->insertTeacher('Unreferenced teacher', -2);
        $study = $this->insertStudy();
        $this->linkTeacher($study, $inUse);

        $model = $this->createModel();
        $pks   = [$inUse, $free];

        $this->runDelete($model, $pks);

        // $pks is by-reference and is what the pruning acts on: the in-use
        // teacher is dropped from the batch and the other is left for
        // parent::delete() to handle.
        $this->assertNotContains($inUse, $pks, 'An in-use teacher must be pruned from the batch');
        $this->assertContains($free, $pks, 'An unreferenced teacher must be left in the batch');

        $this->assertSame(
            1,
            $this->teacherExists($inUse),
            'A teacher credited on a study must survive the delete'
        );
        $this->assertSame(
            1,
            $this->junctionRows($inUse),
            'The surviving teacher keeps its junction row'
        );
    }

    #[TestDox('A batch of only in-use teachers deletes nothing and still reports success')]
    public function testBatchOfOnlyInUseTeachersDeletesNothing(): void
    {
        $inUse = $this->insertTeacher('Only in-use teacher', -2);
        $study = $this->insertStudy();
        $this->linkTeacher($study, $inUse);

        $model = $this->createModel();
        $pks   = [$inUse];

        // Every item pruned means parent::delete() is never reached, so this
        // exercises the whole path without the plugin dispatch below it.
        $this->assertTrue($model->delete($pks), 'Nothing left to delete is not a failure');
        $this->assertSame([], $pks, 'The batch should be empty after pruning');
        $this->assertSame(1, $this->teacherExists($inUse));
    }

    #[TestDox('Deleting an in-use teacher reports which studies credit them')]
    public function testInUseRefusalNamesTheStudies(): void
    {
        $teacher = $this->insertTeacher('Reported teacher', -2);
        $study   = $this->insertStudy('Reported study');
        $this->linkTeacher($study, $teacher);

        $app = Factory::getApplication();
        $app->getMessageQueue(true);

        $model = $this->createModel();
        $pks   = [$teacher];
        $model->delete($pks);

        // ConsoleApplication groups the queue by message type; the web
        // application returns a flat list of ['message' => ..., 'type' => ...].
        // Flatten whichever shape this harness produced.
        $queue    = $app->getMessageQueue(true);
        $messages = [];

        array_walk_recursive(
            $queue,
            static function ($value, $key) use (&$messages) {
                if (\is_string($value) && $key !== 'type') {
                    $messages[] = $value;
                }
            }
        );

        $joined = implode(' | ', $messages);

        $this->assertNotEmpty($messages, 'Refusing to delete must tell the administrator why');
        $this->assertStringContainsString('Reported teacher', $joined, 'The teacher should be named');
        $this->assertStringContainsString('Reported study', $joined, 'The blocking study should be named');
    }

    #[TestDox('canDelete() refuses a teacher that has not been trashed first')]
    public function testCanDeleteRequiresTrashed(): void
    {
        $model  = $this->createModel();
        $method = new \ReflectionMethod(CwmteacherModel::class, 'canDelete');

        $published = (object) ['id' => 12345, 'published' => 1];
        $trashed   = (object) ['id' => 12345, 'published' => -2];

        $this->assertFalse(
            $method->invoke($model, $published),
            'A published teacher must be trashed before it can be deleted'
        );

        // The trashed record gets as far as the ACL check, which is all this
        // asserts -- the permission answer itself depends on the test identity.
        $this->assertIsBool($method->invoke($model, $trashed));
    }

    #[TestDox('canDelete() refuses a record with no id')]
    public function testCanDeleteRefusesMissingId(): void
    {
        $model  = $this->createModel();
        $method = new \ReflectionMethod(CwmteacherModel::class, 'canDelete');

        $this->assertFalse($method->invoke($model, (object) ['id' => 0, 'published' => -2]));
    }

    /**
     * Run the model's delete(), tolerating the one failure this harness causes.
     *
     * parent::delete() imports the content plugin group, and a test Joomla root
     * has plugin rows whose classes it does not ship. The pruning under test
     * happens before that point, so the by-reference $pks the caller inspects is
     * already correct when it blows up.
     *
     * Catches \Throwable, not \Exception: locally this surfaces as a
     * \ReflectionException, but on CI as a plain \Error ("Class ... not found"),
     * which \Exception does not cover -- the same gap #1583 was about. The
     * message is matched on the plugin namespace so a genuine failure in the
     * code under test still fails the test.
     *
     * @param   CwmteacherModel  $model  Model under test.
     * @param   int[]            $pks    Batch, modified in place.
     *
     * @return  void
     */
    private function runDelete(CwmteacherModel $model, array &$pks): void
    {
        try {
            $model->delete($pks);
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), 'Joomla\\Plugin\\')) {
                throw $e;
            }
        }
    }

    /**
     * @return  CwmteacherModel
     */
    private function createModel(): CwmteacherModel
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwmteacherModel $model */
        $model = $factory->createModel('Cwmteacher', 'Administrator', ['ignore_request' => true]);

        $this->assertInstanceOf(CwmteacherModel::class, $model);

        return $model;
    }

    /**
     * Delete everything this test seeded.
     *
     * The surrounding transaction is not enough: Table::store()/delete() touch
     * #__assets, whose nested-set bookkeeping runs LOCK TABLES -- an implicit
     * COMMIT in MySQL -- so earlier writes are already durable by then.
     *
     * @return  void
     */
    private function purgeCreatedRows(): void
    {
        foreach ($this->created['#__bsms_teachers'] ?? [] as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.teacher.' . $id)
            )->execute();
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_study_teachers')
                . ' WHERE ' . $this->db->quoteName('teacher_id') . ' = ' . $id
            )->execute();
        }

        foreach ($this->created['#__bsms_studies'] ?? [] as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.message.' . $id)
            )->execute();
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_study_teachers')
                . ' WHERE ' . $this->db->quoteName('study_id') . ' = ' . $id
            )->execute();
        }

        foreach ($this->created as $table => $ids) {
            if ($ids === []) {
                continue;
            }

            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName($table)
                . ' WHERE ' . $this->db->quoteName('id')
                . ' IN (' . implode(',', array_map('intval', $ids)) . ')'
            )->execute();
        }

        $this->created = [];
    }

    /**
     * @param   int  $teacherId  Teacher ID.
     *
     * @return  int  1 when the row is still present, 0 otherwise.
     */
    private function teacherExists(int $teacherId): int
    {
        return (int) $this->db->setQuery(
            'SELECT COUNT(*) FROM ' . $this->db->quoteName('#__bsms_teachers')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $teacherId
        )->loadResult();
    }

    /**
     * @param   int  $teacherId  Teacher ID.
     *
     * @return  int  Junction row count for the teacher.
     */
    private function junctionRows(int $teacherId): int
    {
        return (int) $this->db->setQuery(
            'SELECT COUNT(*) FROM ' . $this->db->quoteName('#__bsms_study_teachers')
            . ' WHERE ' . $this->db->quoteName('teacher_id') . ' = ' . $teacherId
        )->loadResult();
    }

    /**
     * @param   string  $name       Teacher name.
     * @param   int     $published  Published state (-2 = trashed).
     *
     * @return  int  Teacher ID.
     */
    private function insertTeacher(string $name, int $published): int
    {
        $row = (object) [
            'teachername' => $name,
            'alias'       => 'tch-' . uniqid('', true),
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $row);

        $id                                  = (int) $this->db->insertid();
        $this->created['#__bsms_teachers'][] = $id;

        return $id;
    }

    /**
     * @param   string  $title  Study title.
     *
     * @return  int  Study ID.
     */
    private function insertStudy(string $title = 'Delete-test study'): int
    {
        $row = (object) [
            'studytitle'  => $title,
            'alias'       => 'std-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'messagetype' => '1',
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        $id                                 = (int) $this->db->insertid();
        $this->created['#__bsms_studies'][] = $id;

        return $id;
    }

    /**
     * @param   int  $studyId    Study ID.
     * @param   int  $teacherId  Teacher ID.
     *
     * @return  void
     */
    private function linkTeacher(int $studyId, int $teacherId): void
    {
        // insertObject() takes the object by reference, so it cannot be an
        // inline cast.
        $row = (object) [
            'study_id'   => $studyId,
            'teacher_id' => $teacherId,
            'ordering'   => 0,
        ];

        $this->db->insertObject('#__bsms_study_teachers', $row);
    }
}
