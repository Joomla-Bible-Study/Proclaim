<?php

/**
 * Unit tests for CwmschemaorgHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmschemaorgHelper
 *
 * Tests the pure data-transform methods that build JSON-LD arrays.
 * These methods accept stdClass objects and return arrays — no DB required.
 *
 * @since  10.1.0
 */
class CwmschemaorgHelperTest extends ProclaimTestCase
{
    /**
     * Create a fully-populated sermon item for testing.
     *
     * @return \stdClass
     */
    private function makeSermonItem(): \stdClass
    {
        $item                = new \stdClass();
        $item->id            = 42;
        $item->studytitle    = 'The Good Samaritan';
        $item->studyintro    = '<p>A parable about <strong>compassion</strong> and mercy.</p>';
        $item->studydate     = '2025-06-15 10:00:00';
        $item->teachername   = 'Pastor John';
        $item->series_text   = 'Parables of Jesus';
        $item->series_id     = 5;
        $item->topic_text    = 'Compassion, Love, Service';
        $item->messageType   = 'Sermon';
        $item->location_text = 'Main Sanctuary';

        return $item;
    }

    /**
     * Create a minimal sermon item with only required fields.
     *
     * @return \stdClass
     */
    private function makeMinimalSermonItem(): \stdClass
    {
        $item             = new \stdClass();
        $item->id         = 1;
        $item->studytitle = 'Untitled';

        return $item;
    }

    /**
     * Create a fully-populated teacher item for testing.
     *
     * @return \stdClass
     */
    private function makeTeacherItem(): \stdClass
    {
        $item                    = new \stdClass();
        $item->id                = 10;
        $item->teachername       = 'Pastor Jane Smith';
        $item->title             = 'Senior Pastor';
        $item->short             = '<p>An experienced <em>teacher</em> and speaker.</p>';
        $item->information       = 'Full bio text here.';
        $item->teacher_image     = 'media/com_proclaim/images/teachers/jane.jpg';
        $item->image             = '';
        $item->facebooklink      = 'https://facebook.com/pastorjane';
        $item->twitterlink       = 'https://twitter.com/pastorjane';
        $item->bloglink          = '';
        $item->website           = 'https://pastorjane.example.com';
        $item->link1             = '';
        $item->link2             = '';
        $item->teacher_thumbnail = '';

        return $item;
    }

    /**
     * Create a series item for testing.
     *
     * @return \stdClass
     */
    private function makeSeriesItem(): \stdClass
    {
        $item                   = new \stdClass();
        $item->id               = 5;
        $item->series_text      = 'Parables of Jesus';
        $item->description      = '<p>A deep dive into the parables.</p>';
        $item->series_thumbnail = 'media/com_proclaim/images/series/parables.jpg';
        $item->teachername      = 'Pastor John';

        return $item;
    }

    /**
     * Create study items for series testing.
     *
     * @return array
     */
    private function makeStudies(): array
    {
        $study1             = new \stdClass();
        $study1->id         = 42;
        $study1->studytitle = 'The Good Samaritan';
        $study1->studydate  = '2025-06-15 10:00:00';

        $study2             = new \stdClass();
        $study2->id         = 43;
        $study2->studytitle = 'The Prodigal Son';
        $study2->studydate  = '2025-06-22 10:00:00';

        return [$study1, $study2];
    }

    // ----- Sermon Detail Tests -----

    /**
     * Test buildSermonDetail with full data.
     *
     * @return void
     */
    public function testBuildSermonDetailFullData(): void
    {
        $item   = $this->makeSermonItem();
        $result = CwmschemaorgHelper::buildSermonDetail($item, 'https://example.com/sermon/42', 'My Church');

        $this->assertEquals('https://schema.org', $result['@context']);
        $this->assertEquals('CreativeWork', $result['@type']);
        $this->assertEquals('The Good Samaritan', $result['name']);
        $this->assertEquals('https://example.com/sermon/42', $result['url']);
        $this->assertStringNotContainsString('<', $result['description']);
        $this->assertArrayHasKey('datePublished', $result);
        $this->assertEquals('Pastor John', $result['author']['name']);
        $this->assertEquals('Person', $result['author']['@type']);
        $this->assertEquals('Parables of Jesus', $result['isPartOf']['name']);
        $this->assertEquals('CreativeWorkSeries', $result['isPartOf']['@type']);
        $this->assertEquals(['Compassion', 'Love', 'Service'], $result['about']);
        $this->assertEquals('Sermon', $result['genre']);
        $this->assertEquals('Main Sanctuary', $result['locationCreated']['name']);
        $this->assertEquals('My Church', $result['publisher']['name']);
    }

