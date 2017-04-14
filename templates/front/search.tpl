<div class="ia-items events">
    {foreach $entries as $entry}
        <div class="media ia-item ia-item--border event">
            {if $entry.image}
                <a href="{$entry.url}" class="media-object pull-left">{printImage imgfile=$entry.image width=100 height=100 class='img-rounded img-responsive'}</a>
            {else}
                <a href="{$entry.url}" class="media-object pull-left"><img src="{$smarty.const.IA_CLEAR_URL}plugins/events/templates/front/img/preview-image.png" alt="{$entry.title}" width="100" height="100" class="img-rounded img-responsive"></a>
            {/if}

            <div class="media-body">
                <h4 class="media-heading">
                    <a href="{$entry.url}">{$entry.title|escape:'html'}</a>
                </h4>
                <div class="media-date">
                    {if $entry.date_end|strtotime > $smarty.server.REQUEST_TIME}
                        <span class="text-success"><span class="fa fa-clock-o"></span> {$entry.date} - {$entry.date_end}</span>
                    {else}
                        <span class="fa fa-clock-o"></span> {$entry.date} - {$entry.date_end}
                    {/if}
                    {if $entry.venue}, <br><span class="fa fa-map-marker"></span> {$entry.venue}{/if}
                </div>

                {$entry.description|strip_tags|truncate:300:"..."}
            </div>
            <div class="media-info">
                <span class="fa fa-user"></span>
                {lang key='published_by'}
                {if $entry.owner}
                    {ia_url item='members' data=$entry.owner text=$entry.owner_fullname}
                {else}
                    <i>{lang key='guest'}</i>
                {/if}
            </div>
        </div>
    {/foreach}
</div>