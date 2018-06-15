<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: editlocation.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php echo $this->partial('application/modules/Sitereview/views/sitemobile/scripts/dashboard/header.tpl', array('sitereview'=> $this->sitereview));?>
<div class="dashboard-content">
  <div class="global_form">
    <h3><?php echo $this->translate("Edit $this->listing_singular_uc Location") ?></h3>
    <p><?php echo $this->translate("Edit the location of your $this->listing_singular_lc by clicking on 'Edit $this->listing_singular_uc Location' below.") ?>    </p>
    <?php if (!empty($this->location)): ?>
      <div class="t_l">
        <?php
          echo $this->htmlLink(array(
          'route' => "sitereview_specific_listtype_$this->listingtype_id",
          'action' => 'editaddress',
          'listing_id' => $this->sitereview->listing_id
          ), $this->translate("Edit $this->listing_singular_uc Location"), array(
          'class' => 'fright'
          ));
        ?>
        <span class="o_hidden">
          <?php echo $this->translate('Location: ');?>
          <strong><?php echo $this->location->location ?></strong>
        </span>
      </div>
      <div class="sm-ui-map-wrapper sm-ui-edit-location-map clr">
        <div class="sm-ui-map" id="mapCanvas"></div>
        <?php $siteTitle = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title; ?>
        <?php if (!empty($siteTitle)) : ?>
          <div class="sm-ui-map-info">
          <?php echo $this->translate("Locations on %s","<a href='' target='_blank'>$siteTitle</a>");?>
          </div>
        <?php endif; ?>
      </div>	
    <?php else: ?>
      <div class="tip">
        <span>
          <?php $url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'editaddress', 'listing_id' => $this->sitereview->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true);?>
          <?php echo $this->translate('You have not added a location for your '.$this->listing_singular_lc.'. %1$sClick here%2$s to add a location for your '.$this->listing_singular_lc.'.', "<a href='$url'>", "</a>"); ?>
        </span>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php if (!empty($this->location)): ?>
    <script type="text/javascript">
        sm4.core.runonce.add(function() {
          sm4.core.map.initialize('mapCanvas',{latitude:'<?php echo $this->location->latitude ?>',longitude:'<?php echo $this->location->longitude ?>',location_id:'<?php echo $this->location->location_id ?>',marker:true,title:'<?php echo $this->location->location; ?>',zoom:<?php echo $this->location->zoom; ?>});
        });
    </script>
<?php endif; ?>