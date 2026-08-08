<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmseriesdisplaysModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1502: populateState() called
 * $this->setState('params', $params) once using the raw application params,
 * then reassigned its local $params to the template-merged Registry (with
 * the per-field row/column layout settings Cwmlisting::getFluidListing()
 * needs) — but never re-persisted that reassignment to state. Any caller
 * reading $state->get('params') (paginateAjax()'s controller code, matching
 * the pattern already correct in CwmsermonsController::filterAjax()) got
 * the stale, unmerged params, producing "success" responses with
 * correctly-counted but visually empty list items (no fields configured to
 * render). Cwmseriesdisplays/HtmlView was unaffected — it reads
 * $state->get('template')->params instead, which the merge always mutated
 * correctly in place.
 *
 * Fixed by moving setState('params', $params) to fire after the
 * reassignment, matching the equivalent, already-correct line in
 * CwmsermonsModel::populateState().
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmseriesdisplaysModelTest extends ProclaimTestCase
{
    private static function methodBody(): string
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'populateState');
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testSetStateParamsFiresAfterTheTemplateMergeReassignment(): void
    {
        $body = self::methodBody();

        $reassignPos = strpos($body, '$params = $template->params;');
        $setStatePos = strpos($body, "\$this->setState('params', \$params);");

        $this->assertNotFalse($reassignPos, 'Expected the $params = $template->params; reassignment line — see #1502');
        $this->assertNotFalse($setStatePos, "Expected \$this->setState('params', \$params); — see #1502");
        $this->assertGreaterThan(
            $reassignPos,
            $setStatePos,
            "setState('params', ...) must fire AFTER the template-merge reassignment, not before it, or " .
                "\$state->get('params') returns the raw pre-merge Registry — see #1502"
        );
    }

    public function testSetStateParamsIsNotAlsoCalledBeforeTheMerge(): void
    {
        $body = self::methodBody();

        // Only one setState('params', ...) call should exist now — the
        // pre-fix code additionally had one right after `$params =
        // $app->getParams();`, before the merge, whose value was always
        // immediately superseded (and never re-persisted) by the
        // reassignment below it.
        $count = substr_count($body, "setState('params'");

        $this->assertSame(1, $count, "setState('params', ...) should be called exactly once, after the merge — see #1502");
    }
}
