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
 * @since  __DEPLOY_VERSION__
 */
class CwmmessageModelTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmmessageModel method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
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

    public function testSaveDelegatesTopicSyncToSaveTopicsInEveryReturnTrueBranch(): void
    {
        $body = self::methodBody('save');

        $this->assertSame(
            6,
            substr_count($body, '$this->saveTopics('),
            'save() must call saveTopics() alongside every saveScriptures()/saveTeachers() pair — see #1444'
        );
    }

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
     * @return  array<string, array{0: array|string, 1: string[]}>
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
     * @param   array|string  $topicsRaw
     * @param   string[]      $expected
     *
     * @return  void
     */
    #[DataProvider('topicsRawProvider')]
    public function testNormalizeTopicTags(array|string $topicsRaw, array $expected): void
    {
        $method = new \ReflectionMethod(CwmmessageModel::class, 'normalizeTopicTags');
        $this->assertSame($expected, $method->invoke(null, $topicsRaw));
    }

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

    public function testSaveTopicsDelegatesJunctionWritesToHelper(): void
    {
        $body = self::methodBody('saveTopics');

        $this->assertStringContainsString('CwmstudytopicHelper::saveTopics(', $body, 'saveTopics() must delegate the junction-table write to CwmstudytopicHelper — see #1444');
        $this->assertStringContainsString('CwmstudytopicHelper::findTopicIdByText(', $body, 'saveTopics() must delegate the topic lookup to CwmstudytopicHelper — see #1444');
    }

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
}
