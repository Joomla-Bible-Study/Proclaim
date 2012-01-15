<?php
/**
 * @version $Id: default_messages.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

$messages = JRequest::getVar('jbsmessages', null, 'get', 'array');

foreach ($messages AS $message) {
    ?>
    <div>
        <fieldset class="panelform">

    <?php
    echo JHtml::_('sliders.start', 'content-sliders-migration', array('useCookie' => 1));
    echo JHtml::_('sliders.panel', JText::_('JBS_MIGRATE_VERSION') . ' ' . $message['build'], 'publishing-details');
    ?>
            <?php
            if (is_array($message)) {
                foreach ($message AS $msg) {
                    if (is_array($msg)) {
                        foreach ($msg AS $m) {
                            echo $m;
                        }
                    } else {
                        echo $msg;
                    }
                }
            } else {
                print_r($message);
            }
            ?>

            <?php echo JHtml::_('sliders.end'); ?>
        </fieldset>
    </div>

            <?php
        }