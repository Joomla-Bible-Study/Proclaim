<?php

/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Layout\LayoutHelper;

$this->getDocument()->getWebAssetManager()->useScript('com_proclaim.cwmadmin-batch-footer');

echo LayoutHelper::render('html.batch.footer', ['submitTask' => 'cwmserver.batch']);
