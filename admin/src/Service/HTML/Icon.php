<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use CWM\Component\Proclaim\Site\Helper\CWMRouteHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Registry\Registry;

/**
 * Content Component HTML Helper
 *
 * @since  4.0.0
 */
class Icon
{
	/**
	 * The application
	 *
	 * @var    CMSApplication
	 *
	 * @since  4.0.0
	 */
	private $application;

	/**
	 * Service constructor
	 *
	 * @param   CMSApplication  $application  The application
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplication $application)
	{
		$this->application = $application;
	}

	/**
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object    $category  The category information
	 * @param   Registry  $params    The item parameters
	 * @param   array     $attribs   Optional attributes for the link
	 * @param   boolean   $legacy    True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the create item link
	 *
	 * @since  4.0.0
	 */
	public function create($category, $params, $attribs = array(), $legacy = false)
	{
		$uri = Uri::getInstance();

		$url = 'index.php?option=com_proclaim&task=cwmmessage.add&return=' . base64_encode($uri) . '&a_id=0&catid=' . $category->id;

		$text = '';

		if ($params->get('show_icons'))
		{
			$text .= '<span class="icon-plus icon-fw" aria-hidden="true"></span>';
		}

		$text .= Text::_('COM_CONTENT_NEW_ARTICLE');

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] .= ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		return HTMLHelper::_('link', Route::_($url), $text, $attribs);
	}

	/**
	 * Display an edit icon for the article.
	 *
	 * This icon will not display in a popup window, nor if the article is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string	The HTML for the article edit icon.
	 *
	 * @todo   need to work on this more. May use.
	 *
	 * @since  4.0.0
	 */
	public function edit($article, $params, $attribs = array(), $legacy = false)
	{
		$user = $user = Factory::getApplication()->getSession()->get('user');
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return '';
		}

		// Ignore if the state is negative (trashed).
		if (!in_array($article->state, [Workflow::CONDITION_UNPUBLISHED, Workflow::CONDITION_PUBLISHED], true))
		{
			return '';
		}

		// Show checked_out icon if the article is checked out by a different user
		if (property_exists($article, 'checked_out')
			&& property_exists($article, 'checked_out_time')
			&& !is_null($article->checked_out)
			&& $article->checked_out != $user->get('id'))
		{
			$checkoutUser = Factory::getUser($article->checked_out);
			$date         = HTMLHelper::_('date', $article->checked_out_time);
			$tooltip      = Text::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name)
				. ' <br> ' . $date;

			$text = LayoutHelper::render('joomla.content.icons.edit_lock', array('article' => $article, 'tooltip' => $tooltip, 'legacy' => $legacy));

			$attribs['aria-describedby'] = 'editarticle-' . (int) $article->id;
			$output = HTMLHelper::_('link', '#', $text, $attribs);

			return $output;
		}

		$contentUrl = CWMRouteHelper::getMessageRoute($article->slug, $article->catid, $article->language);
		$url        = $contentUrl . '&task=cwmmessage.edit&a_id=' . $article->id . '&return=' . base64_encode($uri);

		if ($article->state == Workflow::CONDITION_UNPUBLISHED)
		{
			$tooltip = Text::_('COM_CONTENT_EDIT_UNPUBLISHED_ARTICLE');
		}
		else
		{
			$tooltip = Text::_('COM_CONTENT_EDIT_PUBLISHED_ARTICLE');
		}

		$text = LayoutHelper::render('joomla.content.icons.edit', array('article' => $article, 'tooltip' => $tooltip, 'legacy' => $legacy));

		$attribs['aria-describedby'] = 'editarticle-' . (int) $article->id;
		$output = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		return $output;
	}

	/**
	 * Method to generate a link to print an article
	 *
	 * @param   Registry  $params  The item parameters
	 * @param   boolean   $legacy  True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 *
	 * @since  4.0.0
	 */
	public function print_screen($params, $legacy = false)
	{
		$text = LayoutHelper::render('joomla.content.icons.print_screen', array('params' => $params, 'legacy' => $legacy));

		return '<button type="button" onclick="window.print();return false;">' . $text . '</button>';
	}
}
