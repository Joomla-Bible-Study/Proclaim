<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update for 7.1.0 class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 * @todo     need to update to new SQL and Joomla @BCC
 */
class JBS710Update
{

	/**
	 * Method to Update to 7.1.0
	 *
	 * @return boolean
	 */
	public function update710()
	{
		$db     = JFactory::getDBO();
		$oldcss = false;
		jimport('joomla.filesystem.file');

		// Check to see if there is an existing css
		$src = JPATH_SITE . '/tmp/biblestudy.css';

		// There is no existing css so let us check for a backup
		$backup  = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'biblestudy.css';
		$default = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'biblestudy.css';

		// If there is no new css file in the media folder, check to see if there is one in the old assets or in the backup folder

		if (JFile::exists($src))
		{
			$oldcss = JFile::read($src);
		}
		elseif (JFile::exists($backup))
		{
			$oldcss = JFile::read($backup);
		}
		else
		{
			$newCSS = JFile::read($default);
		}
		if ($oldcss)
		{
			$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($result)
			{
				$query = 'UPDATE #__bsms_styles SET `stylecode` = "' . $db->escape($oldcss) . '" WHERE `id` = ' . $result->id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)));

					return JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true));
				}
			}
			else
			{
				$query = 'INSERT INTO #__bsms_styles (`published`, `filename`, `stylecode`, `asset_id`) VALUES (1,"biblestudy","' . $db->escape($oldcss) . '",0)';
				$db->setQuery($query);

				if (!$db->execute())
				{
					JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)));

					return JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true));
				}
			}
			if (JFile::exists($src))
			{
				JFile::delete($src);
			}
			// Add CSS to the file
			$new710css = '
/* New Teacher Codes */
#bsm_teachertable_list .bsm_teachername
{
    font-weight: bold;
    font-size: 14px;
    color: #000000;
}
#bsm_teachertable_list
{
    margin: 0;
    border-collapse:separate;
}
#bsm_teachertable_list td {
    text-align:left;
    padding:0 5px 0 5px;
    border:none;
}
#bsm_teachertable_list .titlerow
{
    border-bottom: thick;
}
#bsm_teachertable_list .title
{
    font-size:18px;
    font-weight:bold;
    border-bottom: 3px solid #999999;
    padding: 4px 0px 4px 4px;
}
#bsm_teachertable_list .bsm_separator
{
    border-bottom: 1px solid #999999;
}

.bsm_teacherthumbnail_list
{

}
#bsm_teachertable_list .bsm_teacheremail
{
    font-weight:normal;
    font-size: 11px;
}
#bsm_teachertable_list .bsm_teacherwebsite
{
    font-weight:normal;
    font-size: 11px;
}
#bsm_teachertable_list .bsm_teacherphone
{
    font-weight:normal;
    font-size: 11px;
}
#bsm_teachertable_list .bsm_short
{
    padding: 8px 4px 4px;
    font-weight:normal;
}
#bsm_teachertable .bsm_studiestitlerow {
    background-color: #666;
}
#bsm_teachertable_list .bsm_titletitle
{
    font-weight:bold;
    color:#FFFFFF;
}
#bsm_teachertable_list .bsm_titlescripture
{
    font-weight:bold;
    color:#FFFFFF;
}
#bsm_teachertable_list .bsm_titledate
{
    font-weight:bold;
    color:#FFFFFF;
}
#bsm_teachertable_list .bsm_teacherlong
{
    padding: 8px 4px 4px;
    border-bottom: 1px solid #999999;
}
#bsm_teachertable_list tr.bsodd {
    background-color:#FFFFFF;
    border-bottom: 1px solid #999999;
}
#bsm_teachertable_list tr.bseven {
    background-color:#FFFFF0;
    border-bottom: 1px solid #999999;
}

