<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Classified
* @copyright  Copyright 2006-2010 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: manage.tpl 9987 2013-03-20 00:58:10Z john $
* @author     Jung
*/
?>
<?php $this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<script type="text/javascript">
  var pageAction = function (page) {
    $('page').value = page;
    $('filter_form').submit();
  }


</script>



<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
//'topLevelId' => (int) @$this->topLevelId,
//'topLevelValue' => (int) @$this->topLevelValue
))
?>



<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
<ul class="classifieds_browse aaf_buysell_product_browse">
  <?php  foreach( $this->paginator as $item ): ?>
  <li>
    <?php $photo = Engine_Api::_()->getItem("album_photo", $item->photo_id); 
    if($photo) :
    ?> 
    <div class='classifieds_browse_photo'>
      <img src='<?php echo $photo->getPhotoUrl() ?>' /> 
    </div>
    <?php endif; ?>
    <div class='classifieds_browse_options'>
      <?php echo $this->htmlLink(array(
      'route' => 'default',
      'module' => 'advancedactivity',
      'controller' => 'buy-sell',
      'action' => 'edit',
      'sell_id' => $item->getIdentity(),
      ), $this->translate('Edit Advertising'), array(
      'class' => 'buttonlink icon_classified_edit'
      )) ?>

      <?php?>
     <!-- <?php echo $this->htmlLink(array(
      'route' => 'default',
      'module' => 'advancedactivity',
      'controller' => 'buy-sell',
      'action' => 'manage-photos',
      'sell_id' => $item->getIdentity(),
      ), $this->translate('Add More Photos'), array(
      'class' => 'buttonlink icon_classified_photo_new'
      )) ?> -->


      <?php if( !$item->closed ): ?>
      <?php echo $this->htmlLink(array(
      'route' => 'default',
      'module' => 'advancedactivity',
      'controller' => 'buy-sell',
      'action' => 'open-close',
      'sell_id' => $item->getIdentity(),
      'closed' => 1,
      ), $this->translate('Close Advertising'), array(
      'class' => 'buttonlink icon_classified_close'
      )) ?>
      <?php else: ?>
      <?php echo $this->htmlLink(array(
      'route' => 'default',
      'module' => 'advancedactivity',
      'controller' => 'buy-sell',
      'action' => 'open-close',
      'sell_id' => $item->getIdentity(),
      'closed' => 0,
      ), $this->translate('Open Advertising'), array(
      'class' => 'buttonlink icon_classified_open'
      )) ?>
      <?php endif; ?>

      <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'advancedactivity', 'controller' => 'buy-sell', 'action' => 'delete', 'sell_id' => $item->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete sell'), array(
      'class' => 'buttonlink smoothbox icon_classified_delete'
      )) ?>
    </div>
    <div class='classifieds_browse_info'>
      <div class='classifieds_browse_info_title'>
        <h3>
          <?php if( $item->closed ): ?>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle(),array('class'=>'aaf_product_close')) ?>
          <?php else: ?>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
          <?php endif;?>
        </h3>
      </div>
      <div class='classifieds_browse_info_date'>
        <?php echo $this->timestamp(strtotime($item->date)) ?>
        -
        <?php echo $this->translate('posted by');?> <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>
      </div>
      <div class='classifieds_browse_info_blurb'>
        <?php $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($item)?>
        <?php echo $this->fieldValueLoop($item, $fieldStructure) ?>
        <?php echo $this->translate("at ").$item->place ?>
      </div>
    </div>
  </li>
  <?php endforeach; ?>
</ul>


<?php else:?>
<div class="tip">
  <span>
    <?php echo $this->translate('You do not have any Advertising.');?>
    
  </span>
</div>

<?php endif; ?>
<?php  echo $this->paginationControl($this->paginator, null, null); ?>

