<?php
/*
Plugin Name: CODESIGN2 Content Section Plugin Same-site oEmbed
Plugin URI: http://www.codesign2.co.uk
Description: Ensure Content Sections Are Embeddable
Author: CODESIGN2
Version: 0.0.1
Author URI: http://www.codesign2.co.uk/
License: AGPL
*/

register_activation_hook( __FILE__, function() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cd2_content_section%'");
});

new cd2_content_sectionOEmbedClass();
class cd2_content_sectionOEmbedClass {

    public function __construct() {
        add_action( 'init', [ $this, 'init' ], 80 );
    }

    public function init() {
        define(
            'CD2_CONTENT_SHORTCODE_OEMBED_PATTERN',
            '#('.admin_url( 'admin-ajax.php' ).'\?action=get_content_section&name=([a-zA-Z0-9-_]+).+)$#i'
        );
        
        
        add_action( 'wp_ajax_nopriv_get_content_section',
            [ $this, 'ajax_content_section' ]
        );
        
        load_plugin_textdomain(
	        'cd2_content_section_oembed',
	        false,
	        dirname( plugin_basename( __FILE__ ) ).'/languages'
        );
        
        wp_embed_register_handler(
            'cd2-content-section-internal',
            CD2_CONTENT_SHORTCODE_OEMBED_PATTERN,
            [ $this, 'handle_embed' ]
        );
        
        add_filter(
            'oembed_providers',
            [ $this, 'embed_providers' ]
        );
        
        add_action( 'save_post', [ $this, 'save_post_callback' ] );
    }
    
    public function ajax_content_section() {
        $name = isset( $_GET['name'] ) ? urldecode( $_GET['name'] ) : 'invalid';
        die(
            do_shortcode(
                '[content_section name="' . esc_attr( $name ) . '"]'
            )
        );
    }
    
    public function handle_embed( $m, $attr, $url, $rattr ) {
        $urlHash = $this->getHash($url);
        $content = get_transient( "cd2_content_section_{$urlHash}" );
        if ( false === ( $content ) || empty(trim($content)) ) {
            $request = wp_remote_get($url);
            $response = wp_remote_retrieve_body( $request );
            $content = trim($response);
            set_transient( "cd2_content_section_{$urlHash}", $content, 0 );
        }
        if(strlen("{$content}") > 5) {
            return $this->embedWrap($url, $urlHash, $content);
        }
        return $this->embedWrap($url, $urlHash, "<!-- Try re-saving the content section or using it's title ;) {$url} -->");
    }
    
    protected function embedWrap($url, $urlHash, $html) {
        return "<div class=\"oembed-content-section\" id=\"{$urlHash}\" data-url=\"{$url}\">".$html."</div>";
    }
    
    public function save_post_callback($post_id){
        global $post; 
        if ($post->post_type != 'content_section') {
            return;
        }
        $title = get_the_title($post_id);
        $slug = sanitize_title($title);
        
        $url = trim(admin_url( 'admin-ajax.php' ).'?action=get_content_section&name='.$slug);
        $urlHash = $this->getHash($url);
        
        $content = do_shortcode(
            '[content_section name="' . esc_attr( $slug ) . '"]'
        );
        
        set_transient( "cd2_content_section_{$urlHash}", $content, 0 );        
    }
    
    protected function getHash($url) {
        return md5($url);
    }
    
    public function embed_providers( $providers ) {
        //Support to Press This.
        global $pagenow;
        if ( 'press-this.php' == $pagenow && ! array_key_exists( CD2_CONTENT_SHORTCODE_OEMBED_PATTERN, $providers ) ) {
            $providers[ CD2_CONTENT_SHORTCODE_OEMBED_PATTERN ] = [
                admin_url( 'admin-ajax.php' ).'\?action=get_content_section&name={slug}{params}',
                true
            ];
        }
        return $providers;
    }
}

