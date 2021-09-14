<?php
/**
 * Types html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

$app = Factory::getApplication();
$this->recordId = $app->input->get('recordId');
?>
<script type="text/javascript">
    setServer = function(type) {
        window.parent.Joomla.submitbutton('server.setType', type);
        window.parent.SqueezeBox.close();
    }
</script>
<div id="server_type" class="accordion">
    <?php $first = true;?>
    <?php foreach($this->types as $type => $servers): ?>
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#server_type" href="#<?php echo $type;?>">*<?php echo $type; ?>*</a>
        </div>
        <div id="<?php echo $type; ?>" class="accordion-body collapse <?php echo ($first) ? 'in': ''; ?>">
            <div class="accordion-inner">
                <?php foreach($servers as $server): ?>
                    <div>
                        <img class="pull-left" style="padding: 5px;" src="<?php echo $server->image_url; ?>"/>
                        <a href="#" title="<?php echo $server->title; ?>"
                           onclick="setServer('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'name' => $server->name))); ?>')">
                            <?php echo $server->title; ?>
                        </a>
                        <div>
                            <p><?php echo $server->description; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php $first = false; ?>
    <?php endforeach; ?>
</div>
