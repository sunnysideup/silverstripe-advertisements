<% if AdvertisementSet %>
<div id="AdvertisementsHolder">
<% control AdvertisementSet %>
	<div class="advertisement"><% if Link %><a href="$Link"><% end_if %><img src="$ResizedAdvertisementImage.FileName" alt="$Title.ATT" /><% if Link %></a><% end_if %></div>
<% end_control %>
</div>
<div id="AdvertisementsPreviousNext"></div>
<% end_if %>
