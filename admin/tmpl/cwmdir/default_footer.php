<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No direct access
use Joomla\CMS\Session\Session;

\defined('_JEXEC') or die();
/** @var CWM\Component\Proclaim\Administrator\View\Cwmdir\HtmlView $this */
?>
<input type="hidden" name="current_folder" value="<?php
echo $this->currentFolder; ?>"/>
<input type="hidden" name="<?php
echo Session::getFormToken(); ?>" value="1"/>
</tbody>
</table>
</form>
