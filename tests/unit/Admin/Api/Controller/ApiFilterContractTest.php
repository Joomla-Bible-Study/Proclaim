<?php

/**
 * Contract test: every API list filter reaches a model that reads it.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * ⚠️ An API controller sanitises `?filter[x]=` and writes it to `modelState` as
 * `filter.x`. If the list model never reads that state key, the filter does
 * nothing — no error, no 400, no hint. The caller gets **the whole unfiltered
 * set back**, correctly shaped, and cannot tell the scoping was discarded.
 *
 * That is worse than a missing response field: a dropped attribute is visibly
 * absent, wrong rows are not. Five such filters shipped from 10.3.0 (#1753).
 *
 * Sibling of JsonapiViewFieldsTest, which guards the same "declared but never
 * delivered" shape one layer down, in the response fields.
 *
 * @since  __DEPLOY_VERSION__
 */
class ApiFilterContractTest extends ProclaimTestCase
{
    /**
     * API controller => the list model it dispatches to.
     *
     * Resolved through each controller's own getModel() map, not by convention:
     * SermonsController maps 'sermons' to Cwmmessages, SeriesController maps
     * 'series' to Cwmseries or Cwmserie depending on route.
     *
     * @var array<string, string>
     * @since __DEPLOY_VERSION__
     */
    private const CONTROLLER_MODELS = [
        'Comments'     => 'CwmcommentsModel',
        'Locations'    => 'CwmlocationsModel',
        'Media'        => 'CwmmediafilesModel',
        'Messagetypes' => 'CwmmessagetypesModel',
        'Playlists'    => 'CwmplaylistsModel',
        'Podcasts'     => 'CwmpodcastsModel',
        'Series'       => 'CwmseriesModel',
        'Sermons'      => 'CwmmessagesModel',
        'Servers'      => 'CwmserversModel',
        'Teachers'     => 'CwmteachersModel',
        'Templates'    => 'CwmtemplatesModel',
        'Topics'       => 'CwmtopicsModel',

        // Info is a synthetic singleton with no list route and no filters.
        'Info' => '',
    ];

    /**
     * @return  string
     * @since   __DEPLOY_VERSION__
     */
    private static function repoRoot(): string
    {
        return \dirname(__DIR__, 5);
    }

    /**
     * Every API controller on disk, by name.
     *
     * @return  array<string, array{0: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function controllerProvider(): array
    {
        $cases = [];

        foreach (glob(self::repoRoot() . '/api/src/Controller/*Controller.php') as $file) {
            $name = basename($file, 'Controller.php');

            // The two abstract bases dispatch nothing and set no filters.
            if (str_starts_with($name, 'Abstract')) {
                continue;
            }

            $cases[$name] = [$name];
        }

        return $cases;
    }

    /**
     * The `filter.*` keys a controller writes into modelState.
     *
     * @param   string  $controller  Controller name without the suffix
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function filtersSetBy(string $controller): array
    {
        $src = file_get_contents(self::repoRoot() . '/api/src/Controller/' . $controller . 'Controller.php');

        preg_match_all("/modelState->set\('filter\.([a-z_]+)'/", $src, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * Whether a model reads a given filter key.
     *
     * ⚠️ Deliberately permissive: any mention of the key in the model source
     * counts, whether through getState() or getUserStateFromRequest(). It can
     * therefore pass a filter that is read and then not applied to the query.
     * It cannot pass one the model never mentions, which is the failure this
     * test exists for.
     *
     * @param   string  $model   Model class name
     * @param   string  $filter  Filter key without the `filter.` prefix
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function modelReads(string $model, string $filter): bool
    {
        $src = file_get_contents(self::repoRoot() . '/admin/src/Model/' . $model . '.php');

        return (bool) preg_match("/'filter\.{$filter}'|\.filter\.{$filter}'/", $src);
    }

    #[DataProvider('controllerProvider')]
    #[TestDox('every filter $controller accepts reaches a model that reads it')]
    public function testEveryFilterReachesAModelThatReadsIt(string $controller): void
    {
        $this->assertArrayHasKey(
            $controller,
            self::CONTROLLER_MODELS,
            "The $controller API controller has no entry in CONTROLLER_MODELS, so none of its filters are checked."
        );

        $model   = self::CONTROLLER_MODELS[$controller];
        $filters = self::filtersSetBy($controller);

        if ($model === '') {
            $this->assertSame([], $filters, "$controller reads no table but sets filters.");

            return;
        }

        $this->assertFileExists(self::repoRoot() . '/admin/src/Model/' . $model . '.php');

        $ignored = array_values(array_filter(
            $filters,
            static fn (string $f) => !self::modelReads($model, $f)
        ));

        $this->assertSame(
            [],
            $ignored,
            \sprintf(
                '%sController accepts %s, but %s never reads that state key. The request succeeds and the '
                . 'filter is discarded, so the caller gets the whole unfiltered set back with nothing to say '
                . 'so. Either apply it in the model or stop accepting it in the controller.',
                $controller,
                implode(', ', array_map(static fn ($f) => "filter[$f]", $ignored)),
                $model
            )
        );
    }

    #[TestDox('the filter scan is finding filters, so the checks above are not vacuous')]
    public function testTheFilterScanIsNotVacuous(): void
    {
        $found = [];

        foreach (array_keys(self::controllerProvider()) as $controller) {
            foreach (self::filtersSetBy($controller) as $filter) {
                $found[] = $controller . '.' . $filter;
            }
        }

        // Every controller that has a list route sets at least filter.published
        // and filter.search. A scan returning far fewer pairs than that has
        // stopped matching and is asserting nothing.
        $this->assertGreaterThan(
            24,
            \count($found),
            'The filter scan found almost nothing. If the regex or the glob has drifted, every case above '
            . 'passes for free.'
        );

        // Spot-check the pairs #1753 turned on, so a rename cannot quietly
        // remove them from the scan.
        foreach (['Comments.study_id', 'Playlists.series_id', 'Servers.type', 'Topics.language'] as $pair) {
            $this->assertContains($pair, $found, "The scan no longer sees $pair.");
        }
    }

    #[TestDox('every API controller on disk is covered by these checks')]
    public function testEveryControllerIsCovered(): void
    {
        $found = array_keys(self::controllerProvider());

        $this->assertNotEmpty($found, 'No API controllers were found — the glob is wrong and nothing is checked.');
        $this->assertSame([], array_values(array_diff($found, array_keys(self::CONTROLLER_MODELS))));
    }
}
