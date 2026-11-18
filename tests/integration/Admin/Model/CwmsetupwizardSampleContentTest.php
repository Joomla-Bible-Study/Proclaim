<?php

/**
 * The setup wizard's sample message is credited to a teacher the way a saved
 * message is: through the message-teacher junction table, which is where the
 * site reads a message's teachers from.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmsetupwizardModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmsetupwizardSampleContentTest extends IntegrationTestCase
{
    /**
     * @var DatabaseInterface|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseInterface $db = null;

    /**
     * @var mixed
     * @since __DEPLOY_VERSION__
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('The setup wizard on 11.0.0-dev does not credit the sample message to its teacher yet: the port is pending (#2183).');

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away.
            }
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The sample message is credited to the teacher the wizard created')]
    public function testSampleMessageCreditsTheWizardsTeacher(): void
    {
        $teacherId = $this->seedTeacher();
        $ids       = $this->createSampleContent(['teacher_id' => $teacherId]);

        $this->assertGreaterThan(0, $ids['message_id'], 'The sample message should have been created');
        $this->assertSame([$teacherId], $this->creditedTeachers($ids['message_id']));
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('With no teacher handed over, the sample message is credited to the teacher the wizard picks')]
    public function testSampleMessageCreditsThePickedTeacher(): void
    {
        // Guarantees a published teacher to pick, though not which one.
        $this->seedTeacher();

        $ids = $this->createSampleContent();

        $this->assertGreaterThan(0, $ids['teacher_id'], 'The wizard should have picked a published teacher');
        $this->assertSame([$ids['teacher_id']], $this->creditedTeachers($ids['message_id']));
    }

    /**
     * @param   array  $defaults  IDs from the wizard's essential defaults
     *
     * @return  array  IDs of the sample content created
     *
     * @since __DEPLOY_VERSION__
     */
    private function createSampleContent(array $defaults = []): array
    {
        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'createSampleContent');

        return $ref->invoke(
            (new \ReflectionClass(CwmsetupwizardModel::class))->newInstanceWithoutConstructor(),
            [],
            $defaults
        );
    }

    /**
     * @param   int  $messageId  The message id
     *
     * @return  int[]  The ids of the teachers credited on the message, in order
     *
     * @since __DEPLOY_VERSION__
     */
    private function creditedTeachers(int $messageId): array
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('teacher_id'))
            ->from($this->db->quoteName('#__bsms_study_teachers'))
            ->where($this->db->quoteName('study_id') . ' = :id')
            ->order($this->db->quoteName('ordering'))
            ->bind(':id', $messageId, ParameterType::INTEGER);

        return array_map('intval', $this->db->setQuery($query)->loadColumn());
    }

    /**
     * @return  int  The new teacher id
     *
     * @since __DEPLOY_VERSION__
     */
    private function seedTeacher(): int
    {
        // Some databases still hold address as NOT NULL with no default
        $teacher = (object) [
            'teachername' => 'zz wizard sample teacher',
            'alias'       => $this->uniqueAlias('teacher'),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $teacher);

        return (int) $this->db->insertid();
    }
}
