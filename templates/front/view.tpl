<div class="event-view">
    {if $item.image}
        <div class="event-view__image m-b">
            <div class="ia-item-image">{ia_image file=$item.image class='img-responsive'}</div>
        </div>
    {/if}

    <div class="event-view__text m-b">
        {$item.description}
    </div>

    <div class="event-view__date m-b">
        {if $item.date_end|strtotime > $smarty.server.REQUEST_TIME}
            <span class="text-success"><span class="fa fa-clock-o"></span> {$item.date} - {$item.date_end}
        {else}
            <span class="fa fa-clock-o"></span> {$item.date} - {$item.date_end}
        {/if}
    </div>

    {if $item.venue}
        <div id="event-venue-box" class="event-view__venue m-b">
            <p><span class="fa fa-map-marker"></span> {$item.venue}</p>
            <input type="hidden" id="event-venue" value="{$item.venue}">
            <input type="hidden" name="longitude" value="{if isset($item.longitude)}{$item.longitude}{/if}">
            <input type="hidden" name="latitude" value="{if isset($item.latitude)}{$item.latitude}{/if}">
            <div id="event-gmap"></div>
        </div>
    {/if}
</div>

<div class="ia-item__panel">
    <!-- AddThis Button BEGIN -->
    <div class="addthis_toolbox addthis_default_style ia-item__panel__item pull-left">
        <a class="addthis_counter addthis_pill_style"></a>
    </div>
    <script type="text/javascript">var addthis_config = { "data_track_addressbar":true };</script>
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5228073734bf0c90"></script>
    <!-- AddThis Button END -->

    <span class="ia-item__panel__item pull-right">
        <span class="fa fa-user"></span>
        {if $item.owner}
            {lang key='published_by'}
            <a href="{$smarty.const.IA_URL}member/{$item.owner}.html">{$item.owner_fullname}</a>
        {else}
            <i>{lang key='guest'}</i>
        {/if}
    </span>
</div>
{ia_print_css files='_IA_URL_modules/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_modules/events/js/frontend/view'}
