<?php
/**
 * Plugin Name:    Calenderize It Event Export
 * Description:    Export Calenderize It events into HTML file.
 * Version:        0.5.1
 * Author:         MF Softworks
 * Author URI:     https://mf.nygmarosebeauty.com/
 * License:        GPLv3
 * Copyright:      MF Softworks
 */

/**
 * Define plugin version
 */ 
define('CALENDERIZE_IT_EVENT_EXPORT_VERSION', '0.5.1');

/**
 * Create plugin wp-admin page
 */
add_action( 'admin_menu', array( 'Calenderize_It_Export_Events', 'create_admin_page' ) );
register_activation_hook( __FILE__, array( 'Calenderize_It_Export_Events', 'make_download_dir' ) );

class Calenderize_It_Export_Events
{
    /**
     * Declare global date options
     */
    private $start_date;
    private $end_date;
    private $plugin_file_dir;

    /**
     * Construct the export event class
     */
    public function __construct($start_date, $end_date) {
        // Log object creation and data
        $this->console_log("Creating export event object");
        //$this->console_log($start_date);
        //$this->console_log($end_date);
        // Set global date options
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        // Get events
        $this->get_event_list();
    }

    /**
     * Make file download dir
     */
    public function make_download_dir() {
        $dir = wp_upload_dir()['basedir'] . '/calenderize-it-event-export';
        wp_mkdir_p($dir);
        $this->plugin_file_dir = $dir;
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
        //$this->console_log($args);
        // Get events
        $eventlist = new WP_Query($args);
        // Log WP Query result
        //$this->console_log($eventlist);

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
        ?>
        <link rel='stylesheet' id='cspm_font-css'  href='//fonts.googleapis.com/css?family=Source+Sans+Pro%3A400%2C200%2C200italic%2C300%2C300italic%2C400italic%2C600%2C600italic%2C700%2C700italic&#038;subset=latin%2Cvietnamese%2Clatin-ext&#038;ver=4.9.7' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_icheck_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/icheck/polaris/polaris.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_bootstrap_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/bootstrap.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_carousel_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/jcarousel.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_loading_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/loading.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_mCustomScrollbar_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/jquery.mCustomScrollbar.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_rangeSlider_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/ion.rangeSlider.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_rangeSlider_skin_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/ion.rangeSlider.skinFlat.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_nprogress_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/nprogress.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_animate_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/animate.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspm_map_css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/css/min/style.min.css?ver=2.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_selectize-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/selectize.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_selectize_skin-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/selectize.bootstrap3.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_ion_check_radio-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/ion.checkRadio.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_ion_check_radio_skin-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/ion.checkRadio.html5.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_spinner-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/bootstrap-spinner.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_hover-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/hover.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='cspml_styles-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/css/min/style.min.css?ver=1.0' type='text/css' media='all' />

<link rel='stylesheet' id='rs-plugin-settings-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/revslider/public/assets/css/settings.css?ver=5.4.1' type='text/css' media='all' />

<style id='rs-plugin-settings-inline-css' type='text/css'>

#rs-demo-id {}

</style>

<link rel='stylesheet' id='custom-jquery-ui-css'  href='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/stylesheets/jquery-ui.min.css?ver=1.0.0' type='text/css' media='all' />

<link rel='stylesheet' id='custom-jquery-ui-theme-css'  href='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/stylesheets/jquery-ui.theme.min.css?ver=1.0.0' type='text/css' media='all' />

<link rel='stylesheet' id='selectize-css-css'  href='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/stylesheets/selectize.default.css?ver=1.0.0' type='text/css' media='all' />

<link rel='stylesheet' id='main-stylesheet-css'  href='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/stylesheets/theme.css?ver=1.0.17' type='text/css' media='all' />

<link rel='stylesheet' id='dashicons-css'  href='https://project1095.simge.edu.sg/wp-includes/css/dashicons.min.css?ver=4.9.7' type='text/css' media='all' />

<link rel='stylesheet' id='zoom-instagram-widget-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/instagram-widget-by-wpzoom/css/instagram-widget.css?ver=1.2.10' type='text/css' media='all' />

<link rel='stylesheet' id='js_composer_front-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/js_composer/assets/css/js_composer.min.css?ver=5.0.1' type='text/css' media='all' />

<link rel='stylesheet' id='js_composer_custom_css-css'  href='//project1095.simge.edu.sg/wp-content/uploads/js_composer/custom.css?ver=5.0.1' type='text/css' media='all' />

<link rel='stylesheet' id='rhc-print-css-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/css/print.css?ver=1.0.0' type='text/css' media='all' />

<link rel='stylesheet' id='calendarizeit-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/css/frontend.min.css?ver=4.0.8.4' type='text/css' media='all' />

<link rel='stylesheet' id='rhc-last-minue-css'  href='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/css/last_minute_fixes.css?ver=1.0.10' type='text/css' media='all' />

<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js?ver=2.1.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/revslider/public/assets/js/jquery.themepunch.tools.min.js?ver=5.4.1'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/revslider/public/assets/js/jquery.themepunch.revolution.min.js?ver=5.4.1'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/instagram-widget-by-wpzoom/js/instagram-widget.js?ver=1.2.10'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/js/bootstrap.min.js?ver=3.0.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/js/bootstrap-select.js?ver=1.0.2'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/core.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/widget.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/accordion.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/mouse.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/slider.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/resizable.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/draggable.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/button.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/position.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/dialog.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/tabs.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/sortable.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/droppable.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/datepicker.min.js?ver=1.11.4'></script>

<script type='text/javascript'>

jQuery(document).ready(function(jQuery){jQuery.datepicker.setDefaults({"closeText":"Close","currentText":"Today","monthNames":["January","February","March","April","May","June","July","August","September","October","November","December"],"monthNamesShort":["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],"nextText":"Next","prevText":"Previous","dayNames":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],"dayNamesShort":["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],"dayNamesMin":["S","M","T","W","T","F","S"],"dateFormat":"MM d, yy","firstDay":1,"isRTL":false});});

</script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/menu.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/wp-a11y.min.js?ver=4.9.7'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/jquery/ui/autocomplete.min.js?ver=1.11.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/js/deprecated.js?ver=bundled-jquery-ui'></script>

<script type='text/javascript'>

/* <![CDATA[ */

var RHC = {"ajaxurl":"https:\/\/project1095.simge.edu.sg\/","mobile_width":"480","last_modified":"43ed41870e32fabaaf915fe1c44b7b7f","tooltip_details":[],"visibility_check":"1","gmt_offset":"8","disable_event_link":"0"};

/* ]]> */

</script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/js/frontend.min.js?ver=4.3.4.6'></script>

<script type='text/javascript' src='https://maps.google.com/maps/api/js?libraries=places&#038;key=AIzaSyCRGO4ipIZyiHggm4boPgqG1RAOamBkjQM&#038;ver=3.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/calendarize-it/js/rhc_gmap3.js?ver=1.0.1'></script>

<!--[if lte IE 9]><link rel="stylesheet" type="text/css" href="https://project1095.simge.edu.sg/wp-content/plugins/js_composer/assets/css/vc_lte_ie9.min.css" media="screen"><![endif]-->
<script type="text/javascript">var _CSPM_DONE = {}; var _CSPM_MAP_RESIZED = {};</script>
<style type="text/css">.details_container{width:250px;height:150px;}.item_img{width:200px; height:149px;float:left;}.details_btn{left:170px;top:100px;}.details_title{width:250px;}.details_infos{width:250px;font-size:0.8em;
line-height:1.1em;}.jcarousel-skin-default .jcarousel-container-vertical{height:700px !important;}.jcarousel-skin-default .jcarousel-prev-horizontal,.jcarousel-skin-default .jcarousel-next-horizontal,.jcarousel-skin-default .jcarousel-direction-rtl .jcarousel-next-horizontal,.jcarousel-skin-default .jcarousel-next-horizontal:hover,.jcarousel-skin-default .jcarousel-next-horizontal:focus,.jcarousel-skin-default .jcarousel-direction-rtl .jcarousel-prev-horizontal,.jcarousel-skin-default .jcarousel-prev-horizontal:hover,.jcarousel-skin-default .jcarousel-prev-horizontal:focus,.jcarousel-skin-default .jcarousel-direction-rtl .jcarousel-next-vertical,.jcarousel-skin-default .jcarousel-next-vertical:hover,.jcarousel-skin-default .jcarousel-next-vertical:focus,.jcarousel-skin-default .jcarousel-direction-rtl .jcarousel-prev-vertical,.jcarousel-skin-default .jcarousel-prev-vertical:hover,.jcarousel-skin-default .jcarousel-prev-vertical:focus{background-color:#fff;}div[class^=codespacing_map_zoom_in], div[class^=codespacing_light_map_zoom_in]{}div[class^=codespacing_map_zoom_out], div[class^=codespacing_light_map_zoom_out]{}div[class^=faceted_search_container]{background:#ffffff}div[class^=search_form_container_]{background:#ffffff;}div.cspm_arrow_down { display:none; }</style>
<style type="text/css" data-type="vc_shortcodes-custom-css">.vc_custom_1475831462166{background-color: #ececec !important;}.vc_custom_1477648010480{background-color: #ebebeb !important;}.vc_custom_1475831462166{background-color: #ececec !important;}.vc_custom_1477855765664{background-color: #ebebeb !important;}.vc_custom_1475831462166{background-color: #ececec !important;}.vc_custom_1477860629451{background-image: url(https://project1095.simge.edu.sg/wp-content/uploads/2016/10/green.png?id=1133) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}</style>
<noscript><style type="text/css"> .wpb_animate_when_almost_visible { opacity: 1; }</style></noscript>		
<script src="https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/javascript/common/modernizr.min.js"></script>
<script src="https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/javascript/common/masonry.min.js"></script>
		
        
        <section id="events">
            <div class="masonry">
        <?php
        foreach($events as $event) {
            $this->display_event($event);
        }
        ?>
            </div>
        </section>
        <script>
            jQuery(window).on('load', function () {
                var m = $(".masonry");
                m.masonry({itemSelector: ".masonryitem"});
            });
        </script>
        		<link rel='stylesheet' id='vc_google_fonts_roboto100100italic300300italicregularitalic500500italic700700italic900900italic-css'  href='//fonts.googleapis.com/css?family=Roboto%3A100%2C100italic%2C300%2C300italic%2Cregular%2Citalic%2C500%2C500italic%2C700%2C700italic%2C900%2C900italic&#038;subset=latin&#038;ver=4.9.7' type='text/css' media='all' />

<script type='text/javascript' src='//maps.google.com/maps/api/js?v=3.exp&#038;key=AIzaSyDh_GglyLEDeaaV_utESbq_Wzt86Lc5zzQ&#038;language=en&#038;libraries=geometry%2Cplaces&#038;ver=4.9.7'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/gmap3.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/jquery.livequery.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/jquery.jcarousel.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/jquery.easing.1.3.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/jquery.mCustomScrollbar.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/jquery.mousewheel.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/jquery.icheck.min.js?v=0.9.1&#038;ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/nprogress.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/ion.rangeSlider.min.js?ver=2.8.4'></script>

<script type='text/javascript'>

/* <![CDATA[ */

var progress_map_vars = {"ajax_url":"https:\/\/project1095.simge.edu.sg\/wp-admin\/admin-ajax.php","plugin_url":"https:\/\/project1095.simge.edu.sg\/wp-content\/plugins\/codespacing-progress-map\/","number_of_items":"","center":"41.0463196,55.5772046","zoom":"3","scrollwheel":"false","panControl":"true","mapTypeControl":"false","streetViewControl":"false","zoomControl":"true","zoomControlType":"customize","defaultMarker":"default","marker_icon":"https:\/\/s3-ap-southeast-1.amazonaws.com\/sim-1095-cdn\/wp-content\/uploads\/2017\/01\/map-marker-icon4.png","big_cluster_icon":"https:\/\/project1095.simge.edu.sg\/wp-content\/plugins\/codespacing-progress-map\/img\/big-cluster.png","big_cluster_size":"106x106","medium_cluster_icon":"https:\/\/project1095.simge.edu.sg\/wp-content\/plugins\/codespacing-progress-map\/img\/medium-cluster.png","medium_cluster_size":"75x75","small_cluster_icon":"https:\/\/project1095.simge.edu.sg\/wp-content\/plugins\/codespacing-progress-map\/img\/small-cluster.png","small_cluster_size":"57x57","cluster_text_color":"#ffffff","grid_size":"100","retinaSupport":"false","initial_map_style":"custom_style","markerAnimation":"flushing_infobox","marker_anchor_point_option":"manual","marker_anchor_point":"15,30","map_draggable":"true","min_zoom":"3","max_zoom":"19","zoom_on_doubleclick":"true","items_view":"listview","show_carousel":"true","carousel_scroll":"3","carousel_wrap":"circular","carousel_auto":"0","carousel_mode":"false","carousel_animation":"fast","carousel_easing":"linear","carousel_map_zoom":"12","scrollwheel_carousel":"false","touchswipe_carousel":"false","layout_fixed_height":"700","horizontal_item_css":"","horizontal_item_width":"450","horizontal_item_height":"150","vertical_item_css":"","vertical_item_width":"204","vertical_item_height":"290","items_background":"#fff","items_hover_background":"#fbfbfb","faceted_search_option":"true","faceted_search_multi_taxonomy_option":"true","faceted_search_input_skin":"polaris","faceted_search_input_color":"blue","faceted_search_drag_map":"no","show_posts_count":"no","fillColor":"#189AC9","fillOpacity":"0.1","strokeColor":"#189AC9","strokeOpacity":"1","strokeWeight":"1","search_form_option":"true","before_search_address":"","after_search_address":"","geo":"false","show_user":"true","user_marker_icon":"","user_map_zoom":"7","user_circle":"0","geoErrorTitle":"Give Maps permission to use your location!","geoErrorMsg":"If you can't center the map on your location, a couple of things might be going on. It's possible you denied Google Maps access to your location in the past, or your browser might have an error.","geoDeprecateMsg":"IMPORTANT NOTE: Browsers no longer supports obtaining the user's location using the HTML5 Geolocation API from pages delivered by non-secure connections. This means that the page that's making the Geolocation API call must be served from a secure context such as HTTPS.","cluster_text":"Click to view all markers in this area","count_marker_categories":"0"};

/* ]]> */

</script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/codespacing-progress-map/js/min/progress_map.min.js?ver=2.8.4'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/js/min/selectize.min.js?ver=1.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/js/min/ion.checkRadio.min.js?ver=1.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/js/min/jquery.spinner.min.js?ver=1.0'></script>

<script type='text/javascript'>

/* <![CDATA[ */

var cspml_vars = {"ajax_url":"https:\/\/project1095.simge.edu.sg\/wp-admin\/admin-ajax.php","plugin_url":"https:\/\/project1095.simge.edu.sg\/wp-content\/plugins\/progress-map-list-and-filter\/","show_view_options":"no","grid_cols":"cols3"};

/* ]]> */

</script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/progress-map-list-and-filter/js/min/progress-map-list.min.js?ver=1.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/javascript/common/selectize.min.js?ver=1.0.0'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/javascript/theme.js?ver=2.6.1'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/themes/project1095/assets/javascript/custom/wp_datatable_filter.js?ver=1.0.2'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-includes/js/wp-embed.min.js?ver=4.9.7'></script>

<script type='text/javascript' src='https://project1095.simge.edu.sg/wp-content/plugins/js_composer/assets/js/dist/js_composer_front.min.js?ver=5.0.1'></script>

        <?php
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
                                         'excerpt'=>preg_replace("~\[[0-9a-zA-Z_\\\/]+\]~","",get_the_excerpt())
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