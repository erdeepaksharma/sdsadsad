<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: faq_help.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
  function faq_show(id) {
    if($(id).style.display == 'block') {
      $(id).style.display = 'none';
    } else {
      $(id).style.display = 'block';
    }
  }
</script>

<?php if(!empty($this->faq)) : ?>
	<p><?php echo $this->translate("Browse the different FAQ sections of this plugin by clicking on the corresponding tabs below.") ?><p>
	<br />
	<?php $action = 'faq' ?>
<?php else : ?>
	<?php $action = 'readme' ?>
<?php endif; ?>

<div class='tabs'>
	<ul class="navigation">
		<li class="<?php if($this->faq_type == 'general') { echo "active"; } ?>">
			<?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'general'), $this->translate('General'), array())
		?>
		</li>
		<li class="<?php if($this->faq_type == 'multiplelistingtype') { echo "active"; } ?>">
			<?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'multiplelistingtype'), $this->translate('Multiple Listing Types'), array())
		?>
		</li>			
  <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')):?>
    <li class="<?php if($this->faq_type == 'package') { echo "active"; } ?>">
     <?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'package'), $this->translate('Packages'), array())
    ?>
    </li>
  <?php endif;?>
  <li class="<?php if($this->faq_type == 'claim') { echo "active"; } ?>">
   <?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'claim'), $this->translate('Claims'), array())
  ?>
  </li>
		<li class="<?php if($this->faq_type == 'listingfeatures') { echo "active"; } ?>">
			<?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'listingfeatures'), $this->translate('Listing Features'), array())
		?>
		</li>	
		<li class="<?php if($this->faq_type == 'editors') { echo "active"; } ?>">
			<?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'editors'), $this->translate('Editors'), array())
		?>
		</li>
		<li class="<?php if($this->faq_type == 'import') { echo "active"; } ?>">
			<?php echo $this->htmlLink(array('route'=>'admin_default','module' => 'sitereview','controller' => 'settings','action' => $action, 'faq_type' => 'import'), $this->translate('Import'), array())
		?>
		</li>	
	</ul>
</div>

