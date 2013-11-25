<?php // no direct access
defined('_JEXEC') or die('Restricted Access');

?>

<form action="" method="post">

    <div id="uploader">

        <p><?php JText::printf('JBS_ERROR_RUNTIME_NOT_SUPORTED', $this->runtime) ?></p>

    </div>
    <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />

</form>