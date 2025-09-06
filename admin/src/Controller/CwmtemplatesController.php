<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Input\Files;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Controller for Templates
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplatesController extends AdminController
{
    /**
     * Import Template
     *
     * @return CwmtemplatesController|integer
     *
     * @throws \Exception
     * @since 8.0
     */
    public function templateImport(): CwmtemplatesController|int
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Set Variables.
        $sermonstemplate        = null;
        $sermontemplate         = null;
        $teacherstemplate       = null;
        $teachertemplate        = null;
        $seriesdisplaystemplate = null;
        $seriesdisplaytemplate  = null;
        $moduletemplate         = null;

        set_time_limit(300);

        $input    = new Files();
        $userfile = $input->get('template_import');
        $app      = Factory::getApplication();
        $tc       = 0;

        // Make sure that file uploads are enabled in php
        if (!(bool)ini_get('file_uploads')) {
            $app->enqueueMessage(Text::_('JBS_CMN_UPLOADS_NOT_ENABLED'), 'warning');
            $this->setRedirect('index.php?option=com_proclaim&view=templates');
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile)) {
            $app->enqueueMessage(Text::_('JBS_CMN_NO_FILE_SELECTED'), 'warning');
            $this->setRedirect('index.php?option=com_proclaim&view=templates');
        }

        // Check if there was a problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            $app->enqueueMessage(Text::_('JBS_CMN_WARN_INSTALL_UPLOAD_ERROR'), 'warning');
            $this->setRedirect('index.php?option=com_proclaim&view=templates');
        }

        // Build the appropriate paths
        $tmp_dest = JPATH_SITE . '/tmp/' . $userfile['name'];

        $tmp_src = $userfile['tmp_name'];

        // Move uploaded file
        move_uploaded_file($tmp_src, $tmp_dest);

        $db = Factory::getContainer()->get('DatabaseDriver');

        $query   = file_get_contents(JPATH_SITE . '/tmp/' . $userfile['name']);
        $queries = DatabaseDriver::splitSql($query);

        if (count($queries) === 0) {
            // No queries to process
            return 0;
        }

        foreach ($queries as $querie) {
            $querie = trim($querie);

            if (substr_count($querie, 'INSERT')) {
                if ($querie !== '' && $querie[0] !== '#') {
                    // Check for duplicate names and change
                    if (substr_count($querie, '#__bsms_templatecode')) {
                        // Start to insert new Record
                        $this->performDB($querie);

                        // Get new  record insert to change name
                        $query = $db->getQuery(true);
                        $query->select('filename, id, type')
                            ->from('#__bsms_templatecode')
                            ->order($db->q('id') . ' DESC');
                        $db->setQuery($query, 0, 1);
                        $data  = $db->loadObject();
                        $query = $db->getQuery(true);
                        $query->update('#__bsms_styles')
                            ->set($db->qn('filename') . ' = ' . $db->q($data->filename . '_copy' . $data->id))
                            ->where($db->qn('id') . ' = ' . (int)$data->id);
                        $this->performDB($query);

                        $tc++;

                        // Store new Recorded so it can be seen.
                        $table = new CwmtemplatecodeTable($db);

                        try {
                            $table->load($data->id);
                            $table->store();
                        } catch (\Exception $e) {
                            echo 'Caught exception: ', $e->getMessage(), "\n";
                        }
                    } elseif (substr_count($querie, '#__bsms_templates')) {
                        // Start to insert new Record
                        $this->performDB($querie);

                        // Get new  record insert to change name
                        $query = $db->getQuery(true);
                        $query->select('id, title, params')
                            ->from('#__bsms_templates')
                            ->order($db->q('id') . ' DESC');
                        $db->setQuery($query, 0, 1);
                        $data  = $db->loadObject();
                        $query = $db->getQuery(true);
                        $query->update('#__bsms_templates')
                            ->set($db->qn('title') . ' = ' . $db->q($data->filename . '_copy' . $data->id))
                            ->where($db->qn('id') . ' = ' . (int)$data->id);
                        $this->performDB($query);
                    }
                }
            }
        }

        // Get new  record insert to change name
        $query = $db->getQuery(true);
        $query->select('id, type, filename')
            ->from('#__bsms_templatecode')
            ->order($db->q('id') . ' DESC');
        $db->setQuery($query, 0, $tc);
        $data = $db->loadObjectList();

        foreach ($data as $tpcode) {
            // Preload variables for templates
            $type = $tpcode->type;

            switch ($type) {
                case 1:
                    // Sermonlist
                    $sermonstemplate = $tpcode->filename;
                    break;
                case 2:
                    // Sermon
                    $sermontemplate = $tpcode->filename;
                    break;
                case 3:
                    // Teachers
                    $teacherstemplate = $tpcode->filename;
                    break;
                case 4:
                    // Teacher
                    $teachertemplate = $tpcode->filename;
                    break;
                case 5:
                    // Serieslist
                    $seriesdisplaystemplate = $tpcode->filename;
                    break;
                case 6:
                    // Series
                    $seriesdisplaytemplate = $tpcode->filename;
                    break;
                case 7:
                    // Module
                    $moduletemplate = $tpcode->filename;
                    break;
            }
        }

        // Get new record insert to change name
        $query = $db->getQuery(true);
        $query->select('id, title, params')
            ->from('#__bsms_templates')
            ->order('id');
        $db->setQuery($query, 1);
        $data = $db->loadObject();

        // Load Table Data.
        $table = new CwmtemplateTable($db);

        try {
            $table->load($data->id);
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        // Need to adjust the params and write back
        $registry = new Registry();
        $registry->loadString($table->params);
        $params = $registry;
        $params->set('sermonstemplate', $sermonstemplate);
        $params->set('sermontemplate', $sermontemplate);
        $params->set('teacherstemplate', $teacherstemplate);
        $params->set('teachertemplate', $teachertemplate);
        $params->set('seriesdisplaystemplate', $seriesdisplaystemplate);
        $params->set('seriesdisplaytemplate', $seriesdisplaytemplate);
        $params->set('moduletemplate', $moduletemplate);

        // Now write the params back into the $table array and store.
        $table->params = (string)$params->toString();

        $table->store();

        $message = Text::_('JBS_TPL_IMPORT_SUCCESS');

        return $this->setRedirect('index.php?option=com_proclaim&view=cwmtemplates', $message);
    }

    /**
     * Perform DB Query
     *
     * @param string $query Query
     *
     * @return void
     *
     * @since 8.0
     */
    private function performDB(string $query): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Export the Template
     *
     * @return CwmtemplatesController|false
     *
     * @since 8.0
     */
    public function templateExport(): bool|CwmtemplatesController
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $input          = new Input();
        $data           = $input->get('template_export');
        $exporttemplate = $data;

        if (!$exporttemplate) {
            $message = Text::_('JBS_TPL_NO_FILE_SELECTED');
            $this->setRedirect('index.php?option=com_proclaim&view=templates', $message);
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('t.id, t.type, t.params, t.title, t.text');
        $query->from('#__bsms_templates as t');
        $query->where('t.id = ' . $exporttemplate);
        $db->setQuery($query);
        $result       = $db->loadObject();
        $objects[]    = $this->getExportSetting($result);
        $filecontents = implode(' ', $objects);
        $filename     = $result->title . '.sql';
        $filepath     = JPATH_ROOT . '/tmp/' . $filename;

        if (!File::write($filepath, $filecontents)) {
            return false;
        }

        $xport = new Cwmbackup();
        $xport->outputFile($filepath, $filename, 'text/x-sql');
        File::delete($filepath);
        $message = Text::_('JBS_TPL_EXPORT_SUCCESS');

        return $this->setRedirect('index.php?option=com_proclaim&view=templates', $message);
    }

    /**
     * Get Exported Template Settings
     *
     * @param object $result ?
     *
     * @return string
     *
     * @since 8.0
     */
    private function getExportSetting($result): string
    {
        // Export must be in this order: css, template files, template.
        $registry = new Registry();
        $registry->loadString($result->params);
        $params  = $registry;
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $objects = '';
        $css     = $params->get('css');
        $css     = substr($css, 0, -4);

        if ($css) {
            $objects = "--\n-- CSS Style Code\n--\n";
            $query2  = $db->getQuery(true);
            $query2->select('style.*');
            $query2->from('#__bsms_styles AS style');
            $query2->where('style.filename = "' . $css . '"');
            $db->setQuery($query2);
            $db->execute();
            $cssresult = $db->loadObject();
            $objects .= "\nINSERT INTO #__bsms_styles SET `published` = '1',\n`filename` = " . $db->q(
                $cssresult->filename
            )
                . ",\n`stylecode` = " . $db->q($cssresult->stylecode) . ";\n";
        }

        // Get the individual template files
        $sermons = $params->get('sermonstemplate');

        if ($sermons) {
            $objects .= "\n--\n-- Sermons\n--";
            $objects .= $this->getTemplate($sermons);
        }

        $sermon = $params->get('sermontemplate');

        if ($sermon) {
            $objects .= "\n--\n-- Sermon\n--";
            $objects .= $this->getTemplate($sermon);
        }

        $teachers = $params->get('teacherstemplate');

        if ($teachers) {
            $objects .= "\n--\n-- Teachers\n--";
            $objects .= $this->getTemplate($teachers);
        }

        $teacher = $params->get('teachertemplate');

        if ($teacher) {
            $objects .= "\n--\n-- Teacher\n--";
            $objects .= $this->getTemplate($teacher);
        }

        $seriesdisplays = $params->get('seriesdisplaystemplate');

        if ($seriesdisplays) {
            $objects .= "\n--\n-- Seriesdisplays\n--";
            $objects .= $this->getTemplate($seriesdisplays);
        }

        $seriesdisplay = $params->get('seriesdisplaytemplate');

        if ($seriesdisplay) {
            $objects .= "\n--\n-- SeriesDisplay\n--";
            $objects .= $this->getTemplate($seriesdisplay);
        }

        $objects .= "\n\n--\n-- Template Table\n--\n";

        // Create the main template insert
        $objects .= "\nINSERT INTO #__bsms_templates SET `type` = " . $db->q($result->type) . ",";
        $objects .= "\n`params` = " . $db->q($result->params) . ",";
        $objects .= "\n`title` = " . $db->q($result->title) . ",";
        $objects .= "\n`text` = " . $db->q($result->text) . ";";

        $objects .= "\n-- --------------------------------------------------------\n\n";

        return $objects;
    }

    /**
     * Get Template Settings
     *
     * @param array $template ?
     *
     * @return bool|string
     *
     * @since 8.0
     */
    public function getTemplate($template): bool|string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('tc.id, tc.templatecode,tc.type,tc.filename');
        $query->from('#__bsms_templatecode as tc');
        $query->where('tc.filename ="' . $template . '"');
        $db->setQuery($query);

        if (!$object = $db->loadObject()) {
            return false;
        }

        $templatereturn = '
                        INSERT INTO `#__bsms_templatecode` SET `type` = "' . $db->escape($object->type) . '",
                        `templatecode` = "' . $db->escape($object->templatecode) . '",
                        `filename`="' . $db->escape($template) . '",
                        `published` = "1";
                        ';

        return $templatereturn;
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Cwmtemplate', $prefix = 'Administrator', $config = ['ignore_request' => true]): \Joomla\CMS\MVC\Model\BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }
}
