<?php
/**
 
 * @license		GNU/GPL
 */
//This function is designed to save the template params to the database
// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 
 */
function bind($array, $ignore = '')
{ //dump($array, 'array: ');
        if (key_exists( 'params', $array ) && is_array( $array['params'] ))
        {
                $registry = new JRegistry();
                $registry->loadArray($array['params']);
                $array['params'] = $registry->toString();
        }
        return parent::bind($array, $ignore);
}

?>
