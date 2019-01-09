<% if AdvertisementSet %>
<div id="AdvertisementsHolder" class="cycle-slideshow">
<% loop AdvertisementSet %>
    <div class="advertisement">
        <% if $Link %><a href="$Link"><% end_if %>
        <img src="$AdvertisementImage.Link" alt="$AdvertisementImage.Title.ATT" />
        <% if $Link %></a><% end_if %>
        <% if $ShowTitle %><% if $Title %><h2>$Title</h2><% end_if %><% end_if %>
        <% if $ShowDescription %><% if $Description %><p>$Description</p><% end_if %><% end_if %>
    </div>
<% end_loop %>
</div>
<div id="AdvertisementsPreviousNext"></div>
<% end_if %>
