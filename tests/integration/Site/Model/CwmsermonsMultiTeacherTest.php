<?php

/**
 * A study with two teachers must appear once, not twice.
 *
 * The listings LEFT JOIN #__bsms_study_teachers to name a study's teacher. That
 * join used to read `COALESCE(stj.teacher_id, study.teacher_id)`, and dropping
 * the COALESCE (#1731) is only safe while the join is constrained to a single
 * junction row. Without that constraint a second teacher multiplies the study
 * through every list on the site -- silently, and only for the studies that have
 * one, which is why no existing fixture would have shown it.
 *
 * No development site has a two-teacher study, so this builds one.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Model;

use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Site\Model\CwmsermonsModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Form\FormFactoryInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CwmsermonsModel::class)]
class CwmsermonsMultiTeacherTest extends IntegrationTestCase
{
    /**
     * @var DatabaseDriver|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var int
     * @since __DEPLOY_VERSION__
     */
    private int $studyId = 0;

    /**
     * @var int[]
     * @since __DEPLOY_VERSION__
     */
    private array $teacherIds = [];

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        CwmstudyteacherHelper::resetCache();
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        CwmstudyteacherHelper::resetCache();

        try {
            $this->db?->transactionRollback(true);
        } catch (\Throwable) {
            // Connection lost; nothing to roll back.
        }

        parent::tearDown();
    }

    /**
     * A published, publicly visible study taught by two people.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    private function studyWithTwoTeachers(): void
    {
        foreach (['cwm1731 primary', 'cwm1731 second'] as $ordering => $name) {
            $alias = 'cwm1731-teacher-' . $ordering . '-' . uniqid('', false);
            // #__bsms_teachers declares several NOT NULL columns with no
            // default, so a minimal fixture will not insert.
            $teacher = (object) [
                'teachername' => $name,
                'alias'       => $alias,
                'published'   => 1,
                'access'      => 1,
                'language'    => '*',
                'ordering'    => 0,
                'address'     => '',
                'phone'       => '',
                'website'     => '',
                'email'       => '',
                'title'       => '',
                'short'       => '',
                'information' => '',
                'params'      => '{}',
            ];
            $this->db->insertObject('#__bsms_teachers', $teacher, 'id');
            $this->teacherIds[] = (int) $this->db->insertid();
        }

        $study = (object) [
            'studytitle'  => 'cwm1731 two-teacher fixture',
            'alias'       => 'cwm1731-two-teacher-' . uniqid('', false),
            'studydate'   => '2026-01-15 10:00:00',
            'series_id'   => 0,
            'messagetype' => 1,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => '{}',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $this->studyId = (int) $this->db->insertid();

        foreach ($this->teacherIds as $ordering => $teacherId) {
            $link = (object) [
                'study_id'   => $this->studyId,
                'teacher_id' => $teacherId,
                'ordering'   => $ordering,
            ];
            $this->db->insertObject('#__bsms_study_teachers', $link);
        }
    }

    /**
     * Build a real site sermons model through a component MVCFactory.
     *
     * @return  CwmsermonsModel
     * @since __DEPLOY_VERSION__
     */
    private function createModel(): CwmsermonsModel
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));

        // The harness container does not always carry a form factory, and the
        // list query does not need one.
        if ($container->has(FormFactoryInterface::class)) {
            $factory->setFormFactory($container->get(FormFactoryInterface::class));
        }

        $model = $factory->createModel('Cwmsermons', 'Site', ['ignore_request' => true]);

        // The list query reads template params off the state, which nothing
        // populates outside a request. setModuleState() is the seam mod_proclaim
        // uses and the one CwmsermonsModuleStateTest drives.
        $this->silenced(static fn () => $model->setModuleState(new Registry(['moduleitems' => 100])));

        return $model;
    }

    /**
     * How many rows the real listing query returns for the fixture study.
     *
     * @return  int
     * @since __DEPLOY_VERSION__
     */
    private function listingRowsForFixture(): int
    {
        $model = $this->createModel();
        $ref   = new \ReflectionMethod($model, 'getListQuery');
        $query = $this->silenced(static fn () => $ref->invoke($model));

        $this->assertInstanceOf(QueryInterface::class, $query);

        $query->where($this->db->quoteName('study.id') . ' = ' . $this->studyId);

        return \count($this->silenced(fn () => $this->db->setQuery($query)->loadObjectList()) ?: []);
    }

    #[TestDox('a two-teacher study appears once in the listing, not once per teacher')]
    public function testTwoTeachersDoNotDuplicateTheStudy(): void
    {
        $this->studyWithTwoTeachers();

        $this->assertSame(
            1,
            $this->listingRowsForFixture(),
            'A two-teacher study came back twice. Two things stop that here -- GROUP BY study.id and the '
            . 'join being pinned to ordering 0 -- and this holds whichever of them changes.'
        );
    }

    #[TestDox('the teacher join is pinned to a single junction row')]
    public function testTheTeacherJoinIsPinnedToOneRow(): void
    {
        // ⚠️ Asserted on the SQL, deliberately. The row count above cannot see
        // this: GROUP BY study.id collapses a duplicate on its own, so the
        // listing looks right even with the join unpinned -- and every query
        // that joins teachers without a GROUP BY then shows the study twice.
        $model = $this->createModel();
        $ref   = new \ReflectionMethod($model, 'getListQuery');
        $sql   = (string) $this->silenced(static fn () => $ref->invoke($model));

        $this->assertMatchesRegularExpression(
            '/bsms_study_teachers.{0,200}?ordering.{0,10}=\s*0/s',
            $sql,
            'The teacher join is not constrained to one junction row. Dropping the COALESCE (#1731) is only '
            . 'safe while it is.'
        );
    }

    #[TestDox('both teachers are readable from the junction, in order')]
    public function testBothTeachersAreReadable(): void
    {
        $this->studyWithTwoTeachers();

        $teachers = CwmstudyteacherHelper::getTeachersForStudy($this->studyId);

        $this->assertCount(2, $teachers, 'The junction holds both; a reader that finds one is reading the column.');
        $this->assertSame(
            $this->teacherIds,
            array_map(static fn ($t) => (int) ($t->teacherId ?? $t->teacher_id ?? 0), $teachers),
            'Order matters: ordering 0 is the primary, and that is the one single-teacher displays show.'
        );
    }

    #[TestDox('the listing names the primary teacher, not the second')]
    public function testTheListingNamesThePrimary(): void
    {
        $this->studyWithTwoTeachers();

        $model = $this->createModel();
        $ref   = new \ReflectionMethod($model, 'getListQuery');
        $query = $this->silenced(static fn () => $ref->invoke($model));
        $query->where($this->db->quoteName('study.id') . ' = ' . $this->studyId);

        $row = $this->silenced(fn () => $this->db->setQuery($query)->loadObject());

        $this->assertNotNull($row, 'The fixture study should be in the listing.');
        $this->assertSame(
            'cwm1731 primary',
            $row->teachername ?? null,
            'The join is pinned to ordering 0, so a second teacher must not become the one on show.'
        );
    }

    /**
     * Run something with Joomla's language warnings suppressed.
     *
     * ⚠️ The list query resolves language strings, and the harness has no active
     * language, so Language.php emits a warning PHPUnit reports as risky output.
     * Nothing to do with what is under test. Same helper as
     * CwmsermonsModuleStateTest.
     *
     * @param   callable  $fn  Work to run
     *
     * @return  mixed
     *
     * @since __DEPLOY_VERSION__
     */
    private function silenced(callable $fn): mixed
    {
        set_error_handler(
            static fn (int $errno, string $errstr, string $errfile): bool
                => str_contains($errfile, 'joomla/language/src/Language.php')
        );

        try {
            return $fn();
        } finally {
            restore_error_handler();
        }
    }
}
