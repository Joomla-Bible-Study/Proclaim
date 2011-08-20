<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_biblestudy' . DS . 'lib' . DS . 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_biblestudy' . DS . 'helpers' . DS . 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewstudiesedit extends JView {

	protected $form;
	protected $item;
	protected $state;
	protected $admin;

	function display($tpl = null) {
		$this->form = $this->get("Form");
		$this->item = $this->get("Item");

		$this->mediafiles = $this->get('MediaFiles');
		$this->setLayout('form');

		$this->loadHelper('params');
		$this->admin = BsmHelper::getAdmin();
		$this->canDo = BibleStudyHelper::getActions($type = 'studiesedit', $Itemid = $this->item->id);
		$this->addToolbar();

		$document = JFactory::getDocument();
		$document->addScript(JURI::base() . 'components/com_biblestudy/js/plugins/jquery.tokeninput.js');
		$document->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/token-input-jbs.css');

		$script = "
            \$j(document).ready(function() {
                \$j('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . JText::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . JText::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . JText::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    prePopulate: " . $this->get('topics') . "
                });
            });
             ";

		$document->addScriptDeclaration($script);


		$document->addStyleSheet(JURI::base() . 'components/com_biblestudy/js/ui/theme/ui.all.css');
		$document->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/jquery.tagit.css');


		$document->addScript(JURI::base() . 'components/com_biblestudy/js/biblestudy.js');





		parent::display($tpl);
	}

	protected function addToolbar() {
		$isNew = ($this->item->id < 1);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>', 'studies.png');

		$canDo = BibleStudyHelper::getActions($this->item->id, 'studiesedit');
		if ($this->canDo->get('core.edit', 'com_biblestudy')) {
			JToolBarHelper::save('studiesedit.save');
			JToolBarHelper::apply('studiesedit.apply');
		}
		JToolBarHelper::cancel('studiesedit.cancel', 'JTOOLBAR_CANCEL');
		if ($this->canDo->get('core.edit', 'com_biblestudy') && !$isNew) {
			JToolBarHelper::divider();
			JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

}
