<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: feed-settings.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>

    <?php if (count($this->navigation)): ?>
    <div class='seaocore_admin_tabs'>
        <?php
        // Render the menu
        //->setUlClass()
        echo $this->navigation()->menu()->setContainer($this->navigation)->render()
        ?>
    </div>
<?php endif; ?>
 <?php 
 $this->navigationAAF = $navigationAAF = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main_settings', array(), 'advancedactivity_admin_main_settings_style');
   
 ?> 

<?php if (count($this->navigationAAF)): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigationAAF)->render() ?>
  </div>
<?php endif; ?>
 
  
<h3>
  <?php echo $this->translate('Manage Words Style'); ?>
</h3>
<p>
  <?php echo $this->translate('Below, you can manage the words which will stand out in a status update. You can add a new word via ‘Add New Word’ link, set color for word and its background as per your choice.'); ?> 
</p>
<br />
<div>
  
  <a href="<?php echo $this->url(array('action' => 'create-word-style')) ?>" class="buttonlink seaocore_icon_add " title="<?php echo $this->translate('Add New Word'); ?>"><?php echo $this->translate('Add New Word'); ?></a>
</div>
<br />
 
<?php $totalWords = count($this->words); if(!empty($totalWords)) : ?>  
 <table class='admin_table' width= "100%" >
      <thead>
        <tr>
          <th >
               <?php echo $this->translate("Word"); ?>
          </th>
          <th align="left">
              <?php echo $this->translate("Word Color"); ?>
          </th>
          <th align="left">
          <?php echo $this->translate("Background Color"); ?>
          </th>
          
          <th align="left">
            <?php echo $this->translate("Options"); ?>
          </th>
        </tr>
      </thead>
      <tbody> 
          
       <?php foreach ($this->words as $item) : $params = $item->params; ?>     
                      <tr>
           <td class="admin_table_bold">
               <span style=" <?php echo 'font-size:20px; font-weight:500; font-style:'.$item->style.'; color:'.$item->color.'; background-color:'.(!empty($params['bg_enabled']) ? $item->background_color : null); ?>">  <?php echo $item->title; ?> </span> 
              </td>
             <td class=""> 
                   <?php    echo  $item->color;   ?>
              </td>
               <td class=""> 
                   <?php    echo  $item->background_color;   ?>
              </td>
              <td>
              <a href='<?php echo $this->url(array('action' => 'edit-word-style', 'word_id' => $item->getIdentity())) ?>'>
                <?php echo $this->translate("Edit") ?>
              </a>
              | <a href='<?php echo $this->url(array('action' => 'delete-word', 'word_id' => $item->getIdentity())) ?>' class="smoothbox">
                <?php echo $this->translate("Delete") ?>
              </a>
                
              </td>
            </tr>
   <?php endforeach; ?>
      </tbody>
    </table>
<?php else: ?>  
    <br />
    <div class='tip'>
    <span>
    <?php echo $this->translate('You have not added any word yet.') ?>
    </span>
</div>
<?php endif; ?>