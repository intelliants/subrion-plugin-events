{if isset($events.past) && $events.past}
    <div class="media-items events-list">
        {foreach $events.past as $event}
            <div class="media">
                {if $event.image}
                    <a href="{ia_url type='url' item='events' data=$event}" class="media-object pull-left">{ia_image file=$event.image type="thumbnail" class='img-rounded img-responsive' width=60}</a>
                {else}
                    <a href="{ia_url type='url' item='events' data=$event}" class="media-object pull-left">{ia_image class='img-rounded img-responsive' width=60}</a>
                {/if}
                <div class="media-body">
                    <h5 class="media-heading"><a href="{ia_url type='url' item='events' data=$event}">{$event.title|escape:'html'}</a></h5>
                    <span class="fa fa-clock-o"></span>
                    {if $event.date|date_format:$core.config.date_format == $event.date_end|date_format:$core.config.date_format}
                        {$event.date|date_format:$core.config.date_format}
                    {else}
                        {$event.date|date_format:$core.config.date_format} - {$event.date_end|date_format:$core.config.date_format}
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
{/if}