<?php

/**
 * Default View file
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

if ($this->params->get('useexpert_seriesdetail') > 0) {
    echo $this->loadTemplate('custom');
} elseif ($this->params->get('seriesdisplaytemplate')) {
    echo $this->loadTemplate($this->params->get('seriesdisplaytemplate'));
} else {
    echo $this->loadTemplate('main');
}
