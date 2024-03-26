<?php

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

?>
<div class="container-fluid"> <!-- This div is the container for the whole page --><?php

if ($this->item->params->get('sermontemplate') && !$this->simple->mode) {
    echo $this->loadTemplate($this->item->params->get('sermontemplate'));
} elseif ($this->simple->mode === 1) {
    echo $this->loadTemplate('simple');
} else {
    echo '<div>' . $this->loadTemplate('main') . '</div>';
}
    $show_comments = $this->item->params->get('show_comments');
if ($show_comments >= 1) {
    $container            = Factory::getContainer();
    $app                  = $container->get(SiteApplication::class);
    Factory::$application = $app;
    $user                 = Factory::getApplication()->getSession()->get('user');
    $groups               = $user->getAuthorisedViewLevels();
    $comment_access       = $this->item->params->get('comment_access');
    if (in_array($show_comments, $groups, false)) {
        echo '<div style="padding-top: 10px; margin: auto;">' . $this->loadTemplate('commentsform') . '</div>';
    }
}
    echo $this->loadTemplate('footer');
    echo $this->loadTemplate('footerlink');

?>
</div><!--end of container fluid-->
