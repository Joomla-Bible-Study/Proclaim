<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
namespace CWM\Component\Proclaim\Administrator\Helper;
defined('JPATH_BASE') or die;

use CWM\Component\Proclaim\Site\Helper\CWMHelperRoute;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Html\HtmlHelper;

/**
 * Content Component HTML Helper
 *
 * @since  1.5
 */
class CWMIcon
{
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
	 * @since 7.0
	 */
	public static function create($category, $params, $attribs = array(), $legacy = false)
	{

		$uri = Uri::getInstance();

		$url = 'index.php?option=com_proclaim&task=cwmmessageform.add&return=' . base64_encode($uri) . '&a_id=0';

		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = HtmlHelper::_('image', 'system/new.png', Text::_('JNEW'), null, true);
			}
			else
			{
				$text = '<span class="icon-plus"></span>' . Text::_('JNEW');
			}
		}
		else
		{
			$text = Text::_('JNEW') . '&#160;';
		}

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] = $attribs['class'] . ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = HtmlHelper::_('link', Route::_($url), $text, $attribs);

		$output = '<span class="hasTooltip" title="' . HtmlHelper::tooltipText('JBS_CREATE_SERMON') . '">' . $button . '</span>';

		return $output;
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
	 * @return  string    The HTML for the article edit icon.
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	public static function edit($article, $params, $attribs = array(), $legacy = false)
	{
		$user = Factory::getApplication()->getSession()->get('user');
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return null;
		}

		// Ignore if the state is negative (trashed).
		if ($article->published < 0)
		{
			return null;
		}

		// Show checked_out icon if the article is checked out by a different user
		if (property_exists($article, 'checked_out')
			&& property_exists($article, 'checked_out_time')
			&& $article->checked_out > 0
			&& $article->checked_out != $user->get('id'))
		{
			$checkoutUser = Factory::getUser($article->checked_out);
			$button       = HtmlHelper::_('image', 'system/checked_out.png', null, null, true);
			$date         = HtmlHelper::_('date', $article->checked_out_time);
			$tooltip      = Text::_('JLIB_HTML_CHECKED_OUT') . ' :: ' . Text::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name)
				. ' <br /> ' . $date;

			return '<span class="hasTooltip" title="' . HtmlHelper::tooltipText($tooltip . '', 0) . '">' . $button . '</span>';
		}

		$url = 'index.php?option=com_proclaim&task=cwmmessageform.edit&a_id=' . $article->id . '&return=' . base64_encode($uri);

		if ($article->published == 0)
		{
			$overlib = Text::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = Text::_('JPUBLISHED');
		}

		if ($legacy)
		{
			$icon = $article->state ? 'edit.png' : 'edit_unpublished.png';

			if (strtotime($article->publish_up) > strtotime(Factory::getDate())
				|| ((strtotime($article->publish_down) < strtotime(Factory::getDate())) && $article->publish_down != Factory::getDbo()->getNullDate()))
			{
				$icon = 'edit_unpublished.png';
			}

			$text = HtmlHelper::_('image', 'system/' . $icon, Text::_('JGLOBAL_EDIT'), null, true);
		}
		else
		{
			$icon = $article->published ? 'edit' : 'eye-close';

			if (strtotime($article->publish_up) > strtotime(Factory::getDate())
				|| ((strtotime($article->publish_down) < strtotime(Factory::getDate())) && $article->publish_down != Factory::getDbo()->getNullDate()))
			{
				$icon = 'eye-close';
			}

			$text = '<span class="hasTooltip icon-' . $icon . ' tip" title="' . HtmlHelper::tooltipText(Text::_('COM_CONTENT_EDIT_ITEM'), $overlib, 0)
				. '"></span>'
				. Text::_('JGLOBAL_EDIT');
		}

		return HtmlHelper::_('link', Route::_($url), $text, $attribs);
	}

	/**
	 * Method to generate a popup link to print an article
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public static function print_popup($article, $params, $attribs = array(), $legacy = false)
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$request = $input->request;

		$url = CWMHelperRoute::getArticleRoute($article->id, $article->language);
		$url .= '&tmpl=component&print=1&layout=default&page=' . (int) @$request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// Checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = HtmlHelper::_('image', 'system/printButton.png', Text::_('JGLOBAL_PRINT'), null, true);
			}
			else
			{
				$text = '<span class="icon-print"></span>' . Text::_('JGLOBAL_PRINT');
			}
		}
		else
		{
			$text = Text::_('JGLOBAL_PRINT');
		}

		$attribs['title']   = Text::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';

		return HtmlHelper::_('link', Route::_($url), $text, $attribs);
	}

	/**
	 * Method to generate a link to print an article
	 *
	 * @param   object    $article  Not used, @deprecated for 4.0
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Not used, @deprecated for 4.0
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 *
	 * @since 7.0
	 */
	public static function print_screen($article, $params, $attribs = array(), $legacy = false)
	{
		// Checks template image directory for image, if none found default are loaded
		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = HtmlHelper::_('image', 'system/printButton.png', Text::_('JGLOBAL_PRINT'), null, true);
			}
			else
			{
				$text = '<span class="icon-print"></span>' . Text::_('JGLOBAL_PRINT');
			}
		}
		else
		{
			$text = Text::_('JGLOBAL_PRINT');
		}

		return '<a href="#" onclick="window.print();return false;">' . $text . '</a>';
	}
}
