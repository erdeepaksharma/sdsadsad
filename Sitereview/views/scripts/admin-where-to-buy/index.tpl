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

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<h3><?php echo $this->translate('Where to Buy Settings'); ?></h3>
<p class="form-description">
  <?php echo $this->translate('This feature enables you to allow users of your site to add price and links of their listings available at various e-commerce sites where there item will be available for purchase. With this feature users will be able to add various links for the availability of their listings at one place.<br/><br/>Here, you can manage various "Where to Buy" options which will be available to users to add price and links for their listings. Below, you can add new option by using "Add New \'Where to Buy\'" link. You can also edit and delete options added by you by clicking on the links for each.<br/><br /><b>Note:</b> You can also use \'Where to Buy\' option as \'References\' by allowing Where to Buy, without Price field while adding / editing a listing type from the \'Manage Listing Types\' section of this plugin. With the \'Others\' option, users will be able to add links of their listings available at other e-commerce site, which are not added here.') ?>
</p>

<br />
<p>
  <a href='<?php echo $this->url(array("module" => "sitereview", "controller" => "where-to-buy", "action" => 'add'), "admin_default", true) ?>' class="smoothbox buttonlink seaocore_icon_add"><?php echo $this->translate("Add New 'Where to Buy'"); ?></a>
</p>

<br />
<?php if ($this->totalCount): ?>

  <table class='admin_table'>
    <thead>
      <tr>
        <th style='width: 1%;' align="center" title="<?php echo $this->translate('ID'); ?>"><?php echo $this->translate('ID'); ?></th>
        <th align="left" title="<?php echo $this->translate('Title'); ?>"><?php echo $this->translate('Title'); ?></th>
        <th align="left" title="<?php echo $this->translate('Icon'); ?>"><?php echo $this->translate('Icon'); ?></th>
        <th align="left" title="<?php echo $this->translate('Enabled'); ?>"><?php echo $this->translate('Enabled'); ?></th>

        <th class='admin_table_options' align="left" title="<?php echo $this->translate('Options'); ?>"><?php echo $this->translate('Options'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->list as $item): ?>
        <tr>
          <td style='width: 1%;' class="admin_table_centered"><?php echo $item->getIdentity() ?></td>
          <td align="left"><?php echo $item->getTitle() ?></td>
          <td align="left">
             <span  title="<?php echo $item->getTitle() ?>" class="sr_price_info_image">
            <?php if($item->photo_id): ?>
              <?php echo $this->itemPhoto($item, null, ""); ?>
            <?php elseif( $item->getIdentity()==1): ?>
            N/A
            <?php else:?>
            <?php echo $item->getTitle() ?>
          <?php endif;?></span></td>
          <td align="left">
            <a href='<?php echo $this->url(array("module" => "sitereview", "controller" => "where-to-buy", "action" => 'enabled', 'id' => $item->getIdentity()), "admin_default", true) ?>' >
              <?php if (!empty($item->enabled)): ?>
                <img src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/approved.gif' ?>" alt="" title="Make Disabled">
              <?php else: ?>
                <img src="<?php echo $this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/images/disapproved.gif' ?>" alt="" title="Make Enabled">
              <?php endif; ?></a>

          </td>
          <td align="left">
            <a href='<?php echo $this->url(array("module" => "sitereview", "controller" => "where-to-buy", "action" => 'edit', 'id' => $item->getIdentity()), "admin_default", true) ?>' class="smoothbox"><?php echo "Edit"; ?></a> |
            <?php if($item->getIdentity()==1): ?> Remove Icon | Delete
            <?php else:?>
            <?php if($item->photo_id): ?> <a href='<?php echo $this->url(array("module" => "sitereview", "controller" => "where-to-buy", "action" => 'remove-icon', 'id' => $item->getIdentity()), "admin_default", true) ?>' class="smoothbox"><?php echo "Remove Icon"; ?></a> <?php else:?>
            <?php echo "Remove Icon"; ?>
          <?php endif;?>|
            <a href='<?php echo $this->url(array("module" => "sitereview", "controller" => "where-to-buy", "action" => 'delete', 'id' => $item->getIdentity()), "admin_default", true) ?>' class="smoothbox"><?php echo "Delete"; ?></a>
            
          <?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <div class="tip" style="width: 100%">
    <span style="width: 100%"><?php echo $this->translate('No record Found!') ?></span>
  </div>
<?php endif; ?>
