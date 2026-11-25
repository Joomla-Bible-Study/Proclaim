<?php

/**
 * Unit tests for CwmmessageModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmessageModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test for #1444: CwmmessageController::save() deleted
 * #__bsms_studytopics rows, looped over tags doing topic lookups/creation,
 * and wrote Cwmstudytopics rows directly, using $data['id'] from raw POST
 * BEFORE parent::save() ran. For a new record that ID is always 0/empty —
 * meaning tags never linked correctly when creating a new study. Moved into
 * CwmmessageModel::saveTopics(), called after parent::save() succeeds
 * (matching the existing saveScriptures()/saveTeachers() pattern), so it
 * uses the real post-insert ID via getState() instead.
 *
 * @since  10.5.6
 */
class CwmmessageModelTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmmessageModel method for structural assertions.
     *
     * @param   string  $method  The method's name
     *
     * @return  string  The method's source, from the signature to the closing brace
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmmessageModel::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * save() calls saveTopics() alongside every saveScriptures() and saveTeachers()
     * pair, six times in all.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testSaveDelegatesTopicSyncToSaveTopicsInEveryReturnTrueBranch(): void
    {
        $body = self::methodBody('save');

        $this->assertSame(
            6,
            substr_count($body, '$this->saveTopics('),
            'save() must call saveTopics() alongside every saveScriptures()/saveTeachers() pair — see #1444'
        );
    }

    /**
     * saveTopics() takes the study id from the model state, not from the raw posted
     * data.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testSaveTopicsDerivesStudyIdFromStateNotRawPostData(): void
    {
        $body = self::methodBody('saveTopics');

        $this->assertStringContainsString(
            "\$this->getState(\$this->getName() . '.id')",
            $body,
            'saveTopics() must use the post-insert ID from state, matching saveScriptures()/saveTeachers() — see #1444'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\$data\[.id.\]/',
            $body,
            'saveTopics() must not read a raw $data[\'id\'] — that is 0/empty for new records before parent::save() runs — see #1444'
        );
    }

    /**
     * Regression test discovered via live testing on j5-dev: submitting two
     * tags (an existing numeric ID + a new free-text tag) through the real
     * admin form produced a SINGLE junction row with topic_text literally
     * "145,ClaudeNewTopic1444" — the comma-joined string was never split.
     *
     * Root cause: by the time Form::filter() processes the TopicsForm field
     * (declared multiple="true"), $data['topics'] arrives as an array
     * wrapping the whole CSV string as its one element (e.g. ['145,Claude'])
     * rather than as a plain string — not what the original controller code
     * (which read raw $_POST, bypassing Form::filter()) ever had to handle.
     *
     * @return  array<string, array{0: array|string, 1: string[]}>  The topics as submitted, then the tags they become
     *
     * @since   10.5.6
     */
    public static function topicsRawProvider(): array
    {
        return [
            'array wrapping a multi-tag CSV string' => [['145,ClaudeNewTopic1444'], ['145', 'ClaudeNewTopic1444']],
            'array wrapping a single tag'           => [['145'], ['145']],
            'plain CSV string'                      => ['1,2,3', ['1', '2', '3']],
            'plain single value'                    => ['145', ['145']],
            'empty string'                          => ['', []],
            'empty array'                           => [[], []],
            'array with empty/null noise'           => [['', '145', null], ['145']],
        ];
    }

    /**
     * normalizeTopicTags() splits the submitted topics into one tag per value,
     * dropping empty ones.
     *
     * @param   array|string  $topicsRaw  The topics as submitted
     * @param   string[]      $expected   The tags they should become
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('topicsRawProvider')]
    public function testNormalizeTopicTags(array|string $topicsRaw, array $expected): void
    {
        $method = new \ReflectionMethod(CwmmessageModel::class, 'normalizeTopicTags');
        $this->assertSame($expected, $method->invoke(null, $topicsRaw));
    }

    /**
     * saveTopics() returns void and takes the topics, intro, text and language, in
     * that order.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testSaveTopicsSignature(): void
    {
        $method = new \ReflectionMethod(CwmmessageModel::class, 'saveTopics');
        $this->assertReturnTypeName('void', $method);

        $params = $method->getParameters();
        $this->assertSame('topicsRaw', $params[0]->getName());
        $this->assertSame('studyIntro', $params[1]->getName());
        $this->assertSame('studyText', $params[2]->getName());
        $this->assertSame('language', $params[3]->getName());
    }

    /**
     * saveTopics() leaves the junction write and the topic lookup to CwmstudytopicHelper.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testSaveTopicsDelegatesJunctionWritesToHelper(): void
    {
        $body = self::methodBody('saveTopics');

        $this->assertStringContainsString('CwmstudytopicHelper::saveTopics(', $body, 'saveTopics() must delegate the junction-table write to CwmstudytopicHelper — see #1444');
        $this->assertStringContainsString('CwmstudytopicHelper::findTopicIdByText(', $body, 'saveTopics() must delegate the topic lookup to CwmstudytopicHelper — see #1444');
    }

    /**
     * saveTopics() passes id 0 when it creates a topic, so the save inserts rather
     * than updating the previous one.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testSaveTopicsPreservesNewTopicCreationIdZeroFix(): void
    {
        $body = self::methodBody('saveTopics');

        // See observation 7165 / issue history: omitting id=>0 makes AdminModel
        // reuse the previous state ID and UPDATE instead of INSERT, silently
        // overwriting the same topic row across loop iterations.
        $this->assertMatchesRegularExpression(
            '/\[.id.\s*=>\s*0,\s*.topic_text.\s*=>/',
            $body,
            'saveTopics() must pass id=>0 to force INSERT when creating a new topic — see #1444'
        );
    }

    /**
     * Regression test: save()'s a_id override let an authenticated admin-side
     * request redirect the write to a different row than the one the
     * controller's allowEdit() had just authorised (confirmed live against a
     * real item-level permission deny). getForm() in this same class already
     * gates the identical a_id read on isClient('site'); save() must too.
     *
     * A live end-to-end call isn't possible in this harness — the booted
     * application here is the bare Joomla\Console\Application, which has no
     * isClient() method at all (unlike a real site/administrator app) — so
     * this follows the file's existing structural-assertion pattern instead.
     *
     * @return  void
     *
     * @since   10.7.2
     * @throws  \ReflectionException
     */
    public function testSaveGuardsTheAIdOverrideToTheSiteClient(): void
    {
        $body = self::methodBody('save');

        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$app->isClient\(.site.\)\s*&&\s*\$input->get\(.a_id.\)\s*\)\s*\{\s*\$data\[.id.\]\s*=\s*\$input->get\(.a_id.\);/',
            $body,
            "save() must guard the a_id override on isClient('site') — an admin-side request must never have its "
                . 'write target redirected by an a_id parameter the controller never authorised against'
        );
    }
}
