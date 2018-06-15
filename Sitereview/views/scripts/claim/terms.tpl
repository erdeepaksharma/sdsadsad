<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: terms.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<div class="sitereview_claim_turms">
  <b><?php echo $this->translate('Terms of claiming a Listing') ?></b>
  <ol>
    <li><?php echo $this->translate('LISTING_TERMS_CLAIM_1') ?></li>		
    <li><?php echo $this->translate('LISTING_TERMS_CLAIM_2') ?></li>
  </ol>
  <br />
  <p><?php echo $this->translate('Thank you for your cooperation.') ?></p>
</div>

<style type="text/css">
  *{
    font-size:12px;
    font-family:Arial, Helvetica, sans-serif;
  }
  .sitereview_claim_turms
  {
    margin:10px;
  }
  ol{
    float:left;
    width:100%;
    clear:both;
    margin-bottom:10px;
  }
  ol li
  {
    margin-left:30px;
    clear:both;
    margin-top: 5px;
  }
  p{
    margin-left:30px;
    float:left;
  }
</style>