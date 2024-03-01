<!-- modules/Dietary/Views/public/index.tpl -->
{if $do_i_have_location eq false}
	<div class="text-center">
	<h1>Advanced Health Care Public Menu Board</h1>
	<p>We're sorry! This page displays the current menu and activities for each Advanced
	Health Care location. However, this page is only accessible from within an Advanced
	Heath Care facility.</p>
{else}

{* IF ARR, expand to type? *}
{if $location->id == 36}
<style>
@font-face {
	font-family: Cormorant;
	src: url(/css/Cormorant-Bold.otf);
}
@font-face {
	font-family: Street;
	src: url(/css/strexp__.ttf);
}
#headerLogo {
	display: none;
}
body {
	background-color: white;
	color: black;
}
#menuTitle {
	display: none;
}
#menuPic {
	position: absolute;
	background: url(./img/leaf.png) no-repeat;
	background-position-y: 100px;
	background-position-x: center;
	background-size: 350px;
	margin-top
}
#date {
	margin-top: 3px;
}
div.menu h2 {
	text-transform: uppercase; 
	font-family: Cormorant;
}
p.time {
	font-family: Street;
	/*font-weight: bold;*/
}

#altMenu h2 {
	font-family: Street;
}

.raisingTheStandard {
	font-family: Street;
	font-weight: bolder;
	text-transform: uppercase;
}

.raisingTheStandard::before {
	content: "";
	text-align-center;
}

.raisingTheStandard img{
	display: none;
}

.activitiesStandard img {
	display: none;
}

#headerTop img{
	display: none;

}
#activitiesTitle::before {
	content: "Upcoming Activities!";
	text-align-center;
	font-family: Cormorant;
	color: white;
	font-size: 40pt;
	height: 50px;
	/*background-image: linear-gradient(to right, #175F72, #6797A9);*/
}

#activitiesTitle {
	background-image: linear-gradient(to right, #175F72, #6797A9);
	padding: 10px;
}

#activitiesTitle img{
	display: none;
	margin-bottom: 40px;
}
#headerTop {
	height: 15px;
	background-image: linear-gradient(to right, #175F72, #6797A9);
}

#teton {
	background: none;
	height: 350px;
}

.activity h2 {
	text-transform: uppercase; 
	font-family: Cormorant;
}

</style>
{/if}

<div id="transitionDiv">
	{if $warning}
	<div id="warning">
		<img src="{$IMAGES}/warning.png" alt="">
	</div>
	{/if}
	{if $zoom}
		<link rel="stylesheet" href="https://ahc.aptitudecare.com/css/public_styles_zoom.css" type="text/css" />
	{/if}

	<!-- Main menu content page -->
	<div id="panel-0" class="rotatingPage" time={if $location->id != 36}18{else}1800{/if}>
		<div id="mainContent">
			<div id="mainLogo">
				<img src="{$IMAGES}/facility_logo/{$location->logo}" alt="">
			</div>
			<div id="menuTitle">
				<img src="{$IMAGES}/featured_menu.png" alt="">
			</div>
				<div id="menuContent">
					<div class="menu">
						<h2>Breakfast</h2>
						<p class="text-14 time">{$meal[0]->start|default:""|date_format:"%l:%M %P"} - {$meal[0]->end|default:""|date_format:"%l:%M %P"}</p>
						{foreach from=$menuItems[0]->content item=menu}
						<p>{$menu|strip_tags:true}</p>
						{/foreach}
					</div>

					<div class="menu">
						<h2>Lunch</h2>
						<p class="text-14 time">{$meal[1]->start|default:""|date_format:"%l:%M %P"} - {$meal[1]->end|default:""|date_format:"%l:%M %P"}</p>
						{foreach from=$menuItems[1]->content item=menu}
						<p>{$menu|strip_tags:true}</p>
						{/foreach}
					</div>

					<div class="menu">
						<h2>Dinner</h2>
						<p class="text-14 time">{$meal[2]->start|default:""|date_format:"%l:%M %P"} - {$meal[2]->end|default:""|date_format:"%l:%M %P"}</p>
						{foreach from=$menuItems[2]->content item=menu}
						<p>{$menu|strip_tags:true}</p>
						{/foreach}
					</div>

					<div id="altMenu">
						<h2 style="font-size: 16px;">Alternate Menu Items</h2>			
						{$alternates->content}
					</div>
					<div id="guestWelcome">
						{$locationDetail->menu_greeting}
					</div>
			</div>
			<div id="menuPic">
				<div class="raisingTheStandard">
					<img src="{$IMAGES}/raising_the_standard.png" alt="">
				</div>
			</div>
		</div>	


	</div>

	{if $location->id != 36}
	<!-- Activities page -->
	<div id="panel-1" class="rotatingPage" style="display: none;" time=6>
		<div class="transitionDiv">
			<div id="activitiesContent">	
				<div id="teton">
					<div id="mainLogo">
						<img src="{$IMAGES}/facility_logo/{$location->logo}" alt="">
						{if $location->id == 12}
							<div class="grangevilleActivitiesStandard">
						{else}
							<div class="activitiesStandard">
						{/if}
							<img src="{$IMAGES}/raising_the_standard.png" alt="Raising the Standard">
						</div>
					</div>
				</div>
				

				<div id="activitiesTitle">
					<img src="{$IMAGES}/weekly_activities.png" alt="Weekly Activities">
				</div>
				{foreach $weekActivities as $k => $activity}
					<div class="activity">
						<h2>{$k|date_format: "%A"}</h2>
						{if is_array($activity)}
						{foreach $activity as $a}
							<p>
 								<strong>{$a->time_start|date_format: "%l:%M %P"|default:""}</strong>
 								{$a->description}
							</p>
							
						{/foreach}
						{/if}
					</div>
				{/foreach}

			</div>
		</div>
	</div>
	{/if}
{/if}