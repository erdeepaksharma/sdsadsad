<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: topic-create.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
    function showUploader()
    {
        $('photo').style.display = 'block';
        $('photo-label').style.display = 'none';
    }
</script>

<?php echo $this->form->render($this) ?>

<script type="text/javascript">
    $$('.core_main_siteforum').getParent().addClass('active');
</script>
