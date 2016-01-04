{if $events.past}
	<div class="media-items events-list">
		{foreach from=$events.past item=event}
			<div class="media">
				{if $event.image}
					<a href="{$event.url}" class="media-object pull-left">{printImage imgfile=$event.image width=60 height=60 class='img-rounded img-responsive'}</a>
				{else}
					<a href="{$event.url}" class="media-object pull-left"><img src="{$smarty.const.IA_CLEAR_URL}plugins/events/templates/front/img/preview-image.png" alt="{$event.title}" width="60" height="60" class="img-rounded img-responsive"></a>
				{/if}

				<div class="media-body">
					<h5 class="media-heading"><a href="{$event.url}">{$event.title}</a></h5>
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