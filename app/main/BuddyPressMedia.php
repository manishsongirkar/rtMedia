<?php

/**
 * Description of BPMedia
 *
 * @author Saurabh Shukla <saurabh.shukla@rtcamp.com>
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!defined('ABSPATH'))
    exit;

class BuddyPressMedia {

    public $text_domain = 'bp-media';
    public $options;
    public $support_email = 'support@rtcamp.com';
    public $query;
    public $albums_query;
    public $counter = 0;
    public $count = null;
    public $posts_per_page = 10;
    public $activity_types = array(
        'media_upload',
        'album_updated',
        'album_created'
    );
    public $hidden_activity_cache = array();

    public function __construct() {
        $this->get_option();
        $this->constants();

        add_action('bp_include', array($this, 'init'));

        if (file_exists(BP_MEDIA_PATH . '/languages/' . get_locale() . '.mo'))
            load_textdomain('bp-media', BP_MEDIA_PATH . '/languages/' . get_locale() . '.mo');

        global $bp_admin;
        $bp_admin = new BPMAdmin();
    }

    public function get_option() {
        $this->options = bp_get_option('bp_media_options');
    }

    public function constants() {

        /* If the plugin is installed. */
        if (!defined('BP_MEDIA_IS_INSTALLED'))
            define('BP_MEDIA_IS_INSTALLED', 1);

        /* Current Version. */
        if (!defined('BP_MEDIA_VERSION'))
            define('BP_MEDIA_VERSION', '2.3.2');

        /* Required Version  */
        if (!defined('BP_MEDIA_REQUIRED_BP'))
            define('BP_MEDIA_REQUIRED_BP', '1.6.2');

        /* Database Version */
        if (!defined('BP_MEDIA_DB_VERSION'))
            define('BP_MEDIA_DB_VERSION', '2.1');

        /**
          /* A constant to Active Collab API Assignee ID
          if ( ! defined( 'BP_MEDIA_AC_API_ASSIGNEE_ID' ) )
          define( 'BP_MEDIA_AC_API_ASSIGNEE_ID', '5' );

          /* A constant to Active Collab API Assignee ID
          if ( ! defined( 'BP_MEDIA_AC_API_LABEL_ID' ) )
          define( 'BP_MEDIA_AC_API_LABEL_ID', '1' );

          /* A constant to Active Collab API priority
          if ( ! defined( 'BP_MEDIA_AC_API_PRIORITY' ) )
          define( 'BP_MEDIA_AC_API_PRIORITY', '2' );

          /* A constant to Active Collab API priority
          if ( ! defined( 'BP_MEDIA_AC_API_CATEGORY_ID' ) )
          define( 'BP_MEDIA_AC_API_CATEGORY_ID', '224' );
         */
        /* Slug Constants */
        if (!defined('BP_MEDIA_SLUG'))
            define('BP_MEDIA_SLUG', 'media');

        if (!defined('BP_MEDIA_UPLOAD_SLUG'))
            define('BP_MEDIA_UPLOAD_SLUG', 'upload');

        if (!defined('BP_MEDIA_DELETE_SLUG'))
            define('BP_MEDIA_DELETE_SLUG', 'delete');

        if (!defined('BP_MEDIA_IMAGES_SLUG'))
            define('BP_MEDIA_IMAGES_SLUG', 'photos');

        if (!defined('BP_MEDIA_IMAGES_ENTRY_SLUG'))
            define('BP_MEDIA_IMAGES_ENTRY_SLUG', 'view');

        if (!defined('BP_MEDIA_IMAGES_EDIT_SLUG'))
            define('BP_MEDIA_IMAGES_EDIT_SLUG', 'edit');

        if (!defined('BP_MEDIA_VIDEOS_SLUG'))
            define('BP_MEDIA_VIDEOS_SLUG', 'videos');

        if (!defined('BP_MEDIA_VIDEOS_ENTRY_SLUG'))
            define('BP_MEDIA_VIDEOS_ENTRY_SLUG', 'watch');

        if (!defined('BP_MEDIA_VIDEOS_EDIT_SLUG'))
            define('BP_MEDIA_VIDEOS_EDIT_SLUG', 'edit');

        if (!defined('BP_MEDIA_AUDIO_SLUG'))
            define('BP_MEDIA_AUDIO_SLUG', 'music');

        if (!defined('BP_MEDIA_AUDIO_ENTRY_SLUG'))
            define('BP_MEDIA_AUDIO_ENTRY_SLUG', 'listen');

        if (!defined('BP_MEDIA_AUDIO_EDIT_SLUG'))
            define('BP_MEDIA_AUDIO_EDIT_SLUG', 'edit');

        if (!defined('BP_MEDIA_ALBUMS_SLUG'))
            define('BP_MEDIA_ALBUMS_SLUG', 'albums');

        if (!defined('BP_MEDIA_ALBUMS_ENTRY_SLUG'))
            define('BP_MEDIA_ALBUMS_ENTRY_SLUG', 'list');

        if (!defined('BP_MEDIA_ALBUMS_EDIT_SLUG'))
            define('BP_MEDIA_ALBUMS_EDIT_SLUG', 'edit');

        /* Labels loaded via text domain, can be translated */
        if (!defined('BP_MEDIA_LABEL'))
            define('BP_MEDIA_LABEL', __('Media', $this->text_domain));

        if (!defined('BP_MEDIA_LABEL_SINGULAR'))
            define('BP_MEDIA_LABEL_SINGULAR', __('Media', $this->text_domain));

        if (!defined('BP_MEDIA_IMAGES_LABEL'))
            define('BP_MEDIA_IMAGES_LABEL', __('Photos', $this->text_domain));

        if (!defined('BP_MEDIA_IMAGES_LABEL_SINGULAR'))
            define('BP_MEDIA_IMAGES_LABEL_SINGULAR', __('Photo', $this->text_domain));

        if (!defined('BP_MEDIA_VIDEOS_LABEL'))
            define('BP_MEDIA_VIDEOS_LABEL', __('Videos', $this->text_domain));

        if (!defined('BP_MEDIA_VIDEOS_LABEL_SINGULAR'))
            define('BP_MEDIA_VIDEOS_LABEL_SINGULAR', __('Video', $this->text_domain));

        if (!defined('BP_MEDIA_AUDIO_LABEL'))
            define('BP_MEDIA_AUDIO_LABEL', __('Music', $this->text_domain));

        if (!defined('BP_MEDIA_AUDIO_LABEL_SINGULAR'))
            define('BP_MEDIA_AUDIO_LABEL_SINGULAR', __('Music', $this->text_domain));

        if (!defined('BP_MEDIA_ALBUMS_LABEL'))
            define('BP_MEDIA_ALBUMS_LABEL', __('Albums', $this->text_domain));

        if (!defined('BP_MEDIA_ALBUMS_LABEL_SINGULAR'))
            define('BP_MEDIA_ALBUMS_LABEL_SINGULAR', __('Album', $this->text_domain));

        if (!defined('BP_MEDIAUPLOAD_LABEL'))
            define('BP_MEDIA_UPLOAD_LABEL', __('Upload', $this->text_domain));

        if (!defined('BP_MEDIA_TMP_DIR'))
            define('BP_MEDIA_TMP_DIR', WP_CONTENT_DIR . '/bp-media-temp');

        if (!defined('BP_MEDIA_SUPPORT_EMAIL'))
            define('BP_MEDIA_SUPPORT_EMAIL', $this->support_email);
    }

    function init() {
        if (defined('BP_VERSION') && version_compare(BP_VERSION, BP_MEDIA_REQUIRED_BP, '>')) {
            add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);
            require( BP_MEDIA_PATH . 'includes/bp-media-loader.php' );
//require( BP_MEDIA_PLUGIN_DIR . '/includes/bp-media-groups-loader.php');
        }
    }

    function settings_link($links, $file) {
        /* create link */
        $plugin_name = plugin_basename(__FILE__);
        $admin_link = bp_media_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php'));
        if ($file == $plugin_name) {
            array_unshift(
                    $links, sprintf('<a href="%s">%s</a>', $admin_link, __('Settings'))
            );
        }
        return $links;
    }

    function media_sizes() {
        $def_sizes = array(
            'activity_image' => array(
                'width' => 320,
                'height' => 240
            ),
            'activity_video' => array(
                'width' => 320,
                'height' => 240
            ),
            'activity_audio' => array(
                'width' => 320,
            ),
            'single_image' => array(
                'width' => 800,
                'height' => 0
            ),
            'single_video' => array(
                'width' => 640,
                'height' => 480
            ),
            'single_audio' => array(
                'width' => 640,
            ),
        );

        return apply_filters('bpm_media_sizes', $def_sizes);
    }

    function excerpt_lengths() {
        $def_excerpt = array(
            'single_entry_title' => 100,
            'single_entry_description' => 500,
            'activity_entry_title' => 50,
            'activity_entry_description' => 500
        );

        return apply_filters('bpm_excerpt_lengths', $def_excerpt);
    }

    private function activate() {
        
    }

    private function deactivate() {
        
    }

    public function autoload_js_css() {
        
    }

}

?>
