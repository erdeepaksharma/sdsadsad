<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions 
 * @package    Advancedactivity
 * @copyright  Copyright 2009-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl  2011-02-16 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php if ($this->addType == 1): ?>
<div class="cmad_ad_clm">
    <div class="cmad_block_wrp">
        <?php
        include APPLICATION_PATH . '/application/modules/Advancedactivity/views/scripts/_displayCoreAds.tpl';
        ?>
    </div>
</div>
<?php else: ?>
<?php $content_id = $this->identity ? $this->identity : ($this->widgetId?$this->widgetId:rand(1000000000, 9999999999))?>
<?php if (!empty($this->showContent)): ?>
<div class="cmad_ad_clm">
    <div class="cmad_block_wrp">
        <?php
        include APPLICATION_PATH . '/application/modules/Communityad/views/scripts/_adsDisplay.tpl';
        ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

