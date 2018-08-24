<?php
/**
 * Plugin Name:    Calenderize It Event Export
 * Description:    Export Calenderize It events into HTML file.
 * Version:        0.3.1
 * Author:         MF Softworks
 * Author URI:     https://mf.nygmarosebeauty.com/
 * License:        GPLv3
 * Copyright:      MF Softworks
 */

/**
 * Define plugin version
 */ 
define('CALENDERIZE_IT_EVENT_EXPORT_VERSION', '0.3.1');

/**
 * Create plugin wp-admin page
 */
add_action( 'admin_menu', array( 'Calenderize_It_Export_Events', 'create_admin_page' ) );

class Calenderize_It_Export_Events
{
    /**
     * Declare global date options
     */
    private $start_date;
    private $end_date;

    /**
     * Construct the export event class
     */
    public function __construct($start_date, $end_date) {
        // Log object creation and data
        $this->console_log("Creating export event object");
        $this->console_log($start_date);
        $this->console_log($end_date);
        // Set global date options
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        // Get events
        $this->get_event_list();
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
    public function get_event_list(){
        // WordPress Query arguments
        $args = array(
            'meta_type'      => 'DATETIME',
            'meta_key'       => 'fc_start',
            'posts_per_page' => -1,
            'post_status'=>'publish',
            'post_type' => array( 'events' ),
        );
        // Log WP Query arguments
        $this->console_log($args);
        // Get events
        $eventlist = new WP_Query($args);
        // Log WP Query result
        $this->console_log($eventlist);

        // If events are found, prepare events, build HTML
        if( $eventlist->have_posts() ) {
            $events = $this->prepare_events_post($eventlist);
            $this->build_event_html($events);
        }
    }

    /**
     * Build HTML file of events
     */
    private function build_event_html($events) {
        // Log prepared events
        $this->console_log($events);
        foreach($events as $event) {
            $this->display_event($event);
        }
    }

    /**
     * Write event HTML
     */
    private function display_event($event) { ?>
        <a href="<?php echo $event['permalink']; ?>"  class="masonryitem">
            <div class="item <?php echo $event['section_name']; ?>">
                <div class="image-wrapper">
                    <img src="<?php if(!empty($event['post_thumbnail'])) echo $event['post_thumbnail']; else echo get_template_directory_uri().'/assets/images/common/placeholder.png' ?>" />
                </div>
                <div class="date">
                    <div class="month"><?php echo date('M',strtotime($event['startdate'])); ?></div>
                    <div class="day"><?php echo date('d',strtotime($event['startdate'])); ?></div></div>
                <div class="title"><?php echo $event['title']; ?></div>
                <div class="time"><?php
                    if ($event['starttime'] === null && $event['endtime'] === null) {
                        if ($event['startdate']==$event['enddate']) {
                            echo date('d M',strtotime($event['startdate']));
                        } else {
                            echo date('d M',strtotime($event['startdate'])) . ' to ' . date('d M', strtotime($event['enddate']));
                        }
                    } else {
                        if($event['startdate']==$event['enddate'])
                        {
                            echo date('d M',strtotime($event['startdate'])) . ', '. date('g:ia',strtotime($event['starttime'])).' to '.date('g:ia',strtotime($event['endtime']));
                        }
                        else
                        {
                            echo '<div>'.date('d M',strtotime($event['startdate'])).', '.date('g:ia',strtotime($event['starttime'])).' to</div><div>'.date('d M',strtotime($event['enddate'])).', '.date('g:ia',strtotime($event['endtime'])).'</div>';
                        }
                    }
                    ?></div>
                <div class="excerpt"><p><?php echo $event['excerpt'] ?></p></div>
            </div>
        </a>
    <?php }

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
            if(strtotime($startdate)>=strtotime($this->start_date) && strtotime($startdate)<=strtotime($this->end_date))
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

    /**
     * HTML Console log for testing
     */
    private function console_log($log) {
        echo '<script>';
        echo 'console.log('. json_encode( $log ) .')';
        echo '</script>';
    }
}
?>