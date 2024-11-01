<?php
include_once('videoconverter-LifeCycle.php');

class videoconverter_Plugin extends videoconverter_LifeCycle {
     //return array of option metadata.
   public function getOptionMetaData() {
          return array(
        //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
        'LinkText' =>array(__('Enter the text that the link should display, default is \'Download Now\'', 'videoconverter')),
        'LinkPosition' =>array(__('Select the position where the link should be displayed under the video', 'videoconverter'), __('Left', 'videoconverter'), __('Right', 'videoconverter')),
        'LinkCssClass' =>array(__('Enter the css class to decorate the a-href html tag', 'videoconverter')),
        'ContainerCssClass' =>array(__('Enter the css class to decorate the div containing a-href html tag', 'videoconverter')),
        'Categories' =>array(__('Enter the blog-post categorie(s) separated by a \',\' on which the plugin will run', 'videoconverter')), 
        'RedirectLink' =>array(__('Enter the URL where the download link will be redirected. with \'http://\' and without \'/\' at the end. Default: \'http://speeddownloader.com\'', 'videoconverter')),
        'UrlParameters' =>array(__('You may need to change the options at the end of the url', 'videoconverter'), '/?', '/?url=', '/#url=')
          );
       }
       // protected function getOptionValueI18nString($optionValue) {
       // $i18nValue = parent::getOptionValueI18nString($optionValue);
       // return $i18nValue;
       // }
       
   protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
             foreach ($options as $key => $arr) {
                  if (is_array($arr) && count($arr > 1)) {
                       $this->addOption($key, $arr[1]);
                  }
             }
        }
   }
   
   public function getPluginDisplayName() {
        return'Video Converter';
   }
   
   protected function getMainPluginFileName() {
        return'videoconverter.php';
   }
   
   /**
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
     // global $wpdb;
     // $tableName = $this->prefixTableName('videoconverter');
     // $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` ( `id` INTEGER NOT NULL");
    }
    
   /**
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
     // global $wpdb;
     // $tableName = $this->prefixTableName('videoconverter');
     // $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }
    
    /**
     * Perform actions when upgrading version
     * @return void
     */
    public function upgrade() {            
   }
   
    public function addActionsAndFilters() {
        //Add options administration page 
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage')); 
        add_filter( 'the_content', array(&$this, 'add_videoconverter_buttons'), 99999999);
        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        wp_enqueue_style('videoconverter_admin-style', plugins_url('/css/videoconverter_admin-style.css', __FILE__));
         }
        wp_enqueue_style('videoconverter-style', plugins_url('/css/videoconverter-style.css', __FILE__));
        // Example adding a script & style just for the options administration page
        // if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        // wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        // wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        // }
        // Adding scripts & styles to all pages
        // Examples:
        // wp_enqueue_script('jquery');
        // wp_enqueue_style('my-style', plugins_url('/css/catconvert-style.css', __FILE__));
        // wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__)); 
        // Add Actions & Filters
        //add_filter( 'embed_oembed_html', array(&$this, 'add_catconvert_buttons'), 99999 , 4 );            
        // Register short codes
        // Register AJAX hooks       
   }        

   function add_videoconverter_buttons( $html ) {
        //check if html is empty
        if(empty($html)){
             return $html;
        }
        
     $categories_enabled = $this->getOption('Categories');
     
        if(isset($categories_enabled) && $categories_enabled != ''){
            // check if category is supported
            $categories_enabled = explode(",", $categories_enabled);
            $categories = get_the_category();
            $separator = ' ';
            $output = '';
            $is_enabled = false;
            if($categories){
                foreach($categories as $category) {
                    if(in_array($category->name, $categories_enabled)){
                        $is_enabled = true;
                    }
                }

                if(!$is_enabled){
                    return $html;
                }
            }
        }
 
        $linkCssClass = $this->getOption('LinkCssClass');
        $linkPosition = $this->getOption('LinkPosition');
        $linkText = $this->getOption('LinkText');
        $containerCssClass = $this->getOption('ContainerCssClass');
        $redirectLink = $this->getOption('RedirectLink');
        $urlParameters = $this->getOption('UrlParameters');
        
        $redirectLink = isset($redirectLink) && $redirectLink != '' ? $redirectLink : 'http://speeddownloader.com';
        $urlParameters = isset($urlParameters) && $urlParameters != '' ? $urlParameters : '/?url=';
        $linkPosition = isset($linkPosition) && $linkPosition != '' ? $linkPosition : __('Left', 'videoconverter');
        $linkText = isset($linkText) && $linkText != '' ? $linkText : __('Download Now', 'videoconverter');
        $linkCssClass = isset($linkCssClass) && $linkCssClass != '' ? $linkCssClass : 'videoconverter-default-btn';
        $containerCssClass = isset($containerCssClass) && $containerCssClass != '' ? $containerCssClass : 'videoconverter-default-container';
        $containerPosistionCssClass = $linkPosition == __('Left', 'videoconverter') ? 'videoconverter-default-container-position-left' : 'videoconverter-default-container-position-right';
   
        $containerCssClass = $containerCssClass . ' ' . $containerPosistionCssClass;
   
        $dom = new DomDocument();
        libxml_use_internal_errors(true);
   
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
   
        $dom->loadHtml($html);
        libxml_clear_errors();
   
        $xpath = new DomXpath($dom);
        $iframes = $xpath->query("//iframe[contains(@src,'youtube')]");
   
        // support for
        // - Default Wordpress behavior without any wordpress plugin,
        // - Smart Youtube PRO
        // - YouTube
        // - Advanced YouTube Embed by Embed Plus
        foreach ($iframes as $iframe) {
            $url = $iframe->getAttribute('src');
            $videoId = $this->get_youtube_id_from_url($url);

            $containerElement = $dom->createElement('div');
            $containerClassAttribute = $this->createAttribute($dom, 'class', $containerCssClass);
            $containerElement->appendChild($containerClassAttribute);

            $linkElement = $dom->createElement('a', $linkText);
            $linkClassAttribute = $this->createAttribute($dom, 'class', $linkCssClass);
            $linkElement->appendChild($linkClassAttribute);

            $linkNoFollowAttribute = $this->createAttribute($dom, 'rel', 'nofollow');
            $linkElement->appendChild($linkNoFollowAttribute);

            $linkTargetAttribute = $this->createAttribute($dom, 'target', '_blank');
            $linkElement->appendChild($linkTargetAttribute);
            
            $videoconverterUrl = $redirectLink . $urlParameters . "http://www.youtube.com/watch?v=" .$videoId;
            $linkHrefAttribute = $this->createAttribute($dom, 'href', $videoconverterUrl);
            $linkElement->appendChild($linkHrefAttribute);

            $containerElement->appendChild($linkElement);

            if($iframe->parentNode->nodeName != 'object'){
                $iframe->parentNode->appendChild($containerElement);
            }else{
                $iframe->parentNode->parentNode->appendChild($containerElement);
            }
        }
        // support for
        // - Vimeo
        $iframes = $xpath->query("//iframe[contains(@src,'player')]");
        foreach ($iframes as $iframe) {
            $vurl = $iframe->getAttribute('src');
            $videoId = $this->get_vimeo_id_from_url($vurl);

            $containerElement = $dom->createElement('div');
            $containerClassAttribute = $this->createAttribute($dom, 'class', $containerCssClass);
            $containerElement->appendChild($containerClassAttribute);

            $linkElement = $dom->createElement('a', $linkText);
            $linkClassAttribute = $this->createAttribute($dom, 'class', $linkCssClass);
            $linkElement->appendChild($linkClassAttribute);

            $linkNoFollowAttribute = $this->createAttribute($dom, 'rel', 'nofollow');
            $linkElement->appendChild($linkNoFollowAttribute);

            $linkTargetAttribute = $this->createAttribute($dom, 'target', '_blank');
            $linkElement->appendChild($linkTargetAttribute);

            $videoconverterUrl = $redirectLink . $urlParameters . "https://vimeo.com/" .$videoId;
            $linkHrefAttribute = $this->createAttribute($dom, 'href', $videoconverterUrl);
            $linkElement->appendChild($linkHrefAttribute);

            $containerElement->appendChild($linkElement);
            
            if($iframe->parentNode->nodeName != 'object'){
                $iframe->parentNode->appendChild($containerElement);
            }else{
                $iframe->parentNode->parentNode->appendChild($containerElement);
            }
        }
        // support for
        // - viper plugin
        $iframes = $xpath->query("//span[contains(@class,'vvqbox')]/span/a  ");
        foreach ($iframes as $iframe) {
            $url = $iframe->getAttribute('href');
            $videoId = $this->get_youtube_id_from_url($url);

            $containerElement = $dom->createElement('div');
            $containerClassAttribute = $this->createAttribute($dom, 'class', $containerCssClass);
            $containerStyleAttribute = $this->createAttribute($dom, 'style', 'margin-top: -7px;');
            $containerElement->appendChild($containerStyleAttribute);
            $containerElement->appendChild($containerClassAttribute);

            $linkElement = $dom->createElement('a', $linkText);
            $linkClassAttribute = $this->createAttribute($dom, 'class', $linkCssClass);
            $linkElement->appendChild($linkClassAttribute);

            $linkNoFollowAttribute = $this->createAttribute($dom, 'rel', 'nofollow');
            $linkElement->appendChild($linkNoFollowAttribute);

            $linkTargetAttribute = $this->createAttribute($dom, 'target', '_blank');
            $linkElement->appendChild($linkTargetAttribute);

            $videoconverterUrl = $redirectLink . $urlParameters . "http://www.youtube.com/watch?v=" .$videoId;
            $linkHrefAttribute = $this->createAttribute($dom, 'href', $videoconverterUrl);
            $linkElement->appendChild($linkHrefAttribute);

            $containerElement->appendChild($linkElement);

            $iframe->parentNode->parentNode->appendChild($containerElement);
        }

        return utf8_decode($dom->saveHTML());
    }

    function createAttribute($dom, $name, $value){
        $attribute = $dom->createAttribute($name);
        $attribute->value = $value;
        return $attribute;
    }
     // Youtube videos detect
    function get_youtube_id_from_url($url)
    {
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            return $match[1];
    }
}
     // Vimeo videos detect 
    function get_vimeo_id_from_url($vurl){
	$regex = '~
		# Match Vimeo link and embed code
		(?:<iframe [^>]*src=")?         # If iframe match up to first quote of src
		(?:                             # Group vimeo url
				https?:\/\/             # Either http or https
				(?:[\w]+\.)*            # Optional subdomains
				vimeo\.com              # Match vimeo.com
				(?:[\/\w]*\/videos?)?   # Optional video sub directory this handles groups links also
				\/                      # Slash before Id
				([0-9]+)                # $1: VIDEO_ID is numeric
				[^\s]*                  # Not a space
		)                               # End group
		"?                              # Match end quote if part of src
		(?:[^>]*></iframe>)?            # Match the end of the iframe
		(?:<p>.*</p>)?                  # Match any title information stuff
		~ix';
	
	preg_match( $regex, $vurl, $matches );
	
	return $matches[1];
} 

}