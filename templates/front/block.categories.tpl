{if isset($events.categories) && $events.categories}
    <div class="list-group">
        {foreach $events.categories as $category}
            <a href="{$smarty.const.IA_URL}events/{$category.slug}/" class="list-group-item{if isset($core.page.info.events_category_id) && $category.id == $core.page.info.events_category_id} active{/if}">
                <span class="badge">{$category.num}</span>
                {$category.title|escape:'html'}
            </a>
        {/foreach}
    </div>
{/if}