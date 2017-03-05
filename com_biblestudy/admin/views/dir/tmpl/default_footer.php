<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No direct access
defined('_JEXEC') or die();

?>
        <input type="hidden" name="current_folder" value="<?php echo $this->currentFolder; ?>" />
        <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
        </tbody>
    </table>
</form>
<?php if(JBSMDEBUG) : ?>
BREADCRUMBS
<?php var_dump($this->breadcrumbs); ?>
FOLDERS
<?php var_dump($this->folders); ?>
Files
<?php var_dump($this->files); ?>
<?php endif;
