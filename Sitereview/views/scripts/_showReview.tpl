<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _showReview.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
 
<?php $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $this->sitereview->listingtype_id);?>
<?php if(isset ($this->sitereview->rating_editor) && $this->sitereview->rating_editor && $this->sitereview->review_count==1):
  $html_title=$this->translate('1 Editor Review');
elseif(isset ($this->sitereview->rating_editor) && $this->sitereview->rating_editor && !empty($listingType->allow_review)):
   $html_title=$this->translate(array('1 Editor Review and %s User Review', '1 Editor Review and %s User Reviews',($this->sitereview->review_count-1)), $this->locale()->toNumber(($this->sitereview->review_count-1)));
 elseif(!empty($listingType->allow_review)): 
  $html_title=$this->translate(array('%s User Review', '%s User Reviews', $this->sitereview->review_count), $this->locale()->toNumber($this->sitereview->review_count));
endif;
?>

<?php if(!empty($listingType->allow_review)):?>
	<span title="<?php echo $html_title ?>">
	<?php echo $this->translate(array('%s review', '%s reviews', $this->sitereview->review_count), $this->locale()->toNumber($this->sitereview->review_count)) ?>
	</span>
<?php elseif(isset($this->sitereview->rating_editor) && $this->sitereview->rating_editor):?>
  <span title="<?php echo $html_title ?>">
   <?php echo $this->translate('1 review'); ?>
  </span>
<?php endif;?>