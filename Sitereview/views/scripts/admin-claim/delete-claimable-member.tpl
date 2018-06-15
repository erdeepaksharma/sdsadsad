<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete-claimable-member.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo "Remove Member?"; ?></h3>
    <p>
      <?php echo "Are you sure that you want to remove this member from this list of listing creators?"; ?>
    </p>
    <br />
    <p>
      <input type="hidden" name="confirm" value="<?php echo $this->user_id ?>"/>
      <button type='submit'><?php echo "Remove"; ?></button>
      <?php echo " or "; ?> 
      <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
        <?php echo "cancel"; ?>
      </a>
    </p>
  </div>
</form>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>