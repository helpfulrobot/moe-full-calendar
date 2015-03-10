<?php

/**
 * Class FullCalendar
 */
class FullCalendar extends Page
{
    private static $singular_name = "[Calendar] Page";

    private static $plural_name = "[Calendar] Page";

    private static $can_be_root = true;

    private static $allowed_children = array(
        "FullCalendarEvent"
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

    public function getDocumentRoot()
    {
        return $this;
    }
}

/**
 * Class FullCalendar_Controller
 */
class FullCalendar_Controller extends Page_Controller
{

    private static $allowed_actions = array(
        'eventsAsJson',
    );

    /**
     * Blocks default silverstripe jquery, and loads all required JS and CSS
     */
    public function init()
    {
        parent::init();

        Requirements::block(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::block(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');

        // Standard stylesheets
        Requirements::combine_files('silverstripe-calendar.css', array(
            "calendar/css/lib/fullcalendar.css",
            "calendar/css/style.css"
        ));

        // Print stylesheet
        Requirements::css('silverstripe-calendar-print.css', 'calendar/css/lib/fullcalendar.print.css', 'print');

        //Moment breaks silverstripes minimisation process so is loaded normally.
        Requirements::javascript("calendar/javascript/lib/moment.min.js");
        Requirements::combine_files('silverstripe-calendar.js', array(
            "calendar/javascript/lib/jquery.min.js",
            "calendar/javascript/lib/fullcalendar.min.js",
            "calendar/javascript/functions.js",
        ));
    }

    /**
     * Ajax call to return all events to the calendar frontend
     *
     * @param string $message
     * @param string $status
     * @return string
     */
    public function eventsAsJson($message = "", $status = "success")
    {
        $this->getResponse()->addHeader(
            "Content-Type",
            "application/json; charset=utf-8",
            "Cache-Control: public, max-age=290304000"
        );

        if ($status != "success") {
            $this->setStatusCode(400, $message);
        }

        return $this->cachedData();
    }

    /**
     * Builds a cache of events if one doesn't exist, store the cache for 12 hours. The cache is cleared/reset
     * when a new event is published.
     *
     * Only return events that are set to IncludeOnCalendar and the EndDate is greater than today (Don't show
     * legacy events)
     *
     * @return json load of events to display
     */
    public function cachedData()
    {
        $cache = SS_Cache::factory('calendar');
        SS_Cache::set_cache_lifetime('calendar', 60 * 60 * 12);

        if (!($result = unserialize($cache->load('events')))) {
            $result = array();

            foreach (FullCalendarEvent::get()->filter(array('IncludeOnCalendar' => true, 'EndDate:GreaterThan' => date("Y-m-d"))) as $event) {
                $result[] = array(
                    "title"     => $event->Title,
                    "start"     => $event->StartDate,
                    "end"       => $event->EndDate,
                    "url"       => $event->URLSegment,
                    "color"     => $event->BackgroundColor,
                    "textColor" => $event->TextColor,
                    "content"   => $event->Content,
                );
            }
            $cache->save(serialize($result), 'events');
        }

        return json_encode($result);
    }
}