#bsm_teachertable_list .lastrow td {
    border-bottom:1px solid grey;
    padding-bottom:7px;
    padding-top:7px;
}
#bsm_teachertable_list .bsm_teacherfooter
{
    border-top: 1px solid #999999;
    padding: 4px 1px 1px 4px;
}
/* New Teacher Details Codes */

#bsm_teachertable .teacheraddress{
    text-align:left;
}

#bsm_teachertable .teacherwebsite{
    text-align:left;}

#bsm_teachertable .teacherfacebook{
    text-align:left;
}

#bsm_teachertable .bsm_teachertwitter{
    text-align:left;
}

#bsm_teachertable .bsm_teacherblog{
    text-align:left;
}

#bsm_teachertable .bsm_teacherlink1{
    text-align:left;
}


/* New Landing Page CSS */

.landingtable {
    clear:both;
    width:auto;
    display:table;

}

.landingrow {
    display:inline;
    padding: 1em;
}
.landingcell {
    display:table-cell;
}

.landinglink a{
    display:inline;
}

/* Terms of use or donate display settings */
.termstext {
}

.termslink{
}
/* Podcast Subscription Display Settings */

.podcastsubscribe{
    clear:both;
    display:table;
    width:100%;
    background-color:#eee;
    border-radius: 15px 15px 15px 15px;
    border: 1px solid grey;
    padding: 1em;
}
.podcastsubscribe .image {
    float: left;
    padding-right: 5px;
    display: inline;
}
.podcastsubscribe .image .text {
    display:inline;
    position:relative;
    right:50px;
    bottom:-10px;
}
.podcastsubscribe .prow {
    display: table-row;
    width:auto;
    clear:both;
}
.podcastsubscribe .pcell {
    display: table-cell;
    float:left;
    background-color:#e3e2e2;
    border-radius: 15px 15px 15px 15px;
    border: 1px solid grey;
    padding: 1em;
    margin-right: 5px;
}
.podcastheader h3{
    display:table-header;
    text-align:center;
}

.podcastheader{
    font-weight: bold;
}

.podcastlinks{
    display: inline;

}

.fltlft {
  float:left;
  padding-right: 5px;
}

/* Listing Page Items */
#subscribelinks {

}

div.listingfooter ul li {
    list-style: none outside none;
}

';

			if (JBSMDbHelper::fixupcss('biblestudy', true, $new710css, null))
			{
				$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
				$db->setQuery($query);
				$result = $db->loadObject();
				JBSMDbHelper::reloadtable($result, 'Style');
				self::setemptytemplates();

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
			$db->setQuery($query);
			$result = $db->loadObject();

			if (!$result)
			{
				$query = 'INSERT INTO #__bsms_styles (`published`, `filename`, `stylecode`, `asset_id`) VALUES (1,"biblestudy","' . $db->escape($newCSS) . '",0)';
				$db->setQuery($query);

				if (!$db->execute())
				{
					JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)));

					return JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true));
				}
				$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
				$db->setQuery($query);
				$result = $db->loadObject();
				JBSMDbHelper::reloadtable($result, 'Style');
				self::setemptytemplates();
				JError::raiseNotice(1, 'No CSS files where found so loaded default css info');

				return true;
			}
		}

		return true;

		// End if no new css file
	}

	/**
	 *  Set Empty templates
	 *
	 * @return void
	 */
	public static function setemptytemplates()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT id FROM #__bsms_templates';
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			// Store new Record so it can be seen.
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$table = JTable::getInstance('Template', 'Table', array('dbo' => $db));

			try
			{
				$table->load($result->id);

				// @todo this is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
				@$table->store();
				$table->load($result->id);
				$registry = new JRegistry;
				$registry->loadString($table->params);
				$css = $registry->get('css');
				$registry->set('css', 'biblestudy.css');

				// Now write the params back into the $table array and store.
				$table->params = (string) $registry->toString();

				// @todo this is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
				@$table->store();
			}
			catch (Exception $e)
			{
				JError::raiseWarning(1, 'Caught exception: ' . $e->getMessage());
			}
		}
	}

}
