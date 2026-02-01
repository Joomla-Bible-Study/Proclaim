<?php

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Site\View\Cwmteachers\HtmlView $this */

?>
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<?php

if ($this->params->get('teacherstemplate')) {
    echo $this->loadTemplate($this->params->get('teacherstemplate'));
} else {
    echo $this->loadTemplate('main');
}
