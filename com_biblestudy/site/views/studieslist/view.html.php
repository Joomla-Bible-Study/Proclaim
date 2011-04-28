<?php

defined('_JEXEC') or die();
jimport('joomla.application.component.view');
require_once (JPATH_ROOT . DS . 'components' . DS . 'com_biblestudy' . DS . 'lib' . DS . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DS . 'components' . DS . 'com_biblestudy' . DS . 'lib' . DS . 'biblestudy.stats.class.php');
require_once (JPATH_ROOT . DS . 'components' . DS . 'com_biblestudy' . DS . 'lib' . DS . 'biblestudy.admin.class.php');

class biblestudyViewstudieslist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    /**
     * studieslist view display method
     * @return void
     * */
    function display($tpl = null) {


        $this->state = $this->get('State');
      //  $items = $this->get('Items');
        $items = $this->get('Data');
        $this->pagination = $this->get('Pagination');

        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);
        
        $admin_parameters = $this->get('Admin');
        
        $this->admin_params = new JParameter($admin_parameters[0]->params);
        
        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups	= $user->getAuthorisedViewLevels(); 
        $count = count($items);
        
        for ($i = 0; $i < $count; $i++)
        {
            
            if ($items[$i]->access > 1)
            {
               if (!in_array($items[$i]->access,$groups))
               {
                    unset($items[$i]); 
               } 
	        }
        }
        $this->items = $items;
      
 $t = JRequest::getInt('t','get',1);
        if (!$t) {
            $t = 1;
        }
        JRequest::setVar('t', $t, 'get');
        $template = $this->get('template');
        $params = new JParameter($template[0]->params);
        $a_params = $this->get('Admin');
        $this->admin_params = new JParameter($a_params[0]->params);
        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $path1 = JPATH_SITE . DS . 'components' . DS . 'com_biblestudy' . DS . 'helpers' . DS;
        include_once($path1 . 'image.php');

        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers');
        $document = & JFactory::getDocument();
        $model = & $this->getModel();
        
        //See if user has permission to edit and whether admin params are set to allow front end editing of studies
        $admin = new JBSAdmin();
        $allow = $admin->getPermission();
        $this->assignRef('allow', $allow);
        
        $document = & JFactory::getDocument();
        JHTML::_('behavior.mootools');
        $document->addStyleSheet(JURI::base() . 'components/com_biblestudy/assets/css/biblestudy.css');
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        //Errors when using local swfobject.js file.  IE 6 doesn't work
        //Import Scripts
        $document->addScript(JURI::base() . 'components/com_biblestudy/assets/js/jquery.js');
        $document->addScript(JURI::base() . 'components/com_biblestudy/assets/js/noconflict.js');
        $document->addScript(JURI::base() . 'components/com_biblestudy/assets/js/biblestudy.js');
        $document->addScript(JURI::base() . 'components/com_biblestudy/assets/js/views/studieslist.js');
        $document->addScript(JURI::base() . 'components/com_biblestudy/tooltip.js');
        $document->addScript(JURI::base().'components/com_biblestudy/assets/css/biblestudy.js');
        //Styles from tooltip.css moved to assets/css/biblestudy.css
        //Import Stylesheets
        $document->addStylesheet(JURI::base() . 'components/com_biblestudy/assets/css/general.css');

        $url = $params->get('stylesheet');
        if ($url) {
            $document->addStyleSheet($url);
        }

        $uri = & JFactory::getURI();
        $filter_topic = $mainframe->getUserStateFromRequest($option . 'filter_topic', 'filter_topic', 0, 'int');
        $filter_book = $mainframe->getUserStateFromRequest($option . 'filter_book', 'filter_book', 0, 'int');

        if ($filter_book != 0) {
            $filter_chapter = $mainframe->getUserStateFromRequest($option . 'filter_chapter', 'filter_chapter', 0, 'int');
        }
        $filter_teacher = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
        $filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
        $filter_messagetype = $mainframe->getUserStateFromRequest($option . 'filter_messagetype', 'filter_messagetype', 0, 'int');
        $filter_year = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
        $filter_location = $mainframe->getuserStateFromRequest($option . 'filter_location', 'filter_location', 0, 'int');
        $filter_orders = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');
        $search = JString::strtolower($mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string'));
        $total = $this->get('Total');
        //Remove the studies the user is not allowed to see

        $pagination = $this->get('Pagination');
        $teachers = $this->get('Teachers');
        $series = $this->get('Series');
        $messageTypes = $this->get('MessageTypes');
        $studyYears = $this->get('StudyYears');
        $locations = $this->get('Locations');
        $topics = $this->get('Topics');
        $orders = $this->get('Orders');
        $books = $this->get('Books');

        //This is the helper for scripture formatting
        $scripture_call = Jview::loadHelper('scripture');
        //end scripture helper
        $translated_call = JView::loadHelper('translated');
        $topics = getTranslated($topics);

        $orders = getTranslated($orders);
        $book = getTranslated($books);
        $this->assignRef('template', $template);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('order', $orders);
        $this->assignRef('topic', $topics);
        $menu = & JSite::getMenu();
        $item = & $menu->getActive();
        $images = new jbsImages();
        $main = $images->mainStudyImage(); // dump ($main, 'main: ');

        $this->assignRef('main', $main);

        //Get the Popular stats
        $stats = new jbStats();
        $popular = $stats->top_score_site($item->id);
        $this->assignRef('popular', $popular);
        //Get whether "Go" Button is used then turn off onchange if it is
        if ($params->get('use_go_button', 0) == 0) {
            $go = 'onchange="this.form.submit()"';
        }
        $types[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TEACHER'));
        $types = array_merge($types, $teachers);
        $lists['teacher_id'] = JHTML::_('select.genericlist', $types, 'filter_teacher', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_teacher");

        //Build Series List for drop down menu
        $types3[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_SERIES'));
        $types3 = array_merge($types3, $series);
        $lists['seriesid'] = JHTML::_('select.genericlist', $types3, 'filter_series', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_series");

        //Build message types
        $types4[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_MESSAGE_TYPE'));
        $types4 = array_merge($types4, $messageTypes);
        $lists['messagetypeid'] = JHTML::_('select.genericlist', $types4, 'filter_messagetype', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_messagetype");

        //build study years
        $years[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_YEAR'));
        $years = array_merge($years, $studyYears);
        $lists['studyyear'] = JHTML::_('select.genericlist', $years, 'filter_year', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_year");

        //build locations
        $loc[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_LOCATION'));
        $loc = array_merge($loc, $locations);
        $lists['locations'] = JHTML::_('select.genericlist', $loc, 'filter_location', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_location");

        //Build Topics
        $top[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_TOPIC'));
        $top = array_merge($top, $topics);
        $lists['topics'] = JHTML::_('select.genericlist', $top, 'filter_topic', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_topic");


        //Build Books
        $boo[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_BOOK'));
        $boo = array_merge($boo, $book);
        $lists['books'] = JHTML::_('select.genericlist', $boo, 'filter_book', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_book");

        //Build Chapters
        $chap[] = JHTML::_('select.option', '0', '- ' . JTEXT::_('JBS_STY_SELECT_CHAPTER') . ' -');
        switch (JRequest::getInt('filter_book')) {
            case 101:
                $maxBooks = 50;
                break;
            case 102:
                $maxBooks = 40;
                break;
            case 103:
                $maxBooks = 27;
                break;
            case 104:
                $maxBooks = 36;
                break;
            case 105:
                $maxBooks = 34;
                break;
            case 106:
                $maxBooks = 24;
                break;
            case 107:
                $maxBooks = 21;
                break;
            case 108:
                $maxBooks = 4;
                break;
            case 109:
                $maxBooks = 31;
                break;
            case 110:
                $maxBooks = 24;
                break;
            case 111:
                $maxBooks = 22;
                break;
            case 112:
                $maxBooks = 25;
                break;
            case 113:
                $maxBooks = 29;
                break;
            case 114:
                $maxBooks = 36;
                break;
            case 115:
                $maxBooks = 10;
                break;
            case 116:
                $maxBooks = 13;
                break;
            case 117:
                $maxBooks = 10;
                break;
            case 118:
                $maxBooks = 42;
                break;
            case 119:
                $maxBooks = 150;
                break;
            case 120:
                $maxBooks = 31;
                break;
            case 121:
                $maxBooks = 12;
                break;
            case 122:
                $maxBooks = 8;
                break;
            case 123:
                $maxBooks = 66;
                break;
            case 124:
                $maxBooks = 52;
                break;
            case 125:
                $maxBooks = 5;
                break;
            case 126:
                $maxBooks = 48;
                break;
            case 127:
                $maxBooks = 14;
                break;
            case 128:
                $maxBooks = 14;
                break;
            case 129:
                $maxBooks = 4;
                break;
            case 130:
                $maxBooks = 9;
                break;
            case 131:
                $maxBooks = 1;
                break;
            case 132:
                $maxBooks = 4;
                break;
            case 133:
                $maxBooks = 7;
                break;
            case 134:
                $maxBooks = 3;
                break;
            case 135:
                $maxBooks = 3;
                break;
            case 136:
                $maxBooks = 3;
                break;
            case 137:
                $maxBooks = 2;
                break;
            case 138:
                $maxBooks = 14;
                break;
            case 139:
                $maxBooks = 3;
                break;
            case 140:
                $maxBooks = 28;
                break;
            case 141:
                $maxBooks = 16;
                break;
            case 142:
                $maxBooks = 24;
                break;
            case 143:
                $maxBooks = 21;
                break;
            case 144:
                $maxBooks = 28;
                break;
            case 145:
                $maxBooks = 16;
                break;
            case 146:
                $maxBooks = 16;
                break;
            case 147:
                $maxBooks = 13;
                break;
            case 148:
                $maxBooks = 6;
                break;
            case 149:
                $maxBooks = 6;
                break;
            case 150:
                $maxBooks = 4;
                break;
            case 151:
                $maxBooks = 4;
                break;
            case 152:
                $maxBooks = 5;
                break;
            case 153:
                $maxBooks = 3;
                break;
            case 154:
                $maxBooks = 6;
                break;
            case 155:
                $maxBooks = 4;
                break;
            case 156:
                $maxBooks = 3;
                break;
            case 157:
                $maxBooks = 1;
                break;
            case 158:
                $maxBooks = 13;
                break;
            case 159:
                $maxBooks = 5;
                break;
            case 160:
                $maxBooks = 5;
                break;
            case 161:
                $maxBooks = 3;
                break;
            case 162:
                $maxBooks = 5;
                break;
            case 163:
                $maxBooks = 1;
                break;
            case 164:
                $maxBooks = 1;
                break;
            case 165:
                $maxBooks = 1;
                break;
            case 166:
                $maxBooks = 22;
                break;
            case 167:   // JBS_BBK_TOBIT
                $maxBooks = 14;
                break;
            case 168:   // JBS_BBK_JUDITH
                $maxBooks = 16;
                break;
            case 169:   // JBS_BBK_1MACCABEES
                $maxBooks = 16;
                break;
            case 170:   // JBS_BBK_2MACCABEES
                $maxBooks = 15;
                break;
            case 171:   // JBS_BBK_WISDOM
                $maxBooks = 19;
                break;
            case 172:   // JBS_BBK_SIRACH
                $maxBooks = 51;
                break;
            case 173:   // JBS_BBK_BARUCH
                $maxBooks = 6;
                break;
        }
        for ($c = 1; $c <= $maxBooks; $c++) {
            $chap[] = JHTML::_('select.option', $c, $c);
        }
        $lists['chapters'] = JHTML::_('select.genericlist', $chap, 'filter_chapter', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_chapter");

        //Build order
        $ord[] = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_ORDER'));
        $ord = array_merge($ord, $orders);
        $lists['orders'] = JHTML::_('select.genericlist', $ord, 'filter_orders', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_orders");

        $lists['search'] = $search;

        $this->assignRef('lists', $lists);
        $this->assignRef('items', $items);

        $this->assignRef('request_url', $uri->toString());
        $this->assignRef('params', $params);
        parent::display($tpl);
    }

}

?>