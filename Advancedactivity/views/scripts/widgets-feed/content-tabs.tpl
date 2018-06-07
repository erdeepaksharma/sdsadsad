<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: content-tabs.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php if( count($this->filterTabs) > 1 || ($this->canCreateCustomList || $this->canCreateCategroyList) ): ?>
  <script type="text/javascript">

    var tabAdvancedFeedContainerSwitch = function (element, actionFilter, list_id) {
      //if( en4.core.request.isRequestActive())return;
      if (element.tagName.toLowerCase() == 'a') {
        element = element.getParent('li');
      }

      var myContainer = element.getParent('.aaf_tabs_feed').getParent();
      myContainer.getElements('ul > li').removeClass('aaf_tab_active');
      element.addClass('aaf_tab_active');
      getTabBaseContentFeed(actionFilter, list_id);
      if ((DetectMobileQuick() || DetectIpad()) && $$(".aaf_tabs_feed_tab")) {
        $$(".aaf_tabs_feed_tab").removeClass('aaf_tabs_feed_tab_open').addClass('aaf_tabs_feed_tab_closed');
      }
    }
    var moreAdvancedFeedTabSwitch = function (el) {
      el.toggleClass('aaf_tabs_feed_tab_open');
      el.toggleClass('aaf_tabs_feed_tab_closed');
    }
    $(document.body).addEvent('click', function(e){ 
        var moreTab = $('aaf_tabs_feed').getElements(".aaf_tabs_feed_tab_more")[0];
        if( moreTab && !e.target.getParent().hasClass('aaf_tabs_feed_tab_more')) {
            moreTab.addClass('aaf_tabs_feed_tab_closed');
            moreTab.removeClass('aaf_tabs_feed_tab_open');
        }
    });
    var activeAAFAllTAb = function () {
      if ($('update_advfeed_blink'))
        $('update_advfeed_blink').style.display = 'none';
      var element = $('tab_advFeed_all');
      if (element.tagName.toLowerCase() == 'a') {
        element = element.getParent('li');
      }

      var myContainer = element.getParent('.aaf_tabs_feed').getParent();

      myContainer.getElements('ul > li').removeClass('aaf_tab_active');
      element.addClass('aaf_tab_active');
    }
  </script>
  <div class="aaf_tabs_feed <?php if( !$this->viewer()->getIdentity() ): ?>aaf_tabs_feed_none <?php endif; ?>" id="aaf_tabs_feed" <?php if( $this->contentTabMax < 1 || count($this->filterTabs) < 2 ): ?> style="background-color: transparent;"<?php endif; ?>>
    <div class="aaf_tabs_loader" style="display: none;" id="aaf_tabs_loader">
      <img alt="Loading" src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" align="left" />
    </div>
    <ul>
      <?php foreach( $this->filterTabs as $key => $tab ): ?>
        <?php if( $tab['filter_type'] == 'separator' ): ?>
          <?php continue; ?>
        <?php endif; ?>
        <?php
        $class = array();
        $class[] = 'tab_' . $key;
        $class[] = 'tab_item_icon_feed_' . $tab['filter_type'];
        if( $this->actionFilter == $tab['filter_type'] )
          $class[] = 'aaf_tab_active';
        $class = join(' ', $class);
        ?>
        <?php if( $key == $this->contentTabMax ): ?>
          <?php break; ?>
        <?php endif; ?>
        <li id="tab_advFeed_<?php echo $tab['filter_type'] ?>" class="<?php echo $class ?>">
          <a href="javascript:void(0);" onclick="tabAdvancedFeedContainerSwitch($(this), '<?php echo $tab['filter_type'] ?>', '<?php echo $tab['list_id'] ?>');"  ><?php echo $this->translate($tab['tab_title']) ?>
            <?php if( $tab['filter_type'] == 'all' ) : ?>
              <span id="update_advfeed_blink" class="notification_star"></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
      <?php if( count($this->filterTabs) > $this->contentTabMax || $this->canCreateCustomList || $this->canCreateCategroyList ): ?>
        <li class="aaf_tabs_feed_tab aaf_tabs_feed_tab_more aaf_tabs_feed_tab_closed" onclick="moreAdvancedFeedTabSwitch($(this));" >
          <a href="javascript:void(0);">
           <?php echo count($this->filterTabs) > 0 && $this->contentTabMax != 0 ? $this->translate('More') : count($this->filterTabs) < 1 ? $this->translate('More') : $this->translate('Filter') ?>
            &nbsp;<i class="fa fa-angle-down"></i>
          </a>
          <div class="aaf_pulldown_contents_wrapper">
            <div class="aaf_pulldown_contents">
              <ul>
                <?php foreach( $this->filterTabs as $key => $tab ): ?>
                  <?php if( $tab['filter_type'] == 'separator' ): ?>
                    <li class="sep"></li>
                  <?php else: ?>
                    <?php
                    $class = array();
                    $class[] = 'tab_' . $key;
                    if( strpos($tab['filter_type'], '_listtype_') !== false ) {
                      $class[] = 'item_icon_sitereview_listing';
                    }
                    $class[] = 'item_icon_' . $tab['filter_type'];
                    if( isset($tab['list_id']) && !empty($tab['list_id']) )
                      $class[] = 'item_icon_' . $tab['filter_type'] . '_' . $tab['list_id'];
                    if( $this->actionFilter == $tab['filter_type'] )
                      $class[] = 'aaf_tab_active';
                    $class = join(' ', array_filter($class));
                    ?>
                    <?php if( $key >= $this->contentTabMax ): ?>
                      <li id="tab_advFeed_<?php echo $tab['filter_type'] ?>" class="aaf_custom_list <?php if( $this->actionFilter == $tab['filter_type'] ): ?>aaf_tab_active<?php endif; ?>" onclick="tabAdvancedFeedContainerSwitch($(this), '<?php echo $tab['filter_type'] ?>', '<?php echo $tab['list_id'] ?>')">
                        <i class="<?php echo $class ?> aaf_content_list_icon"></i>
                        <?php if( $tab['filter_type'] == 'custom_list' || $tab['filter_type'] == 'category_list' ): ?>
                          <span class="aaf_<?php echo $tab['filter_type'] ?>_icon">
                            <a href="<?php echo $this->url(array('controller' => 'custom-list', 'action' => 'edit', 'list_id' => $tab['list_id']), 'advancedactivity_extended') ?>"  class="smoothbox edit_custom_list_icon" title="<?php echo $this->translate("Edit this List") ?>"></a>
                          </span>
                        <?php endif; ?>
                        <div><?php echo $this->translate($tab['tab_title']) ?></div>
                      </li>
                    <?php endif; ?>
                  <?php endif; ?>
                <?php endforeach; ?>
                <?php if( $this->viewer()->getIdentity() ): ?>
                  <?php if( count($this->filterTabs) > $this->contentTabMax  && ($this->canCreateCustomList|| $this->canCreateCategroyList)): ?>
                    <li class="sep"></li>
                  <?php endif; ?>
                  <?php if( $this->canCreateCustomList ): ?>
                    <li id="" class="aaf_custom_list_link">
                      <a href="<?php echo $this->url(array('controller' => 'custom-list', 'action' => 'create'), 'advancedactivity_extended') ?>" class="smoothbox aaf_icon_feed_create">
                        <?php echo $this->translate("Create a List") ?>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if( $this->canCreateCategroyList ): ?>
                    <li id="" class="aaf_custom_list_link">
                      <a href="<?php echo $this->url(array('controller' => 'custom-list', 'action' => 'create', 'type' => 'category'), 'advancedactivity_extended') ?>" class="smoothbox aaf_icon_feed_create">
                        <?php echo $this->translate("Create a Categories List") ?>
                      </a>
                    </li>
                  <?php endif; ?>
                <?php endif; ?>
              </ul>
            </div>
          </div>

        </li>
      <?php elseif( $this->canCreateCustomList ): ?>
        <li id="" class="icon_feed_create_customlist">
          <a href="<?php echo $this->url(array('controller' => 'custom-list', 'action' => 'create'), 'advancedactivity_extended') ?>" class="smoothbox">
            <?php echo $this->translate("Create a List") ?>
          </a>
        </li>
      <?php endif; ?>
      <?php if( $this->viewer()->getIdentity() ): ?>
        <li>
          <a href="<?php echo $this->url(array('controller' => 'feed', 'action' => 'edit-hide-options'), 'advancedactivity_extended') ?>" class="smoothbox" title="<?php echo $this->translate("Edit Activity Feed Settings") ?>" >
            <i class="fa fa-cog"></i>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>