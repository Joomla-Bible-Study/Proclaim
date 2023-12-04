<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No direct access
defined('_JEXEC') or die();

?>
<input type="hidden" name="current_folder" value="<?php
echo $this->currentFolder; ?>"/>
<input type="hidden" name="<?php
echo JSession::getFormToken(); ?>" value="1"/>
</tbody>
</table>
</form>
