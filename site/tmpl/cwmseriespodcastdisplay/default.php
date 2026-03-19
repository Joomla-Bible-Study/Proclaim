<?php

/**
 * Layout for Series Podcast Display
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** @var CWM\Component\Proclaim\Site\View\Cwmseriespodcastdisplay\HtmlView $this */

$CWMedia = new Cwmmedia();
?>
<div class="com-proclaim">
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-6">
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
                <div class="col-12 col-lg-6">
                    <?php
                    $this->params->set('player_width', ''); ?>
                    
                    <div id="audio-loading" style="display:none; margin-bottom: 10px;"></div>
                    
                    <?php
                    echo $CWMedia->getFluidMedia($this->media[0], $this->params, $this->template); ?>

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
                            $reg = new Registry();
                            $reg->loadString($item->sparams);
                            $item->sparams = $reg;

                            // Params are the individual params for the media file record
                            $reg = new Registry();
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
                                    echo htmlspecialchars(stripslashes($item->studytitle), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td>
                                    <?php
                                    echo HTMLHelper::Date($item->createdate); ?>
                                </td>
                                <td>
                                    <a href="javascript:loadVideo(<?php
                                    echo htmlspecialchars(json_encode($path1), ENT_QUOTES, 'UTF-8'); ?>, <?php
                                    echo htmlspecialchars(json_encode($item->series_thumbnail), ENT_QUOTES, 'UTF-8'); ?>)">
                                        <?php
                                        echo Text::_('JBS_CMN_LISTEN'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        } ?>
                    </table>
                    
                    <?php
                    // Add pagination
                    if ($this->pagination->pagesTotal > 1) : ?>
                        <div class="proclaim-pagination-bar">
                            <span class="proclaim-page-counter"><?php echo $this->pagination->getPagesCounter(); ?></span>
                            <?php echo $this->pagination->getPagesLinks(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            } else { ?>
                <div class="col-12 col-lg-6">
                    <p><?php
                        echo Text::_('JBS_CMN_NO_PODCASTS'); ?></p>
                </div>
                <?php
            } ?>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>
