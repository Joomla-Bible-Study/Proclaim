<?php

/**
 * Pins CwmdbHelper::orderByWhitelisted() — the whitelist the 14 list models
 * lean on for their ORDER BY.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Unit\Query;

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The `$db->escape()` these call sites used to wrap the ordering in did nothing
 * — escape() is for a quoted string context, and an ORDER BY value is not
 * quoted. The helper replaced it with a real whitelist + quoteName. The case
 * that a green suite and a happy-path click both miss is an empty direction:
 * core permits `list.direction = ''`, Registry returns it verbatim (not the
 * default), and the old unquoted clause let MySQL apply ASC implicitly. A naive
 * ASC/DESC check would fall back to the model's default instead and silently
 * flip every descending list. This test nails that case by name.
 *
 * @since  __DEPLOY_VERSION__
 */
class OrderByWhitelistTest extends ProclaimTestCase
{
    /**
     * The columns a model would list in filter_fields.
     *
     * @var string[]
     */
    private const FIELDS = ['study.studydate', 'teacher.teachername', 'ordering'];

    /**
     * Build a query, apply the helper, return the normalised ORDER BY tail.
     *
     * @param   ?string  $column     The requested order column.
     * @param   ?string  $direction  The requested direction.
     * @param   string   $default    The fallback column.
     *
     * @return  string
     */
    private function orderOf(?string $column, ?string $direction, string $default = 'study.studydate'): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()->select('1')->from('dual');

        CwmdbHelper::orderByWhitelisted($query, self::FIELDS, $column, $direction, $default);

        $sql = preg_replace('/\s+/', ' ', (string) $query);

        return trim(substr($sql, strpos($sql, 'ORDER BY')));
    }

    /**
     * The column quoted as the driver quotes identifiers.
     *
     * @param   string  $column  Column name.
     *
     * @return  string
     */
    private function q(string $column): string
    {
        return Factory::getContainer()->get(DatabaseInterface::class)->createQuery()->quoteName($column);
    }

    #[TestDox('A whitelisted column with DESC is quoted and kept descending')]
    public function testWhitelistedColumnDesc(): void
    {
        $this->assertSame('ORDER BY ' . $this->q('teacher.teachername') . ' DESC', $this->orderOf('teacher.teachername', 'DESC'));
    }

    #[TestDox("An empty direction sorts ASC, not the model's default — the flip guard")]
    public function testEmptyDirectionIsAscendingNotDefault(): void
    {
        // The discriminator: a descending-default list whose session holds an
        // empty direction must still render ASC, exactly as the old unquoted
        // clause left it implicitly.
        $this->assertSame('ORDER BY ' . $this->q('study.studydate') . ' ASC', $this->orderOf('study.studydate', ''));
    }

    #[TestDox('A lowercase direction is honoured')]
    public function testLowercaseDirection(): void
    {
        $this->assertSame('ORDER BY ' . $this->q('ordering') . ' ASC', $this->orderOf('ordering', 'asc'));
        $this->assertSame('ORDER BY ' . $this->q('ordering') . ' DESC', $this->orderOf('ordering', 'desc'));
    }

    #[TestDox('A column not on the whitelist falls back to the default column')]
    public function testNonWhitelistedColumnFallsBack(): void
    {
        $this->assertSame('ORDER BY ' . $this->q('study.studydate') . ' DESC', $this->orderOf("study.id); DROP TABLE x --", 'DESC'));
    }

    #[TestDox('A null column and null direction give the default column, ASC')]
    public function testNullsGiveDefaults(): void
    {
        $this->assertSame('ORDER BY ' . $this->q('study.studydate') . ' ASC', $this->orderOf(null, null));
    }

    #[TestDox('A combined "column direction" pair (list.fullordering) is split and validated')]
    public function testCompoundFullorderingIsSplit(): void
    {
        // list.fullordering arrives as one string with no separate direction.
        $this->assertSame('ORDER BY ' . $this->q('teacher.teachername') . ' DESC', $this->orderOf('teacher.teachername DESC', ''));
        // …and the column half is still whitelisted.
        $this->assertSame('ORDER BY ' . $this->q('study.studydate') . ' ASC', $this->orderOf('bogus.column ASC', ''));
    }

    #[TestDox('A garbage direction becomes ASC rather than reaching the SQL')]
    public function testGarbageDirectionIsNeutralised(): void
    {
        $this->assertSame('ORDER BY ' . $this->q('ordering') . ' ASC', $this->orderOf('ordering', 'DESC; DROP TABLE x'));
    }
}
