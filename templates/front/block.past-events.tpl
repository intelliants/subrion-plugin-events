{if $events.past}
	<div class="media-items">
		{foreach from=$events.past item=event}
			<div class="media">
				<div class="media-body">
					<h4 class="media-heading"><a href="{$event.url}">{$event.title}</a></h4>
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