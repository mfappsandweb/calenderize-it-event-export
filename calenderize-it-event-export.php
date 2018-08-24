<?php
/**
 * Plugin Name:    Calenderize It Event Export
 * Description:    Export Calenderize It events into HTML file.
 * Version:        0.2.2
 * Author:         MF Softworks
 * Author URI:     https://mf.nygmarosebeauty.com/
 * License:        GPLv3
 * Copyright:      MF Softworks
 */

/**
 * Define plugin version
 */ 
define('CALENDERIZE_IT_EVENT_EXPORT_VERSION', '0.2.2');

/**
 * Create plugin wp-admin page
 */
add_action( 'admin_menu', array( 'Calenderize_It_Export_Events', 'create_admin_page' ) );

class Calenderize_It_Export_Events
{
    /**
     * Construct the export event class
     */
    public function __construct($start_date, $end_date) {
        $this->get_event_list($start_date, $end_date);
    }

    /**
     * Create HTML for admin page form
     */
    public function create_admin_page_html() {
        // Set today's date
        $today = new DateTime(date('Y-m-d')); 
        ?>
        <div class="wrap">
            <h1>Calenderize It Event Export</h1>
            <form method="post">
                <table class="optiontable form-table">
                    <tr valign="top">
                        <th><label for="start-date">Start Date</label></th>
                        <td>
                            <input name="start-date" type="date" id="start-date" value="<?php $today->modify('+1 day'); echo $today->format('Y-m-d') ?>" class="date">
                            <span class="description">Enter the first date of events that should be included</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end-date">End Date</label></th>
                        <td>
                            <input name="end-date" type="date" id="end-date" value="<?php $today->modify('+1 month'); echo $today->format('Y-m-d') ?>" class="date">
                            <span class="description">Enter the last date of events that should be included</span>
                        </td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="export_events" id="export_events" class="button-primary" value="Download" /></p>
            </form>
        </div>
        <?php
        if( isset ( $_POST ) ) {
            if( isset($_POST['start-date']) && isset($_POST['end-date']) ) {
                new Calenderize_It_Export_Events($_POST['start-date'], $_POST['end-date']);
            }
        }
    }
    /**
     * Hook add wp-admin plugin page
     */
    public function create_admin_page() {
        // Add page under "Tools"
        add_management_page(
            'Calenderize It Event Export',
            'Calenderize It Event Export',
            'publish_events',
            'ciee',
            array( 'Calenderize_It_Export_Events', 'create_admin_page_html' )
        );
    }

    /**
     * Get event list from start to end dates
     */
    public function get_event_list($start_date = "2018-08-25", $end_date = "2018-09-25"){
        // Format start date
        $start_date = new DateTime($start_date);
        // Format end date
        $end_date = new DateTime($end_date);
        // WordPress Query arguments
        $args = array(
            'date_query' => array(
                'after' => $start_date->format('F jS, Y'),
                'before' => $end_date->format('F jS, Y'),
                'inclusive' => true,
            ),
            'meta_type'      => 'DATETIME',
            'meta_key'       => 'fc_start',
            'posts_per_page' => -1,
            'post_status'=>'publish',
            'post_type' => array( 'events' ),
        );
        $eventlist = new WP_Query($args);

        if( $eventlist->have_posts() ) {
            $events = $this->prepare_events_post($eventlist);
            $this->build_event_html($events);
        }
    }

    /**
     * Build HTML file of events
     */
    private function build_event_html($events) {
        var_dump($events);
    }

    /**
     * Prepare events array
     */
    function prepare_events_post($postlist) {
        $result = array();
        while ( $postlist->have_posts() ) : $postlist->the_post();
            $temp= get_post_custom();
            $startdate = $temp['fc_start'][0];
            $enddate = $temp['fc_end'][0];
            $starttime = $temp['fc_start_time'][0];
            $endtime = $temp['fc_end_time'][0];
    
            if(has_term('career-development-events','calendar'))
                $section_name="career-development";
            elseif(has_term('global-learning-events','calendar'))
                $section_name="global-learning";
            elseif(has_term('student-development-events','calendar'))
                $section_name="student-development";
            elseif(has_term('student-care-events','calendar'))
                $section_name="student-care";
            if(strtotime("today")<=strtotime($enddate))
            {
                array_push($result,array('permalink'=>get_the_permalink(),
                                         'title'=>get_the_title(),
                                         'section_name'=>$section_name,
                                         'post_thumbnail'=>get_the_post_thumbnail_url(),
                                         'startdate'=>$startdate,
                                         'enddate'=>$enddate,
                                         'starttime'=>$starttime,
                                         'endtime'=>$endtime,
                                         'excerpt'=>get_the_excerpt()
                ));
            }
        endwhile;
    
        usort($result, function($a, $b) {
            return strtotime($a['startdate']) - strtotime($b['startdate']);
        });
    
        return $result;
    }
}
?>