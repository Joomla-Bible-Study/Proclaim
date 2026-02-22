<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\View\Cwmlicense;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * License acceptance view.
 *
 * @since  10.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the license acceptance screen.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        ToolbarHelper::title(Text::_('JBS_LICENSE_TITLE'), 'lock');

        parent::display($tpl);
    }
}
