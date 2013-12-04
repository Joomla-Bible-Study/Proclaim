<?php

// No direct access
defined('_JEXEC') or die('Restricted Access');

?>
<script>
    jQuery(document).ready(function(){
        jQuery("body").html("<b>Hello World</b>");
    });
</script>
<body>
<form action="" method="post">

    <div id="uploader">

        <p><?php JText::printf('JBS_ERROR_RUNTIME_NOT_SUPORTED', $this->runtime) ?></p>

    </div>
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />


</form>

</body>
