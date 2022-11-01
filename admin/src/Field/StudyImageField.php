<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Administrator\Field;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Field\MediaField;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;


/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class StudyImageField extends MediaField
{

//The field class must know its own type through the variable $type.
	protected $type = 'StudyImageField';

	public function getInput() {
		// code that returns HTML that will be shown as the form field
	$form = 	FormHelper::loadFieldClass('media');

			$db = Factory::getContainer()->get('DatabaseDriver');
			$query  = $db->getQuery(true);
			$query->select('*')
				->from('#__extensions')
				->where('name = "plg_filesystem_local"');
			$db->setQuery($query);
			$local = $db->loadObject();
			$pparams = $local->params;
			$ismedia = substr_count($pparams, 'media');
			if ($ismedia !== 1)
			{
				if ($pparams == '{}' || is_null($pparams))
				{
					$newmedia = '{"directories":{"directories0":{"directory":"media"}}}';
					$newmedia = addslashes($newmedia);
				}
				else{
					$dircount = substr_count($pparams, 'directory');
					$end = strlen($pparams);
					$getstring = substr($pparams, 0, $end - 2);
					$newmedia = $getstring.', "directories'.$dircount.'":{"directory":"media"}}}';
					$newmedia = addslashes($newmedia);
				}
				$query = $db->getQuery(true);
				$query->update('#__extensions')
					->set('params = "'.$newmedia.'"')
					->where('name = "plg_filesystem_local"');
				$db->setQuery($query);
				$db->execute();
			}
			//$form = '<field name="studyimage" type="media" directory="/media/com_proclaim/images/stockimages" label="CWM_STOCK_IMAGE" hide_default="true" />';
			return parent::getInput();
	}

}
