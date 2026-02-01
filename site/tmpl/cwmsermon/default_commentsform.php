<?php

/**
 * Default CommentForm
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermon\HtmlView $this */

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

// Check permissions for comments
$user           = Factory::getApplication()->getIdentity();
$groups         = $user->getAuthorisedViewLevels();
$showComments   = (int) $this->item->params->get('show_comments', 0);
$commentAccess  = (int) $this->item->params->get('comment_access', 0);
$canView        = \in_array($showComments, $groups, true);
$canPost        = \in_array($commentAccess, $groups, true);
$linkComments   = (int) $this->item->params->get('link_comments', 0);
$pageclassSfx   = htmlspecialchars($this->item->params->get('pageclass_sfx', ''), ENT_QUOTES, 'UTF-8');

// Ensure we have the study ID
$input = Factory::getApplication()->getInput();

if (!$this->item->id) {
    $this->item->id = $input->getInt('id', 0);
}
?>

<?php if ($linkComments === 0) : ?>
    <div class="proclaim-comments-toggle mb-3">
        <button type="button"
                class="btn btn-outline-secondary heading<?php echo $pageclassSfx; ?>"
                data-bs-toggle="collapse"
                data-bs-target="#JBScomments"
                aria-expanded="false"
                aria-controls="JBScomments">
            <span class="icon-comment" aria-hidden="true"></span>
            <?php echo Text::_('JBS_CMT_SHOW_HIDE_COMMENTS'); ?>
        </button>
    </div>
    <div class="collapse" id="JBScomments">
<?php else : ?>
    <div id="JBScomments">
<?php endif; ?>

        <div id="commentstable" class="card">
            <div class="card-header">
                <h2 class="h4 mb-0"><?php echo Text::_('JBS_CMN_COMMENTS'); ?></h2>
            </div>

            <div class="card-body">
                <?php if (empty($this->comments)) : ?>
                    <p class="text-muted"><?php echo Text::_('JBS_STY_NO_COMMENT'); ?></p>
                <?php else : ?>
                    <div class="comments-list">
                        <?php foreach ($this->comments as $comment) : ?>
                            <?php $commentDate = HTMLHelper::_('date', $comment->comment_date, Text::_('DATE_FORMAT_LC3')); ?>
                            <div class="comment-item border-bottom pb-3 mb-3">
                                <div class="comment-meta mb-2">
                                    <strong class="comment-author"><?php echo htmlspecialchars($comment->full_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span class="text-muted ms-2">
                                        <time datetime="<?php echo HTMLHelper::_('date', $comment->comment_date, 'c'); ?>">
                                            <?php echo $commentDate; ?>
                                        </time>
                                    </span>
                                </div>
                                <div class="comment-text">
                                    <?php echo $comment->comment_text; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($canPost) : ?>
                <div class="card-footer">
                    <h3 class="h5 mb-3"><?php echo Text::_('JBS_CMT_POST_COMMENT'); ?></h3>

                    <form action="<?php echo htmlspecialchars(Uri::getInstance()->toString(), ENT_QUOTES, 'UTF-8'); ?>"
                          method="post"
                          name="commentForm"
                          id="commentForm"
                          class="form-validate">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">
                                    <?php echo Text::_('JBS_CMT_FULL_NAME'); ?>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       name="full_name"
                                       id="full_name"
                                       value="<?php echo htmlspecialchars($user->name ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="user_email" class="form-label">
                                    <?php echo Text::_('JBS_CMT_EMAIL'); ?>
                                </label>
                                <input type="email"
                                       class="form-control"
                                       name="user_email"
                                       id="user_email"
                                       value="<?php echo htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="col-12">
                                <label for="comment_text" class="form-label">
                                    <?php echo Text::_('JBS_CMN_COMMENT'); ?>
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control"
                                          name="comment_text"
                                          id="comment_text"
                                          rows="4"
                                          required></textarea>
                            </div>

                            <?php // Render captcha and other form fields?>
                            <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                                <?php if ($fieldset->name === 'captcha' && !$this->captchaEnabled) : ?>
                                    <?php continue; ?>
                                <?php endif; ?>

                                <?php $fields = $this->form->getFieldset($fieldset->name); ?>
                                <?php if (\count($fields)) : ?>
                                    <div class="col-12">
                                        <?php if (isset($fieldset->label) && ($legend = trim(Text::_($fieldset->label))) !== '') : ?>
                                            <h4 class="h6"><?php echo $legend; ?></h4>
                                        <?php endif; ?>

                                        <?php foreach ($fields as $field) : ?>
                                            <?php echo $field->renderField(); ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <span class="icon-check" aria-hidden="true"></span>
                                    <?php echo Text::_('JSUBMIT'); ?>
                                </button>
                            </div>
                        </div>

                        <?php // Hidden fields?>
                        <input type="hidden" name="option" value="com_proclaim">
                        <input type="hidden" name="task" value="cwmsermon.comment">
                        <input type="hidden" name="study_id" value="<?php echo $this->item->id; ?>">
                        <input type="hidden" name="study_detail_id" value="<?php echo $this->item->id; ?>">
                        <input type="hidden" name="t" value="<?php echo $input->getInt('t', 0); ?>">
                        <input type="hidden" name="published" value="<?php echo (int) $this->item->params->get('comment_publish', 1); ?>">
                        <input type="hidden" name="comment_date" value="<?php echo (new Date())->toSql(); ?>">
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </form>
                </div>
            <?php elseif ($canView) : ?>
                <div class="card-footer">
                    <p class="text-muted mb-0">
                        <span class="icon-lock" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_CMT_REGISTER_TO_POST_COMMENTS'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

    </div>
