<?php

defined('_JEXEC') or die();

class Tablestudytopics extends JTable
{
        var $id = null;
        var $study_id = null;
        var $topic_id = null;
        
        function __construct(&$db)
        {
                parent::__construct( '#__bsms_studytopics', 'id', $db );
        }
}
