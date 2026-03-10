<?php

/**
 * Default for sermons
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

use Joomla\CMS\Language\Text;

?>
<div class="com-proclaim">
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<?php

echo $this->loadTemplate('formheader');
if ($this->params->get('sermonstemplate')) {
    echo $this->loadTemplate($this->params->get('sermonstemplate'));
} elseif ((int)$this->params->get('simple_mode') === 1) {
    $mode = $this->params->get('simple_mode_template');
    if ($mode === 'simple_mode1') {
        echo $this->loadTemplate('simple');
    }
    if ($mode === 'simple_mode2') {
        echo $this->loadTemplate('simple2');
    }
} else {
    echo $this->loadTemplate('main');
}

echo $this->loadTemplate('formfooter');
?>
</div>
