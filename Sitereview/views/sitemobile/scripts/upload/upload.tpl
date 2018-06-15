<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitemobile
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: upload.tpl 6590 2013-06-03 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<div id="photo-wrapper" class="form-wrapper">
  <div>
    <?php echo $this->translate('SITEMOBILE_STORAGE_UPLOAD_DESCRIPTION') ?>
  </div>
  <div id="photo-label" class="form-label">
    <label for="Filedata" class="optional ui-input-text"><?php echo $this->translate('Upload Photos') ?></label></div>
  <div id="photo-element" class="form-element">
    <input type="file" name="Filedata[]" multiple="multiple" accept="image/*" />
  </div>
</div>