<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: custom-block-view.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php

echo '<b><a href="javascript:void()" title="' . $this->itemObj->title . '">' . $this->itemObj->title . '</a></b><br />';
echo $this->itemObj->description;