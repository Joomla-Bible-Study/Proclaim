<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Teacher
 *
 * @since  7.0.0
 */
class BiblestudyViewTeacher extends JViewLegacy
{

	/** @var  object Item */
	protected $item;

	/** @var  object Contact */
	protected $contact;

	/** @var  Registry Admin */
	protected $state;

	/** @var  Registry Params */
	protected $params;

	/** @var  TableTemplate Template Info */
	protected $template;

	/** @var  JObject Template Studies */
	protected $teacherstudies;

	/** @var  JObject Print */
	protected $print;

	/** @var  JDocument Print */
	public $document;

	/** @var  JObject Studies */
	protected $studies;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$pagebuilder = new JBSMPageBuilder;

		$images        = new JBSMImages;
		$this->state   = $this->get('state');

		/** @var Registry $params */
		$params = $this->state->template->params;

		JHtml::_('biblestudy.framework');
		JHtml::_('biblestudy.loadCss', $params, null, 'font-awesome');

		$input = new JInput;

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
			$language = JFactory::getLanguage();
			$language->load('com_contact', JPATH_SITE);
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
			$cregistry         = new Registry;
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
		$template      = $this->get('template');

		if ($params->get('show_teacher_studies') > 0)
		{
			$studies = $pagebuilder->studyBuilder(
				$whereitem,
				$wherefield,
				$params,
				$limit,
				$order,
				$template
			);

			foreach ($studies as $i => $study)
			{
				$pelements               = $pagebuilder->buildPage($study, $params, $template);
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
			$this->studies = $studies;
		}

		$this->item = $item;
		$print      = $input->get('print', '', 'bool');

		// Build the html select list for ordering
		$this->print    = $print;
		$this->params   = $params;
		$this->template = $this->state->template;
		$this->document = JFactory::getDocument();

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document;
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication('site');
		$menus = $app->getMenu();

		/** @var $itemparams Registry */
		$itemparams = $app->getParams();
		$title      = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}
		$title = $this->params->get('page_title', '');
		$title .= ' : ' . $this->item->teachername . ' - ' . $this->item->title;

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		$this->document->setTitle($title);

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$this->document->setMetaData('keywords', $itemparams->get('metakey'));
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetaData('keywords', $this->params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$this->document->setDescription($itemparams->get('metadesc'));
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('metadesc'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}
	}

}
