<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMPagebuilder', BIBLESTUDY_PATH_LIB . '/biblestudy.pagebuilder.class.php');
JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('JBSMParams', BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php');
JLoader::register('JBSMTeacher', BIBLESTUDY_PATH_HELPERS . '/teacher.php');

/**
 * View class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 *
 * @todo     need to make the title block. BCC
 */
class BiblestudyViewTeacher extends JViewLegacy
{
	/**
	 * @var object
	 */
	protected $item;

	protected $contact;

	protected $admin_params;

	protected $admin;

	protected $params;

	protected $template;

	public $teacherstudies;

	public $print;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$app         = JFactory::getApplication();
		$pagebuilder = new JBSPagebuilder;
		$document    = JFactory::getDocument();
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
		$pathway = $app->getPathWay();
		$images  = new JBSMImages;

		$this->admin        = JBSMParams::getAdmin();
		$this->admin_params = $this->admin->params;

		$itemparams = $app->getPageParameters();

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($this->admin_params->get('metadesc'));
		}
		$input = new JInput;
		$t     = $input->get('t', 1, 'int');

		if (!$t)
		{
			$t = 1;
		}

		$template = JBSMParams::getTemplateparams();

		$params = $template->params;

		$css = $params->get('css');

		if ($css <= "-1")
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
		}
		else
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		}

		$item = $this->get('Item');

		// Add the slug
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : str_replace(
			' ',
			'-',
			htmlspecialchars_decode($item->teachername, ENT_QUOTES)
		) . ':' . $item->id;
		$id         = $input->get('id', '0', 'get');
		$item->id   = $id;

		$image            = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
		$largeimage       = $images->getTeacherImage($item->image, $item->teacher_image);
		$item->image      = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$item->largeimage = '<img src="' . $largeimage->path . '" height="' . $largeimage->height . '" width="' . $largeimage->width . '" alt="" />';

		if (isset($item->information))
		{
			$item->text        = $item->information;
			$information       = $pagebuilder->runContentPlugins($item, $params);
			$item->information = $information->text;
		}
		elseif (isset($item->short))
		{
			$item->text  = $item->short;
			$short       = $pagebuilder->runContentPlugins($item, $params);
			$item->short = $short->text;
		}

		// Check to see if com_contact used instead
		if ($item->contact)
		{
			require_once JPATH_ROOT . '/components/com_contact/models/contact.php';
			$contactmodel  = JModelLegacy::getInstance('contact', 'contactModel');
			$this->contact = $contactmodel->getItem($pk = $item->contact);

			// Substitute contact info from com_contacts for duplicate fields
			$item->title       = $this->contact->con_position;
			$item->teachername = $this->contact->name;
			$item->email       = $this->contact->email_to;
			$largeimage        = $images->getImagePath($this->contact->image);
			$item->largeimage  = '<img src="' . $largeimage->path . '" height="' . $largeimage->height . '" <width="' . $largeimage->width . '" alt="" />';
			$item->information = $this->contact->misc;
			$item->phone       = $this->contact->telephone;
			$cregistry         = new JRegistry;
			$cregistry->loadString($this->contact->params);
			$contact_params     = $cregistry;
			$item->facebooklink = $contact_params->get('linka');
			$item->twitterlink  = $contact_params->get('linkb');
			$item->bloglink     = $contact_params->get('linkc');
			$item->link1        = $contact_params->get('linkd');
			$item->linklabel1   = $contact_params->get('linklabel1');
			$item->link2        = $contact_params->get('linke');
			$item->linklabel2   = $contact_params->get('linke_name');
			$item->website      = $this->contact->webpage;
			$item->address      = $this->contact->address;
		}

		$this->item = $item;

		$whereitem  = intval($item->id);
		$wherefield = 'study.teacher_id';
		$limit      = $params->get('studies', '20');
		$order      = 'DESC';

		if ($params->get('show_teacher_studies') > 0)
		{
			$studies = $pagebuilder->studyBuilder(
				$whereitem,
				$wherefield,
				$params,
				$this->admin_params,
				$limit,
				$order
			);

			foreach ($studies as $i => $study)
			{
				$pelements               = $pagebuilder->buildPage($study, $params, $this->admin_params);
				$studies[$i]->scripture1 = $pelements->scripture1;
				$studies[$i]->scripture2 = $pelements->scripture2;
				$studies[$i]->media      = $pelements->media;
				$studies[$i]->duration   = $pelements->duration;
				$studies[$i]->studydate  = $pelements->studydate;
				$studies[$i]->topics     = $pelements->topics;

				if (isset($pelements->study_thumbnail))
				{
					$studies[$i]->study_thumbnail = $pelements->study_thumbnail;
				}
				else
				{
					$studies[$i]->study_thumbnail = null;
				}

				if (isset($pelements->series_thumbnail))
				{
					$studies[$i]->series_thumbnail = $pelements->series_thumbnail;
				}
				else
				{
					$studies[$i]->series_thumbnail = null;
				}
				$studies[$i]->detailslink = $pelements->detailslink;

				if (!isset($pelements->studyintro))
				{
					$pelements->studyintro = '';
				}
				$studies[$i]->studyintro = $pelements->studyintro;

				if (isset($pelements->secondary_reference))
				{
					$studies[$i]->secondary_reference = $pelements->secondary_reference;
				}
				else
				{
					$studies[$i]->secondary_reference = '';
				}
				if (isset($pelements->sdescription))
				{
					$studies[$i]->sdescription = $pelements->sdescription;
				}
				else
				{
					$studies[$i]->sdescription = '';
				}
			}
			$this->teacherstudies = $studies;
		}

		$this->item = $item;
		$print      = $input->get('print', '', 'bool');

		// Build the html select list for ordering
		$this->print    = $print;
		$this->params   = $params;
		$this->template = $template;

		parent::display($tpl);
	}

}
