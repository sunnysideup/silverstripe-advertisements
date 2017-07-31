<% if AdvertisementSet %>
<div id="AdvertisementsHolder" class="cycle-slideshow">
<% loop AdvertisementSet %>
    <div class="advertisement"><% if Link %>
        <a href="$Link"><% end_if %>
        <img src="$AdvertisementImage.FileName" alt="$Title.ATT" />
        <% if Link %></a><% end_if %>
        <h2>$Title</h2>
        <p>$Description</p>
    </div>
<% end_loop %>
</div>
<div id="AdvertisementsPreviousNext"></div>
<% end_if %>
