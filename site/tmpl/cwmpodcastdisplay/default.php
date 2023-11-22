<?php

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  2007 - 2016 (C) CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HtmlHelper::_('dropdown.init');
HtmlHelper::_('formbehavior.chosen', 'select');
HtmlHelper::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'ordering';

$CWMedia = new Cwmmedia();
?>
<div class="container-fluid">
    <div class="col-lg-6">
        <?php
        echo $this->item->image; ?>
        <h2><?php
            echo Text::_($this->item->series_text); ?></h2>
        <p class="description"><?php
            echo $this->item->description; ?></p>
    </div>

    <?php
    if (!empty($this->media)) {
        ?>
        <div class="col-lg-6">
            <?php
            $this->params->set('player_width', ''); ?>
            <?php
            echo $CWMedia->getFluidMedia($this->media[0], $this->params, $this->template); ?>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>
                    <?php
                    echo Text::_('JBS_CMN_TITLE'); ?>
                </th>
                <th>
                    <?php
                    echo Text::_('JBS_CPL_DATE'); ?>
                </th>
                <th>

                </th>
            </tr>
            </thead>
            <?php
            foreach ($this->media as $item) {
                // Sparams are the server parameters
                $reg = new Joomla\Registry\Registry();
                $reg->loadString($item->sparams);
                $item->sparams = $reg;

                // Params are the individual params for the media file record
                $reg = new Joomla\Registry\Registry();
                $reg->loadString($item->params);
                $item->params = $reg;
                ?>
                <tr>
                    <?php
                    $path1 = Cwmhelper::mediaBuildUrl(
                        $item->sparams->get('path'),
                        $item->params->get('filename'),
                        $item->params,
                        true
                    ); ?>
                    <td>
                        <?php
                        echo stripslashes($item->studytitle); ?>
                    </td>
                    <td>
                        <?php
                        echo HtmlHelper::Date($item->createdate); ?>
                    </td>
                    <td class="row">
                        <a href="javascript:loadVideo('<?php
                        echo $path1; ?>', '<?php
                        echo $item->series_thumbnail; ?>')">
                            <?php
                            echo Text::_('JBS_CMN_LISTEN'); ?>
                        </a>
                    </td>
                </tr>
                <?php
            } ?></table>
        <?php
    } else { ?>
        <div style="clear: both"></div>
        <p><?php
            echo Text::_('JBS_CMN_NO_PODCASTS'); ?></p>
        <?php
    } ?>
</div>
