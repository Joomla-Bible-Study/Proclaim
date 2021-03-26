<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// This is the popup window for the teachings.  We could put anything in this window.

/**
 * View class for Popup
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewPopup extends JViewLegacy
{
	/** @var  string Player
	 *
	 * @since 7.0 */
	public string $player;

	/** @var  string Media
	 *
	 * @since 7.0 */
	public $media;

	/** @var object Media info
	 *
	 * @since 7.0 */
	public object $getMedia;

	/** @var  string Scripture Text
	 *
	 * @since 7.0 */
	public string $scripture;

	/** @var  string Date
	 *
	 * @since 7.0 */
	public string $date;

	/** @var  string Series Thumbnail
	 *
	 * @since 7.0 */
	public string $series_thumbnail;

	/** @var  string Teacher Image
	 *
	 * @since 7.0 */
	public string $teacherimage;

	/** @var  string Path 1
	 *
	 * @since 7.0 */
	public string $path1;

	/** @var  string Width
	 *
	 * @since 7.0 */
	public string $playerwidth;

	/** @var  string Player Height
	 *
	 * @since 7.0 */
	public string $playerheight;

	// @todo Need to remove as Flash is no longer users in browsers

	/** @var  string Flash Vars
	 *
	 * @since 7.0 */
	public string $flashvars;

	/** @var  string Back Color
	 *
	 * @since 7.0 */
	public string $backcolor;

	/** @var  string Front Color
	 *
	 * @since 7.0 */
	public string $frontcolor;

	/** @var  string Light Color
	 *
	 * @since 7.0 */
	public string $lightcolor;

	/** @var  string Screen Color
	 *
	 * @since 7.0 */
	public string $screencolor;

	/** @var  string Auto Start
	 *
	 * @since 7.0 */
	public string $autostart;

	/** @var  string Player Idle Hide
	 *
	 * @since 7.0 */
	public string $playeridlehide;

	/** @var  string Header Text
	 *
	 * @since 7.0 */
	public string $headertext;

	/** @var  string Footer Text
	 *
	 * @since 7.0 */
	public string $footertext;

	/** @var  Registry Params
	 *
	 * @since 7.0 */
	protected Registry $params;

	/** @var  Registry Params
	 *
	 * @since 7.0 */
	protected Registry $state;

	/** @var  Registry Extra Params
	 *
	 * @since 7.0 */
	protected Registry $extraparams;

	/** @var  TableTemplate Template
	 *
	 * @since 7.0 */
	protected TableTemplate $template;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$input = new JInput;
		$input->get('tmpl', 'component', 'string');
		$mediaid      = $input->getInt('mediaid');
		$close        = $input->getInt('close', '0');
		$this->player = $input->getInt('player', '1');

		/*
		 *  If this is a direct new window then all we need to do is perform hitPlay and close this window
		 */
		if ($close === 1)
		{
			echo JHtml::_('content.prepare', '<script language="javascript" type="text/javascript">window.close();</script>');
		}

		$this->getMedia = new JBSMMedia;
		$this->media    = $this->getMedia->getMediaRows2($mediaid);
		$this->state    = $this->get('state');
		$this->template = $this->state->get('template');

		/*
		 *  Convert parameter fields to objects.
		 */
		$registry = new Registry;
		$registry->loadString($this->template->params);
		$this->params = $registry;

		$registry = new Registry;
		$registry->loadString($this->media->sparams);
		$this->params->merge($registry);
		$this->media->sparams = $registry;
		$registry = new Registry;
		$registry->loadString($this->media->params);
		$this->params->merge($registry);

		JHtml::_('biblestudy.framework');
		JHtml::_('biblestudy.loadcss', $this->params);

		$saveid          = (int) $this->media->id;
		$this->media->id = (int) $this->media->study_id;
		$JBSMListing   = new JBSMListing;
		$this->scripture = $JBSMListing->getScripture($this->params, $this->media, $esv = '0', $scripturerow = '1');
		$this->media->id = $saveid;
		$this->date      = $JBSMListing->getStudyDate($this->params, $this->media->studydate);

		/*
		 *  The popup window call the counter function
		 */
		$this->getMedia->hitPlay($mediaid);

		$images                 = new JBSMImages;
		$seriesimage            = $images->getSeriesThumbnail($this->media->series_thumbnail);
		$this->series_thumbnail = '<img src="' . JUri::base() . $seriesimage->path . '" width="' . $seriesimage->width . '" height="'
			. $seriesimage->height . '" alt="' . $this->media->series_text . '" />';
		$image                  = $images->getTeacherThumbnail($this->media->teacher_thumbnail, $this->media->thumb);
		$this->teacherimage     = '<img src="' . JUri::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height
			. '" alt="' . $this->media->teachername . '" />';

		$this->path1 = JBSMHelper::MediaBuildUrl($this->media->spath, $this->params->get('filename'), $this->params, true);

		$this->playerwidth  = $this->params->get('player_width');
		$this->playerheight = $this->params->get('player_height');

		if ($this->params->get('playerheight') < '55' && $this->params->get('playerheight'))
		{
			$this->playerheight = '55';
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

		if ($this->player === '100')
		{
			$this->player = $this->template->params->get('player', '0');
		}

		$this->backcolor   = $this->params->get('backcolor', '0x287585');
		$this->frontcolor  = $this->params->get('frontcolor', '0xFFFFFF');
		$this->lightcolor  = $this->params->get('lightcolor', '0x000000');
		$this->screencolor = $this->params->get('screencolor', '0xFFFFFF');

		if ($this->params->get('autostart', '1') === '1')
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

		if ($this->params->get('autostart') === '1')
		{
			$this->autostart = 'true';
		}
		elseif ($this->params->get('autostart') === '2')
		{
			$this->autostart = 'false';
		}

		$this->headertext = $this->titles($this->params->get('popuptitle'), $this->media, $this->scripture, $this->date);

		if ($this->params->get('itempopuptitle'))
		{
			$this->headertext = $this->titles($this->params->get('itempopuptitle'), $this->media, $this->scripture, $this->date);
		}

		$this->footertext = $this->titles($this->params->get('popupfooter'), $this->media, $this->scripture, $this->date);

		if ($this->params->get('itempopupfooter'))
		{
			$this->footertext = $this->titles($this->params->get('itempopupfooter'), $this->media, $this->scripture, $this->date);
		}

		parent::display($tpl);
	}

	/**
	 * Set Titles
	 *
	 * @param   string  $text       Text info
	 * @param   object  $media      Media info
	 * @param   string  $scripture  scripture
	 * @param   string  $date       Date
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private function titles($text, $media, $scripture, $date)
	{
		if (isset($media->teachername))
		{
			$text = str_replace('{{teacher}}', $media->teachername, $text);
		}

		if (isset($date))
		{
			$text = str_replace('{{studydate}}', $date, $text);
		}

		if ($this->params->get('filename'))
		{
			$text = str_replace('{{filename}}', $this->params->get('filename'), $text);
		}

		if (isset($media->studyintro))
		{
			$text = str_replace('{{description}}', $media->studyintro, $text);
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
