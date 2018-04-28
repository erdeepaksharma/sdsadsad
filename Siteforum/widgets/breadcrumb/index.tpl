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
?><?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Siteforum/externals/styles/style_siteforum.css'); ?>

<?php
$count = count($this->navigation);
foreach ($this->navigation as $key => $value) {
    if (0 === --$count) {
        echo $this->translate($key);
        break;
    }
    echo $this->htmlLink($value, $this->translate($key));
    ?>

    <span class="brd-sep seaocore_txt_light"><?php echo '&#187'; ?></span> <?php
}
?>

    <?php if ($this->showDashboardLink && $this->viewer->getIdentity()): ?>
    <div class="fright">
        <a href="<?php echo $this->url(array('controller' => 'dashboard', 'action' => 'my-topics'), 'siteforum_specific'); ?>"><?php echo $this->translate('User Dashboard'); ?></a>
    </div>
<?php endif; ?>


