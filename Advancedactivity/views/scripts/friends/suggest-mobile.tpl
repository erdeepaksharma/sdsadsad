<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetagcheckin
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: suggest.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php if ($this->paginator->getTotalItemCount() > 0) : ?>
<ul class="aaf-mobile-aad-tag-autosuggest">
  <?php foreach ($this->paginator as $item): ?>
    <li class="autocompleter-choices">
      <div class="autocompleter-choice">
        <div>
          <?php echo $item->getTitle(); ?>
        </div>
        <div>
          <a href="javascript:void(0);"  class="aaf_mobile_add_tag"  rel="<?php echo $item->getIdentity() ?>"  rev="<?php echo $this->string()->escapeJavascript($item->getTitle()) ?>"> <?php echo $this->translate('Tag Friend'); ?></a>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php if ($this->paginator->count() > 1): ?>
  <div class="clr aaf_autosuggest_pagination">
    <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
      <a rev ="<?php echo ($this->paginator->getCurrentPageNumber() - 1) ?>" class="aff_list_pagination buttonlink icon_previous">Previous</a>
    <?php endif; ?>
    <?php if ($this->paginator->count() > 2): ?>
      <?php if ($this->paginator->getCurrentPageNumber() > 1): ?>
      |
      <?php endif; ?>
      <select class="aff_list_pagination_select">
        <?php for ($i = 1; $i <= $this->paginator->count(); $i++): ?>
          <option value="<?php echo $i; ?>" <?php if ($this->paginator->getCurrentPageNumber() == $i): ?> selected="selected" <?php endif; ?> ><?php echo $this->translate($i); ?></option>
        <?php endfor; ?>
      </select>
      <?php if ($this->paginator->count() > $this->paginator->getCurrentPageNumber()): ?>
      |
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($this->paginator->count() > $this->paginator->getCurrentPageNumber()): ?>
      
      <a rev ="<?php echo ($this->paginator->getCurrentPageNumber() + 1) ?>" class="aff_list_pagination buttonlink_right icon_next" >Next</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php else : ?>
<?php echo $this->translate('No any friend found.')?>
<?php endif; ?>
