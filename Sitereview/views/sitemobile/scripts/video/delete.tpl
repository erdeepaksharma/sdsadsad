<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
* @package    Sitereview
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete.tpl 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */ 
?>
<?php 

$breadcrumb = array(
    array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
    array("href"=>$this->sitereview->getHref(array('tab' => $this->tab_selected_id)),"title"=>"Videos","icon"=>"arrow-d")
     );

echo $this->breadcrumb($breadcrumb);
?>
<div class="layout_middle">
  <div class='global_form'>
    <form method="post" class="global_form">
      <div>
        <div>
          <h3><?php echo $this->translate('Delete %s Video?',$this->listingType->title_singular); ?></h3>
					<p> 
						<?php echo $this->translate('Are you sure that you want to delete the video titled "%1$s" last modified %2$s? It will not be 	recoverable after being deleted.', $this->title, $this->timestamp($this->sitereview_video->modified_date)) ?>
					</p>
          <br />

          <input type="hidden" name="confirm" value="true"/>
          <button type='submit' data-theme="b"><?php echo $this->translate('Delete'); ?></button>
          <div style="text-align: center"><?php echo $this->translate('or'); ?> </div>
          <a href="<?php echo $this->sitereview->getHref(array('tab' => $this->tab_selected_id)) ?>" data-rel="back" data-role="button">
            <?php echo $this->translate('Cancel') ?>
          </a>

        </div>
      </div>
    </form>
  </div>
</div>	