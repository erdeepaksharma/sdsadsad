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
<?php if ($this->showContent): ?>
  <?php if($this->addEditorReview): ?>
    <script type="text/javascript">
      var editorPageAction = function(page){
        $.mobile.activePage.find('#pagination_loader_image').css('display', 'block');
        var url = sm4.core.baseUrl + 'core/widget/index/mod/sitereview/name/editor-reviews-sitereview';
        sm4.core.request.send({
          'url' : url,
					 type: "POST", 
					 dataType: "html", 
          'data' : {
            'format' : 'html',
            subject : sm4.core.subject.guid,
            'isAjax' : 1,
            'page' : page
          },
          success : function(response) {        
            $.mobile.activePage.find('#pagination_loader_image').css('display', 'none');
          }
        }, {
          'element' : $.mobile.activePage.find('#editorReviewContent').parent(),
          'showLoading': true
        });
      }
    </script>
  <?php endif; ?>

  <div class="sr_profile_tab_content clr">
    <div id='editorReviewContent'>
      <?php if($this->addEditorReview): ?>
				<?php if ($this->current == 1): ?>
         <section class="sm-widget-block" id="review_content">
						<table class="sm-rating-table">
							<?php $ratingData = Engine_Api::_()->getDbtable('ratings', 'sitereview')->profileRatingbyCategory($this->review->review_id); ?>
							<?php foreach($ratingData as $reviewCat):?>
								<?php if (empty($reviewCat['ratingparam_name'])): ?>
									<tr valign="middle">
										<td class="rating-title">
                      <strong><?php echo $this->translate("Editor Rating"); ?></strong>
										</td>
										<td>
											<?php echo $this->showRatingStar($reviewCat['rating'], 'editor', 'big-star', $this->sitereview->listingtype_id); ?>
										</td>
									</tr>
									<?php break; ?>
								<?php endif; ?>
							<?php endforeach;?>
							<?php foreach($ratingData as $reviewCat):?>
								<?php if (!empty($reviewCat['ratingparam_name'])): ?>
									<tr valign="middle">
										<td class="rating-title">
											<?php echo $this->translate($reviewCat['ratingparam_name']); ?>
										</td>
										<td>
											<?php echo $this->showRatingStar($reviewCat['rating'], 'editor', 'small-box', $this->sitereview->listingtype_id); ?>
										</td>
									</tr>
									<?php continue; ?>
								<?php endif; ?>
							<?php endforeach;?>
              <?php if ($this->min_price < 0): ?>
                <tr>
                  <td colspan="2">
                    <?php echo $this->timestamp(strtotime($this->review->creation_date)) ?>
                  </td>
                </tr> 
              <?php else: ?>
                <tr>
                  <td colspan="2">
                    <?php if ($this->min_price == $this->max_price && $this->min_price > 0): ?>
                      <span style='font-size:18px;'>
                        <?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($this->min_price);?>
                      </span>
                    <?php elseif($this->min_price > 0 && $this->max_price > 0): ?>
                      <?php echo $this->translate("%s to %1s", "<span style='font-size:18px;'>" . Engine_Api::_()->sitereview()->getPriceWithCurrency($this->min_price) . "</span>", Engine_Api::_()->sitereview()->getPriceWithCurrency($this->max_price)); ?>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <?php echo $this->timestamp(strtotime($this->review->creation_date)); ?>
                  </td>
                </tr>
                <?php endif; ?>
              </div>
            </table>
         </section>


          <?php if($this->review->pros):?>
          <div class='t_l'>
            <?php echo '<b>' . $this->translate("The Good:") . ' </b>' . $this->viewMore($this->review->pros); ?>
          </div>
          <?php endif;?>
          <?php if($this->review->cons):?>
          <div class="t_l"> 
            <?php echo '<b>' . $this->translate("The Bad:") . ' </b>' . $this->viewMore($this->review->cons); ?>
          </div>
          <?php endif;?>

          <?php if($this->review->title):?>
          <div class="t_l">
            <?php echo '<b>' . $this->translate("The Bottom Line:") . ' </b>' . $this->review->title; ?>
          </div>
          <?php endif;?>

          <?php if($this->review->profile_type_review): ?>
            <div class="t_l"> 
              <?php $custom_field_values = $this->fieldValueLoopReview($this->review, $this->fieldStructure); ?>
              <?php echo htmlspecialchars_decode($custom_field_values); ?>        
            </div> 
          <?php endif; ?>
          <?php if($this->review->update_reason):?>
          <div class="t_l">
            <?php echo '<b>' . $this->translate("Update On "). $this->timestamp(strtotime($this->review->modified_date)) . ': </b>' . $this->review->update_reason; ?>
          </div>
          <?php endif;?>
        <?php endif; ?>

        <div class="sr_editor_full_review">
          <?php echo $this->body_pages; ?>
        </div>    

        <?php if ($this->showconclusion && $this->review->body): ?>
          <div class='sr_reviews_listing_proscons sr_editor_review_conclusion b_medium'>
            <?php echo '<b>' . $this->translate("Conclusion: ") . '</b>'; ?>
            <?php echo $this->review->body; ?>
          </div>
        <?php endif; ?>
        <?php if ($this->pageCount > 1): ?>
					<div class="paginationControl" data-role="controlgroup" data-type="horizontal" data-mini="true" data-inset="true">
						<?php
						if (isset($this->previous)):
							$preClass = "previous";
						else:
							$preClass = "previous ui-disabled";
						endif;
						?>

						<a class='<?php echo $preClass ;?>' data-transition = "turn" data-role = "button" data-icon = "double-angle-left" data-inline = "true"
						data-iconpos = "notext" data-corners = "false" data-shadow = "false" data-iconshadow = "true"  data-pagination="1" onclick="javascript:editorPageAction('<?php echo $this->previous; ?>')"></a>

						<a class='<?php echo $preClass ;?>' data-transition = "turn" data-role = "button" data-icon = "angle-left" data-inline = "true"
						data-iconpos = "notext" data-corners = "false" data-shadow = "false" data-iconshadow = "true"  data-pagination="<?php echo $this->previous;?>" onclick="javascript:editorPageAction('<?php echo $this->previous; ?>')"></a>

						<a  data-transition="turn"  data-role="button" data-icon="false" data-corners="false" data-shadow="false" class="ui-disabled pagination_text">
							<?php echo $this->translate('%s - %1s of %2s', $this->locale()->toNumber($this->current),$this->locale()->toNumber(count($this->pagesInRange)),$this->locale()->toNumber(count($this->pagesInRange))) ?>
						</a>
						<?php
						if (isset($this->next)):
							$nextClass = "next";
						else:
							$nextClass = "next ui-disabled";
						endif;
						?>
						<a class='<?php echo $nextClass ;?>'  data-transition = "turn" data-role = "button" data-icon = "angle-right" data-inline = "true"
						data-iconpos = "notext" data-corners = "false" data-shadow = "false" data-iconshadow = "true" 
						data-pagination="<?php echo $this->next;?>" onclick="javascript:editorPageAction('<?php echo $this->next; ?>')"></a>
						<a class='<?php echo $nextClass ;?>'  data-transition = "turn" data-role = "button" data-icon = "double-angle-right" data-inline = "true"
						data-iconpos = "notext" data-corners = "false" data-shadow = "false" data-iconshadow = "true" data-pagination="<?php echo $this->last;?>" onclick="javascript:editorPageAction('<?php echo $this->last; ?>')"></a>
					</div>
				<?php endif; ?>
			 <?php else:?>
      <div class="sr_profile_overview">
      	<?php echo $this->listingType->overview && $this->overview ? $this->overview : $this->sitereview->body?>
      </div>	
      <?php endif;?>
    </div>
  </div>

  <?php if( empty($this->isAjax) && $this->showComments):?>
     <?php //echo $this->action("list", "comment", "sitemobile", array("type" => $this->sitereview->getType(), "id" => $this->sitereview->listing_id)); ?>
      <?php if(!Engine_Api::_()->sitemobile()->isApp()):?>
			<?php echo $this->content()->renderWidget("sitemobile.comments", array('type' => $this->sitereview->getType(), 'id' => $this->sitereview->getIdentity())); ?>
  <?php endif;?>
  <?php endif;?>
<?php endif; ?>