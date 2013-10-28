<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMPagebuilder', BIBLESTUDY_PATH_LIB . '/pagebuilder.php');
JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('JBSMParams', BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php');
JLoader::register('JBSMTeacher', BIBLESTUDY_PATH_HELPERS . '/teacher.php');
JLoader::register('JBSMListing', BIBLESTUDY_PATH_LIB . '/listing.php');

/**
 * View class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewTeacher extends JViewLegacy
{

	/** @var  object Item */
	protected $item;

	/** @var  String Contact */
	protected $contact;

	/**  @var JRegistry Admin Params */
	protected $admin_params;

	/** @var  JObject Admin */
	protected $admin;

	/** @var  JRegistry Params */
	protected $params;

	/** @var  JObject Template Info */
	protected $template;

	/** @var  JObject Template Studies */
	protected $teacherstudies;

	/** @var  JObject Print */
	protected $print;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$app         = JFactory::getApplication();
		$pagebuilder = new JBSMPagebuilder;
		$document    = JFactory::getDocument();
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-responsive.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-extended.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-responsive-min.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-min.css');

        $this->studies = $this->get('studies');
		$images = new JBSMImages;

		$this->admin        = JBSMParams::getAdmin();
		$this->admin_params = $this->admin->params;
		$input              = new JInput;

		$template = JBSMParams::getTemplateparams();

		$params = $template->params;

		$css = $params->get('css');

        $input        = new JInput;
        $t            = $params->get('teachertemplateid');
        if (!$t)
        {
            $t = $input->get('t', 1, 'int');
        }
        $this->t = $t;
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
        //Only use Pagebuilder if using a template other than the default_main
        if ($params->get('useexpert_teacherdetail') > 0 || $params->get('teachertemplate') > 0)
        {
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
        }
		$this->item = $item;
		$print      = $input->get('print', '', 'bool');

		// Build the html select list for ordering
		$this->print    = $print;
		$this->params   = $params;
		$this->template = $template;

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

		/** @var $itemparams JRegistry */
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
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$this->document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->admin_params->get('metakey'));
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
			$this->document->setDescription($this->admin_params->get('metadesc'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}
