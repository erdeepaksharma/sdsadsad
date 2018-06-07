<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: viewcomment.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php if( !isset($this->form) ) return; ?>


<?php echo $this->translate("Comment:") ?>
<?php echo $this->form->render($this) ?>

<script type="text/javascript">
//<![CDATA[
document.getElementsByTagName('form')[0].style.display = 'block';
//]]>
</script>
