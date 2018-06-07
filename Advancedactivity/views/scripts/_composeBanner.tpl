<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _composeLink.tpl 10245 2014-05-28 18:08:24Z lucas $
 * @author     John
 */
?>
<?php if( empty($this->isAFFWIDGET) || ($this->forEdit && $this->action->attachment_count > 0) ) : return;
endif;
?>
<?php
$this->headScript()
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/scripts/composer_banner.js')
?>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    <?php $defaultBanner = $banners = array(); ?>
      <?php if($this->forEdit && !empty($this->action->params['feed-banner']['background-color'])):?>
      <?php ?>
      <?php $defaultBanner = array(
        'backgroundImage' => $this->action->params['feed-banner']['image'],
        'backgroundColor' => $this->action->params['feed-banner']['background-color'],
        'color' => $this->action->params['feed-banner']['color']
      );
      ?>
     <?php $banners = Engine_Api::_()->getDbTable('banners', 'advancedactivity')->getBanners(); ?>
      <?php endif; ?>
    var plugin = new Composer.Plugin.Banner({
      title: '<?php echo $this->string()->escapeJavascript($this->translate('Add Banner')) ?>',
      lang: {

        'Add Banner': '<?php echo $this->string()->escapeJavascript($this->translate('Add Banner')) ?>'
      },
      requestOptions: {
        'banners': <?php echo $this->jsonInline($banners); ?>,
        'feed_length': <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.banner.feed.length', 100) ?>
      },
      defaultBanner: <?php echo $defaultBanner ? $this->jsonInline($defaultBanner) : 0 ?>
    });
    <?php if( $this->forEdit ) : ?>
    document.retrieve('editComposeInstance<?php echo $this->forEdit ?>').addPlugin(plugin);
    <?php else: ?>
    composeInstance.addPlugin(plugin);
    <?php endif; ?>
  });
</script>