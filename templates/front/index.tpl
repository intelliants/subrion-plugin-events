{if isset($items) && $items && isset($term)}
    <div class="info m-b text-i">{lang key='listings_found'}: <b>{$paginator.total}</b></div>
{/if}
{if isset($items) && $items}

    <div class="ia-items events">
        {foreach $items as $event}
            <div class="media ia-item ia-item--border event">
                {if $event.image}
                    <a href="{ia_url type='url' item='events' data=$event}" class="media-object pull-left">{ia_image file=$event.image type="thumbnail" class='img-rounded img-responsive' width="100"}</a>
                {else}
                    <a href="{ia_url type='url' item='events' data=$event}" class="media-object pull-left">{ia_image class='img-rounded img-responsive' width="100"}</a>
                {/if}

                <div class="media-body">
                    <h4 class="media-heading">
                        <a href="{ia_url type='url' item='events' data=$event}">{$event.title|escape:'html'}</a>
                    </h4>
                    <div class="media-date">
                        {if $event.date_end|strtotime > $smarty.server.REQUEST_TIME}
                            <span class="text-success"><span class="fa fa-clock-o"></span> {$event.date} - {$event.date_end}</span>
                        {else}
                            <span class="fa fa-clock-o"></span> {$event.date} - {$event.date_end}
                        {/if}
                        {if $event.venue}, <br><span class="fa fa-map-marker"></span> {$event.venue|escape:'html'}{/if}
                    </div>

                    {if !empty($event.summary)}
                        {$event.summary}
                    {else}
                        {$event.description|truncate:300:'...'}
                    {/if}
                </div>
                <div class="media-info">
                    <span class="fa fa-user"></span>
                    {lang key='published_by'}
                    {if $event.owner}
                        <a href="{$smarty.const.IA_URL}member/{$event.owner}.html">{$event.owner_fullname}</a>
                    {else}
                        <i>{lang key='guest'}</i>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
    {navigation aTotal=$paginator.total aTemplate=$paginator.template aItemsPerPage=$paginator.limit aNumPageItems=5 aTruncateParam=1}
{else}
    <div class="message alert bg-warning">{lang key='no_events'}</div>
{/if}

{ia_print_css files='_IA_URL_modules/events/templates/front/css/style'}
