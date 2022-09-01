<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;

/**
 * Template controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTemplateController extends FormController
{
	/**
	 * Copy Template
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function copy()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		ArrayHelper::toInteger($cid);

		$baseDatabaseModel = $this->getModel('template');
		$model             = &$baseDatabaseModel;

		if ($model->copy($cid))
		{
			$msg = Text::_('JBS_TPL_TEMPLATE_COPIED');
		}
		else
		{
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_proclaim&view=templates', $msg);
	}

	/**
	 * Make Template Default
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function makeDefault()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$cid   = $input->get('cid', array(0), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			$app->enqueueMessage(Text::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'), 'error');
		}

		$model = $this->getModel('template');

		if (!$model->makeDefault($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_proclaim&view=cwmtemplates');
	}

	/**
	 * Get Template Settings
	 *
	 * @param   string  $template  filename
	 *
	 * @return boolean|string
	 *
	 * @since      7.0
	 *
	 * @deprecated 8.0.0 Not used in scope bcc
	 */
	public function getTemplate($template)
	{
		$db     = Factory::getContainer()->get('DatabaseDriver');
		$query  = $db->getQuery(true);
		$query->select('tc.id, tc.templatecode,tc.type,tc.filename');
		$query->from('#__bsms_templatecode as tc');
		$query->where('tc.filename ="' . $template . '"');
		$db->setQuery($query);

		if (!$object = $db->loadObject())
		{
			return false;
		}

		$templatereturn = '
                        INSERT INTO `#__bsms_templatecode` SET `type` = ' . $db->q($object->type) . ',
                        `templatecode` = ' . $db->q($object->templatecode) . ',
                        `filename` = ' . $db->q($template) . ',
                        `published` = ' . $db->q('1');

		return $templatereturn;
	}
}
