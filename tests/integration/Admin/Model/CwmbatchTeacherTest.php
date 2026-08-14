<?php

/**
 * Integration tests for the message teacher batch action.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmmessageModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1793.
 *
 * A message can credit several teachers, but the batch handed its single
 * selection to saveTeachers(), which rewrites the whole junction. Setting a
 * teacher on a message that already credited two silently dropped the other
 * one, with the batch reporting success.
 *
 * The batch now carries a mode, the way core's batchTag carries tag_addremove,
 * and defaults to adding so the destructive path has to be chosen.
 *
 * ⚠️ These tests cannot rely on transaction rollback. batchTeacher() calls
 * CwmmessageTable::store(), and Table::store() commits, so every row is removed
 * by hand in tearDown instead.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmmessageModel::class)]
class CwmbatchTeacherTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * Study rows created by a test, removed in tearDown.
     *
     * @var  int[]
     */
    private array $studies = [];

    /**
     * Teacher rows created by a test, removed in tearDown.
     *
     * @var  int[]
     */
    private array $teachers = [];

    /**
     * @var  mixed
     */
    private mixed $savedIdentity = null;

    /**
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

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

        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();
        $this->db            = Factory::getContainer()->get(DatabaseDriver::class);

        // batchTeacher() checks core.edit per row, so borrow a real Super User
        // rather than inventing an id with no groups.
        $superUser = (int) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('user_id') . ' FROM ' . $this->db->quoteName('#__user_usergroup_map')
            . ' WHERE ' . $this->db->quoteName('group_id') . ' = 8',
            0,
            1
        )->loadResult();

        if ($superUser === 0) {
            $this->markTestSkipped('No Super User available to authorise the batch');
        }

        $app->loadIdentity(User::getInstance($superUser));
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            foreach ($this->studies as $id) {
                $this->db->setQuery(
                    'DELETE FROM ' . $this->db->quoteName('#__bsms_study_teachers')
                    . ' WHERE ' . $this->db->quoteName('study_id') . ' = ' . $id
                )->execute();
                $this->db->setQuery(
                    'DELETE FROM ' . $this->db->quoteName('#__bsms_studies')
                    . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
                )->execute();
            }

            foreach ($this->teachers as $id) {
                $this->db->setQuery(
                    'DELETE FROM ' . $this->db->quoteName('#__bsms_teachers')
                    . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
                )->execute();
            }
        }

        CwmstudyteacherHelper::resetCache();

        if ($this->savedIdentity !== null) {
            Factory::getApplication()->loadIdentity($this->savedIdentity);
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    #[TestDox('adding leaves the teachers already credited in place')]
    public function testAddKeepsExistingTeachers(): void
    {
        $first  = $this->createTeacher('Batch Test One');
        $second = $this->createTeacher('Batch Test Two');
        $third  = $this->createTeacher('Batch Test Three');
        $study  = $this->createStudy([$first, $second]);

        $this->runBatch($study, $third, 'a');

        self::assertSame(
            [$first, $second, $third],
            $this->teacherIdsFor($study),
            'Adding a teacher must not disturb the ones already credited.'
        );
    }

    #[TestDox('adding a teacher the message already credits changes nothing')]
    public function testAddIsIdempotent(): void
    {
        $first  = $this->createTeacher('Batch Test One');
        $second = $this->createTeacher('Batch Test Two');
        $study  = $this->createStudy([$first, $second]);

        $this->runBatch($study, $second, 'a');

        self::assertSame([$first, $second], $this->teacherIdsFor($study), 'No duplicate row, no reordering.');
    }

    #[TestDox('replacing sets the teacher list to exactly the chosen teacher')]
    public function testReplaceOverwritesTheWholeList(): void
    {
        $first  = $this->createTeacher('Batch Test One');
        $second = $this->createTeacher('Batch Test Two');
        $third  = $this->createTeacher('Batch Test Three');
        $study  = $this->createStudy([$first, $second]);

        $this->runBatch($study, $third, 'r');

        self::assertSame([$third], $this->teacherIdsFor($study), 'Replace is still available, and still replaces.');
    }

    /**
     * The mode is a separate command, so a request that omits it must not get
     * the destructive branch.
     */
    #[TestDox('only an explicit r replaces; every other command shape adds')]
    public function testOnlyExplicitReplaceIsDestructive(): void
    {
        $resolve = new \ReflectionMethod(CwmmessageModel::class, 'resolveTeacherMode');

        self::assertSame('r', $resolve->invoke(null, ['teacher_mode' => 'r']));
        self::assertSame('a', $resolve->invoke(null, ['teacher_mode' => 'a']));
        self::assertSame('a', $resolve->invoke(null, []), 'An absent mode must not reach the destructive branch.');
        self::assertSame('a', $resolve->invoke(null, ['teacher_mode' => '']), 'Nor must an empty one.');
        self::assertSame('a', $resolve->invoke(null, ['teacher_mode' => 'R']), 'Nor a near-miss.');
    }

    /**
     * @param   int    $study    Study id to batch
     * @param   int    $teacher  Teacher id to apply
     * @param   string $mode     'a' to add, 'r' to replace
     *
     * @return  void
     */
    private function runBatch(int $study, int $teacher, string $mode): void
    {
        $model = $this->createModel();

        // batchTeacher() directly, not batch(): the latter imports the content
        // plugins and the console harness has no Finder to load. The mode is set
        // the way batch() sets it, and resolveTeacherMode() is asserted on its own.
        $property = new \ReflectionProperty(CwmmessageModel::class, 'batchTeacherMode');
        $property->setValue($model, $mode);

        $method = new \ReflectionMethod(CwmmessageModel::class, 'batchTeacher');
        $method->invoke($model, $teacher, [$study], [$study => 'com_proclaim.cwmmessage']);
    }

    /**
     * @param   int  $studyId  Study id
     *
     * @return  int[]  Teacher ids in stored order
     */
    private function teacherIdsFor(int $studyId): array
    {
        CwmstudyteacherHelper::resetCache($studyId);

        return array_map(
            static fn (int $id): int => $id,
            array_map(
                'intval',
                $this->db->setQuery(
                    'SELECT ' . $this->db->quoteName('teacher_id')
                    . ' FROM ' . $this->db->quoteName('#__bsms_study_teachers')
                    . ' WHERE ' . $this->db->quoteName('study_id') . ' = ' . $studyId
                    . ' ORDER BY ' . $this->db->quoteName('ordering')
                )->loadColumn() ?: []
            )
        );
    }

    /**
     * @param   string  $name  Teacher name
     *
     * @return  int  New teacher id
     */
    private function createTeacher(string $name): int
    {
        $this->db->setQuery(
            'INSERT INTO ' . $this->db->quoteName('#__bsms_teachers')
            . ' (' . $this->db->quoteName('teachername') . ', ' . $this->db->quoteName('alias') . ', '
            . $this->db->quoteName('published') . ', ' . $this->db->quoteName('language') . ', '
            . $this->db->quoteName('address') . ')'
            . ' VALUES (' . $this->db->quote($name) . ', '
            . $this->db->quote('batch-teacher-' . uniqid('', true)) . ', 1, '
            . $this->db->quote('*') . ', ' . $this->db->quote('') . ')'
        )->execute();

        $id = (int) $this->db->insertid();

        $this->teachers[] = $id;

        return $id;
    }

    /**
     * @param   int[]  $teacherIds  Teachers to credit, in order
     *
     * @return  int  New study id
     */
    private function createStudy(array $teacherIds): int
    {
        $this->db->setQuery(
            'INSERT INTO ' . $this->db->quoteName('#__bsms_studies')
            . ' (' . $this->db->quoteName('studytitle') . ', ' . $this->db->quoteName('alias') . ', '
            . $this->db->quoteName('published') . ', ' . $this->db->quoteName('access') . ', '
            . $this->db->quoteName('language') . ')'
            . ' VALUES (' . $this->db->quote('Batch Teacher Test') . ', '
            . $this->db->quote('batch-teacher-test-' . uniqid()) . ', 1, 1, ' . $this->db->quote('*') . ')'
        )->execute();

        $id = (int) $this->db->insertid();

        $this->studies[] = $id;

        foreach (array_values($teacherIds) as $ordering => $teacherId) {
            $this->db->setQuery(
                'INSERT INTO ' . $this->db->quoteName('#__bsms_study_teachers')
                . ' (' . $this->db->quoteName('study_id') . ', ' . $this->db->quoteName('teacher_id') . ', '
                . $this->db->quoteName('ordering') . ')'
                . ' VALUES (' . $id . ', ' . (int) $teacherId . ', ' . $ordering . ')'
            )->execute();
        }

        CwmstudyteacherHelper::resetCache($id);

        return $id;
    }

    /**
     * @return  CwmmessageModel
     */
    private function createModel(): CwmmessageModel
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwmmessageModel $model */
        $model = $factory->createModel('Cwmmessage', 'Administrator', ['ignore_request' => true]);

        $this->assertInstanceOf(CwmmessageModel::class, $model);

        return $model;
    }
}