<?php switch($this->faq_type) : ?>
<?php case 'general': ?>
  <div class="admin_seaocore_files_wrapper">
    <ul class="admin_seaocore_files seaocore_faq">	
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_1');"><?php echo $this->translate("How should I start with creating listings on my site?");?></a>
				<div class='faq' style='display: none;' id='faq_1'>
					<?php echo $this->translate("After plugin installation, follow the steps below:");?>
					<ul>
						  <li>
							  <?php echo $this->translate("Start by configuring the Global Settings for your plugin.");?>
							</li>
								<li>
									<?php echo $this->translate("Then go to Manage Listing Types section and configure settings for the default listing type. You can also add more listing types from this section and configure various settings for each.");?>
								</li>
								<li>
									<?php echo $this->translate("Now configure Member Level Settings for each listing type and general Member Level Settings.");?>
								</li>
								<li>
									<?php echo $this->translate("Then create the categories, sub-categories and 3rd level categories and choose the listing comparison level for each listing type.");?>
								</li>
								<li>
									<?php echo $this->translate("Then go to the Profile Fields section to create custom fields if required for any listing type on your site and configure mapping between listing categories and profile types such that custom profile fields can be based on categories, sub-categories and 3rd level categories for each listing types.");?>
								</li>
								<li>
									<?php echo $this->translate("Configure the reviews and ratings settings for your site from the Reviews & Ratings section.");?>
									<br />
									<?php echo $this->translate('1) Go to the Review Settings sub-section and configure the settings here.');?>
									<br />
									<?php echo $this->translate('2) Then go to the Review Profile Fields sub-section to create custom review profile fields if required and configure the mapping between listing categories and review profile types such that custom profile fields can be based on categories, sub-categories and 3rd level categories for each listing types.');?>
									<br />
									<?php echo $this->translate('3) Now, go to the Rating Parameters sub-section and create rating parameters for different categories in each listing type.');?>
									<br />
								</li>
                <li>
									<?php echo $this->translate('Go to the Comparison Settings section to configure various comparison fields for each listing type.');?>
                </li>
                 <li>
									<?php echo $this->translate('Choose Editors and Super Editor from the Manage Editors section and add Editor badges to assign them to the Editors.');?>
                </li>
                <li>
									<?php echo $this->translate('Then, go to the Video Settings section to configure various settings for videos.');?>
                </li>
                <li>
									<?php echo $this->translate('Add and manage ‘Where to Buy’ options to be available to users to add links of other sites to their listings from Where to Buy section.');?>
                </li>
                <li>
									<?php echo $this->translate('Customize various widgetized pages from the Layout Editor section.');?>
                </li>
              <br />
              <?php echo $this->translate("You can now start creating the listings on your site.");?>
						</li>
					</ul>
				</div>
			</li>
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_5');"><?php echo $this->translate("The widths of the page columns are not coming fine on the Listings Home page. What might be the reason?");?></a>
				<div class='faq' style='display: none;' id='faq_5'>
					<?php echo $this->translate('This is happening because none or very few listings have been created and viewed on your site, and thus the widgets on the Listings Home page are currently empty. Once listings are rated and liked on your site, and more activity happens, these widgets will get populated and the Listings Home page will look good.<br /> If still the width of the pages are not coming fine, then edit the width of the page column by using the "Column Width" widget available in the SociaEngineAddOns-Core block in the Available Blocks section of the Layout Editor.');?>
				</div>
			</li>
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_7');"><?php echo $this->translate("How can I change the labels of Featured and Sponsored markers for the Listings?");?></a>
				<div class='faq' style='display: none;' id='faq_7'>
					<?php echo $this->translate('You can change the labels of Featured and New markers for the Listings by replacing the "featured-label.png", "new-label.png" images respectively at the path "application/modules/Sitereview/externals/images/".<br /> To change the label for Sponsored marker, you can select a different color from "Manage Listing Types" section in the admin panel of this plugin.');?>
				</div>
			</li>

			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_34');"><?php echo $this->translate('How can I mark listings as Hot instead of New on my site?');?></a>
				<div class='faq' style='display: none;' id='faq_34'>
					<?php echo $this->translate('To mark listings as Hot instead of New, you can change the label of New marker for the Listings by replacing the "new-label.png" image at the path "application/modules/Sitereview/externals/images/".');?>
				</div>
			</li>

			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_60');"><?php echo $this->translate('There is a setting to choose between “Official SocialEngine Videos Plugin” and “Review - Inbuilt Videos”. What is the difference between these two?');?></a>
				<div class='faq' style='display: none;' id='faq_60'>
					<?php echo $this->translate('If you enable "Official SocialEngine Videos Plugin", then video settings will be inherited from the "Videos" plugin and videos uploaded in listings will be displayed on "Video Browse Page" and "Listings Profile" pages.<br /> If you enable "Multiple Listing Types - Inbuilt Videos", then Videos uploaded in the listings will only be displayed on Listing Profile pages and will have their own widgetized Multiple Listing Types - Video View page.');?>
				</div>
			</li>

			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_28');"><?php echo $this->translate("The CSS of this plugin is not coming on my site. What should I do ?");?></a>
				<div class='faq' style='display: none;' id='faq_28'>
					<?php echo $this->translate("Please enable the 'Development Mode' system mode for your site from the Admin homepage and then check the page which was not coming fine. It should now seem fine. Now you can again change the system mode to 'Production Mode'.");?>
				</div>
			</li>
      
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_29');"><?php echo $this->translate("I want emails sent out from this plugin to be attractive. How can I do this?");?></a>
				<div class='faq' style='display: none;' id='faq_29'>
					<?php echo $this->translate('You can send attractive emails to your users via rich, branded, professional and impact-ful emails by using our "%1$s". To see details, please %2$s.', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-email-templates-plugin">Email Templates Plugin</a>', '<a target="blank" href="http://www.socialengineaddons.com/socialengine-email-templates-plugin">visit here</a>');?>
				</div>
			</li>      
      
		</ul>
	</div>
	<?php break; ?>

	<?php case 'multiplelistingtype': ?>
    <div class="admin_seaocore_files_wrapper">
      <ul class="admin_seaocore_files seaocore_faq">	
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_2');"><?php echo $this->translate("How can I change the layout of Home, Profile and Browse pages of multiple listing types on my site?");?></a>
				<div class='faq' style='display: none;' id='faq_2'>
					<?php echo $this->translate('New Home, Profile and Browse pages are created for every new listing type in the Layout Editor. Now, you can easily customize these pages using the Layout Editor and have different layout for multiple listing types on your site. Multiple widgets are available which can be easily moved, added or removed.');?>
				</div>
			</li>
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_3');"><?php echo $this->translate("What are multiple Listing Types and how can these be configure?");?></a>
				<div class='faq' style='display: none;' id='faq_3'>
					<?php echo $this->translate("Multiple Listing Types enable you to have different independent Listing Systems on your website. A very beneficial aspect of this feature is that it allows you future extension of your website as you can easily create a new listing type with this plugin at any time. You can easily create multiple listings for different types of content. This extremely power feature helps you in managing and organizing different types of listings on your site.<br /> You can configure all these independent listing types such that they appear completely different from each other in terms of Layout, Features, Custom Fields and many more.<br /> To view the demo, please visit: <a href='http://demo.socialengineaddons.com/products' target='_blank'>http://demo.socialengineaddons.com/products</a>");?>
				</div>
			</li>
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_4');"><?php echo $this->translate('What is the use of Visibility option in the "Manage Listing Types" section?');?></a>
				<div class='faq' style='display: none;' id='faq_4'>
					<?php echo $this->translate('While setting up a new listing type on your site, you can disable the visibility of respective listing type by using the "Visibility" option below, so that users can not see listings of that listing type during the setup. If you disable the visibility of a listing type, then the link for that listing type will not be shown in any navigation bar, drop-downs in search forms and other places.');?>
				</div>
			</li>
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_5');"><?php echo $this->translate('I want 2 listings belonging to different categories mapped with 2 different Profile Fields to be compared over a particular attribute. How do I configure the Profile Question for this attribute?');?></a>
				<div class='faq' style='display: none;' id='faq_5'>
					<?php echo $this->translate('In this case, you should first create the Profile Question for this attribute for first Profile Field and then duplicate this Question for the second Profile Field. Example: You may want to compare 2 listings belonging to categories Phone and Tablet based on their Screen Size.');?>
				</div>
			</li>
      
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_6');"><?php echo $this->translate('I have created various listing types on my site. How can I upload different icons for each listing type?');?></a>
				<div class='faq' style='display: none;' id='faq_6'>
					<?php echo $this->translate(' To upload different icons for each listing type, please follow the steps below:')?>
          <p>
            <b><?php echo $this->translate("Step 1:") ?></b>
          </p>
          <div class="code">
            <?php echo $this->translate(nl2br("a) Make an icon for the listing type.")) ?>
            <br /><br />
            <?php echo $this->translate(nl2br("b) Now, go to the path: <b class='bold'>'/application/modules/Sitereview/externals/images/types/'</b> and upload the icon.")) ?>
          </div><br />           
          <p>
            <b><?php echo $this->translate("Step 2:") ?></b>
          </p>        
          <div class="code">
            <?php echo $this->translate(nl2br("a) In the below lines of code, replace LISTING_TYPE_ID with the id of listing type and ICON_NAME with the name of the icon made in Step 1.")) ?>
          <br /><br />
          <?php echo $this->translate("<b class='bold'>.activity_icon_sitereview_new_listtype_LISTING_TYPE_ID,<br/>.item_icon_sitereview_listtype_LISTING_TYPE_ID,<br/>.notification_type_sitereview_LISTING_TYPE_ID_suggestion{background-image:url(~/application/modules/Sitereview/externals/images/types/ICON_NAME.file_extension);}</b><br/><br/>(Note: To get the LISTING_TYPE_ID, go to the ‘Admin Panel’ >> ‘PLUGIN_NAME’ >> ‘Manage Listing Types’. For example: if the id of the listing type is 1, then the icon name will be: “item_icon_sitereview_1”.)<br/><br/>Copy the lines of code after replacing the LISTING_TYPE_ID and ICON_NAME.<br/>"); ?><br/>

 
            <?php echo $this->translate(nl2br("b) Now, Open the file:  <b class='bold'>'/application/modules/Sitereview/externals/styles/main.css'</b> and paste the copied lines of code in the last of this file.")) ?>
          </div><br />           
          
				</div>
			</li>      
      
      
			</ul>
		</div>
	<?php break; ?>
 	<?php case 'package': ?>
	<div class="admin_sitepage_files_wrapper">
		<ul class="admin_sitepage_files sitepage_faq">
			<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_2');"><?php echo $this->translate("What are Listing Packages? How are Packages and Member Level Settings related?");?></a>
				<div class='faq' style='display: none;' id='faq_2'>
					<?php echo $this->translate('Ans: Both packages and member level settings enable you to configure settings for listings belonging to them / created by members belonging to them, like auto-approve, featured, sponsored, features available, apps accessible, etc. Packages can be enabled from "Manage Listing Types" corresponding listing type and created from "Manage Packages" section. If packages are disabled, then settings configured in Member Level Settings apply. If packages are enabled, then before creating a listing on your site, users will have to choose a package for it. Packages in this system are very flexible and create many settings as mentioned below, to suit your needs:');?>
					<ul>
						<li>
							<?php echo $this->translate('If you have enabled packages for listings on your site, then create packages from the "Manage Packages" section. Users will have to select a package before creating a listing. You can choose settings for packages like free/paid, duration, auto-approve, featured, sponsored, features available, etc.');?>
						</li>
						<li>
							<?php echo $this->translate('If you have disabled packages, then settings for listings like auto-approve, featured, sponsored, features available, etc will depend on Member Level Settings. You may configure them from the "Member Level Settings" section.');?>
						</li>
						<li>
							<?php echo $this->translate('If you have configured paid listing packages on your site, then configure payment related settings on your site from the "Billing" > "Settings" and "Billing" > "Gateways" sections.');?>
						</li>
						<li>
							<?php echo $this->translate('Paid / Free package. Paid packages enable you to monetize your site!');?>
						</li>
						<li>
							<?php echo $this->translate('Package cost');?>
						</li>
						<li>
							<?php echo $this->translate('Lifetime Duration for listings of this package (forever, or fixed duration)');?>
						</li>
						<li>
							<?php echo $this->translate('Auto-Approve listings of this package');?>
						</li>
						<li>
							<?php echo $this->translate('Make listings of package sponsored');?>
						</li>
						<li>
							<?php echo $this->translate('Make listings of package featured');?>
						</li>
						<li>
							<?php echo $this->translate('Which of the following features should be available to listings of this package:');?>
							<ul>
								<li>
									<?php echo $this->translate('Overview');?>
								</li>
								<li>
									<?php echo $this->translate('Location Map');?>
								</li>
        <li>
									<?php echo $this->translate('User Review');?>
								</li>
							</ul>
							<?php echo $this->translate('and more'); ?>
						</li>
						<li>
							<?php echo $this->translate('Show all, none or restricted profile information for listings of this package. Restricted profile information will enable you to choose the profile fields that should be available to listings of this package.'); ?>
						</li>
					</ul>
				</div>
			</li>
   <li>
					<a href="javascript:void(0);" onClick="faq_show('faq_4');"><?php echo "How can I disable Packages functionality for a Listing Type ?";?></a>
					<div class='faq' style='display: none;' id='faq_4'>
						<?php echo 'You can disable Packages functionality for a listing type using the “Packages” setting available in the "Manage Listing Types" section of this plugin.';?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_61');"><?php echo $this->translate("Can the Listing owner change the package of a Listing after its creation?");?></a>
					<div class='faq' style='display: none;' id='faq_61'>
						<?php echo $this->translate("Ans: Yes, Listing owners can change the package of their Listings from the 'Packages' section at Listing Dashboard. All the available packages with their features are listed there. Once the package for a listing is changed, all the settings of the listing will be applied according to the new package, including apps available, features available, price, etc.<br />Please note that if you(admin) have not selected the 'Auto-Approve' field for the new package in 'Manage Packages' section, then the Listing will get dis-approved. Also, if the new package is a paid package then the Listing will get dis-approved even if it has been made auto-approved by you(admin) and listing owner will have to first make the payment to make it approved again.");?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_59');"><?php echo $this->translate("Why am I not able to delete a Listing Package?");?></a>
					<div class='faq' style='display: none;' id='faq_59'>
						<?php echo $this->translate("Ans: You can not delete a Listing Package once you have created it. This is because the consistency of the already created listings in that package would get affected in that case. But if you wish, you can disable a listing package from Manage Packages section and hence it would not be displayed in the list of packages during the initial step of the listing creation and also while upgrading the package of a listing at Listing's Dashboard.");?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_60');"><?php echo $this->translate("Is it possible to let users create free trial listings on my site valid only for a limited period of time?");?></a>
					<div class='faq' style='display: none;' id='faq_60'>
						<?php echo $this->translate('Ans: Yes, it is possible to do so by doing the following things:<br />1) You can disable the existing free package from the "Manage Packages" section at the admin panel of this plugin.<br />2) Now, create a new free package from there and you can set the specifications including the "Duration" and the features you want to give users in that package and then do not select the checkbox for field \'Show in "Other available Packages" List\' so that users will not be able to upgrade their listing\'s package and use this package for more than the no. of days you have set there.<br />3) Also, in future when you do not want users to create free Listings anymore at your site, you can disable all the FREE packages from "Manage Packages" section.');?>
					</div>
				</li>
			</ul>
		</div>
	<?php break; ?>
 
 <?php case 'claim': ?>
		<div class="admin_sitepage_files_wrapper">
			<ul class="admin_sitepage_files sitepage_faq">
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_13');"><?php echo $this->translate("Can users claim listings on my site, if they are the rightful owner? How does this work?");?></a>
					<div class='faq' style='display: none;' id='faq_13'>
						<?php echo $this->translate('Ans: The claiming of listings feature can be enabled from the "Claim a Listing" field in corresponding Listing Type. If enabled, users will be able to file claims for listings. You can customize the position of the "Claim a Listing" link from Listing Type. Claims filed by users can be managed from the "Manage Claims" section. From "Member Level Settings", you can choose if users of a member level should be able to claim listings. Whenever someone makes a claim for a listing, that claim comes to you(admin) for review and approval.<br />You(admin) can also assign certain users as "Claimable Listing Creators" from the "Manage Claims" section. Though using the "Claim a Listing" link a user can make a claim for any listing, the listings created by "Claimable Listing Creators" get the "Claim this Listing" link on the listing profile itself. This would be useful in cases like if you have certain members whose job is to create only those listings on your site which could later be easily claimed by their 
rightful owners.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_14');"><?php echo $this->translate("How can I modify the Terms of Service for claiming a listing?");?></a>
					<div class='faq' style='display: none;' id='faq_14'>
					<?php echo $this->translate('Ans: You can modify them from the Language variables of the plugin using the Language Manager. Go to the Layout > Language Manager and search for the two variables "LISTING_TERMS_CLAIM_1" and "LISTING_TERMS_CLAIM_2" and edit them according to your specifications.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_15');"><?php echo $this->translate("If I approve a claim request for a Listing, what changes take place?");?></a>
					<div class='faq' style='display: none;' id='faq_15'>
					<?php echo $this->translate("Ans: If you approve a claim request by a user for a Listing, it means that you are assigning that Listing to the claimer and the claimer will now be the new owner of that listing. All ownership rights for this listing will then be transferred to the new owner. In this case, both the new and old owners will be recieving an email concerning the change in the ownership of the Listing.");?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_16');"><?php echo $this->translate("Can I change the owner of a Listing and assign it to someone else even if I have not recieved a claim request for that Listing?");?></a>
					<div class='faq' style='display: none;' id='faq_16'>
					<?php echo $this->translate("Ans. Yes, you can change the owner of a listing from the 'change owner' option in the 'Mange Listings' section. Both the new and old owner of the listing will be recieving an email concerning the change in the ownership of the listing.");?>
					</div>
				</li>
			</ul>
		</div>
	<?php break; ?>
 
	<?php case 'listingfeatures': ?>
<div class="admin_seaocore_files_wrapper">
	<ul class="admin_seaocore_files seaocore_faq">	
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_11');"><?php echo $this->translate("How can I disable the Price, Location, Overview and Where to Buy features in a listing type?");?></a>
					<div class='faq' style='display: none;' id='faq_11'>
						<?php echo $this->translate('You can disable Price, Location, Overview, Where to Buy and many other features in a listing type using the respective fields available in the "Manage Listing Types" section of this plugin.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_12');"><?php echo $this->translate("What is 'Where To Buy'? How is this useful for my users?");?></a>
					<div class='faq' style='display: none;' id='faq_12'>
						<?php echo $this->translate('"Where to Buy" feature enables you to allow users of your site to add price and links of their listings available at various e-commerce sites where there item will be available for rent / purchase. With this feature users will be able to add various links for the availability of their listings at one place.<br /> You can also choose to enable this feature without price to use it as "References" so that users can add only links of other sites in listing types like Blogs, Education, etc where price is not required, by using the "Allow Where to Buy" field while adding / editing a listing type from the "Manage Listing Types" section of this plugin.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_45');"><?php echo $this->translate("In how many ways can I enable reviews on my site?");?></a>
					<div class='faq' style='display: none;' id='faq_45'>
						<?php echo $this->translate("You can either disable reviews for a listing type or choose to enable 'editor reviews', 'user reviews' or 'both editor and user reviews' by using the 'Allow Reviews' field while adding / editing a listing type from 'Manage Listing Types' section of this plugin.");?>
					</div>
				</li>
				<li>
				<a href="javascript:void(0);" onClick="faq_show('faq_43');"><?php echo $this->translate("I want to display Listing Owner's profile picture as Profile photo of the listings in a particular listing type. How can I do this?");?></a>
				<div class='faq' style='display: none;' id='faq_43'>
					<?php echo $this->translate('You can display Listing Owner’s profile picture as Profile photo of the listings in any listing type by using the "Listing Profile Photo" field while adding / editing a listing type from the "Manage Listing Types" section of this plugin.');?>
				</div>
			</li>
			</ul>
		</div>
	<?php break; ?>

	<?php case 'editors': ?>
  <div class="admin_seaocore_files_wrapper">
    <ul class="admin_seaocore_files seaocore_faq">	
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_13');"><?php echo $this->translate("What are Editor Reviews and how they can be useful for my site?");?></a>
					<div class='faq' style='display: none;' id='faq_13'>
						<?php echo $this->translate('Editor reviews are helpful in displaying accurate, trusted and unbiased reviews that will showcase listings’ (for example: listings of hotels, products, etc.) quality, features, and value. This will bring more user engagement to your site, as editor reviews provide reviews from expert people (editors) on the products of their interest.<br /> You can choose Editors from “Manage Editors” section of this plugin.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_14');"><?php echo $this->translate("I can choose various Editors for my site, but only one Super Editor. Why is this so? What is the difference between an Editor and a Super Editor?");?></a>
					<div class='faq' style='display: none;' id='faq_14'>
						<?php echo $this->translate('You can choose any number of Editors for your site who all will be allowed to write editor reviews for various listings. You can also choose to make different Editors for different listing types on your site who all will be allowed to write editor reviews for allowed listing types only. There can be only one Super Editor as this Editor will be allowed to write editor reviews on all the listing types created on your site. Also, if any Editor deletes the respective member profile, then all the editor reviews written by that Editor will be automatically assigned to the Super Editor.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_15');"><?php echo $this->translate("I am removing a member as Editor from my site. What will happen to the editor reviews written by him?");?></a>
					<div class='faq' style='display: none;' id='faq_15'>
						<?php echo $this->translate('If you remove a member as Editor from your site, then you would be able to assign all Editor reviews written by that editor to any other editor on your site.<br /> You can remove an editor by using "Remove" option from "Manage Editors" section of this plugin.');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_16');"><?php echo $this->translate("How can I select a Featured Editor on my site?");?></a>
					<div class='faq' style='display: none;' id='faq_16'>
						<?php echo $this->translate('You can select a Featured Editor on your site by using the "Use the auto-suggest field to select Featured Editor." field available in the edit settings of "Featured Editor" widget from the Layout editor by placing the widget on any widgetized page.<br /> You can place this widget multiple times on different pages with different featured editor chosen for each placement.');?>
					</div>
				</li>
			</ul>
		</div>
	<?php break; ?>

	<?php case 'import': ?>
    <div class="admin_seaocore_files_wrapper">
      <ul class="admin_seaocore_files seaocore_faq">	
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_6');"><?php echo $this->translate("I want to import many Listings altogether from a CSV file. Is that possible with this plugin?");?></a>
					<div class='faq' style='display: none;' id='faq_6'>
						<?php echo $this->translate('Yes, you can import many listings at the same time from a CSV file. To do so, please go to the "Import" section at the admin side of this plugin and read all the instructions given there under the section "Import Listings from a CSV file" and then click on "Import Listings" link. You can also import listings from "Listings / Catalog Showcase Plugin", "Recipes Plugin" and "Official SocialEngine Blog Plugin".');?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_51');"><?php echo $this->translate("I want to convert all the Listings of my site into Review Listings. How can it be done ?");?></a>
					<div class='faq' style='display: none;' id='faq_51'>
						<?php echo $this->translate("Yes, you can convert your Listings into Review Listings. Please go to the 'Import' section at the admin side of this plugin. Then, you can import your Listings into Review Listings there.");?>
					</div>
				</li>
				<li>
					<a href="javascript:void(0);" onClick="faq_show('faq_52');"><?php echo $this->translate("In the import section at admin side, I can not see any button or link to import Listings at my site into Review Listings. Why is it so ?");?></a>
					<div class='faq' style='display: none;' id='faq_52'>
						<?php echo $this->translate("There are some required conditions which are required to be fulfilled before starting to import Listings into Review Listings. When the condition becomes true or is fulfilled, the button for 'Import listings' will automatically appear there.");?>
					</div>
				</li>
			</ul>
		</div>
	<?php break; ?>
<?php endswitch; ?>
