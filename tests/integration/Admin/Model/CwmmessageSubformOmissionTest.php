<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmmessageModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Scriptures and topics persist by replacing every row a study owns, so the
 * value the model receives has to distinguish two different things:
 *
 *   []    the form rendered the field and the user emptied it  → clear the rows
 *   null  the form never rendered the field at all             → leave them be
 *
 * Simple Mode hides both fields, so a save there submitted neither. Both
 * arrived as "empty", and every scripture reference and topic on the message
 * was deleted — silently, on a screen that never showed them.
 *
 * These tests pin both directions: omission must preserve, and an explicit
 * empty set must still clear, because collapsing them the other way would
 * break the user's ability to remove the last reference.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmessageSubformOmissionTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private int $studyId = 0;

    private int $topicId = 0;

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        $study = (object) [
            'published'  => 0,
            'studytitle' => 'simple-mode omission fixture',
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $this->studyId = (int) $this->db->insertid();

        $topic = (object) ['topic_text' => 'omission fixture topic', 'published' => 1, 'language' => '*'];
        $this->db->insertObject('#__bsms_topics', $topic, 'id');
        $this->topicId = (int) $this->db->insertid();
    }

    /**
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $this->db?->transactionRollback(true);

        parent::tearDown();
    }

    /**
     * Call one of the model's private subform writers with the study id in
     * state, the way save() reaches them.
     *
     * @param   string  $method  Method name
     * @param   array   $args    Positional arguments
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function invoke(string $method, array $args): void
    {
        // No mock: the model is only a carrier for the state the writers read,
        // and getMockBuilder() is soft-deprecated in PHPUnit 12.
        $model = (new \ReflectionClass(CwmmessageModel::class))->newInstanceWithoutConstructor();
        $model->setState('cwmmessage.id', $this->studyId);

        $ref = new \ReflectionMethod(CwmmessageModel::class, $method);
        $ref->invokeArgs($model, $args);
    }

    /**
     * @return  int  Reference rows owned by the fixture study.
     * @since   __DEPLOY_VERSION__
     */
    private function countReferences(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
        )->loadResult();
    }

    /**
     * @return  int  Topic rows owned by the fixture study.
     * @since   __DEPLOY_VERSION__
     */
    private function countTopics(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_studytopics'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
        )->loadResult();
    }

    /**
     * @return  object  A reference shaped like ScriptureReference.
     * @since   __DEPLOY_VERSION__
     */
    private function ref(int $book): object
    {
        return (object) [
            'booknumber'    => $book,
            'chapterBegin'  => 1,
            'verseBegin'    => 1,
            'chapterEnd'    => 1,
            'verseEnd'      => 2,
            'bibleVersion'  => 'kjv',
            'referenceText' => 'Book ' . $book . ' 1:1-2',
        ];
    }

    #[TestDox('Omitting the scriptures subform leaves existing references alone')]
    public function testOmittedScripturesArePreserved(): void
    {
        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(1), $this->ref(2)]);
        $this->assertSame(2, $this->countReferences(), 'fixture should start with two references');

        $this->invoke('saveScriptures', [null]);

        $this->assertSame(
            2,
            $this->countReferences(),
            'A form that never rendered the scriptures field must not delete the references it could not show.'
        );
    }

    #[TestDox('An explicitly emptied scriptures subform still clears the references')]
    public function testEmptyScripturesStillClear(): void
    {
        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(1)]);
        $this->assertSame(1, $this->countReferences());

        // Straight at the helper: the model guard only decides whether to call
        // it, and the point here is that an explicit empty set still clears —
        // removing the last reference has to stay possible.
        CwmscriptureHelper::saveScriptures($this->studyId, []);

        $this->assertSame(
            0,
            $this->countReferences(),
            'Omission and emptying are different inputs; emptying must still clear.'
        );
    }

    #[TestDox('Omitting the topics field leaves existing topics alone')]
    public function testOmittedTopicsArePreserved(): void
    {
        $row = (object) ['study_id' => $this->studyId, 'topic_id' => $this->topicId];
        $this->db->insertObject('#__bsms_studytopics', $row, 'id');
        $this->assertSame(1, $this->countTopics(), 'fixture should start with one topic');

        $this->invoke('saveTopics', [null, '', '', '*']);

        $this->assertSame(
            1,
            $this->countTopics(),
            'A form that never rendered the topics field must not unassign the topics it could not show.'
        );
    }

    #[TestDox('Omitting the teachers subform leaves existing assignments alone')]
    public function testOmittedTeachersArePreserved(): void
    {
        $row = (object) ['study_id' => $this->studyId, 'teacher_id' => 1, 'ordering' => 0];
        $this->db->insertObject('#__bsms_study_teachers', $row, 'id');
        $this->assertSame(1, $this->countTeachers(), 'fixture should start with one teacher');

        $this->invoke('saveTeachers', [null]);

        $this->assertSame(
            1,
            $this->countTeachers(),
            'Omitting the teachers subform must not unassign the teachers it could not show.'
        );
    }

    /**
     * @return  int  Teacher rows owned by the fixture study.
     * @since   __DEPLOY_VERSION__
     */
    private function countTeachers(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_study_teachers'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
        )->loadResult();
    }
}
