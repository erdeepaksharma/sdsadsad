<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: header.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
Engine_Api::_()->sitereview()->setListingTypeInRegistry($this->sitereview->listingtype_id);
$listingType = Zend_Registry::get('listingtypeArray' . $this->sitereview->listingtype_id);
?>

<div class="dashboard-header o_hidden b_medium">
  <h3>
   <b> <?php echo $this->translate('Dashboard'); ?>:</b> 
   <?php echo $this->htmlLink($this->sitereview->getHref(), $this->sitereview->getTitle()) ?>
  </h3>
  <?php echo $this->htmlLink($this->sitereview->getHref(), $this->translate('View '.ucfirst($listingType->title_singular)), array("class" => 'ui-btn ui-icon-arrow-r ui-btn-icon-right')) ?>
</div>