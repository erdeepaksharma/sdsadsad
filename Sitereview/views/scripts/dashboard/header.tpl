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

<div class="sr_dashboard_header">
  <span class="fright">
    <?php echo $this->htmlLink($this->sitereview->getHref(), $this->translate('View '.ucfirst($listingType->title_singular)), array("class" => 'sr_buttonlink')) ?>
  </span>
  <span class="sr_dashboard_header_title o_hidden">
    <?php echo $this->translate('Dashboard'); ?>: 
    <?php echo $this->htmlLink($this->sitereview->getHref(), $this->sitereview->getTitle()) ?>
  </span>	
</div>