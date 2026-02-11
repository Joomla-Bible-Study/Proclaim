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
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplate\HtmlView $this */

// Create shortcut to parameters.
/** @type Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

// Add lazy loading script and pass CSRF token for AJAX calls
$wa->useScript('com_proclaim.template-lazyload');
$this->getDocument()->addScriptOptions('csrf.token', \Joomla\CMS\Session\Session::getFormToken());

// Add layout editor assets
$wa->useScript('bootstrap.modal')
    ->useScript('com_proclaim.sortable')
    ->useScript('com_proclaim.layout-editor')
    ->useStyle('com_proclaim.layout-editor');

// =====================================================================
// Layout Editor: ALL script options and language strings are registered
// HERE because the layout tab is lazy-loaded via AJAX (format=raw).
// addScriptOptions() and Text::script() in AJAX context don't reach
// the main page — they must be on the parent page before AJAX loads.
// =====================================================================

// Template params for initial loading
$tplParams = $this->item->params;

if (\is_string($tplParams)) {
    $paramsArray = json_decode($tplParams, true, 512, JSON_THROW_ON_ERROR) ?: [];
} elseif (\is_object($tplParams)) {
    $paramsArray = $tplParams->toArray();
} else {
    $paramsArray = (array) $tplParams;
}

// Element definitions extracted from template.xml (single source of truth)
$form = $this->form;

$getFieldsetLabel = function (string $fieldsetName) use ($form): ?string {
    $fieldsets = $form->getFieldsets();

    if (isset($fieldsets[$fieldsetName]) && !empty($fieldsets[$fieldsetName]->label)) {
        return Text::_($fieldsets[$fieldsetName]->label);
    }

    return null;
};

$deprecatedElements = ['scripture1', 'scripture2'];

$elementSectionMap = [
    'scripture1'        => 'JBS_TPL_SECTION_SCRIPTURE', 'scripture2' => 'JBS_TPL_SECTION_SCRIPTURE',
    'scriptures'        => 'JBS_TPL_SECTION_SCRIPTURE',
    'secondary'         => 'JBS_TPL_SECTION_SCRIPTURE',
    'title'             => 'JBS_TPL_SECTION_MESSAGE', 'date' => 'JBS_TPL_SECTION_MESSAGE',
    'studyintro'        => 'JBS_TPL_SECTION_MESSAGE', 'studynumber' => 'JBS_TPL_SECTION_MESSAGE',
    'duration'          => 'JBS_TPL_SECTION_MESSAGE',
    'teacher'           => 'JBS_TPL_SECTION_TEACHER', 'teacherimage' => 'JBS_TPL_SECTION_TEACHER',
    'teacher-title'     => 'JBS_TPL_SECTION_TEACHER', 'teachershort' => 'JBS_TPL_SECTION_TEACHER',
    'teacherlong'       => 'JBS_TPL_SECTION_TEACHER', 'teacherlargeimage' => 'JBS_TPL_SECTION_TEACHER',
    'teacherallinone'   => 'JBS_TPL_SECTION_TEACHER',
    'teacheremail'      => 'JBS_TPL_SECTION_CONTACT', 'teacherweb' => 'JBS_TPL_SECTION_CONTACT',
    'teacherphone'      => 'JBS_TPL_SECTION_CONTACT',
    'teacherfb'         => 'JBS_TPL_SECTION_SOCIAL', 'teachertw' => 'JBS_TPL_SECTION_SOCIAL',
    'teacherblog'       => 'JBS_TPL_SECTION_SOCIAL',
    'series'            => 'JBS_TPL_SECTION_SERIES', 'seriesthumbnail' => 'JBS_TPL_SECTION_SERIES',
    'seriesdescription' => 'JBS_TPL_SECTION_SERIES', 'description' => 'JBS_TPL_SECTION_SERIES',
    'jbsmedia'          => 'JBS_TPL_SECTION_MEDIA', 'thumbnail' => 'JBS_TPL_SECTION_MEDIA',
    'downloads'         => 'JBS_TPL_SECTION_MEDIA',
    'topic'             => 'JBS_TPL_SECTION_METADATA', 'locations' => 'JBS_TPL_SECTION_METADATA',
    'messagetype'       => 'JBS_TPL_SECTION_METADATA', 'hits' => 'JBS_TPL_SECTION_METADATA',
    'custom'            => 'JBS_TPL_SECTION_CUSTOM', 'dcustom' => 'JBS_TPL_SECTION_CUSTOM',
];

$extractElements = function (array $fieldsetNames, string $prefix) use ($form, $elementSectionMap, $deprecatedElements): array {
    $elements = [];
    $seen     = [];

    foreach ($fieldsetNames as $fieldsetName) {
        foreach ($form->getFieldset($fieldsetName) as $field) {
            $name = $field->fieldname;

            if (!str_ends_with($name, 'row')) {
                continue;
            }

            $elementId = substr($name, \strlen($prefix), -3);

            if (isset($seen[$elementId])) {
                continue;
            }
            $seen[$elementId] = true;

            $entry = [
                'id'      => $elementId,
                'label'   => Text::_($field->getAttribute('label')),
                'section' => Text::_($elementSectionMap[$elementId] ?? 'JBS_TPL_SECTION_OTHER'),
            ];

            if (\in_array($elementId, $deprecatedElements, true)) {
                $entry['deprecated'] = true;
            }

            $elements[] = $entry;
        }
    }

    usort($elements, function ($a, $b) {
        $cmp = strcmp($a['section'], $b['section']);

        return $cmp !== 0 ? $cmp : strcmp($a['label'], $b['label']);
    });

    return $elements;
};

$elementDefinitions = [
    'messages' => [
        'label'    => $getFieldsetLabel('DISPLAYELEMENTS1') ?? Text::_('JBS_TPL_MESSAGES_LIST'),
        'prefix'   => '',
        'elements' => $extractElements(
            ['DISPLAYELEMENTS1', 'DISPLAYELEMENTS2', 'DISPLAYELEMENTS3',
             'DISPLAYELEMENTS4', 'DISPLAYELEMENTS5', 'DISPLAYELEMENTS6'],
            ''
        ),
    ],
    'details' => [
        'label'    => $getFieldsetLabel('DDISPLAYELEMENTS1') ?? Text::_('JBS_TPL_STUDY_DETAILS'),
        'prefix'   => 'd',
        'elements' => $extractElements(
            ['DDISPLAYELEMENTS1', 'DDISPLAYELEMENTS2', 'DDISPLAYELEMENTS3',
             'DDISPLAYELEMENTS4', 'DDISPLAYELEMENTS5', 'DDISPLAYELEMENTS6'],
            'd'
        ),
    ],
    'teachers' => [
        'label'    => $getFieldsetLabel('TEACHERDISPLAY') ?? Text::_('JBS_TPL_TEACHERS_LIST'),
        'prefix'   => 'ts',
        'elements' => $extractElements(['TEACHERDISPLAY'], 'ts'),
    ],
    'teacherDetails' => [
        'label'    => $getFieldsetLabel('TEACHERDETAILSDISPLAY') ?? Text::_('JBS_TPL_TEACHER_DETAILS'),
        'prefix'   => 'td',
        'elements' => $extractElements(['TEACHERDETAILSDISPLAY'], 'td'),
    ],
    'series' => [
        'label'    => $getFieldsetLabel('SERIESDISPLAY') ?? Text::_('JBS_TPL_SERIES_LIST'),
        'prefix'   => 's',
        'elements' => $extractElements(['SERIESDISPLAY'], 's'),
    ],
    'seriesDetails' => [
        'label'    => $getFieldsetLabel('SERIESDETAILDISPLAY') ?? Text::_('JBS_TPL_SERIES_DETAILS'),
        'prefix'   => 'sd',
        'elements' => $extractElements(['SERIESDETAILDISPLAY'], 'sd'),
    ],
    'landingPage' => [
        'label'       => $getFieldsetLabel('LANDINGPAGE') ?? Text::_('JBS_TPL_LANDING_PAGE'),
        'prefix'      => '',
        'isOrderOnly' => true,
        'elements'    => (function () use ($form): array {
            $field    = $form->getField('headingorder_1', 'params');
            $sections = [];

            if ($field && ($element = $field->element ?? null)) {
                foreach ($element->option as $option) {
                    $sections[] = [
                        'id'         => (string) $option['value'],
                        'label'      => Text::_((string) $option),
                        'showParam'  => 'show' . (string) $option['value'],
                        'labelParam' => (string) $option['value'] . 'label',
                    ];
                }
            }

            if (empty($sections)) {
                $sections = [
                    ['id' => 'teachers', 'label' => Text::_('JBS_CMN_TEACHERS'), 'showParam' => 'showteachers', 'labelParam' => 'teacherslabel'],
                    ['id' => 'series', 'label' => Text::_('JBS_CMN_SERIES'), 'showParam' => 'showseries', 'labelParam' => 'serieslabel'],
                    ['id' => 'books', 'label' => Text::_('JBS_CMN_BOOKS'), 'showParam' => 'showbooks', 'labelParam' => 'bookslabel'],
                    ['id' => 'topics', 'label' => Text::_('JBS_CMN_TOPICS'), 'showParam' => 'showtopics', 'labelParam' => 'topicslabel'],
                    ['id' => 'locations', 'label' => Text::_('JBS_CMN_LOCATIONS'), 'showParam' => 'showlocations', 'labelParam' => 'locationslabel'],
                    ['id' => 'messagetypes', 'label' => Text::_('JBS_CMN_MESSAGETYPES'), 'showParam' => 'showmessagetypes', 'labelParam' => 'messagetypeslabel'],
                    ['id' => 'years', 'label' => Text::_('JBS_CMN_YEARS'), 'showParam' => 'showyears', 'labelParam' => 'yearslabel'],
                ];
            }

            return $sections;
        })(),
    ],
];
// Settings panel and landing section config from form fieldsets
$fieldsets              = $form->getFieldsets();
$settingsConfig         = [];
$landingSectionSettings = [];

foreach ($fieldsets as $fieldsetName => $fieldset) {
    if (!empty($fieldset->layoutContext)) {
        $contexts = array_map('trim', explode(',', $fieldset->layoutContext));
        $label    = !empty($fieldset->label) ? Text::_($fieldset->label) : $fieldsetName;

        foreach ($contexts as $context) {
            if (!isset($settingsConfig[$context])) {
                $settingsConfig[$context] = [];
            }
            $settingsConfig[$context][] = [
                'fieldset' => $fieldsetName,
                'label'    => $label,
            ];
        }
    }

    if (!empty($fieldset->landingSection)) {
        $label                                             = !empty($fieldset->label) ? Text::_($fieldset->label) : $fieldsetName;
        $landingSectionSettings[$fieldset->landingSection] = [
            'fieldset' => $fieldsetName,
            'label'    => $label,
        ];
    }
}

// Register all Layout Editor script options via centralized layout
\Joomla\CMS\Layout\LayoutHelper::render('layouteditor.scriptoptions', [
    'form'                   => $this->form,
    'templateId'             => (int) $this->item->id,
    'templateParams'         => $paramsArray,
    'elementDefinitions'     => $elementDefinitions,
    'settingsConfig'         => $settingsConfig,
    'landingSectionSettings' => $landingSectionSettings,
    'prependInherit'         => false,
], JPATH_COMPONENT_ADMINISTRATOR . '/layouts');
?>

<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('text'); ?>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo Text::_('JDETAILS'); ?></h5>
                        <?php echo $this->form->renderField('published'); ?>
                        <?php echo $this->form->renderField('id'); ?>
                    </div>
                </div>
            </div>
        </div>

        <hr />

        <div class="row">
            <div class="col-12">
                <h4><?php echo Text::_('JBS_TPL_TEMPLATES'); ?></h4>
            </div>
            <div class="col-lg-6">
                <?php
                $fields = $this->form->getFieldset('TEMPLATES');
$fieldArray             = iterator_to_array($fields);
$half                   = (int) ceil(\count($fieldArray) / 2);
$i                      = 0;
foreach ($fieldArray as $field) :
    if ($i === $half) {
        echo '</div><div class="col-lg-6">';
    }
    echo $this->form->renderField($field->fieldname, 'params');
    $i++;
endforeach;
?>
            </div>
        </div>

        <hr />

        <div class="row">
            <div class="col-12">
                <h4><?php echo Text::_('JBS_CMN_TERMS_SETTINGS'); ?></h4>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label for="jform_params_terms" class="form-label mb-0 fw-bold">
                        <?php echo Text::_('JBS_CMN_TERMS'); ?>
                    </label>
                    <div class="d-flex align-items-center gap-2">
                        <label for="jform_params_useterms" class="form-label mb-0 small text-muted">
                            <?php echo Text::_('JBS_CMN_USE_TERMS'); ?>
                        </label>
                        <?php echo $this->form->getInput('useterms', 'params'); ?>
                    </div>
                </div>
                <?php echo $this->form->getInput('terms', 'params'); ?>
                <div class="form-text small text-muted"><?php echo Text::_('JBS_CMN_TERMS_DESC'); ?></div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'layout', Text::_('JBS_TPL_LAYOUT_EDITOR')); ?>
        <div class="row">
            <div class="col-12">
                <!-- Layout Editor loads via AJAX when tab is shown for faster initial page load -->
                <div id="layout-editor-ajax-container"
                     data-load-url="<?php echo Route::_('index.php?option=com_proclaim&task=cwmtemplate.loadLayoutEditor&format=raw&id=' . (int) $this->item->id); ?>">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?php echo Text::_('JLIB_HTML_BEHAVIOR_LOADING'); ?></span>
                        </div>
                        <p class="mt-3 text-muted"><?php echo Text::_('JBS_TPL_LAYOUT_LOADING'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'media', Text::_('JBS_CMN_MEDIA')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php
$fields     = $this->form->getFieldset('MEDIA');
$fieldArray = iterator_to_array($fields);
$half       = (int) ceil(\count($fieldArray) / 2);
$i          = 0;
foreach ($fieldArray as $field) :
    if ($i === $half) {
        echo '</div><div class="col-lg-6">';
    }
    echo $this->form->renderField($field->fieldname, 'params');
    $i++;
endforeach;
?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'display-defaults', Text::_('JBS_TPL_DISPLAY_DEFAULTS')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php
foreach ($this->form->getFieldset('VERSES') as $field) :
    echo $this->form->renderField($field->fieldname, 'params');
endforeach;
?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($this->canDo->get('core.admin')) : ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row">
                <?php
echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>

        <input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
