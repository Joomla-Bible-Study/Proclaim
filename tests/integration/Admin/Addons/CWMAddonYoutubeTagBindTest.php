<?php

/**
 * importTagsAsTopics() inserts a new topic with the YouTube tag bound into the
 * VALUES list. The three params UPDATEs elsewhere in the addon are the same
 * bound-set-params shape already proven executing in the setup/location wizards
 * and CWMAddon; this pins the one new shape — a bound value in a builder INSERT.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since 10.7.1
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Addons;

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The YouTube addon's importTagsAsTopics() inserts a topic with the tag bound
 * into the VALUES list.
 *
 * @since  10.7.1
 */
class CWMAddonYoutubeTagBindTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.1
     */
    private ?DatabaseInterface $db = null;

    /**
     * Factory's language before the test replaced it to silence date warnings.
     *
     * @var    mixed
     * @since  10.7.1
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * Skip without a test database. Otherwise silence date language warnings, and open a transaction.
     *
     * @return  void
     *
     * @since   10.7.1
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        // The topic-suggestion path reaches Factory::getDate(); give the
        // language a tag so it does not print a warning that fails CI.
        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
    }

    /**
     * Roll back, and restore the language.
     *
     * @return  void
     *
     * @since   10.7.1
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
     * importTagsAsTopics() inserts a topic with the tag bound into the VALUES list.
     *
     * @return  void
     *
     * @since   10.7.1
     * @throws  \ReflectionException
     */
    #[TestDox("importTagsAsTopics() inserts a topic with the tag bound into the VALUES list")]
    public function testImportTagsAsTopicsBindsTheInsert(): void
    {
        // A tag unlikely to match an existing topic, so the code reaches the
        // bound INSERT rather than the de-dup skip.
        $tag = 'zz youtube tag ' . uniqid('', true);

        $model = (new \ReflectionClass(CWMAddonYoutube::class))->newInstanceWithoutConstructor();
        $ref   = new \ReflectionMethod(CWMAddonYoutube::class, 'importTagsAsTopics');
        $ref->invoke($model, [$tag]);

        $query = $this->db->createQuery()
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__bsms_topics'))
            ->where($this->db->quoteName('topic_text') . ' = :tag')
            ->bind(':tag', $tag, ParameterType::STRING);

        $this->assertSame(1, (int) $this->db->setQuery($query)->loadResult(), 'The tag should have been inserted as a topic');
    }
}
