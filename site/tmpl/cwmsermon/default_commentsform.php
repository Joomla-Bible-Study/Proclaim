<?php

/**
 * Default CommonetForm
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HtmlHelper::_('behavior.keepalive');
?>

<?php
$commentjava = "javascript:ReverseDisplay2('JBScomments')";

switch ($this->item->params->get('link_comments', 0)) {
    case 0:
        echo '<div style="margin: auto;"></div><strong><a class="heading' . $this->item->params->get(
            'pageclass_sfx'
        ) . '" href="' . $commentjava . '"><i class="fas fa-comment fa-3x"></i>'
        . Text::_('JBS_CMT_SHOW_HIDE_COMMENTS') . '</a></strong></div>';
        ?>
<div id="JBScomments" style="display:none;">

        <?php
        break;
    case 1:
        echo '<div id="JBScomments">';
        break;
}
?>
    <div id="commentstable">
        <div class="container-fluid" style="padding-bottom: 10px;">

            <div class="row-fluid">
                <div class="col-lg-12" style="padding-bottom: 10px;">
                    <h2><?php
                        echo Text::_('JBS_CMN_COMMENTS'); ?></h2>
                </div>
            </div>

            <?php
            $input = Factory::getApplication()->input;

            if (!$this->item->id) {
                $this->item->id = $input->get('id', '', 'int');
            }


            if (!count($this->comments)) {
                ?>
                <div class="row-fluid">
                    <div class="col-12"><?php
                        echo Text::_('JBS_STY_NO_COMMENT') ?></div>
                </div>

                <?php
            } else {
                foreach ($this->comments as $comment) {
                    $comment_date_display = HtmlHelper::_('date', $comment->comment_date, Text::_('DATE_FORMAT_LC3'));
                    ?>
                    <div class="row-fluid">
                        <div class="col-lg-6"><strong><?php
                                echo $comment->full_name ?></strong> <i>
                                - <?php
                                echo $comment_date_display ?></i>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="col-lg-12"><?php
                            echo Text::_('JBS_CMN_COMMENT') . ': ' . $comment->comment_text ?></div>
                    </div>
                    <div class="row-fluid">
                        <div class="col-lg-12">
                            <hr/>
                        </div>
                    </div>
                    <?php
                } // End of foreach
                ?>


                <?php
            }
            ?>
            <?php
            // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
            $allow          = 0;
            $user           = $user = Factory::getApplication()->getSession()->get('user');
            $groups         = $user->getAuthorisedViewLevels();
            $show_comments  = $this->item->params->get('show_comments');
            $comment_access = $this->item->params->get('comment_access');

            if (in_array($show_comments, $groups)) {
                $allow = 9;
            }
            if (in_array($comment_access, $groups)) {
                $allow = 10;
            }

            if ($allow > 9) {
                ?>
        </div>
        <div class="container-fluid">
            <form action="index.php" method="post" class="form-validate form-horizontal well">

                <div class="row-fluid">
                    <?php
                    if ($allow < 10) {
                        echo Text::_('JBS_CMT_REGISTER_TO_POST_COMMENTS') ?>

                        <?php
                    }
                    if ($allow >= 10) {
                        ?>
                    <div class="col-lg-12">
                        <?php
                        if ($user->name) {
                            $full_name = $user->name;
                        } else {
                            $full_name = '';
                        }
                        if ($user->email) {
                            $user_email = $user->email;
                        } else {
                            $user_email = '';
                        }
                        ?><strong>
                            <?php
                            echo Text::_('JBS_CMT_POST_COMMENT') ?>
                        </strong>
                    </div>
                    <div class="row-fluid">
                        <div class="col-lg-2">
                            <?php
                            echo Text::_('JBS_CMT_FULL_NAME') ?>
                        </div>
                        <div class="col-lg-7">
                            <input class="text_area" size="50" type="text" name="full_name" id="full_name"
                                   value="<?php
                                    echo $full_name ?>"/>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="col-lg-2">
                            <?php
                            echo Text::_('JBS_CMT_EMAIL') ?>
                        </div>
                        <div class="col-lg-7">
                            <input class="text_area" type="text" name="user_email" id="user_email"
                                   value="<?php
                                    echo $user->email ?>"/>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="col-lg-2">
                            <?php
                            echo Text::_('JBS_CMN_COMMENT') ?>:
                        </div>
                        <div class="col-lg-7">
                    <textarea class="text_area" cols="20" rows="4" name="comment_text"
                              id="comment_text">
                    </textarea>
                        </div>
                    </div>

                    <div class="row-fluid">
                        <div class="col-lg-12">

                            <?php
                            foreach ($this->form->getFieldsets() as $fieldset) : ?>
                                <?php
                                if ($fieldset->name === 'captcha' && !$this->captchaEnabled) : ?>
                                    <?php
                                    continue; ?>
                                    <?php
                                endif; ?>
                                <?php
                                $fields = $this->form->getFieldset($fieldset->name); ?>
                                <?php
                                if (count($fields)) : ?>
                                    <fieldset class="m-0">
                                        <?php
                                        if (
                                            isset($fieldset->label) && ($legend = trim(
                                                Text::_($fieldset->label)
                                            )) !== ''
                                        ) : ?>
                                            <legend><?php
                                                echo $legend; ?></legend>
                                            <?php
                                        endif; ?>
                                        <?php
                                        foreach ($fields as $field) : ?>
                                            <?php
                                            echo $field->renderField(); ?>
                                            <?php
                                        endforeach; ?>
                                    </fieldset>
                                    <?php
                                endif; ?>
                                <?php
                            endforeach; ?>

                            <?php
                            $input = Factory::getApplication()->input;
                            $t     = $input->get('t'); ?>
                            <input type="hidden" name="study_id" id="study_id" value="<?php
                            echo $this->item->id ?>"/>
                            <input type="hidden" name="t" value="<?php
                            echo $t; ?>">
                            <input type="hidden" name="task" value="comment"/>
                            <input type="hidden" name="option" value="com_proclaim"/>
                            <input type="hidden" name="published" id="published"
                                   value="<?php
                                    echo $this->item->params->get('comment_publish') ?>"/>
                            <input type="hidden" name="view" value="sermon"/>

                            <input type="hidden" name="comment_date" id="comment_date"
                                   value="<?php
                                    echo date('Y-m-d H:i:s') ?>"/>
                            <input type="hidden" name="study_detail_id" id="study_detail_id"
                                   value="<?php
                                    echo $this->item->id ?>"/>

                            <input type="submit" class="button" id="button" value="Submit"/>
                            <?php
                    } // End of if $allow > 10
                    ?>
                        </div>
                    </div>
                </div>
            </form>

                <?php
            } // End if $allow > 9
            ?>
        </div>
    </div>



