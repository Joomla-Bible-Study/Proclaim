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
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Addons;

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CWMAddonYoutubeTagBindTest extends IntegrationTestCase
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
