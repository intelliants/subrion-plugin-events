<div id="event-popup" class="well">
    <legend>
        {lang key='closest_events'}
        <button type="button" class="close">&times;</button>
    </legend>
    <div class="progress progress-striped active">
        <div class="bar" style="width: 100%;"></div>
    </div>
    <a href="#" id="view-all-events" class="btn btn-info btn-sm">{lang key='all_events_on_the_day'}</a>
</div>

<div id="event-calendar" data-date="{$smarty.now|date_format:'%Y-%m-%d'}" data-date-format="yyyy-mm-dd"></div>

{ia_add_media files="js:_IA_URL_modules/events/js/frontend/calendar-additions, css:_IA_URL_modules/events/templates/front/css/calendar, css:_IA_URL_modules/events/templates/front/css/datepicker"}