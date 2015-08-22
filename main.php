<?php
/*
Plugin Name: Simple YouTube Embed
Version: 1.0.3
Plugin URI: http://noorsplugin.com/2014/07/17/simple-youtube-embed-plugin/
Author: naa986
Author URI: http://noorsplugin.com/
Description: Embed YouTube video beautifully on your WordPress site
*/

if(!defined('ABSPATH')) exit;
if(!class_exists('SIMPLE_YOUTUBE_EMBED'))
{
    class SIMPLE_YOUTUBE_EMBED
    {
        var $plugin_version = '1.0.3';
        var $plugin_url;
        var $plugin_path;
        function __construct()
        {
            define('SIMPLE_YOUTUBE_EMBED_VERSION', $this->plugin_version);
            define('SIMPLE_YOUTUBE_EMBED_SITE_URL',site_url());
            define('SIMPLE_YOUTUBE_EMBED_URL', $this->plugin_url());
            define('SIMPLE_YOUTUBE_EMBED_PATH', $this->plugin_path());
            $this->plugin_includes();
            add_action( 'wp_enqueue_scripts', array( &$this, 'plugin_scripts' ), 0 );
        }
        function plugin_includes()
        {
            if(is_admin( ) )
            {
                //add_filter('plugin_action_links', array(&$this,'add_plugin_action_links'), 10, 2 );
            }
            //add_action('admin_menu', array( &$this, 'add_options_menu' ));
            add_action('wp_head', 'simple_youtube_video_embed_js');
            add_filter('embed_oembed_html', 'simple_youtube_video_embed', 10, 3);
        }
        function plugin_scripts()
        {
            if (!is_admin()) 
            {
                wp_enqueue_script('jquery');
                wp_register_script('waitforimages', SIMPLE_YOUTUBE_EMBED_URL.'/jquery.waitforimages.min.js', array('jquery'), SIMPLE_YOUTUBE_EMBED_VERSION);
                wp_enqueue_script('waitforimages');
                wp_register_script('prettyembed', SIMPLE_YOUTUBE_EMBED_URL.'/jquery.prettyembed.min.js', array('jquery'), SIMPLE_YOUTUBE_EMBED_VERSION);
                wp_enqueue_script('prettyembed');
                wp_register_script('fitvids', SIMPLE_YOUTUBE_EMBED_URL.'/jquery.fitvids.js', array('jquery'), SIMPLE_YOUTUBE_EMBED_VERSION);
                wp_enqueue_script('fitvids');
            }
        }
        function plugin_url()
        {
            if($this->plugin_url) return $this->plugin_url;
            return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
        }
        function plugin_path(){ 	
            if ( $this->plugin_path ) return $this->plugin_path;		
            return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
        }
        function add_plugin_action_links($links, $file)
        {
            if ( $file == plugin_basename( dirname( __FILE__ ) . '/main.php' ) )
            {
                $links[] = '<a href="options-general.php?page=simple-youtube-embed-settings">Settings</a>';
            }
            return $links;
        }

        function add_options_menu()
        {
            if(is_admin())
            {
                add_options_page('Simple YouTube Embed Settings', 'Simple YouTube Embed', 'manage_options', 'simple-youtube-embed-settings', array(&$this, 'display_options_page'));
            }
        }
    }
    $GLOBALS['simple_youtube_embed'] = new SIMPLE_YOUTUBE_EMBED();
}

function simple_youtube_video_embed($html, $url, $attr) 
{
    $yt_pattern = "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/";
    if(preg_match($yt_pattern,$url,$matches))
    {
        $youtube_id = $matches[1];
        $image_url = 'http://img.youtube.com/vi/'.$youtube_id.'/maxresdefault.jpg';
        $response = wp_remote_request($image_url);
        $response_code = wp_remote_retrieve_response_code($response);
        $videoid = ' data-pe-videoid="'.$youtube_id.'"'; 
        $fitvids = ' data-pe-fitvids="true"';
        $previewsize = '';
        if($response_code=="404"){
            $previewsize = ' data-pe-preview-size="high"';
        }
        $showrelated = ''; 
        $showcontrols = '';
        $parsed_url = parse_url($url); //parse the url to get query parameters
        if(isset($parsed_url['query']) && !empty($parsed_url['query'])){
            parse_str($parsed_url['query'], $data); //get query parameters into an array
            if(isset($data['rel']) && $data['rel']=="0"){
                $showrelated = ' data-pe-show-related="false"';
            }
            if(isset($data['controls']) && $data['controls']=="0"){
                $showcontrols = ' data-pe-show-controls="false"';
            }
        }
        $embed_code = <<<EOT
        <div class="pretty-embed"{$videoid}{$fitvids}{$previewsize}{$showrelated}{$showcontrols}></div>  
EOT;
        $html = $embed_code;
    }
    return $html; 
}

function simple_youtube_video_embed_js()
{
    $output = <<<EOT
    <script type="text/javascript" charset="utf-8">
        /* <![CDATA[ */
        jQuery(document).ready(function($){
            $(function(){
                $().prettyEmbed({ useFitVids: true });
            });
        });
        /* ]]> */
        </script>
EOT;
    echo $output;
}
