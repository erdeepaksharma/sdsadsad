<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php

	$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css');
?>
<?php if($this->viewType == 'horizontal'): ?>
  <div class="seaocore_searchform_criteria  sr_wishlist_browse_search">
    <?php echo $this->form->setAttrib('class', 'sr_item_filters')->render($this) ?>
  </div>
<?php else: ?>
  <div class="seaocore_searchform_criteria">
    <?php echo $this->form->render($this) ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  showMemberNameSearch();
  function showMemberNameSearch() {
    if($('search_wishlist')) {
      $('text-wrapper').setStyle('display', ($('search_wishlist').get('value') == '' ?'block':'none'));
      $('text').value = $('search_wishlist').get('value') == '' ? $('text').value : '';
    }
  }
</script>  
