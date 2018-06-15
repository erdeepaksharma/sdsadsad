<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: .tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php if($this->title_type=='user'):
  $html_title='User Ratings: %s';
//$this->translate(array($html_title.'%s rating', $html_title.'%s ratings', $this->rating), $this->locale()->toNumber($this->rating));
  elseif($this->title_type=='editor'):
  $html_title="Editor Rating: %s";
  else:
  $html_title='Overall Rating: %s';
 endif;
 if($this->html_title){
   $html_title=$this->html_title.': %s';
 }
 $html_title = $this->translate($html_title, $this->rating);
?>
<?php if ($this->sizeType == 'small-star'): ?>

  <span class="list_rating_star" title="<?php echo $html_title; ?>">

      <?php for ($x = 1; $x <= $this->rating; $x++): ?>
        <span class="seao_rating_star_generic <?php echo $this->rating_star_class ?>" ></span>
      <?php endfor; ?>
      <?php $roundrating = round($this->rating); ?>
      <?php if (($roundrating - $this->rating) > 0): ?>
        <span class="seao_rating_star_generic <?php echo $this->rating_half_star_class ?>" ></span>
      <?php endif; ?>
      <?php $roundrating++; ?>
      <?php for ($x = $roundrating; $x <= 5; $x++): ?>
        <span class="seao_rating_star_generic seao_rating_star_disabled" ></span>
      <?php endfor; ?>
  </span>
<?php else: ?>
  <?php
  $this->headLink()
          ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_rating.css');
  ?>
  <ul class='<?php echo $this->rating_star_class; ?>' title="<?php echo $html_title; ?>">
    <li  class="rate one"><?php echo $this->translate('1') ?></li>
    <li  class="rate two"><?php echo $this->translate('2') ?></li>
    <li  class="rate three"><?php echo $this->translate('3') ?></li>
    <li  class="rate four"><?php echo $this->translate('4') ?></li>
    <li  class="rate five"><?php echo $this->translate('5') ?></li>
  </ul>
<?php endif; ?>

