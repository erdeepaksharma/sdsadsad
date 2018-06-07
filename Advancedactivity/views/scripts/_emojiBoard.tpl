<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions 
 * @package    Seaocore
 * @copyright  Copyright 2009-2010 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _invite.tpl 2010-11-18 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$dir = APPLICATION_PATH . '/application/modules/Seaocore/settings/config/emojiInfo.php';
$data = file_get_contents($dir);
$icons = json_decode($data, true);
$idPrefix = $this->idPrefix ?: '';
?>
<span id="<?php echo $idPrefix ?>emoticons-emoji-button" class="seaocore_emoji_main_wapper seaoemoji_noclose">
  <span  class="emoji adv_post_smile seaoemoji_noclose" id="<?php echo $idPrefix ?>adv_post_smile" title="<?php echo $this->translate("Emoji") ?>"></span>
</span>
<div id="<?php echo $idPrefix ?>emoticons-nested-comment-icons_emoji_dummy" class="seaocore_emoji_container seaoemoji_noclose <?php echo $this->contentClass ?> dnone" >
  <div class="aaf_emoticons-emoji_wapper_arrow"> </div>
  <div class="seaocore_embox_emoji seaocore_emoji_wapper">
    <div class="seaocore_emoji_category scrollbars" id="<?php echo $idPrefix ?>seaocore_emoji_category" style="height: 300px;">
      <?php foreach( $icons as $tag_key => $tags ): ?>
        <div class="seaocore_emoji_tab_content" id="<?php echo $idPrefix ?>seaocore_emoji_category_<?php echo $tag_key; ?>" >
          <span class="seaocore_emoji_icons_heading"><?php echo $this->translate($tags['title']); ?></span>
          <?php
          foreach( $tags['icons'] as $key => $tag ):
            ?>
            <span class="aaf_emoj_icon">
              <i class='seaocore_emoji_icon'
                 data-url="emoji_<?php echo $tag ?>.png"
                 data-target = '<?php echo $tag ?>'
                 data-icon = '<?php echo '&#x' . str_replace('_', ';&#x', $tag) ?>' ></i>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <ul id="<?php echo $idPrefix ?>seaocore_emoji_tabs" class="seaocore_emoji_tabs">
      <?php foreach( $icons as $tag_key => $tags ): ?>
        <li title='<?php echo $tags['title']; ?>' id="<?php echo $idPrefix ?>seaocore_emoji_tab_<?php echo $tag_key; ?>" data-target = '<?php echo $tag_key; ?>'>
          <i  class="emoji_tab seaocore_emoji_icon_<?php echo $tag_key; ?>" ></i>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    en4.advancedactivity.bindEmojiIcons('<?php echo $idPrefix ?>');
  });
</script>