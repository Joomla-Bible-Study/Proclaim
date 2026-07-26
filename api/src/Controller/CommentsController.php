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

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * API controller for study comments — READ ONLY.
 *
 * GET /api/index.php/v1/proclaim/comments       — list (published only)
 * GET /api/index.php/v1/proclaim/comments/:id   — detail
 *
 * Filters: ?filter[search]=&filter[study_id]=
 *
 * Comments are queryable but never writable. An HTTP create endpoint would be a
 * spam vector that bypasses the front-end submission path and its moderation
 * gate, so no write route is registered. The view also withholds `user_email`
 * and `user_id`: the address is personal data that must not be published, and
 * the account id would let a caller correlate commenters with site users.
 *
 * Unlike other resources this lists published comments only — an archived or
 * unpublished comment is one a moderator has deliberately withheld.
 *
 * @since  10.3.4
 */
class CommentsController extends AbstractReadOnlyController
{
    protected $contentType = 'comments';

    protected $default_view = 'comments';

    /**
     * List comments — published only.
     *
     * @return  static
     *
     * @since   10.3.4
     */
    public function displayList()
    {
        $this->modelState->set('filter.published', 1);

        $apiFilter = $this->input->get('filter', [], 'array');
        $clean     = InputFilter::getInstance();

        if (\array_key_exists('search', $apiFilter)) {
            $this->modelState->set('filter.search', $clean->clean($apiFilter['search'], 'STRING'));
        }

        if (\array_key_exists('study_id', $apiFilter)) {
            $this->modelState->set('filter.study_id', (int) $apiFilter['study_id']);
        }

        return parent::displayList();
    }

    /**
     * Get the model, mapping API names to Cwm-prefixed Proclaim classes.
     *
     * @param   string  $name    Model name
     * @param   string  $prefix  Model prefix
     * @param   array   $config  Configuration
     *
     * @return  BaseDatabaseModel|false
     *
     * @since   10.3.4
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'comments' => 'Cwmcomments',
            'comment'  => 'Cwmcomment',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}
