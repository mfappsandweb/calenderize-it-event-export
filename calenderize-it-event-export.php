<?php
/**
 * Plugin Name:    Calendarize it! Event Export
 * Description:    Export Calendarize it! events into HTML file.
 * Version:        0.8.6
 * Author:         MF Softworks
 * Author URI:     https://mf.nygmarosebeauty.com/
 * License:        GPLv3
 * Copyright:      MF Softworks
 */

require_once "vendor/autoload.php";

/**
 * Define plugin version
 */ 
define('CALENDARIZE_IT_EVENT_EXPORT_VERSION', '0.8.6');

/**
 * Create plugin wp-admin page and plugin directory
 */
add_action( 'admin_menu', array( 'Calendarize_It_Export_Events', 'create_admin_page' ) );
register_activation_hook( __FILE__, array( 'Calendarize_It_Export_Events', 'make_download_dir' ) );

class Calendarize_It_Export_Events
{
    /**
     * Declare global date options
     */
    private $start_date;
    private $end_date;
    private $filename;

    /**
     * Construct the export event class
     */
    public function __construct($start_date, $end_date) 
    {
        // Log object creation and data
        $this->console_log("Creating export event object");
        // Set global date options
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        // Get events
        $this->get_event_list();
    }

    /**
     * Make file download dir
     */
    public function make_download_dir() 
    {
        $dir = wp_upload_dir()['basedir'] . '/calendarize-it-event-export';
        wp_mkdir_p($dir);
    }
    
    /**
     * Create event file for writing and pass back file handle
     */
    private function make_event_file() 
    {
        // File path format: WP uploads directory -> calendarize-it-event-export sub-folder -> event export specific file
        $this->filename = "event-" . $this->start_date."-".$this->end_date.".html";
        $event_file_path = wp_upload_dir()['basedir'] . "/calendarize-it-event-export/" . $this->filename;
        return fopen($event_file_path,"w");
    }

    /**
     * Create HTML for admin page form
     */
    public function create_admin_page_html() 
    {
        // Set today's date
        $today = new DateTime(date('Y-m-d'));

        ?>
        <div class="wrap">
            <h1>Calendarize it! Event Export</h1>
            <form method="post">
                <table class="optiontable form-table">
                    <tr valign="top">
                        <th><label for="start-date">Start Date</label></th>
                        <td>
                            <input name="start-date" type="date" id="start-date" value="<?php $today->modify('+1 day'); echo $today->format('Y-m-d') ?>" class="date">
                            <span class="description">Enter the first date of events that should be included.</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end-date">End Date</label></th>
                        <td>
                            <input name="end-date" type="date" id="end-date" value="<?php $today->modify('+1 month'); echo $today->format('Y-m-d') ?>" class="date">
                            <span class="description">Enter the last date of events that should be included.</span>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="export_events" id="export_events" class="button-primary" value="Preview" />
                    <input type="submit" name="export_events" id="export_events" class="button-primary" value="Download" />
                </p>
            </form>
        </div>
        <?php
        if( isset ( $_POST ) && isset($_POST['start-date']) && isset($_POST['end-date'])) 
        {
            new Calendarize_It_Export_Events($_POST['start-date'], $_POST['end-date']);
        }
    }

    /**
     * Hook add wp-admin plugin page
     */
    public function create_admin_page() 
    {
        // Add page under "Tools"
        add_management_page(
            'Calendarize it! Event Export',
            'Calendarize it! Event Export',
            'publish_events',
            'calendarize-it-event-export',
            array( 'Calendarize_It_Export_Events', 'create_admin_page_html' )
        );
    }

    /**
     * Get event list from start to end dates
     */
    public function get_event_list()
    {
        // WordPress Query arguments
        $args = array(
            'meta_type'      => 'DATETIME',
            'meta_key'       => 'fc_start',
            'posts_per_page' => -1,
            'post_status'=>'publish',
            'post_type' => array( 'events' ),
        );

        // Get events
        $eventlist = new WP_Query($args);

        // If events are found, prepare events, build HTML
        if( $eventlist->have_posts() ) 
        {
            $events = $this->prepare_events_post($eventlist);
            $this->build_event_html($events);
        }
        else {
            echo "<h3>No events found during this time period:<br>" . $this->start_date . " to " . $this->end_date . "</h3>";
            die;
        }
    }

