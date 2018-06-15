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

<?php if($this->listingtypeArray->allow_review):?>
	<?php if($this->createAllow == 1):?>
		<button class="sr_review_button" onclick="writeAReview('create');"><?php echo $this->translate("Write a Review") ?></button>
	<?php elseif($this->createAllow == 2):?>
		<button class="sr_review_button" onclick="writeAReview('update');"><?php echo $this->translate("Update your Review") ?></button>
	<?php endif;?>
	<script type="text/javascript">
  function writeAReview(option){
    <?php if($this->listing_profile_page): ?>
      if($('main_tabs') && $('main_tabs').getElement('.tab_layout_sitereview_user_sitereview')){
        if($('sitereview_create') && $('main_tabs').getElement('.tab_layout_sitereview_user_sitereview').hasClass('active')){
          window.location.hash = 'sitereview_create';
          return;
        } else if($('sitereview_update') && $('main_tabs').getElement('.tab_layout_sitereview_user_sitereview').hasClass('active')){
          window.location.hash = 'sitereview_update';
          return;
        } 
        tabContainerSwitch($('main_tabs').getElement('.tab_layout_sitereview_user_sitereview'));
          <?php if($this->contentDetails && isset ($this->contentDetails->params['loaded_by_ajax']) && $this->contentDetails->params['loaded_by_ajax']): ?>
        var params = {
          requestParams :<?php echo json_encode($this->contentDetails->params) ?>,
          responseContainer :$$('.layout_sitereview_user_sitereview')
        }

        params.requestParams.content_id = '<?php echo $this->contentDetails->content_id ?>';
        en4.sitereview.ajaxTab.sendReq(params);
        <?php endif; ?>
        if(option == 'create') {
          (function(){
            window.location.hash = 'sitereview_create';
          }).delay(3000);
        } else if(option == 'update') {
          (function(){
            window.location.hash = 'sitereview_update';
          }).delay(3000);
        }
      } else {
        if(option == 'create') {
// 						(function(){
            window.location.hash = 'sitereview_create';
// 						}).delay(3000);
        } else if(option == 'update') {
// 						(function(){
            window.location.hash = 'sitereview_update';
// 						}).delay(3000);
        }
      }
      <?php else:?>
      window.location.href="<?php echo $this->sitereview->getHref(); ?>";
      <?php endif;?>
    }
  </script>
<?php else:?>
  <div id="" class="rating mtop10" onmouseout="rating_out1();">
    <span id="rate1_1" class="sr_rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate1(1);" onmouseover="rating_over1(1);" <?php endif;?> ></span>
    <span id="rate1_2" class="sr_rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate1(2);" onmouseover="rating_over1(2);" <?php endif;?>></span>
    <span id="rate1_3" class="sr_rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate1(3);" onmouseover="rating_over1(3);" <?php endif;?>></span>
    <span id="rate1_4" class="sr_rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate1(4);" onmouseover="rating_over1(4);" <?php endif;?>></span>
    <span id="rate1_5" class="sr_rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate1(5);" onmouseover="rating_over1(5);" <?php endif;?>></span>
    <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?><span id="rating_text" class="rating_text"><?php echo $this->translate('click to rate');?></span><?php endif;?>
  </div>
	<script type="text/javascript">
		en4.core.runonce.add(function() {
			var pre_rate = <?php echo $this->sitereview->rating_users;?>;
			var listing_id = <?php echo $this->sitereview->listing_id;?>;
			new_text = '';

			var rating_over1 = window.rating_over1 = function(rating) {
				for(var x=1; x<=5; x++) {
					if(x <= rating) {
						$('rate1_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big');
						if($('rate2_'+x)) {
							$('rate2_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big');
						}
					} else {
						$('rate1_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big_disabled');
						if($('rate2_'+x)) {
							$('rate2_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big_disabled');
						}
					}
				}
			}
			
			var rating_out1 = window.rating_out1 = function() {
				if (pre_rate != 0){
					set_rating1();
				}
				else {
					for(var x=1; x<=5; x++) {
						$('rate1_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big_disabled');
					}
				}
			}

			var set_rating1 = window.set_rating1 = function() {
				var rating = pre_rate;
				$$('.sr_rating_star_big_generic').each(function(el) {
					el.set('class', 'sr_rating_star_big_generic sr_rating_star_big');
				});
				for(var x=parseInt(rating)+1; x<=5; x++) {
				  if($('rate2_'+x)) {
						$('rate2_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big_disabled');
					}
					$('rate1_'+x).set('class', 'sr_rating_star_big_generic sr_rating_star_big_disabled');
				}

				var remainder = Math.round(rating)-rating;
				if (remainder <= 0.5 && remainder !=0){
					var last = parseInt(rating)+1;
					$('rate1_'+last).set('class', 'sr_rating_star_big_generic sr_rating_star_big_half');
					if($('rate2_'+last)) {
						$('rate2_'+last).set('class', 'sr_rating_star_big_generic sr_rating_star_big_half');
					}
				}
			}

			var rate = window.rate1 = function(rating) {
				(new Request.JSON({
					'format': 'json',
					'url' : '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'review', 'action' => 'rate'), 'default', true) ?>',
					'data' : {
						'format' : 'json',
						'rating' : rating,
						'listing_id': listing_id
					},
					'onRequest' : function(){
					},
					'onSuccess' : function(responseJSON, responseText)
					{
					  pre_rate = responseJSON[0].rating;
            set_rating1();
					}
				})).send();

			}	
			set_rating1();
		});
	</script>  
<?php endif;?>  