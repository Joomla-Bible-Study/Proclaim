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

/** @var CWM\Component\Proclaim\Site\View\Cwmseriesdisplays\HtmlView $this */

use Joomla\CMS\Language\Text;

?>
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<?php

if ($this->params->get('useexpert_serieslist') > 0) {
    echo $this->loadTemplate('custom');
} elseif ($this->params->get('seriesdisplaystemplate')) {
    echo $this->loadTemplate($this->params->get('seriesdisplaystemplate'));
} else {
    echo $this->loadTemplate('main');
}
