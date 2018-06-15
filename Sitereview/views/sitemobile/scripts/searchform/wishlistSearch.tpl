<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _addToWishlist.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<script type="text/javascript">
  function showMemberNameSearch() {
      if($.mobile.activePage.find('#search_wishlist')) {
        $.mobile.activePage.find('#text-wrapper').css('display', ($.mobile.activePage.find('#search_wishlist').val()) == '' ? 'block':'none');
        
        $.mobile.activePage.find('#text').val($.mobile.activePage.find('#search_wishlist').val() == '' ? $.mobile.activePage.find('#text').val() : '');
      }
    }
  sm4.core.runonce.add(function(){    
    showMemberNameSearch();
  });
</script>  