<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Read-only API controller for series.
 *
 * GET /api/index.php/v1/proclaim/series       — list
 * GET /api/index.php/v1/proclaim/series/:id   — detail
 *
 * @since  10.3.0
 */
class SeriesController extends ApiController
{
    protected $contentType = 'series';

    protected $default_view = 'series';

    /**
     * Map API model names to Cwm-prefixed Proclaim classes.
     *
     * Series is special: Doctrine singularize('series') === 'series', so
     * both list and item requests pass 'series'. We track which context
     * we're in via $itemModelRequested (set by displayItem override).
     *
     * @since  10.3.0
     */
    private bool $itemModelRequested = false;

    /**
     * List series — published and archived only.
     *
     * @return  static
     *
     * @since   10.3.0
     */
    public function displayList()
    {
        $this->modelState->set('filter.published', [1, 2]);

        return parent::displayList();
    }

    /**
     * @since  10.3.0
     */
    public function displayItem($id = null)
    {
        $this->itemModelRequested = true;

        return parent::displayItem($id);
    }

    /**
     * @since  10.3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        if (strtolower($name) === 'series') {
            $name = $this->itemModelRequested ? 'Cwmserie' : 'Cwmseries';

            return parent::getModel($name, $prefix, $config);
        }

        $map = [
            'serie' => 'Cwmserie',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}
