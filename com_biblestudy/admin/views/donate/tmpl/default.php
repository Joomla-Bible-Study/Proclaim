<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
// No Direct Access
defined('_JEXEC') or die;

?>
<!-- Header -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="paypal" id="paypal" target="_self">
	<h1 class="center">Please wait loading ...</h1>
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="tfuller@calvarynewberg.org">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="item_name" value="Joomla Bible Study Team">
	<input type="hidden" name="no_note" value="0">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
	       name="submit" alt="PayPal - The safer, easier way to pay online!" style="display: none;">
</form>
<script type="text/javascript">
	document.paypal.submit();
</script>
