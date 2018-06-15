<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php if (!empty ($this->communityad_id)) { ?>
	<script type="text/javascript">

    window.addEvent('load', function() {
      var url = en4.core.baseUrl + 'widget/index/mod/sitereview/name/review-ads';
      var request = new Request.HTML({    
        url : url,
        method: 'get',
        data : {
          format : 'html',
          'load_content' : 1,
          'communityadid' : '<?php echo $this->communityad_id?>',
          'limit' : '<?php echo $this->limit?>'				
        },    
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
          if($('<?php echo $this->communityad_id?>')) {
            $('<?php echo $this->communityad_id?>').innerHTML = responseHTML;	
              Smoothbox.bind($('<?php echo $this->communityad_id ?>'));
          }
        }
        });  
        request.send();
    });
	</script>

<?php } ?>


<?php if (empty ($this->load_content) && $this->identity_temp) : ?>
  <div id="communityadid_widget_showads">
<?php endif;?>

<?php if (!empty ($this->load_content)) { ?>
  <div class="cmad_ad_clm">
    <div>
      <div class="cmad_bloack_top">
        <?php if(Engine_Api::_()->communityad()->enableCreateLink()) : ?>
          <?php echo '<a href="'.$this->url(array(), 'communityad_listpackage', true).'" class="fleft">' . $this->translate('Create an Ad') . '</a>';?>
        <?php endif; ?>
        <?php 
          echo '<a href="'.$this->url(array(), 'communityad_display', true).'">' . $this->translate('More Ads') . '</a>'; 
        ?>
      </div>
        <?php if(!$this->identity): $this->identity= rand(1000000000, 9999999999); endif;?>
        <div class="cmad_block_wrp" >
          <?php
          include APPLICATION_PATH . '/application/modules/Communityad/views/scripts/_adsDisplay.tpl';
          ?>
        </div>
    </div>
  </div>
<?php }?>

<?php if (empty ($this->load_content) && $this->identity_temp) : ?>
  </div>
<?php endif;?>