    /**
     * Build HTML file of events
     */
    private function build_event_html($events) 
    {
        // Get event file handle
        $event_file = $this->make_event_file();

        // Get file download link
        $file_url = get_site_url()."/wp-content/uploads/calendarize-it-event-export/".$this->filename;

        // Save HTML header scripts to variable
        $file_html = '
<html>
    <head>
        <meta charset="utf-8"/>
        <link rel="stylesheet" id="main-stylesheet-css"  href="https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/stylesheets/theme.css?ver=1.0.17" type="text/css" media="all" />
    </head>
    <body>
        <section id="events">
            <div class="masonry">';

                // Format each event and add to HTML variable
                foreach($events as $event) 
                {
                    $file_html .= $this->display_event($event);
                }

        // Add HTML footer scripts to file
        $file_html .= '
            </div>
        </section>

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js?ver=2.1.0"></script>
        <script src="https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/javascript/common/masonry.min.js"></script>
        <script>
            var m = $(".masonry");
            m.masonry({itemSelector: ".masonryitem"});
        </script>
    </body>
</html>';

        // Write HTML to file and display preview
        fwrite($event_file, stripslashes($file_html));
        echo $file_html;

        // If Download was clicked echo JavaScript to download file
        if( $_POST['export_events'] == 'Download' ) 
        {
            ?>
            <script>
                $(document).ready(function() {
                    function downloadFile(uri) {
                        // Create <a> tag
                        var link = "<a id='download-event-file' href='"+uri+"' download='<?php echo $this->filename; ?>' target='_blank' style='display: none;'><?php echo $this->filename; ?></a>";
                        // Append <a> tag
                        $("body").append(link);
                    }
                    downloadFile("<?php echo $file_url; ?>");
                    console.log("Clicking link");
                    document.getElementById('download-event-file').click();
                })
            </script>
            <?php
        }
    }

    /**
     * Write event HTML
     */
    private function display_event($event) 
    { 
        // Set event attributes as variables for easy entry
        $permalink = $event['permalink'];
        $section_name = $event['section_name'];
        if( !empty($event['post_thumbnail']) ) 
        {
            $post_thumbnail = $event['post_thumbnail']; 
        } 
        else 
        {
            $post_thumbnail = get_template_directory_uri().'/assets/images/common/placeholder.png';
        }
        $month = date('M', strtotime($event['startdate']));
        $day = date('d', strtotime($event['startdate']));
        $title = $event['title'];
        if ($event['starttime'] === null && $event['endtime'] === null) 
        {
            if ($event['startdate']==$event['enddate']) 
            {
                $time = date('d M',strtotime($event['startdate']));
            } 
            else 
            {
                $time = date('d M',strtotime($event['startdate'])) . ' to ' . date('d M', strtotime($event['enddate']));
            }
        } 
        else 
        {
            if($event['startdate']==$event['enddate'])
            {
                $time = date('d M',strtotime($event['startdate'])) . ', '. date('g:ia',strtotime($event['starttime'])).' to '.date('g:ia',strtotime($event['endtime']));
            }
            else
            {
                $time = '<div>'.date('d M',strtotime($event['startdate'])).', '.date('g:ia',strtotime($event['starttime'])).' to</div><div>'.date('d M',strtotime($event['enddate'])).', '.date('g:ia',strtotime($event['endtime'])).'</div>';
            }
        }
        $excerpt = $event['excerpt'];
        
        // Generate and return event HTML
        $event_html = "
        <a href=\"$permalink\"  class=\"masonryitem\">
            <div class=\"item $section_name\">
                <div class=\"image-wrapper\">
                    <img src=\"$post_thumbnail\" />
                </div>
                <div class=\"date\">
                    <div class=\"month\">$month</div>
                    <div class=\"day\">$day</div>
                </div>
                <div class=\"title\">$title</div>
                <div class=\"time\">
                $time
                </div>
                <div class=\"excerpt\">
                    <p>
                        $excerpt
                    </p>
                </div>
            </div>
        </a>
        ";

        return $event_html;
    }

    /**
     * Prepare events array
     */
    function prepare_events_post($postlist) 
    {
        $result = array();

        while ( $postlist->have_posts() ) : $postlist->the_post();
            $temp = get_post_custom();
            $startdate = $temp['fc_start'][0];
            $enddate = $temp['fc_end'][0];
            $starttime = $temp['fc_start_time'][0];
            $endtime = $temp['fc_end_time'][0];
    
            if(has_term('career-development-events','calendar'))
            {
                $section_name="career-development";
            }
            elseif(has_term('global-learning-events','calendar'))
            {
                $section_name="global-learning";
            }
            elseif(has_term('student-development-events','calendar'))
            {
                $section_name="student-development";
            }
            elseif(has_term('student-care-events','calendar'))
            {
                $section_name="student-care";
            }
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
                                         'excerpt'=>preg_replace("~\[[0-9a-zA-Z_\\\/]+\]~","",get_the_excerpt())
                ));
            }
        endwhile;
    
        usort($result, function($a, $b) 
        {
            return strtotime($a['startdate']) - strtotime($b['startdate']);
        });

        $this->console_log(count($result));

        if(count($result) < 1) {
            echo "<h3>No events found during this time period:<br>" . $this->start_date . " to " . $this->end_date . "</h3>";
            die;
        }
    
        return $result;
    }

    /**
     * HTML Console log for testing
     */
    private function console_log($log) 
    {
        echo '<script>';
        echo 'console.log('. json_encode( $log ) .')';
        echo '</script>';
    }
}
?>