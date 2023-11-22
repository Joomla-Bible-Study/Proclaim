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


if ($this->params->get('teacherstemplate')) {
    echo $this->loadTemplate($this->params->get('teacherstemplate'));
} else {
    echo $this->loadTemplate('main');
}
