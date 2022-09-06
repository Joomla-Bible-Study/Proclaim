<?php
/**
 * @package    BibleStudy.Module
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;

/**
 * Script file of HelloWorld module
 *
 * @package  Proclaim.Admin
 * @since    9.0.1
 */
class JoomlaInstallerScript
{
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return void
	 *
	 * @since    7.0.0
	 */
	public function install($parent)
	{
		echo '<p>The module has been installed</p>';
	}

	/**
	 * Method to uninstall the extension
	 * $parent is the class calling this method
	 *
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return void
	 *
	 * @since    7.0.0
	 */
	public function uninstall($parent)
	{
		echo '<p>The module has been uninstalled</p>';
	}

	/**
	 * Method to update the extension
	 * $parent is the class calling this method
	 *
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return void
	 *
	 * @since    7.0.0
	 */
	public function update($parent)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__modules')
			->where($db->quoteName('module') . ' LIKE ' . $db->quote('%mod_biblestudy'));
		$db->setQuery($query);
		$data       = $db->loadObjectList();
		$filenumber = 1;

		foreach ($data as $d)
		{
			$registry = new Registry;
			$registry->loadString($d->params);

			if ($registry->get('useexpert_module') > 0)
			{
				$dataheaderlist        = $registry->get('module_headercode');
				$dataitemlist          = $registry->get('templatecode');
				$dataheaderlist        = $this->itemreplace($dataheaderlist);
				$dataitemlist          = $this->itemreplace($dataitemlist);
				$filecontent           = '<?php defined(\'_JEXEC\') or die; ?>' . $dataheaderlist . '<?php foreach ($list as $study){ ?>' .
					$dataitemlist . '<?php } ?>';
				$filename              = 'default_moduletemplate_' . $filenumber;
				$file                  = JPATH_ROOT . '/modules/mod_proclaim/tmpl/' . $filename . '.php';
				File::write($file, $filecontent);
				$profile               = new stdClass;
				$profile->published    = 1;
				$profile->type         = 7;
				$profile->filename     = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id     = '';
				$db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('moduletemplate', $filename);
			}

			$d->params = $registry->toString();
			$db->updateObject('#__modules', $d, 'id');
			$filenumber++;
		}

		echo '<p>The module has been updated to version' . $parent->get('manifest')->version . '</p>';
	}

	/**
	 * Method to run before an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @param   string          $type    Type of install
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return void
	 *
	 * @since    7.0.0
	 */
	public function preflight($type, $parent)
	{
		echo '<p>Anything here happens before the installation/update/uninstallation of the module</p>';
	}

	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @param   string          $type    Type of install
	 * @param   JInstallerFile  $parent  Where it is coming from
	 *
	 * @return void
	 *
	 * @since    7.0.0
	 */
	public function postflight($type, $parent)
	{
		echo '<p>Anything here happens after the installation/update/uninstallation of the module</p>';
	}

	/**
	 * Item Replacemnet
	 *
	 * @param   string  $item  ?
	 *
	 * @return mixed
	 *
	 * @since    7.0.0
	 */
	private function itemreplace($item)
	{
		$item = str_replace('{{teacher}}', '<?php echo $study->teachername; ?>', $item);
		$item = str_replace('{{teachertitle}}', '<?php echo $this->item->title; ?>', $item);
		$item = str_replace('{{teachername}}', '<?php echo $this->item->teachername; ?>', $item);
		$item = str_replace('{{teachertitlelist}}', '<?php echo $teacher->title; ?>', $item);
		$item = str_replace('{{teachernamelist}}', '<?php echo $teacher->teachername; ?>', $item);
		$item = str_replace('{{title}}', '<?php echo $study->studytitle; ?>', $item);
		$item = str_replace('{{date}}', '<?php echo $study->studydate; ?>', $item);
		$item = str_replace('{{studyintro}}', '<?php echo $study->studyintro; ?>', $item);
		$item = str_replace('{{scripture}}', '<?php echo $study->scripture1; ?>', $item);
		$item = str_replace('{{topics}}', '<?php echo $study->topics; ?>', $item);
		$item = str_replace('{{scripture}}', '<?php echo $study->scripture1; ?>', $item);
		$item = str_replace('{{url}}', '<?php echo $study->detailslink; ?>', $item);
		$item = str_replace('{{mediatime}}', '<?php echo $study->duration; ?>', $item);
		$item = str_replace('{{thumbnail}}', '<?php echo $study->study_thumbnail; ?>', $item);
		$item = str_replace('{{seriestext}}', '', $item);
		$item = str_replace('{{bookname}}', '<?php echo $study->scripture1; ?>', $item);
		$item = str_replace('{{hits}}', '<?php echo $study->hits;', $item);
		$item = str_replace('{{location}}', '<?php echo $study->location_text; ?>', $item);
		$item = str_replace('{{plays}}', '<?php echo $study->totaplays; ?>', $item);
		$item = str_replace('{{downloads}}', '<?php echo $study->totaldownloads; ?>', $item);
		$item = str_replace('{{media}}', '<?php echo $study->media; ?>', $item);
		$item = str_replace('{{messagetype}}', '<?php echo $study->messagetypes; ?>', $item);
		$item = str_replace('{{studytext}}', '<?php echo $this->item->studytext; ?>', $item);
		$item = str_replace('{{scipturelink}}', '<?php  echo $this->passage; ?>', $item);
		$item = str_replace('{{share}}', '<?php echo $this->page->social; ?>', $item);
		$item = str_replace('{{printview}}', '<?php echo $this->page->print; ?>', $item);
		$item = str_replace('{{pdfview}}', '', $item);
		$item = str_replace('{{phone}}', '<?php echo $this->item->phone; ?>', $item);
		$item = str_replace('{{teacherphonelist}}', '<?php echo $teacher->phone; ?>', $item);
		$item = str_replace('{{website}}', '<?php echo $this->item->website; ?>', $item);
		$item = str_replace('{{teacherwebsitelist}}', '<?php echo $teacher->website; ?>', $item);
		$item = str_replace('{{information}}', '<?php echo $this->item->information; ?>', $item);
		$item = str_replace('{{teacherinformationlist}}', '<?php echo $teacher->information; ?>', $item);
		$item = str_replace('{{image}}', '<?php echo $this->item->largeimage; ?>', $item);
		$item = str_replace('{{teacherimagelist}}', '<?php echo $teacher->largeimage; ?>', $item);
		$item = str_replace('{{thumbnail}}', '<?php echo $this->item->image; ?>', $item);
		$item = str_replace('{{teacherthumbnaillist}}', '<?php echo $teacher->image; ?>', $item);
		$item = str_replace('{{short}}', '<?php echo $this->item->short; ?>', $item);
		$item = str_replace('{{teachershortlist}}', '<?php echo $teacher->short; ?>', $item);

		return $item;
	}
}
