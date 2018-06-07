<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _activityText.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
  $actions = $this->actions;
}
?>
<?php
foreach( $actions as $action ): // (goes to the end of the file)
  try { // prevents a bad feed item from destroying the entire page
    // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
    if( !$action )
      continue;
    if( !$action->getTypeInfo()->enabled )
      continue;
    if( !$action->getSubject() || !$action->getSubject()->getIdentity() )
      continue;
    if( !$action->getObject() || !$action->getObject()->getIdentity() )
      continue;

    ob_start();
    ?>
    <?php echo $this->advancedActivityViewerActions(array_merge($this->getVars(), array('action' => $action, 'scriptInclude' => true))); ?>
    <?php
    ob_end_flush();
  } catch( Exception $e ) {
    ob_end_clean();
    if( APPLICATION_ENV === 'development' ) {
      echo $e->__toString();
    }
  };
endforeach;
?>