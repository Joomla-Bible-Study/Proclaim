<?php
/**
 * Run on all updates
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

//Remove Old Language Files Administrator
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.sys.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.sys.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.sys.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.sys.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.sys.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.sys.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.sys.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.sys.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/nl-NL/nl-NL.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/nl-NL/nl-NL.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/nl-NL/no-NO.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/nl-NL/no-NO.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ADMINISTRATOR . '/language/no-NO/no-NO.com_biblestudy.sys.ini') == TRUE):
    JFile::delete(JPATH_ADMINISTRATOR . '/language/no-NO/no-NO.com_biblestudy.sys.ini');
endif;

// Language files for Site
if (JFile::exists(JPATH_ROOT . '/language/en-GB/en-GB.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/en-GB/en-GB.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ROOT . '/language/cs-CZ/cs-CZ.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/cs-CZ/cs-CZ.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ROOT . '/language/de-DE/de-DE.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/de-DE/de-DE.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ROOT . '/language/es-ES/es-ES.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/es-ES/es-ES.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ROOT . '/language/hu-HU/hu-HU.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/hu-HU/hu-HU.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ROOT . '/language/nl-NL/nl-NL.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/nl-NL/nl-NL.com_biblestudy.ini');
endif;
if (JFile::exists(JPATH_ROOT . '/language/no-NO/no-NO.com_biblestudy.ini') == TRUE):
    JFile::delete(JPATH_ROOT . '/language/no-NO/no-NO.com_biblestudy.ini');
endif;
