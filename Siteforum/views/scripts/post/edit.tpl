<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: edit.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
    function updateUploader()
    {
        if ($('photo_delete').checked) {
            $('photo_group-wrapper').style.display = 'block';
        }
        else
        {
            $('photo_group-wrapper').style.display = 'none';
        }
    }
</script>
<div class="generic_layout_container layout_middle"> 
    <?php echo $this->form->render($this) ?>
</div>