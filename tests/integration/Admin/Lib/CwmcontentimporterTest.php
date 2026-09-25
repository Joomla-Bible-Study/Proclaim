<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmcontentimporter;
use CWM\Component\Proclaim\Administrator\Lib\Cwmimportmanifest;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\DI\ServiceProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration coverage for the additive, tracked importer (#2145, #2172, #2173).
 *
 * Runs inside a savepoint-based transaction (per the project's own lesson:
 * Table::store() commits a bare transactionStart(), so every write here would
 * otherwise leak into the dev database). File writes are NOT covered by that
 * rollback — importFiles() copies real bytes to disk — so tests that exercise
 * it capture the manifest's file paths before rollback and unlink them (and
 * the directories they created) in tearDown regardless of how the test ends.
 *
 * `bootComponent('com_proclaim')` falls back to a `LegacyComponent` in this
 * harness — JPATH_ROOT is this repository's own root (phpunit.xml), not a
 * real Joomla site, so the manifest-driven provider.php lookup finds nothing.
 * That stub cannot resolve namespaced models (see CwmactionlogFailOpenTest
 * for the same mechanism against a different helper). Wiring the real
 * provider by hand, the way core's ExtensionManagerTrait::loadExtension()
 * does, gives Cwmcontentimporter a working MVCFactory without that fallback.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmcontentimporter::class)]