    /**
     * Test buildSermonDetail with minimal data omits optional fields.
     *
     * @return void
     */
    public function testBuildSermonDetailMinimalData(): void
    {
        $item   = $this->makeMinimalSermonItem();
        $result = CwmschemaorgHelper::buildSermonDetail($item, 'https://example.com/sermon/1', 'My Church');

        $this->assertEquals('CreativeWork', $result['@type']);
        $this->assertEquals('Untitled', $result['name']);
        $this->assertArrayNotHasKey('description', $result);
        $this->assertArrayNotHasKey('datePublished', $result);
        $this->assertArrayNotHasKey('author', $result);
        $this->assertArrayNotHasKey('isPartOf', $result);
        $this->assertArrayNotHasKey('about', $result);
        $this->assertArrayNotHasKey('genre', $result);
        $this->assertArrayNotHasKey('locationCreated', $result);
    }

    /**
     * Test that HTML is stripped from description.
     *
     * @return void
     */
    public function testBuildSermonDetailStripsHtml(): void
    {
        $item   = $this->makeSermonItem();
        $result = CwmschemaorgHelper::buildSermonDetail($item, 'https://example.com/sermon/42', 'Church');

        $this->assertStringNotContainsString('<p>', $result['description']);
        $this->assertStringNotContainsString('<strong>', $result['description']);
        $this->assertStringContainsString('compassion', $result['description']);
    }

    // ----- Sermon List Tests -----

    /**
     * Test buildSermonList with items.
     *
     * @return void
     */
    public function testBuildSermonListWithItems(): void
    {
        $items  = [$this->makeSermonItem(), $this->makeMinimalSermonItem()];
        $result = CwmschemaorgHelper::buildSermonList($items, 'https://example.com/sermons', 'My Church');

        $this->assertEquals('ItemList', $result['@type']);
        $this->assertEquals(2, $result['numberOfItems']);
        $this->assertEquals('https://example.com/sermons', $result['url']);
        $this->assertEquals('My Church', $result['name']);
        $this->assertCount(2, $result['itemListElement']);
        $this->assertEquals(1, $result['itemListElement'][0]['position']);
        $this->assertEquals(2, $result['itemListElement'][1]['position']);
        $this->assertEquals('The Good Samaritan', $result['itemListElement'][0]['item']['name']);
    }

    /**
     * Test buildSermonList with empty array.
     *
     * @return void
     */
    public function testBuildSermonListEmpty(): void
    {
        $result = CwmschemaorgHelper::buildSermonList([], 'https://example.com/sermons', 'My Church');

        $this->assertEquals('ItemList', $result['@type']);
        $this->assertEquals(0, $result['numberOfItems']);
        $this->assertArrayNotHasKey('itemListElement', $result);
    }

    // ----- Teacher Detail Tests -----

    /**
     * Test buildTeacherDetail with full data.
     *
     * @return void
     */
    public function testBuildTeacherDetailFullData(): void
    {
        $item   = $this->makeTeacherItem();
        $result = CwmschemaorgHelper::buildTeacherDetail($item, 'https://example.com/teacher/10');

        $this->assertEquals('Person', $result['@type']);
        $this->assertEquals('Pastor Jane Smith', $result['name']);
        $this->assertEquals('Senior Pastor', $result['jobTitle']);
        $this->assertStringNotContainsString('<', $result['description']);
        $this->assertStringContainsString('teacher', $result['description']);
        $this->assertArrayHasKey('image', $result);
        $this->assertStringContainsString('jane.jpg', $result['image']);
        // sameAs should contain only valid URLs, excluding empty strings
        $this->assertCount(3, $result['sameAs']);
        $this->assertContains('https://facebook.com/pastorjane', $result['sameAs']);
        $this->assertContains('https://twitter.com/pastorjane', $result['sameAs']);
        $this->assertContains('https://pastorjane.example.com', $result['sameAs']);
    }

    /**
     * Test buildTeacherDetail with no social links.
     *
     * @return void
     */
    public function testBuildTeacherDetailNoSocialLinks(): void
    {
        $item               = new \stdClass();
        $item->teachername  = 'Teacher Bob';
        $item->title        = '';
        $item->short        = '';
        $item->information  = '';
        $item->facebooklink = '';
        $item->twitterlink  = '';
        $item->bloglink     = '';
        $item->website      = '';
        $item->link1        = '';
        $item->link2        = '';

        $result = CwmschemaorgHelper::buildTeacherDetail($item, 'https://example.com/teacher/1');

        $this->assertEquals('Person', $result['@type']);
        $this->assertEquals('Teacher Bob', $result['name']);
        $this->assertArrayNotHasKey('jobTitle', $result);
        $this->assertArrayNotHasKey('description', $result);
        $this->assertArrayNotHasKey('sameAs', $result);
    }

