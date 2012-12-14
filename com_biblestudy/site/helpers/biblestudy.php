<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Message model class
 *
 * @package BibleStudy.Admin
 * @since   7.0.0
 */
class BiblestudyHelper
{

	public static $extension = 'com_biblestudy';

	/**
	 * Configure the Linkbar.
	 *
	 * @param    string    $vName    The name of the active view.
	 *
	 * @return    void
	 * @since    1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_biblestudy&view=sermons',
				$vName == 'sermons'
		);
		JHtmlSidebar::addEntry(
			JText::_('JBS_CMN_'),
			'index.php?option=com_biblestudy&view=teachers',
				$vName == 'teachers');
		JHtmlSidebar::addEntry(
			JText::_('JBS_CMN_'),
			'index.php?option=com_biblestudy&view=seriesdispalys',
				$vName == 'seriesdispalys'
		);
		JHtmlSidebar::addEntry(
			JText::_('JBS_CMN_'),
			'index.php?option=com_biblestudy&view=landingpage',
				$vName == 'landingpage'
		);
		JHtmlSidebar::addEntry(
			JText::_('JBS_CMN_'),
			'index.php?option=com_biblestudy&view=latest',
				$vName == 'latest'
		);
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param text The string to filter
	 *
	 * @return string The filtered string
	 */
	public static function filterText($text)
	{
		// Filter settings
		$config     = JComponentHelper::getParams('com_config');
		$user       = JFactory::getUser();
		$userGroups = JAccess::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$blackListTags       = array();
		$blackListAttributes = array();

		$customListTags       = array();
		$customListAttributes = array();

		$whiteListTags       = array();
		$whiteListAttributes = array();

		$noHtml     = false;
		$whiteList  = false;
		$blackList  = false;
		$customList = false;
		$unfiltered = false;

		// Cycle through each of the user groups the user is in.
		// Remember they are included in the Public group as well.
		foreach ($userGroups as $groupId) {
			// May have added a group but not saved the filters.
			if (!isset($filters->$groupId)) {
				continue;
			}

			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType = strtoupper($filterData->filter_type);

			if ($filterType == 'NH') {
				// Maximum HTML filtering.
				$noHtml = true;
			} elseif ($filterType == 'NONE') {
				// No HTML filtering.
				$unfiltered = true;
			} else {
				// Black, white or custom list.
				// Preprocess the tags and attributes.
				$tags           = explode(',', $filterData->filter_tags);
				$attributes     = explode(',', $filterData->filter_attributes);
				$tempTags       = array();
				$tempAttributes = array();

				foreach ($tags as $tag) {
					$tag = trim($tag);

					if ($tag) {
						$tempTags[] = $tag;
					}
				}

				foreach ($attributes as $attribute) {
					$attribute = trim($attribute);

					if ($attribute) {
						$tempAttributes[] = $attribute;
					}
				}

				// Collect the black or white list tags and attributes.
				// Each lists is cummulative.
				if ($filterType == 'BL') {
					$blackList           = true;
					$blackListTags       = array_merge($blackListTags, $tempTags);
					$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
				} elseif ($filterType == 'CBL') {
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes) {
						$customList           = true;
						$customListTags       = array_merge($customListTags, $tempTags);
						$customListAttributes = array_merge($customListAttributes, $tempAttributes);
					}
				} elseif ($filterType == 'WL') {
					$whiteList           = true;
					$whiteListTags       = array_merge($whiteListTags, $tempTags);
					$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the black list uses both sets of arrays).
		$blackListTags        = array_unique($blackListTags);
		$blackListAttributes  = array_unique($blackListAttributes);
		$customListTags       = array_unique($customListTags);
		$customListAttributes = array_unique($customListAttributes);
		$whiteListTags        = array_unique($whiteListTags);
		$whiteListAttributes  = array_unique($whiteListAttributes);

		// Unfiltered assumes first priority.
		if ($unfiltered) {
			// Dont apply filtering.
		} else {
			// Custom blacklist precedes Default blacklist
			if ($customList) {
				$filter = JFilterInput::getInstance(array(), array(), 1, 1);

				// Override filter's default blacklist tags and attributes
				if ($customListTags) {
					$filter->tagBlacklist = $customListTags;
				}
				if ($customListAttributes) {
					$filter->attrBlacklist = $customListAttributes;
				}
			} // Black lists take third precedence.
			elseif ($blackList) {
				// Remove the white-listed attributes from the black-list.
				$filter = JFilterInput::getInstance(
				// Blacklisted tags
					array_diff($blackListTags, $whiteListTags),
					// Blacklisted attributes
					array_diff($blackListAttributes, $whiteListAttributes),
					// Blacklist tags
					1,
					// Blacklist attributes
					1
				);
				// Remove white listed tags from filter's default blacklist
				if ($whiteListTags) {
					$filter->tagBlacklist = array_diff($filter->tagBlacklist, $whiteListTags);
				}
				// Remove white listed attributes from filter's default blacklist
				if ($whiteListAttributes) {
					$filter->attrBlacklist = array_diff($filter->attrBlacklist, '');
				}

			} // White lists take fourth precedence.
			elseif ($whiteList) {
				$filter = JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0); // turn off xss auto clean
			} // No HTML takes last place.
			else {
				$filter = JFilterInput::getInstance();
			}

			$text = $filter->clean($text, 'html');
		}

		return $text;
	}
}