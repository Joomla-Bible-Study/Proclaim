<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die('Restricted access');

        $jconfig = new JConfig();

		$this->config['driver']   = 'mysql';
		$this->config['host']     = $jconfig->host;
		$this->config['user']     = $jconfig->user;
		$this->config['password'] = $jconfig->password;
		$this->config['database'] = $jconfig->db;
		$this->config['prefix']   = $jconfig->dbprefix;
        $this->db_new = JDatabase::getInstance($this->config);
        $db = JFactory::getDBO();
        
        // Getting the asset table
	//	$table = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $db->query();
        $parent_id = $db->loadResult();
        
        
        $query = "SELECT id, server_name FROM #__bsms_servers";
        $db->setQuery($query);
        $db->query();
        $servers = $db->loadObjectList();
        
        foreach ($servers AS $server)
        {
            $table = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));
            $table->name = 'com_biblestudy.serversedit.'.$server->id;
            $table->parent_id = $parent_id;
            $table->level = 2;
            $table->rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';
            $table->title = mysql_real_escape_string($server->server_name);
            
            
          //  echo $parent_id; echo $table->level;
           	$table->store();
         //print_r($table);
           	$query = "UPDATE #__bsms_servers SET asset_id = {$table->id}"
		." WHERE id = {$server->id}";
		$this->db_new->setQuery($query);
		$this->db_new->query();
    
        } 
        
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
 ?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=commentslist'); ?>" method="post" name="adminForm" id="adminForm">
<fieldset id="filter-bar">
    <div class="filter-select fltrt">

			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
   </div>
</fieldset>
<?php //echo $this->lists['studyid']; ?>
<div id="editcell">
	<table class="adminlist">

      <thead>
        <tr> 
          <th width="1"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /> </th>
          <th width="20" align="center"> <?php echo JHTML::_('grid.sort',  'JBS_CMN_PUBLISHED', 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>
          <th width="200"> <?php  echo JHTML::_('grid.sort',  'JBS_CMN_STUDY_TITLE', 's.studytitle', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th width = "100"><?php echo JText::_('JBS_CMT_FULL_NAME'); ?></th>
          <th width = "100">  <?php echo JHTML::_('grid.sort',  'JBS_CMT_COMMENT_DATE', 'c.comment_date', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>       
        </tr>
      </thead>
      <?php
foreach ($this->items as $i => $item) :
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&task=commentsedit.edit&id='. (int) $item->id );
		?>
<tr class="row<?php echo $i % 2; ?>">
                                <td width="1">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
        <td align="center" width="20">
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'commentslist.', true, 'cb', '', ''); ?>
        </td>
        <td> <a href="<?php echo $link; ?>"><?php echo $item->studytitle.' - '.JText::_($item->bookname).' '.$item->chapter_begin; ?></a> </td>
        <td> <?php echo $item->full_name; ?> </td>
        <td> <?php echo $item->comment_date; ?> </td>
      </tr>
      <?php endforeach; ?>
    
      <tfoot><tr>
      <td colspan="10"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
    </table>


</div>
<input type="hidden" name="task" value=""/>
                    <input type="hidden" name="boxchecked" value="0"/>
                    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
                    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
