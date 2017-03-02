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


new cd2_content_sectionOEmbedClass();
class cd2_content_sectionOEmbedClass {

    public function __construct() {
        add_action( 'init', [ $this, 'init' ], 80 );
        add_action( 'wp_loaded', [ $this, 'register_embed_handler'], 9999 );
    }

    public function init() {
        define(
            'CD2_CONTENT_SHORTCODE_OEMBED_PATTERN',
            '#('.admin_url( 'admin-ajax.php' ).'\?action=get_content_section&name=([a-zA-Z0-9-_]+).+)$#i'
        );
        
        load_plugin_textdomain(
	        'cd2_content_section_oembed',
	        false,
	        dirname( plugin_basename( __FILE__ ) ).'/languages'
        );
        
        add_filter(
            'oembed_providers',
            [ $this, 'embed_providers' ]
        );
        
        add_action( 'save_post', [ $this, 'save_post_callback' ] );
    }
    
    public function register_embed_handler() {
        wp_embed_register_handler(
            'cd2-content-section-internal',
            CD2_CONTENT_SHORTCODE_OEMBED_PATTERN,
            [ $this, 'handle_embed' ]
        );
    }
    
    public function handle_embed( $m, $attr, $url, $rattr ) {
        $urlHash = $this->getHash($url);
        $content = get_transient( "cd2_content_section_{$urlHash}" );
        if ( false === ( $content ) ) {
            return $this->embedWrap($url, $urlHash, "<!-- Try re-saving the content section or using it's title ;) {$url} -->");
        }
        return $this->embedWrap($url, $urlHash, $content);
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

