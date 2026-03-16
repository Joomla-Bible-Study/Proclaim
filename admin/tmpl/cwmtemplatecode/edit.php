<?php

/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplatecode\HtmlView $this */

$app   = Factory::getApplication();
$input = $app->getInput();

// Set up defaults
if ($input->getInt('a_id')) {
    $templatecode = $this->item->templatecode;
} else {
    $templatecode = $this->defaultcode;
}

$wa = $this->getDocument()->getWebAssetManager();
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmtemplatecode.cancel', 'formId' => 'item-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit')
    ->useScript('com_proclaim.templatecode-snippets');
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <!-- Begin Content -->
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('filename'); ?>

                <?php
                if ($this->item->id != 0) {
                    $this->form->setFieldAttribute('type', 'disabled', 'true');
                }
                echo $this->form->renderField('type');
                if ($this->item->id != 0) {
                    // Disabled fields don't submit; preserve the value
                    echo '<input type="hidden" name="jform[type]" value="'
                        . (int) $this->item->type . '">';
                }
                ?>
                <?php echo $this->form->renderField('templatecode', null, $templatecode); ?>
            </div>

            <!-- Begin Sidebar -->
            <div class="col-lg-3">
                <div style="position: sticky; top: 70px;">
                    <?php echo $this->form->renderField('id'); ?>
                    <?php echo $this->form->renderField('published'); ?>

                    <!-- Code Snippet Insertion Panel -->
                    <div class="card mt-3" id="snippetPanel"
                         data-current-type="<?php echo (int) $this->item->type; ?>">
                        <div class="card-header p-2">
                            <span class="icon-code me-1" aria-hidden="true"></span>
                            <?php echo Text::_('JBS_TPLCODE_SNIPPETS'); ?>
                        </div>
                        <div class="card-body p-2" style="max-height: 60vh; overflow-y: auto;">
                            <?php
                            // Snippet categories with their type mappings and code buttons
                            // Types: 1=Sermon List, 2=Sermon, 3=Teachers, 4=Teacher, 5=Series Displays, 6=Series Display, 7=Module
                            $snippetCategories = [
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_STUDY_LOOP'),
                                    'types' => '1,2,3,4,5,6,7',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_LOOP'), 'code' => 'foreach ($this->items as $study) {' . "\n" . '    // your code here' . "\n" . '}'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TITLE'), 'code' => 'echo $study->studytitle;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_DATE'), 'code' => 'echo $study->studydate;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_INTRO'), 'code' => 'echo $study->studyintro;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SCRIPTURE1'), 'code' => 'echo $study->scripture1;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SCRIPTURE2'), 'code' => 'echo $study->scripture2;'],
                                        ['label' => Text::_('JBS_CMN_ALL_SCRIPTURES'), 'code' => 'echo $study->scriptures;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_MEDIA'), 'code' => 'echo $study->media;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_DURATION'), 'code' => 'echo $study->duration;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TOPICS'), 'code' => 'echo $study->topics;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_DETAILS_LINK'), 'code' => 'echo $study->detailslink;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TEACHER_NAME'), 'code' => 'echo $study->teachername;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TEACHER_IMAGE'), 'code' => 'echo $study->teacherimage;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_LOCATION'), 'code' => 'echo $study->location_text;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_HITS'), 'code' => 'echo $study->hits;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_THUMBNAIL'), 'code' => 'echo $study->study_thumbnail;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_NUMBER'), 'code' => 'echo $study->studynumber;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SLUG'), 'code' => 'echo $study->slug;'],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_PAGE_CONTROLS'),
                                    'types' => '1,3,5',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SEARCH_TOOLS'), 'code' => 'echo $this->page->searchtools;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_PAGINATION'), 'code' => 'echo $this->page->pagelinks;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_LIMIT_BOX'), 'code' => 'echo $this->page->limitbox;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_LIST_INTRO'), 'code' => "echo \$this->params->get('list_intro');"],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_SERMON_DETAIL'),
                                    'types' => '2',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_FULL_TEXT'), 'code' => 'echo $this->item->studytext;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_PASSAGE'), 'code' => 'echo $this->passage;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_RELATED'), 'code' => 'echo $this->related;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_PRINT'), 'code' => 'echo $this->page->print;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SOCIAL'), 'code' => 'echo $this->page->social;'],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_TEACHER_LIST'),
                                    'types' => '3',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TEACHER_LOOP'), 'code' => 'foreach ($this->items as $teacher) {' . "\n" . '    // your code here' . "\n" . '}'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TEACHER_NAME'), 'code' => 'echo $teacher->teachername;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TEACHER_IMAGE'), 'code' => 'echo $teacher->image;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_TEACHER_LINK'), 'code' => 'echo $teacher->teacherlink;'],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_TEACHER_DETAIL'),
                                    'types' => '4',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_TITLE'), 'code' => 'echo $this->item->title;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_BIO'), 'code' => 'echo $this->item->information;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_INTRO'), 'code' => 'echo $this->item->short;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_IMAGE'), 'code' => 'echo $this->item->image;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_PHONE'), 'code' => 'echo $this->item->phone;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_EMAIL'), 'code' => 'echo $this->item->email;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_WEBSITE'), 'code' => 'echo $this->item->website;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_T_STUDIES'), 'code' => 'foreach ($this->teacherstudies as $study) {' . "\n" . '    // use study codes' . "\n" . '}'],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_SERIES_LIST'),
                                    'types' => '5',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SERIES_LOOP'), 'code' => 'foreach ($this->items as $series) {' . "\n" . '    // your code here' . "\n" . '}'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SERIES_TITLE'), 'code' => 'echo $series->series_text;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SERIES_DESC'), 'code' => 'echo $series->description;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SERIES_IMAGE'), 'code' => 'echo $series->image;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SERIES_LINK'), 'code' => 'echo $series->serieslink;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SERIES_DROPDOWN'), 'code' => 'echo $this->page->series;'],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_SERIES_DETAIL'),
                                    'types' => '6',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SD_TITLE'), 'code' => 'echo $this->page->series_text;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SD_IMAGE'), 'code' => 'echo $this->page->image;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SD_DESC'), 'code' => 'echo $this->page->description;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SD_TEACHER'), 'code' => 'echo $this->page->teachername;'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_SD_STUDIES'), 'code' => 'foreach ($this->seriesstudies as $study) {' . "\n" . '    // use study codes' . "\n" . '}'],
                                    ],
                                ],
                                [
                                    'title' => Text::_('JBS_TPLCODE_SNIPPETS_MODULE'),
                                    'types' => '7',
                                    'snippets' => [
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_MOD_LOOP'), 'code' => 'foreach ($list as $study) {' . "\n" . '    // your code here' . "\n" . '}'],
                                        ['label' => Text::_('JBS_TPLCODE_SNIPPET_MOD_LINK'), 'code' => 'echo $link;'],
                                    ],
                                ],
                            ];

                            foreach ($snippetCategories as $category) : ?>
                                <div class="snippet-category mb-2" data-types="<?php echo $category['types']; ?>">
                                    <small class="text-muted fw-bold d-block mb-1"><?php
                                        echo $category['title']; ?></small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($category['snippets'] as $snippet) : ?>
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm snippet-insert-btn"
                                                    data-snippet="<?php echo $this->escape($snippet['code']); ?>"
                                                    title="<?php echo $this->escape($snippet['code']); ?>">
                                                <?php echo $snippet['label']; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Sidebar -->
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo LayoutHelper::render('edit.publish_tab', $this); ?>

        <?php echo LayoutHelper::render('edit.permissions_tab', ['form' => $this->form, 'canDo' => $this->canDo, 'tabName' => 'myTab']); ?>

        <input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
