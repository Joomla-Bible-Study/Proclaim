<?php
/**
 * jUpgrade
 *
 * @version		$Id: done.php 20605 2011-02-08 00:25:11Z eddieajau $
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

			$assetdofix = $assetfix->AssetEntry();
            echo '<p>';
            if ($assetdofix){echo '<font color="green">'.JText::_('JBS_INS_16_ASSET_SUCCESS').'</font>';}else{echo '<font color="red">'.JText::_('JBS_INS_16_ASSET_FAILURE').'</font>';} 
            echo '</p>';