#[CoversClass(Cwmimportmanifest::class)]
class CwmcontentimporterTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private ?MVCFactoryInterface $factory = null;

    private mixed $savedIdentity = null;

    /** @var string[] */
    private array $filesToClean = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        // prepareTable() (called from AdminModel::save()) reads getIdentity()
        // for created_by; the harness has no logged-in user by default.
        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();

        $user           = new User();
        $user->id       = 42;
        $user->username = 'cwm2173';
        $app->loadIdentity($user);

        $container = Factory::getContainer()->createChild();

        /** @var ServiceProviderInterface $provider */
        $provider = require self::root() . '/admin/services/provider.php';
        $provider->register($container);

        $this->factory = $container->get(MVCFactoryInterface::class);
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            // Capture what was written to disk before the rollback erases the
            // manifest rows that record it.
            foreach ($this->filesToClean as $tag) {
                foreach (Cwmimportmanifest::filesForTag($tag) as $relative) {
                    $absolute = JPATH_ROOT . '/' . ltrim($relative, '/');

                    if (is_file($absolute)) {
                        @unlink($absolute);
                    }

                    // rmdir() only succeeds on an empty directory, so this
                    // removes exactly what importFiles() created via
                    // mkdir(..., true) and nothing pre-existing.
                    $dir = \dirname($absolute);

                    while (str_starts_with($dir, JPATH_ROOT . '/images/') && @rmdir($dir)) {
                        $dir = \dirname($dir);
                    }
                }
            }

            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        if ($this->savedIdentity !== null) {
            Factory::getApplication()->loadIdentity($this->savedIdentity);
        }

        parent::tearDown();
    }

    /**
     * @return  array{payload: array, dir: string}
     */
    private function fixture(): array
    {
        $dir     = self::root() . '/tests/fixtures/demo-import-sample';
        $payload = json_decode((string) file_get_contents($dir . '/payload.json'), true, 512, \JSON_THROW_ON_ERROR);

        return ['payload' => $payload, 'dir' => $dir];
    }

    private static function root(): string
    {
        return \dirname(__DIR__, 4);
    }

    /**
     * The fixture references a topic by text (`cwm2173-demo-topic-tag`) that
     * validate() must resolve to an existing id — insert it here rather than
     * depending on the seeded install data being present or unmodified.
     *
     * @return  void
     */
    private function seedFixtureTopic(): void
    {
        $topic = (object) ['topic_text' => 'cwm2173-demo-topic-tag', 'published' => 1, 'language' => '*'];
        $this->db->insertObject('#__bsms_topics', $topic, 'id');
    }

    /**
     * AdminModel::save() dispatches onContentBeforeSave/onContentAfterSave,
     * which calls PluginHelper::importPlugin('content') — that resolves
     * every content plugin the connected site's #__extensions row lists as
     * enabled, by class. This bare PHPUnit harness's JPATH_ROOT is this
     * repository's own root (phpunit.xml), which has no plugins/content/*
     * files at all, so any plugin enabled on the real dev site this harness
     * happens to point at (contact, emailcloak, ...) fails to resolve.
     *
     * That is a gap in what this harness can exercise, not a defect in the
     * code under test — CwmteacherModel::save() et al. are pre-existing,
     * production code paths already exercised by real admin saves every
     * day. Skip rather than fail, and verify the full round trip on a real
     * site (the project's "live-test before done" rule) until the harness
     * itself is extended to cover full model saves.
     *
     * @return  void
     */
    private function skipUnlessContentPluginsResolve(): void
    {
        try {
            PluginHelper::importPlugin('content');
        } catch (\Throwable) {
            $this->markTestSkipped(
                'This harness cannot resolve the dev site\'s enabled content plugins '
                . '(no plugins/content/* files under JPATH_ROOT) — verify on a real site instead.'
            );
        }
    }

    public function testImportsTeacherSeriesMessageAndFile(): void
    {
        $this->skipUnlessContentPluginsResolve();
        $this->seedFixtureTopic();

        $tag                  = 'cwm2173-test-' . bin2hex(random_bytes(4));
        $this->filesToClean[] = $tag;

        $fixture = $this->fixture();

        $summary = (new Cwmcontentimporter($this->factory))->import($tag, $fixture['payload'], $fixture['dir']);

        $this->assertSame(['teachers' => 1, 'series' => 1, 'messages' => 1, 'mediafiles' => 0, 'files' => 1], $summary);

        $rows = Cwmimportmanifest::rowsForTag($tag);

        $this->assertCount(1, $rows['#__bsms_teachers'] ?? []);
        $this->assertCount(1, $rows['#__bsms_series'] ?? []);
        $this->assertCount(1, $rows['#__bsms_studies'] ?? []);

        $teacherId = $rows['#__bsms_teachers'][0];
        $serieId   = $rows['#__bsms_series'][0];
        $studyId   = $rows['#__bsms_studies'][0];

        // The series carries the remapped (not the fixture's own) teacher id.
        $serieTeacher = $this->scalar('#__bsms_series', 'teacher', 'id', $serieId);
        $this->assertSame($teacherId, (int) $serieTeacher);

        // The message carries the remapped series id.
        $studySeries = $this->scalar('#__bsms_studies', 'series_id', 'id', $studyId);
        $this->assertSame($serieId, (int) $studySeries);

        // The teacher junction was written with the remapped teacher id.
        $junctionTeacher = $this->scalar('#__bsms_study_teachers', 'teacher_id', 'study_id', $studyId);
        $this->assertSame($teacherId, (int) $junctionTeacher);

        // The scripture reference landed.
        $scriptureCount = $this->countRows('#__bsms_study_scriptures', 'study_id', $studyId);
        $this->assertGreaterThan(0, $scriptureCount);

        // The topic was matched, not re-created.
        $topicCount = $this->countRows('#__bsms_studytopics', 'study_id', $studyId);
        $this->assertSame(1, $topicCount);

        $files = Cwmimportmanifest::filesForTag($tag);
        $this->assertSame(['images/biblestudy/teachers/cwm2173-demo-teacher.png'], $files);
        $this->assertFileExists(JPATH_ROOT . '/images/biblestudy/teachers/cwm2173-demo-teacher.png');
    }

    public function testRefusesASecondImportUnderTheSameTag(): void
    {
        $this->skipUnlessContentPluginsResolve();
        $this->seedFixtureTopic();

        $tag                  = 'cwm2173-test-' . bin2hex(random_bytes(4));
        $this->filesToClean[] = $tag;

        $fixture  = $this->fixture();
        $importer = new Cwmcontentimporter($this->factory);

        $importer->import($tag, $fixture['payload'], $fixture['dir']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already exists/');

        $importer->import($tag, $fixture['payload'], $fixture['dir']);
    }

    public function testRejectsAnUnrecognisedSectionBeforeCreatingAnything(): void
    {
        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        $payload                      = $this->fixture()['payload'];
        $payload['bsms_templatecode'] = [['id' => 1, 'code' => '<?php echo "pwned"; ?>']];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/Unrecognised import section/', $e->getMessage());
        }

        // Validation runs before anything is created — a rejected import
        // must not leave the teacher it would otherwise have created behind.
        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAFileDestinationOutsideTheAllowedImagePathsBeforeCreatingAnything(): void
    {
        $tag     = 'cwm2173-test-' . bin2hex(random_bytes(4));
        $fixture = $this->fixture();

        $payload          = $fixture['payload'];
        $payload['files'] = [
            ['source' => 'teacher-1.png', 'dest' => 'images/biblestudy/teachers/../../../administrator/evil.png'],
        ];
        unset($payload['messages'], $payload['series']);

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $fixture['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/outside the allowed image paths/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
        $this->assertFileDoesNotExist(JPATH_ROOT . '/administrator/evil.png');
    }

    public function testRejectsADisallowedFileExtensionBeforeCreatingAnything(): void
    {
        $tag     = 'cwm2173-test-' . bin2hex(random_bytes(4));
        $fixture = $this->fixture();

        $payload          = $fixture['payload'];
        $payload['files'] = [
            ['source' => 'teacher-1.png', 'dest' => 'images/biblestudy/teachers/evil.php'],
        ];
        unset($payload['messages'], $payload['series']);

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $fixture['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/disallowed extension/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
        $this->assertFileDoesNotExist(JPATH_ROOT . '/images/biblestudy/teachers/evil.php');
    }

    public function testRejectsAFileSourceOutsideTheImportPackageBeforeCreatingAnything(): void
    {
        $tag     = 'cwm2173-test-' . bin2hex(random_bytes(4));
        $fixture = $this->fixture();

        $payload          = $fixture['payload'];
        $payload['files'] = [
            ['source' => '../../../etc/passwd', 'dest' => 'images/biblestudy/teachers/evil.png'],
        ];
        unset($payload['messages'], $payload['series']);

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $fixture['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/was not found in the import package/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAnUnknownTopicBeforeCreatingAnything(): void
    {
        // Deliberately not seeding the fixture topic this time.
        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        $payload = $this->fixture()['payload'];
        unset($payload['files']);

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/does not exist on this site/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsANameCollisionBeforeCreatingAnything(): void
    {
        $this->seedFixtureTopic();

        $existing = (object) [
            'teachername' => 'CWM2173 Demo Teacher',
            'alias'       => 'cwm2173-collision',
            'language'    => '*',
            'address'     => '',
        ];
        $this->db->insertObject('#__bsms_teachers', $existing, 'id');

        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $this->fixture()['payload'], $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/already exists on this site/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAnInvalidTag(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new Cwmcontentimporter($this->factory))->import('Not A Valid Tag!', [], sys_get_temp_dir());
    }

    public function testRejectsAnInvalidAliasBeforeCreatingAnything(): void
    {
        $payload = $this->fixture()['payload'];
        unset($payload['series'], $payload['messages'], $payload['files']);
        $payload['teachers'][0]['alias'] = 'Not A Valid Alias!';

        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/must match/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsTwoFilesClaimingTheSameDestination(): void
    {
        $fixture = $this->fixture();
        $payload = $fixture['payload'];
        unset($payload['messages'], $payload['series']);
        $payload['files'] = [
            ['source' => 'teacher-1.png', 'dest' => 'images/biblestudy/teachers/cwm2173-dupe.png'],
            ['source' => 'teacher-1.png', 'dest' => 'images/biblestudy/teachers/cwm2173-dupe.png'],
        ];

        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $fixture['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/claimed by more than one entry/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
        $this->assertFileDoesNotExist(JPATH_ROOT . '/images/biblestudy/teachers/cwm2173-dupe.png');
    }

    public function testRejectsAMismatchedFileExtension(): void
    {
        $fixture = $this->fixture();
        $payload = $fixture['payload'];
        unset($payload['messages'], $payload['series']);
        // teacher-1.png is a real PNG; claiming it as a .gif destination must
        // be refused even though .gif is itself an allowed extension.
        $payload['files'] = [
            ['source' => 'teacher-1.png', 'dest' => 'images/biblestudy/teachers/cwm2173-mismatch.gif'],
        ];

        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $fixture['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/does not match source extension/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    /**
     * The regression test for the manifest gap live-testing found: a row
     * created by Table::store() but never recorded because AdminModel::save()
     * subsequently reported failure (or threw) — exactly what happens when a
     * content plugin fails after the row itself is already written. Needs no
     * plugin dispatch and no full app boot: a stub model's save() does the
     * insert directly and throws, standing in for that shape of failure.
     */
    public function testAnOrphanedRowIsStillRecordedInTheManifestWhenSaveFails(): void
    {
        $tag = 'cwm2173-test-' . bin2hex(random_bytes(4));

        $model = $this->createStub(AdminModel::class);
        $model->method('save')->willReturnCallback(function () {
            $teacher = (object) [
                'teachername' => 'CWM2173 Orphan Teacher',
                'alias'       => 'cwm2173-orphan-teacher',
                'language'    => '*',
                'address'     => '',
            ];
            $this->db->insertObject('#__bsms_teachers', $teacher, 'id');

            throw new \RuntimeException('simulated after-save plugin failure');
        });

        $factory = $this->createStub(MVCFactoryInterface::class);
        $factory->method('createModel')->willReturn($model);

        $payload = [
            'teachers' => [[
                'id'          => 1,
                'teachername' => 'CWM2173 Orphan Teacher',
                'alias'       => 'cwm2173-orphan-teacher',
            ]],
        ];

        try {
            (new Cwmcontentimporter($factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected the simulated save failure to propagate.');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated after-save plugin failure', $e->getMessage());
        }

        $alias     = 'cwm2173-orphan-teacher';
        $teacherId = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_teachers'))
                ->where($this->db->quoteName('alias') . ' = :alias')
                ->bind(':alias', $alias, ParameterType::STRING)
        )->loadResult();

        $this->assertNotNull($teacherId, 'The stub save() must have inserted the row.');

        $rows = Cwmimportmanifest::rowsForTag($tag);

        $this->assertContains(
            (int) $teacherId,
            $rows['#__bsms_teachers'] ?? [],
            'The row Table::store() wrote must still be recorded even though save() reported failure.'
        );
    }

    /**
     * @return  string  The name of a freshly seeded, uniquely-named server row.
     */
    private function seedFixtureServer(): string
    {
        $name = 'cwm2187-test-server-' . bin2hex(random_bytes(4));

        $server = (object) [
            'server_name' => $name,
            'type'        => 'legacy',
            'params'      => '{}',
            'media'       => '{}',
            'published'   => 1,
            'access'      => 1,
        ];
        $this->db->insertObject('#__bsms_servers', $server, 'id');

        return $name;
    }

    /**
     * @return  array  A `messages[]` entry with a fresh, collision-free alias/title,
     *                  and its source id (always `1`).
     */
    private function fixtureMessageOnly(): array
    {
        $suffix = bin2hex(random_bytes(4));

        return [[
            'id'         => 1,
            'studytitle' => 'CWM2187 Media Fixture Message ' . $suffix,
            'alias'      => 'cwm2187-media-fixture-' . $suffix,
            // Explicit, so importMessage() never falls back to Factory::getDate() —
            // that touches the language system, which this bare harness cannot
            // fully resolve (see the class docblock).
            'studydate' => '2026-01-01 00:00:00',
        ]];
    }

    public function testRejectsAMediaFileWithAnUnknownStudyId(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 999,
                'server_name' => $this->seedFixtureServer(),
                'params'      => ['filename' => 'test.mp3', 'size' => 5000, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/does not match any messages\[].id/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileWhenTheServerNameMatchesNoServer(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => 'cwm2187-no-such-server',
                'params'      => ['filename' => 'test.mp3', 'size' => 5000, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/must match exactly one existing server/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileWhenTheServerNameMatchesMoreThanOneServer(): void
    {
        $duplicateName = 'cwm2187-dupe-server-' . bin2hex(random_bytes(4));

        for ($i = 0; $i < 2; $i++) {
            $server = (object) [
                'server_name' => $duplicateName,
                'type'        => 'legacy',
                'params'      => '{}',
                'media'       => '{}',
                'published'   => 1,
                'access'      => 1,
            ];
            $this->db->insertObject('#__bsms_servers', $server, 'id');
        }

        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $duplicateName,
                'params'      => ['filename' => 'test.mp3', 'size' => 5000, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/must match exactly one existing server/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileOnAServerTypeOtherThanLegacy(): void
    {
        $name   = 'cwm2187-local-server-' . bin2hex(random_bytes(4));
        $server = (object) [
            'server_name' => $name,
            'type'        => 'local',
            'params'      => '{}',
            'media'       => '{}',
            'published'   => 1,
            'access'      => 1,
        ];
        $this->db->insertObject('#__bsms_servers', $server, 'id');

        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $name,
                'params'      => ['filename' => 'test.mp3', 'size' => 5000, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/only legacy servers are importable/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileParamsWithAnUnrecognisedKey(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $this->seedFixtureServer(),
                'params'      => [
                    'filename'      => 'test.mp3',
                    'size'          => 5000,
                    'mime_type'     => 'audio/mpeg',
                    'media_minutes' => 5,
                    'docMan_id'     => '999',
                ],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/unrecognised key\(s\): docMan_id/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileReferencingAnUnknownPodcast(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $this->seedFixtureServer(),
                'podcast_id'  => [999999],
                'params'      => ['filename' => 'test.mp3', 'size' => 5000, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/references podcast #999999, which does not exist/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileParamsWithACreateLiveBroadcastKey(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $this->seedFixtureServer(),
                'params'      => [
                    'filename'              => 'test.mp3',
                    'size'                  => 5000,
                    'mime_type'             => 'audio/mpeg',
                    'media_minutes'         => 5,
                    'create_live_broadcast' => 1,
                ],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/must not include "create_live_broadcast"/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileParamsWithALivePrefixedKey(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $this->seedFixtureServer(),
                'params'      => [
                    'filename'      => 'test.mp3',
                    'size'          => 5000,
                    'mime_type'     => 'audio/mpeg',
                    'media_minutes' => 5,
                    'live_privacy'  => 'unlisted',
                ],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/must not include "live_privacy"/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    public function testRejectsAMediaFileParamsThatWouldTriggerRemoteMetadataDetection(): void
    {
        $tag     = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $this->seedFixtureServer(),
                // size is well under the 1000 floor CWMAddon::needsDetection()
                // requires to consider it "already known".
                'params' => ['filename' => 'test.mp3', 'size' => 10, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($this->factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertMatchesRegularExpression('/params\.size must be an integer of at least 1000/', $e->getMessage());
        }

        $this->assertFalse(Cwmimportmanifest::exists($tag));
    }

    /**
     * The mediafiles equivalent of
     * {@see testAnOrphanedRowIsStillRecordedInTheManifestWhenSaveFails()} —
     * `#__bsms_mediafiles` has no `alias` column, so recovery here is
     * exercised via {@see Cwmcontentimporter::recordOrphanedMediaFiles()}'s
     * study_id lookup instead. Two different stub models are needed (one per
     * `createModel()` call) because, unlike the teacher-only fixture above,
     * a real study source-id => target-id mapping is required for
     * `mediafiles[].study_id` to resolve at all.
     */
    public function testAnOrphanedMediaFileRowIsStillRecordedInTheManifestWhenSaveFails(): void
    {
        $tag        = 'cwm2187-test-' . bin2hex(random_bytes(4));
        $serverName = $this->seedFixtureServer();

        $messageModel = $this->createStub(AdminModel::class);
        $messageModel->method('save')->willReturn(true);
        $messageModel->method('getName')->willReturn('cwmmessage');
        $messageModel->method('getState')->willReturn(9001);

        $mediaModel = $this->createStub(AdminModel::class);
        $mediaModel->method('save')->willReturnCallback(function () {
            $mediafile = (object) [
                'study_id' => 9001,
                'metadata' => '{}',
                'language' => '*',
            ];
            $this->db->insertObject('#__bsms_mediafiles', $mediafile, 'id');

            throw new \RuntimeException('simulated after-save plugin failure');
        });

        $factory = $this->createStub(MVCFactoryInterface::class);
        $factory->method('createModel')->willReturnCallback(
            fn (string $name) => $name === 'Cwmmediafile' ? $mediaModel : $messageModel
        );

        $payload = [
            'messages'   => $this->fixtureMessageOnly(),
            'mediafiles' => [[
                'study_id'    => 1,
                'server_name' => $serverName,
                'params'      => ['filename' => 'test.mp3', 'size' => 5000, 'mime_type' => 'audio/mpeg', 'media_minutes' => 5],
            ]],
        ];

        try {
            (new Cwmcontentimporter($factory))->import($tag, $payload, $this->fixture()['dir']);
            $this->fail('Expected the simulated save failure to propagate.');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated after-save plugin failure', $e->getMessage());
        }

        $mediaId = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_mediafiles'))
                ->where($this->db->quoteName('study_id') . ' = 9001')
        )->loadResult();

        $this->assertNotNull($mediaId, 'The stub save() must have inserted the row.');

        $rows = Cwmimportmanifest::rowsForTag($tag);

        $this->assertContains(
            (int) $mediaId,
            $rows['#__bsms_mediafiles'] ?? [],
            'The row Table::store() wrote must still be recorded even though save() reported failure.'
        );
    }

    /**
     * @param   string  $table       `#__`-prefixed table name.
     * @param   string  $column      Column to select.
     * @param   string  $whereCol    Column to match on.
     * @param   int     $whereValue  Value to match.
     *
     * @return  mixed
     */
    private function scalar(string $table, string $column, string $whereCol, int $whereValue): mixed
    {
        return $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName($column))
                ->from($this->db->quoteName($table))
                ->where($this->db->quoteName($whereCol) . ' = :val')
                ->bind(':val', $whereValue, ParameterType::INTEGER)
        )->loadResult();
    }

    /**
     * @param   string  $table       `#__`-prefixed table name.
     * @param   string  $whereCol    Column to match on.
     * @param   int     $whereValue  Value to match.
     *
     * @return  int
     */
    private function countRows(string $table, string $whereCol, int $whereValue): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName($table))
                ->where($this->db->quoteName($whereCol) . ' = :val')
                ->bind(':val', $whereValue, ParameterType::INTEGER)
        )->loadResult();
    }
}
