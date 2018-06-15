<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: add.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
  function otherWhereToBuy(value){
    if(value==1){
      $('#title-wrapper').css('display', 'block');
      $('#address-wrapper').css('display', 'block');
      $('#contact-wrapper').css('display', 'block');
    }else{
     $('#title-wrapper').css('display', 'none'); 
     $('#address-wrapper').css('display', 'none');
     $('#contact-wrapper').css('display', 'none');
    }
  }

  sm4.core.runonce.add(function() {  
     otherWhereToBuy($('#wheretobuy_id').val());
  });

</script>
 
<div>
  <?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview' => $this->sitereview)); ?>
  <?php echo $this->form->render($this) ?>
</div>

