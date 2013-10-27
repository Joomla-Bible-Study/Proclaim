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

// This is the popup window for the teachings.  We could put anything in this window.

/**
 * View class for Popup
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewPopup extends JViewLegacy
{
	/** @var  string Player */
	public $player;

	/** @var  string Media */
	public $media;

	/** @var  JObject Media info */
	public $getMedia;

	/** @var  JRegistry Params */
	protected $params;

	/** @var  string Scripture Text */
	public $scripture;

	/** @var  string Date */
	public $date;

	/** @var  string Length */
	public $lenght;

	/** @var  string Series Thumbnail */
	public $series_thumbnail;

	/** @var  string Teacher Image */
	public $teacherimage;

	/** @var  string Path 1 */
	public $path1;

	/** @var  string Width */
	public $playerwidth;

	/** @var  string Player Height */
	public $playerheight;

	/** @var  JRegistry Extra Params */
	protected $extraparams;

	/** @var  string Flash Vars */
	public $flashvars;

	/** @var  string Back Color */
	public $backcolor;

	/** @var  string Front Color */
	public $frontcolor;

	/** @var  string Light Color */
	public $lightcolor;

	/** @var  string Screen Color */
	public $screencolor;

	/** @var  string Auto Start */
	public $autostart;

	/** @var  string Player Idle Hide */
	public $playeridlehide;

	/** @var  string Header Text */
	public $headertext;

	/** @var  string Footer Text */
	public $footertext;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input = new JInput;
		$input->get('tmpl', 'component', 'string');
		$mediaid      = $input->get('mediaid', '', 'int');
		$close        = $input->get('close', '0', 'int');
		$this->player = $input->get('player', '1', 'int');

		/*
		 *  If this is a direct new window then all we need to do is perform hitPlay and close this window
		 */
		if ($close == 1)
		{
			echo JHTML::_('content.prepare', '<script language="javascript" type="text/javascript">window.close();</script>');
		}

		$document = JFactory::getDocument();

		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');

		jimport('joomla.application.component.helper');

		$this->getMedia = new JBSMMedia;
		$this->media    = $this->getMedia->getMediaRows2($mediaid);
		$template       = JBSMParams::getTemplateparams();
		$this->params   = $template->params;

		/*
		 *  Convert parameter fields to objects.
		 */
		$registry = new JRegistry;
		$registry->loadString($this->media->params);
		$this->params->merge($registry);

		$css = $this->params->get('css', 'biblestudy.css');

		if ($css != '-1')
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		}

		$saveid          = $this->media->id;
		$this->media->id = $this->media->study_id;
		$JBSMElements    = new JBSMElements;
		$this->scripture = $JBSMElements->getScripture($this->params, $this->media, $esv = '0', $scripturerow = '1');
		$this->media->id = $saveid;
		$this->date      = $JBSMElements->getstudyDate($this->params, $this->media->studydate);
		/*
		 *  The popup window call the counter function
		 */
		$this->getMedia->hitPlay($mediaid);
		$this->lenght = $JBSMElements->getDuration($this->params, $this->media);

		$images                 = new JBSMImages;
		$seriesimage            = $images->getSeriesThumbnail($this->media->series_thumbnail);
		$this->series_thumbnail = '<img src="' . JURI::base() . $seriesimage->path . '" width="' . $seriesimage->width . '" height="'
			. $seriesimage->height . '" alt="' . $this->media->series_text . '" />';
		$image                  = $images->getTeacherThumbnail($this->media->teacher_thumbnail, $this->media->thumb);
		$this->teacherimage     = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height
			. '" alt="' . $this->media->teachername . '" />';


		if ($this->media->spath == 'localhost')
		{
			$this->media->spath = JUri::base() . '/';
		}
		$this->path1            = $this->media->spath . $this->media->fpath . $this->media->filename;

		if (!substr_count($this->path1, '://'))
		{
			$protocol = $this->params->get('protocol', 'http://');
			$this->path1     = $protocol . $this->path1;
		}
		$this->playerwidth  = $this->params->get('player_width');
		$this->playerheight = $this->params->get('player_height');

		if ($this->params->get('playerheight') < 55 && $this->params->get('playerheight'))
		{
			$this->playerheight = 55;
		}
		elseif ($this->params->get('playerheight'))
		{
			$this->playerheight = $this->params->get('playerheight');
		}
		if ($this->params->get('playerwidth'))
		{
			$this->playerwidth = $this->params->get('playerwidth');
		}
		if ($this->params->get('playervars'))
		{
			$this->extraparams = $this->params->get('playervars');
		}
		if ($this->params->get('altflashvars'))
		{
			$this->flashvars = $this->params->get('altflashvars');
		}
		$this->backcolor   = $this->params->get('backcolor', '0x287585');
		$this->frontcolor  = $this->params->get('frontcolor', '0xFFFFFF');
		$this->lightcolor  = $this->params->get('lightcolor', '0x000000');
		$this->screencolor = $this->params->get('screencolor', '0xFFFFFF');

		if ($this->params->get('autostart', 1) == 1)
		{
			$this->autostart = 'true';
		}
		else
		{
			$this->autostart = 'false';
		}
		if ($this->params->get('playeridlehide'))
		{
			$this->playeridlehide = 'true';
		}
		else
		{
			$this->playeridlehide = 'false';
		}
		if ($this->params->get('autostart') == 1)
		{
			$this->autostart = 'true';
		}
		elseif ($this->params->get('autostart') == 2)
		{
			$this->autostart = 'false';
		}
		$this->headertext = $this->titles($this->params->get('popuptitle'), $this->media, $this->scripture, $this->date, $this->lenght);

		if ($this->params->get('itempopuptitle'))
		{
			$this->headertext = $this->titles($this->params->get('itempopuptitle'), $this->media, $this->scripture, $this->date, $this->lenght);
		}
		$this->footertext = $this->titles($this->params->get('popupfooter'), $this->media, $this->scripture, $this->date, $this->lenght);

		if ($this->params->get('itempopupfooter'))
		{
			$this->footertext = $this->titles($this->params->get('itempopupfooter'), $this->media, $this->scripture, $this->date, $this->lenght);
		}

		parent::display($tpl);
	}

	/**
	 * Set Titles
	 *
	 * @param   string $text       Text info
	 * @param   object $media      Media info
	 * @param   string $scripture  scripture
	 * @param   string $date       Date
	 * @param   string $length     Length of Title
	 *
	 * @return object
	 */
	private function titles($text, $media, $scripture, $date, $length)
	{
		if (isset($media->teachername))
		{
			$text = str_replace('{{teacher}}', $media->teachername, $text);
		}
		if (isset($date))
		{
			$text = str_replace('{{studydate}}', $date, $text);
		}
		if (isset($media->filename))
		{
			$text = str_replace('{{filename}}', $media->filename, $text);
		}
		if (isset($media->studyintro))
		{
			$text = str_replace('{{description}}', $media->studyintro, $text);
		}
		if (isset($length))
		{
			$text = str_replace('{{length}}', $length, $text);
		}
		if (isset($media->studytitle))
		{
			$text = str_replace('{{title}}', $media->studytitle, $text);
		}
		if (isset($scripture))
		{
			$text = str_replace('{{scripture}}', $scripture, $text);
		}
		if (isset($this->teacherimage))
		{
			$text = str_replace('{{teacherimage}}', $this->teacherimage, $text);
		}
		if (isset($media->series_text))
		{
			$text = str_replace('{{series}}', $media->series_text, $text);
		}
		if (isset($media->series_thumbnail))
		{
			$text = str_replace('{{series_thumbnail}}', $this->series_thumbnail, $text);
		}

		return $text;
	}

}
