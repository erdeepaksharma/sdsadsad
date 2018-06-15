<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: remove.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate('Remove Mapping ?'); ?></h3>
    <p>
      <?php echo $this->translate('Are you sure that you want to remove this review profile - category mapping? (Note that after removing this mapping, the earlier reviews for listings associated with this mapping will loose their custom profile data.)'); ?>
    </p>
    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->category_id ?>"/>
      
      <?php if($this->countChilds && ($this->category->cat_dependency == 0 && $this->category->subcat_dependency == 0)): ?>
        <input type="checkbox" name="import_profile" /><?php echo $this->translate("Map sub-categories of this category with the same review profile type so that reviews for listings associated with them does not loose their custom profile data."); ?><br/><br/>
      <?php elseif($this->countChilds && ($this->category->cat_dependency != 0 && $this->category->subcat_dependency == 0)): ?>  
        <input type="checkbox" name="import_profile" /><?php echo $this->translate("Map 3rd level categories of this sub-category with the same review profile type so that reviews for listings associated with them does not loose their custom profile data."); ?><br/><br/>
      <?php endif;?>
      
      <button type='submit'><?php echo $this->translate('Save Changes'); ?></button>
      or <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate('cancel'); ?></a>
    </p>
  </div>
</form>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>
