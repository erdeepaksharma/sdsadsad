<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<h3>
  <?php echo $this->translate('Manage Greetings / Announcements'); ?>
</h3>
<p>
  <?php echo $this->translate('Below, you can manage various greetings. You can also add a new greeting via ‘Create New Greeting / Announcement’ based on different time duration like for morning, evening, christmas, new year etc. The greeting will be shown to user as per the time duration set by you for the particular greeting.You can also use this feature for announcements / messages / offers etc. which you want to convey to all of your site users. It can be scheduled or can be shown for a particular time duration as set by you while creation of the same.'); ?> 
</p>
<br />
<div>
  
  <a href="<?php echo $this->url(array('action' => 'create')) ?>" class="buttonlink seaocore_icon_add" title="<?php echo $this->translate('Create New Greeting / Announcement'); ?>"><?php echo $this->translate('Create New Greeting / Announcement'); ?></a>
</div>
<br />
 
<?php if(count($this->greetings) > 0): ?> 
<table class='admin_table' width= "100%" >
      <thead>
        <tr>
          <th >
            <?php echo $this->translate("Title"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Start Time"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("End Time"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Repeat"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Enable"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Options"); ?>
          </th>
        </tr>
      </thead>
      <tbody> 
          
       <?php foreach ($this->greetings as $item) : ?>     
                      <tr>
           <td class="admin_table_bold">
             
               <?php 
               if($item->repeat) {
               $item->starttime = date('H:i:s',strtotime($item->starttime));
               $item->endtime = date('H:i:s',strtotime($item->endtime));
               }
               
               ?>
              <?php echo $item->title; ?>
              
              </td>
              <td class="">
               
              <?php echo  $item->starttime; ?>
               
              </td>
               <td class="">
              
             <?php echo  $item->endtime; ?>
              
              </td>
              <td class="">
              
             <?php  if($item->repeat):  echo "Everyday";   else:  echo "No"; endif; ?>
              
              </td>
              <td>
                  
             <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' =>
'advancedactivity', 'controller' => 'greeting', 'action' => 'enabled', 'greeting_id' =>
$item->greeting_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled1.gif',
'', array('title' => $this->translate('Disable Greeting'))), array())  :
$this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'greeting',
'action' => 'enabled', 'greeting_id' => $item->greeting_id),
$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled0.gif', '', array('title' =>
$this->translate('Enable Greeting')))) ) ?>
              </td>
              <td>
              <a href='<?php echo $this->url(array('action' => 'edit', 'greeting_id' => $item->getIdentity())) ?>'>
                <?php echo $this->translate("Edit") ?>
              </a>
              | <a href='<?php echo $this->url(array('action' => 'delete', 'greeting_id' => $item->getIdentity())) ?>' class="smoothbox">
                <?php echo $this->translate("Delete") ?>
              </a>
               | <a href='<?php echo $this->url(array('action' => 'preview', 'greeting_id' => $item->getIdentity())) ?>' class="smoothbox">
                <?php echo $this->translate("Preview") ?>
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
    <?php echo $this->translate('You have not added any greeting yet.') ?>
    </span>
</div>
 <?php endif; ?>