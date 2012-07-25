<?php

/*
 * Class for updating the alias in certain tables
 * 
 */
class fixJBSalias
{
    
    /**
     * Update Alias for Upgrade form 7.1.0
     * @return boolean
     */
    function updateAlias() {
        $db = JFactory::getDBO();
        $objects = $this->getObjects(); 
        foreach ($objects as $object) {
            $results = $this->getTable($table = $object['name'], $title = $object['titlefield']); 
            
            foreach ($results as $result) { 
                $title = $object['titlefield'];
                $alias = JFilterOutput::stringURLSafe($result->title); 
                $query = 'UPDATE TABLE ' . $object['name'] . ' SET alias=' . $alias . 'WHERE id=' . $result->id; 
                dump ($query);
              //  $db->setQuery($query);
              //  $db->query();
            }
         
        }
        return TRUE;
    }
    /**
     * Get Table fiels for updateing.
     * @param string $table
     * @param string $title
     * @return boolean|array
     */
    function getTable($table, $title) {
        $data = array();
        if (!$table) {
            return false;
        }
        $db = JFactory::getDBO();

        $query = 'SELECT id, alias,' . $title . ' FROM ' . $table;
        $db->setQuery($query);
        //$db->query();
        $results = $db->loadObjectList(); 
        if (!$results) {
            return false;
        }
        foreach ($results as $result) {
            foreach ($result as $key => $value) {
                $data[] = $db->getEscaped($value);
            }
            $export = implode(',', $data);
        }
        return $export;
    }

    /**
     * Get Opjects for tables
     * @return array
     */
    function getObjects() {
        $objects = array(array('name' => '#__bsms_series', 'titlefield' => 'series_text'),
            array('name' => '#__bsms_studies', 'titlefield' => 'studytitle'),
            array('name' => '#__bsms_message_type', 'titlefield' => 'message_type'),
            array('name' => '#__bsms_teachers', 'titlefield' => 'teachername'),
        );
        return $objects;
    }
    
}
