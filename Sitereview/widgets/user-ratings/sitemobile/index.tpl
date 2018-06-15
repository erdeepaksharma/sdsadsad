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

<div class="sr_up_overall_rating b_medium">
	<div id="" class="rating" onmouseout="rating_out();">
		<span id="rate_1" class="rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate(1);" onmouseover="rating_over(1);" <?php endif;?> ></span>
		<span id="rate_2" class="rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate(2);" onmouseover="rating_over(2);" <?php endif;?>></span>
		<span id="rate_3" class="rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate(3);" onmouseover="rating_over(3);" <?php endif;?>></span>
		<span id="rate_4" class="rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate(4);" onmouseover="rating_over(4);" <?php endif;?>></span>
		<span id="rate_5" class="rating_star_big_generic" <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?> onclick="rate(5);" onmouseover="rating_over(5);" <?php endif;?>></span>
		 <?php if(!empty($this->viewer_id) && (empty($this->rating_exist) || (!empty($this->rating_exist) && ($this->update_permission)))):?><span id="rating_text" class="rating_text"><?php echo $this->translate('click to rate');?></span><?php endif;?>
	</div>
</div>

<script type="text/javascript">
	var pre_rate = <?php echo $this->sitereview->rating_users;?>;
	var listing_id = <?php echo $this->sitereview->listing_id;?>;
	function rating_over(rating) {
		for(var x=1; x<=5; x++) {
			if(x <= rating) {
				$('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big');
				if($('#rate1_'+x)) {
					$('#rate1_'+x).attr('class', 'rating_star_big_generic rating_star_big');
				}
			} else {
				$('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled');
				if($('#rate1_'+x)) {
					$('#rate1_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled');
				}
			}
		}
	}

	function rating_out() {
		if (pre_rate != 0){
			set_rating();
		}
		else {
			for(var x=1; x<=5; x++) {
				$('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled');
			}
		}
	}

	function set_rating() {
		var rating = pre_rate;

		for(var x=1; x<=parseInt(rating); x++) {
			$('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big');
		}

		for(var x=parseInt(rating)+1; x<=5; x++) {
			$('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled');
		}

		var remainder = Math.round(rating)-rating;
		if (remainder <= 0.5 && remainder !=0){
			var last = parseInt(rating)+1;
			$('#rate_'+last).attr('class', 'rating_star_big_generic rating_star_big_half');
			if($('#rate1_'+last)) {
				$('#rate1_'+last).attr('class', 'rating_star_big_generic rating_star_big_half');
			}
		}
    $.mobile.hidePageLoadingMsg();
	}

  function rate(rating) {
    $.mobile.showPageLoadingMsg();
		sm4.core.request.send({
			type: "GET", 
			dataType: "json", 
			url : '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'review', 'action' => 'rate'), 'default', true) ?>', 
			data: {
				'format' : 'json',
				'rating' : rating,
				'listing_id': listing_id
			},
			success:function (response) {
				pre_rate = response[0].rating;
			}
		});   
	}

  sm4.core.runonce.add(set_rating);
</script>