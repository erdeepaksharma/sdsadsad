<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?><?php
$baseUrl = $this->layout()->staticBaseUrl;
$this->headLink()->prependStylesheet($baseUrl . 'application/modules/Siteforum/externals/styles/style_siteforum.css');
?>

<div class="siteforum_form_quick_search">
    <?php echo $this->searchForm->render($this) ?>
</div>