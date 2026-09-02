<?php

/**
 * Pins the builder shapes the hand-bracketed OR conversions rely on.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Unit\Query;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Thirty-eight call sites were converted from brackets typed into strings to
 * andWhere()/orWhere() on the strength of three renderings. This asserts each
 * against the real driver, so the conversion's premise is pinned rather than
 * remembered — and a Joomla behaviour change fails here, by name, instead of
 * as a subtly re-glued WHERE on some list page.
 *
 * ⚠️ The dangerous property is the glue, not the brackets: where() glues with
 * AND and honours no glue argument after the first call, which is how a
 * top-level OR once escaped published/access/language (the reason
 * WhereClauseContractTest exists).
 *
 * @since  __DEPLOY_VERSION__
 */
class OrGroupShapeTest extends ProclaimTestCase
{
    /**
     * @param   callable  $build  Receives a fresh query, returns it built.
     *
     * @return  string  The rendered WHERE clause, whitespace-normalised.
     */
    private function whereOf(callable $build): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $build($db->createQuery()->select('1')->from('dual'));

        $sql = preg_replace('/\s+/', ' ', (string) $query);

        return trim(substr($sql, strpos($sql, 'WHERE')));
    }

    #[TestDox('andWhere([a, b]) appends (a OR b) with AND, brackets included')]
    public function testAndWhereAppendsAnOrGroup(): void
    {
        // The workhorse: every publish-window pair and series escape uses it.
        $this->assertSame(
            'WHERE (x = 1 AND y = 2) AND (a = 3 OR b = 4)',
            $this->whereOf(static fn ($q) => $q
                ->where('x = 1')
                ->where('y = 2')
                ->andWhere(['a = 3', 'b = 4']))
        );
    }

    #[TestDox('where(a)->orWhere(b) renders (a) OR (b) when the group is the whole clause')]
    public function testWhereOrWhereSeedsTheClause(): void
    {
        $this->assertSame(
            'WHERE (a = 1) OR (b = 2)',
            $this->whereOf(static fn ($q) => $q->where('a = 1')->orWhere('b = 2'))
        );
    }

    #[TestDox("orWhere(rest, 'OR') keeps a flat OR list flat")]
    public function testOrWhereListNeedsTheExplicitInnerGlue(): void
    {
        // ⚠️ orWhere's inner glue defaults to AND — orWhere([b, c]) renders
        // (a) OR (b AND c), which silently narrows a flat OR list. The two
        // dynamic-array conversions pass 'OR' explicitly; this is why.
        $this->assertSame(
            'WHERE (a = 1) OR (b = 2 OR c = 3)',
            $this->whereOf(static fn ($q) => $q->where('a = 1')->orWhere(['b = 2', 'c = 3'], 'OR'))
        );

        $this->assertSame(
            'WHERE (a = 1) OR (b = 2 AND c = 3)',
            $this->whereOf(static fn ($q) => $q->where('a = 1')->orWhere(['b = 2', 'c = 3'])),
            "orWhere's default inner glue is AND — if this ever changes, every call passing 'OR' explicitly should be revisited."
        );
    }
}