    /**
     * Test that invalid URLs are filtered from sameAs.
     *
     * @return void
     */
    public function testBuildTeacherDetailFiltersBadUrls(): void
    {
        $item               = new \stdClass();
        $item->teachername  = 'Teacher';
        $item->facebooklink = 'not-a-url';
        $item->twitterlink  = 'https://twitter.com/valid';
        $item->bloglink     = 'javascript:alert(1)';
        $item->website      = '';
        $item->link1        = '';
        $item->link2        = '';

        $result = CwmschemaorgHelper::buildTeacherDetail($item, 'https://example.com/teacher/1');

        $this->assertCount(1, $result['sameAs']);
        $this->assertEquals('https://twitter.com/valid', $result['sameAs'][0]);
    }

    // ----- Series Detail Tests -----

    /**
     * Test buildSeriesDetail with full data.
     *
     * @return void
     */
    public function testBuildSeriesDetailFullData(): void
    {
        $item    = $this->makeSeriesItem();
        $studies = $this->makeStudies();
        $result  = CwmschemaorgHelper::buildSeriesDetail(
            $item,
            $studies,
            'https://example.com/series/5',
            'My Church'
        );

        $this->assertEquals('CreativeWorkSeries', $result['@type']);
        $this->assertEquals('Parables of Jesus', $result['name']);
        $this->assertStringNotContainsString('<', $result['description']);
        $this->assertArrayHasKey('image', $result);
        $this->assertEquals('Pastor John', $result['author']['name']);
        $this->assertEquals('My Church', $result['publisher']['name']);
        $this->assertCount(2, $result['hasPart']);
        $this->assertEquals('The Good Samaritan', $result['hasPart'][0]['name']);
        $this->assertEquals('The Prodigal Son', $result['hasPart'][1]['name']);
    }

    /**
     * Test buildSeriesDetail with no studies.
     *
     * @return void
     */
    public function testBuildSeriesDetailNoStudies(): void
    {
        $item   = $this->makeSeriesItem();
        $result = CwmschemaorgHelper::buildSeriesDetail($item, [], 'https://example.com/series/5', 'My Church');

        $this->assertEquals('CreativeWorkSeries', $result['@type']);
        $this->assertArrayNotHasKey('hasPart', $result);
    }

    // ----- cleanDescription Tests -----

    /**
     * Test cleanDescription strips HTML and truncates.
     *
     * @return void
     */
    public function testCleanDescriptionStripsAndTruncates(): void
    {
        $long   = '<p>' . str_repeat('A word. ', 50) . '</p>';
        $result = CwmschemaorgHelper::cleanDescription($long);

        $this->assertStringNotContainsString('<p>', $result);
        $this->assertLessThanOrEqual(200, mb_strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    /**
     * Test cleanDescription returns empty for empty input.
     *
     * @return void
     */
    public function testCleanDescriptionEmpty(): void
    {
        $this->assertEquals('', CwmschemaorgHelper::cleanDescription(''));
        $this->assertEquals('', CwmschemaorgHelper::cleanDescription('   '));
    }

    /**
     * Test cleanDescription handles special characters.
     *
     * @return void
     */
    public function testCleanDescriptionSpecialChars(): void
    {
        $result = CwmschemaorgHelper::cleanDescription('&amp; &lt;b&gt;test&lt;/b&gt;');

        $this->assertEquals('& <b>test</b>', $result);
    }

    /**
     * Test that topics are correctly split from comma-separated string.
     *
     * @return void
     */
    public function testBuildSermonDetailParsesTopics(): void
    {
        $item             = $this->makeMinimalSermonItem();
        $item->topic_text = 'Faith, Hope, , Love';
        $result           = CwmschemaorgHelper::buildSermonDetail($item, 'https://example.com/sermon/1', '');

        $this->assertEquals(['Faith', 'Hope', 'Love'], $result['about']);
    }

    /**
     * Test sermon list items include author when available.
     *
     * @return void
     */
    public function testBuildSermonListItemIncludesAuthor(): void
    {
        $items  = [$this->makeSermonItem()];
        $result = CwmschemaorgHelper::buildSermonList($items, 'https://example.com/sermons', '');

        $listItem = $result['itemListElement'][0]['item'];
        $this->assertEquals('Pastor John', $listItem['author']['name']);
    }
}
