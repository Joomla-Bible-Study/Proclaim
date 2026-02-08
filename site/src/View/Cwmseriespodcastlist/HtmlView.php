<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmseriespodcastlist;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Registry\Registry;

/**
 * View class for Messages
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * State object
     *
     * @var object|null
     * @since 7.0
     */
    protected $state;

    /**
     * Items array
     *
     * @var array|null
     * @since 7.0
     */
    protected $items;

    /**
     * Template object
     *
     * @var object|null
     * @since 7.0
     */
    protected $template;

    /**
     * Parameters
     *
     * @var Registry|null
     * @since 7.0
     */
    protected ?Registry $params = null;

    /**
     * Pagination object
     *
     * @var Pagination
     * @since 7.0
     */
    protected Pagination $pagination;

    /**
     * HTML attributes array
     *
     * @var array
     * @since 7.0
     */
    protected array $attribs = [];

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws \Exception
     * @since 7.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $this->state      = $this->get('State');
        $this->items      = $this->get('items');
        $this->pagination = $this->get('Pagination');

        $this->template = $this->state->template;
        $this->params   = $this->state->params;

        $attribs = [
            'class' => "jbsmimg",
        ];

        $this->attribs = $attribs;

        parent::display($tpl);
    }
}
