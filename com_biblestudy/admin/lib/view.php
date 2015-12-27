<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 *
 * @copyright (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * JBSM View Class
 */
class JBSMView extends JViewLegacy
{

	public $document = null;
	public $app = null;
	public $me = null;
	public $config = null;
	public $embedded = false;
	public $templatefiles = array();
	public $teaser = null;

	protected $inLayout = 0;
	protected $_row = 0;

	/**
	 * JBSMView constructor.
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		$name = isset($config['name']) ? $config['name'] : $this->getName();
		$this->document = JFactory::getDocument();
		$this->document->setBase('');
		$this->app = JFactory::getApplication ();

		parent::__construct($config);

		// Use our own browser side cache settings.
		$this->app->allowCache(false);
		$this->app->setHeader( 'Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true );
		$this->app->setHeader( 'Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT', true );
		$this->app->setHeader( 'Cache-Control', 'no-store, must-revalidate, post-check=0, pre-check=0', true );
	}

	public function displayAll()
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		$this->state = $this->get ( 'State' );
		$menu = $this->app->getMenu();
		$home = $menu->getItems('type', 'alias');
		$juricurrent = JURI::current();

		if (JFactory::getApplication()->isAdmin())
		{
			$this->displayLayout();
		}
	}

	/**
	 * Display Layout
	 *
	 * @param null $layout
	 * @param null $tpl
	 *
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function displayLayout($layout = null, $tpl = null)
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if ($layout)
		{
			$this->setLayout ($layout);
		}

		$view = $this->getName();
		$layout = $this->getLayout();
		$viewName = ucfirst($view);
		$layoutName = ucfirst($layout);
		$layoutFunction = 'display' . $layoutName;

		KUNENA_PROFILER ? $this->profiler->start("display {$viewName}/{$layoutName}") : null;

		$this->state = $this->get('State');

		if (method_exists($this, $layoutFunction))
		{
			$contents = $this->$layoutFunction($tpl ? $tpl : null);
		}
		elseif (method_exists($this, 'displayDefault'))
		{
			// TODO: should raise error instead, used just in case..
			$contents = $this->displayDefault($tpl ? $tpl : null);
		}
		else
		{
			// TODO: should raise error instead..
			$contents = $this->display($tpl ? $tpl : null);
		}
		KUNENA_PROFILER ? $this->profiler->stop("display {$viewName}/{$layoutName}") : null;

		return $contents;
	}

	/**
	 * Render new layout if available, otherwise continue to the old logic.
	 *
	 * @param string $layout
	 * @param string $tpl
	 * @param array  $hmvcParams
	 * @throws LogicException
	 */
	public function render($layout, $tpl, array $hmvcParams = array())
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if (isset($tpl) && $tpl == 'default')
		{
			$tpl = null;
		}

		if ($this->embedded)
		{
			// Support legacy embedded views.
			$file = isset($tpl) ? $this->getLayout() . '_' . $tpl : $this->getLayout();
			foreach ($this->_path['template'] as $path)
			{
				$found = !strstr($path, '/com_kunena/') && is_file($path.$file.'.php');

				if ($found)
				{
					$this->display($tpl);

					return;
				}
			}
		}

		// Support new layouts.
		$hmvc = JBSMLayout::factory($layout);
		if ($hmvc->getPath())
		{
			$this->inLayout++;
			if ($hmvcParams)
			{
				$hmvc->setProperties($hmvcParams);
			}

			echo $hmvc->setLegacy($this)->setLayout($tpl ? $tpl : $this->getLayout());

			$this->inLayout--;
		}
		else
		{
			$this->display($tpl);
		}
	}

	/**
	 * @param $position
	 */
	public function displayModulePosition($position)
	{
		echo $this->getModulePosition($position);
	}

	/**
	 * @param $position
	 *
	 * @return int
	 */
	public function isModulePosition($position)
	{
		$document = JFactory::getDocument();

		return method_exists($document, 'countModules') ? $document->countModules ( $position ) : 0;
	}

	/**
	 * @param $position
	 *
	 * @return string
	 */
	public function getModulePosition($position)
	{
		$html = '';
		$document = JFactory::getDocument();

		if (method_exists($document, 'countModules') && $document->countModules ( $position ))
		{
			$renderer = $document->loadRenderer ( 'modules' );
			$options = array ('style' => 'xhtml' );
			$html .= '<div class="'.$position.'">';
			$html .= $renderer->render ( $position, $options, null );
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * This function formats a number to n significant digits when above
	 * 10,000. Starting at 10,0000 the out put changes to 10k, starting
	 * at 1,000,000 the output switches to 1m. Both k and m are defined
	 * in the language file. The significant digits are used to limit the
	 * number of digits displayed when in 10k or 1m mode.
	 *
	 * @param int $number 		Number to be formated
	 * @param int $precision	Significant digits for output
	 * @return string
	 */
	public function formatLargeNumber($number, $precision = 3)
	{
		// Do we need to reduce the number of significant digits?
		if ($number >= 10000)
		{
			// Round the number to n significant digits
			$number = round ($number, -1*(log10($number)+1) + $precision);
		}

		if ($number < 10000)
		{
			$output = $number;
		}
		elseif ($number >= 1000000)
		{
			$output = $number / 1000000 . JText::_('COM_BIBLESTUDY_MILLION');
		}
		else
		{
			$output = $number / 1000 . JText::_('COM_BIBLESTUDY_THOUSAND');
		}

		return $output;
	}

	/**
	 * @param $filename
	 *
	 * @return mixed
	 */
	public function addStyleSheet($filename)
	{
		return KunenaFactory::getTemplate()->addStyleSheet ( $filename );
	}

	/**
	 * @param $filename
	 *
	 * @return mixed
	 */
	public function addScript($filename)
	{
		return KunenaFactory::getTemplate()->addScript ( $filename );
	}

	/**
	 * @param array $messages
	 * @param int   $code
	 */
	public function displayError($messages = array(), $code = 404)
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		$title = JText::_('COM_BIBLESTUDY_ACCESS_DENIED');	// can be overriden

		switch ((int) $code)
		{
			case 400:
				$this->app->setHeader('Status', '400 Bad Request', true);
				break;
			case 401:
				$this->app->setHeader('Status', '401 Unauthorized', true);
				break;
			case 403:
				$this->app->setHeader('Status', '403 Forbidden', true);
				break;
			case 404:
				$this->app->setHeader('Status', '404 Not Found', true);
				break;
			case 410:
				$this->app->setHeader('Status', '410 Gone', true);
				break;
			case 500:
				$this->app->setHeader('Status', '500 Internal Server Error', true);
				break;
			case 503:
				$this->app->setHeader('Status', '503 Service Temporarily Unavailable', true);
				break;
			default:
		}

		$output = '';

		foreach ($messages as $message)
		{
			$output .= "<p>{$message}</p>";
		}

		$this->latest->setLayout('default');
		$this->latest->header = $title;
		$this->latest->body = $output;
		$this->latest->html = true;
		$this->latest->display();

		$this->setTitle($title);
	}

	/**
	 * Display No Access
	 *
	 * @param array $errors
	 *
	 * @return string
	 */
	public function displayNoAccess($errors = array())
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		// Backward compatibility
		$this->displayError($errors, 200);
	}

	/**
	 * Display From Token
	 *
	 * @return string
	 */
	public function displayFormToken()
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		echo '[K=TOKEN]';
	}

	/**
	 * Row
	 *
	 * @param bool $start
	 *
	 * @return string
	 */
	public function row($start = false)
	{
		if ($start)
		{
			$this->_row = 0;
		}

		return ++$this->_row & 1 ? 'odd' : 'even';
	}

	/**
	 * Set Title of Page
	 *
	 * @param $title
	 *
	 * @return void
	 */
	public function setTitle($title)
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if (!$this->state->get('embedded'))
		{
			// Check for empty title and add site name if param is set
			$title = strip_tags($title);
			if ($this->app->get('sitename_pagetitles', 0) == 1)
			{
				$title = JText::sprintf('JPAGETITLE', $this->app->getCfg('sitename'), 'Title' .' - '. $title);
			}
			elseif ($this->app->get('sitename_pagetitles', 0) == 2)
			{
				$title = JText::sprintf('JPAGETITLE', $title .' - '. 'Title', $this->app->getCfg('sitename'));
			}
			else
			{
				// TODO: allow translations/overrides (also above)
				$title = 'Title' .': '. $title;
			}
			$this->document->setTitle($title);
		}
	}

	/**
	 * Set Keywords
	 *
	 * @param $keywords
	 *
	 * @return void
	 */
	public function setKeywords($keywords)
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if (!$this->state->get('embedded'))
		{
			if (!empty($keywords))
			{
				$this->document->setMetadata ( 'keywords', $keywords );
			}
		}
	}

	/**
	 * Set Description
	 *
	 * @param $description
	 *
	 * @return void
	 */
	public function setDescription($description)
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if (!$this->state->get('embedded'))
		{
			// TODO: allow translations/overrides
			$lang = JFactory::getLanguage();
			$length = Joomla\String\StringHelper::strlen($lang->getName());
			$length = 137 - $length;

			if (Joomla\String\StringHelper::strlen($description) > $length)
			{
				$description = JString::substr($description, 0, $length) . '...';
			}

			$this->document->setMetadata('description', $description);
		}
	}
}
