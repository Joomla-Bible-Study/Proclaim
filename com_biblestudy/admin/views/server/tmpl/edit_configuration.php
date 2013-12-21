<?php
//@todo: Add proper header

defined('_JEXEC') or die;

?>
<?php foreach ($this->config as $field): ?>
    <div class="control-group">
        <div class="control-label">
            <?php echo $field->label; ?>
        </div>
        <div class="controls">
            <?php echo $field->input; ?>
        </div>
    </div>
<?php endforeach; ?>