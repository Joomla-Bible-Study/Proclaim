<?php

/**
 * Teacher JViewLegacy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// @todo need to clean up all the includes with better calling in the whay we need the healpers.
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'params.php');
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'teacher.php');
include_once($path1 . 'listing.php');


/**
 * View class for Teacher
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyViewTeacher extends JViewLegacy
{

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{

		$app = JFactory::getApplication();
		$pagebuilder = new JBSPagebuilder();
		JViewLegacy::loadHelper('image');
		$document = JFactory::getDocument();
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
		$pathway = $app->getPathWay();
		$images = new jbsImages();

		//Load the Admin settings and params from the template
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$this->loadHelper('params');
		$this->admin = JBSMParams::getAdmin();
		$this->admin_params = $this->admin->params;

		$itemparams = $app->getPageParameters();

		//Prepare meta information (under development)
		if ($itemparams->get('metakey')) {
			$document->setMetadata('keywords', $itemparams->get('metakey'));
		} elseif (!$itemparams->get('metakey')) {
			$document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

		if ($itemparams->get('metadesc')) {
			$document->setDescription($itemparams->get('metadesc'));
		} elseif (!$itemparams->get('metadesc')) {
			$document->setDescription($this->admin_params->get('metadesc'));
		}
		$input = new JInput;
		$t = $input->get('t', 1, 'int');
		if (!$t) {
			$t = 1;
		}
		$template = JBSMParams::getTemplateparams();
		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($template->params);
		$params = $registry;

		$css = $params->get('css');
		if ($css <= "-1"):
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css'); else:
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		endif;

		$item = $this->get('Item');
		//add the slug
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : str_replace(' ', '-', htmlspecialchars_decode($item->teachername, ENT_QUOTES)) . ':' . $item->id;
		$id = $input->get('id', '', 'get');
		if ($id) {
			$item->id = $id;
		}
		$image = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
		$largeimage = $images->getTeacherImage($item->image, $item->teacher_image);
		$item->image = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$item->largeimage = '<img src="' . $largeimage->path . '" height="' . $largeimage->height . '" width="' . $largeimage->width . '" alt="" />';
		if (isset($item->information)) {
			$item->text = $item->information;
			$information = $pagebuilder->runContentPlugins($item, $params);
			$item->information = $information->text;
		}
		if (isset($item->short)) {
			$item->text = $item->short;
			$short = $pagebuilder->runContentPlugins($item, $params);
			$item->short = $short->text;
		}
		//Check to see if com_contact used instead
		if ($item->contact) {
			require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_contact' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'contact.php';
			$contactmodel = JModel::getInstance('contact', 'contactModel');
			$this->contact = $contactmodel->getItem($pk = $item->contact);
			//Substitute contact info from com_contacts for duplicate fields
			$item->title = $this->contact->con_position;
			$item->teachername = $this->contact->name;
			$item->email = $this->contact->email_to;
			$largeimage = $images->getImagePath($this->contact->image);
			$item->largeimage = '<img src="' . $largeimage->path . '" height="' . $largeimage->height . '" <width="' . $largeimage->width . '" alt="" />';
			$item->information = $this->contact->misc;
			$item->phone = $this->contact->telephone;
			$cregistry = new JRegistry();
			$cregistry->loadString($this->contact->params);
			$contact_params = $cregistry;
			$item->facebooklink = $contact_params->get('linka');
			$item->twitterlink = $contact_params->get('linkb');
			$item->bloglink = $contact_params->get('linkc');
			$item->link1 = $contact_params->get('linkd');
			$item->linklabel1 = $contact_params->get('linklabel1');
			$item->link2 = $contact_params->get('linke');
			$item->linklabel2 = $contact_params->get('linke_name');
			$item->website = $this->contact->webpage;
			$item->address = $this->contact->address;
		}

		$this->item = $item;

		$whereitem = intval($item->id);
		$wherefield = 'study.teacher_id';
		$limit = $params->get('studies', '20');
		$order = 'DESC';
		//var_dump($whereitem, $wherefield, $params, $this->admin_params, $limit, $order);
		if ($params->get('show_teacher_studies') > 0) {
			$studies = $pagebuilder->studyBuilder($whereitem, $wherefield, $params, $this->admin_params, $limit, $order);
			foreach ($studies as $i => $study) {
				$pelements = $pagebuilder->buildPage($study, $params, $this->admin_params);
				$studies[$i]->scripture1 = $pelements->scripture1;
				$studies[$i]->scripture2 = $pelements->scripture2;
				$studies[$i]->media = $pelements->media;
				$studies[$i]->duration = $pelements->duration;
				$studies[$i]->studydate = $pelements->studydate;
				$studies[$i]->topics = $pelements->topics;
				if (isset($pelements->study_thumbnail)):
					$studies[$i]->study_thumbnail = $pelements->study_thumbnail; else:
					$studies[$i]->study_thumbnail = null;
				endif;
				if (isset($pelements->series_thumbnail)):
					$studies[$i]->series_thumbnail = $pelements->series_thumbnail; else:
					$studies[$i]->series_thumbnail = null;
				endif;
				$studies[$i]->detailslink = $pelements->detailslink;
				if (!isset($pelements->studyintro)) {
					$pelements->studyintro = '';
				}
				$studies[$i]->studyintro = $pelements->studyintro;
				if (isset($pelements->secondary_reference)) {
					$studies[$i]->secondary_reference = $pelements->secondary_reference;
				} else {
					$studies[$i]->secondary_reference = '';
				}
				if (isset($pelements->sdescription)) {
					$studies[$i]->sdescription = $pelements->sdescription;
				} else {
					$studies[$i]->sdescription = '';
				}
			}
			$this->teacherstudies = $studies;
		}

		$this->item = $item;
		$print = $input->get('print', '', 'bool');
		// build the html select list for ordering
		$this->print = $print;
		$this->params = $params;
		$this->template = $template;
		parent::display($tpl);
	}

}