<?php
if (!class_exists('WumiiCommentTemplate')) {
    class WumiiCommentTemplate {
        /*
         * !!! You must do the two things before you try to do local test with this plugin:
         * 1. turn on WUMII_DEBUG mode
         * 2. change WUMII_SERVER to "http://{computer-name}:8080"
         *    {computer-name} is the name of computer which running a local test wumii web server.
         */
        const WUMII_DEBUG = false;
        const WUMII_SERVER = 'http://widget.wumii.cn';

        const VERSION = '1.0.0.0';
        const PLUGIN_PATH = '/wp-content/plugins/wumii-comments';
          
        // PHP 5 style constructor
        function __construct() {
            $this->WumiiCommentTemplate();
        }
        
        function WumiiCommentTemplate() {}
        
        function addWumiiContent() {
            $escapedUrl = $this->htmlEscape(get_permalink());
            $escapedTitle = $this->htmlEscape($this->getOrigTitle());
        
            // The first line in 'WUMII_HOOK' must be an empty line. Because some blogs use 'Embeds'(http://codex.wordpress.org/Embeds)
            // in the post content and the embed must be on its own line.
            // For example, if the 'embed' happen to add before our wumii code,
            // then we have to make sure wumii code doesn't follow that within the same line.
            $newContent = <<<WUMII_HOOK

<div id="wumiiComments" class="wumii-comment">
    <input type="hidden" name="wurl" value="$escapedUrl" />
    <input type="hidden" name="wtitle" value="$escapedTitle" />
</div>
WUMII_HOOK;

            $newContent = $newContent . $this->createWumiiCommentScript();
            return $newContent;
        }
        
        private function getOrigTitle() {
            global $post;
            return isset($post->post_title) ? $post->post_title : '';
        }
                
        private function htmlEscape($str) {
            return htmlspecialchars(html_entity_decode($str, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        }
                
        function createWumiiCommentScript() {
            $server = self::WUMII_SERVER;
            $script = '';
            if (self::WUMII_DEBUG) {
                $script = "<script type='text/javascript'>var wumiiCommentDebugServer = '$server';</script>";
            }
            global $wp_version;
            $params = array(
                'version' => self::VERSION,
                'pf' => 'WordPress' . $wp_version
            );
            $queryParams = '';
            foreach ($params as $name => $value) {
                $value = urlencode($value);
                $queryParams .= "&$name=$value";
            }
            $sitePrefix = function_exists('home_url') ? home_url() : get_bloginfo('url');
            $script .= <<<WUMII_SCRIPT

<p style="margin:0;padding:0;height:1px;overflow:hidden;">
    <script type="text/javascript"><!--
        var wumiiSitePrefix = "$sitePrefix";
        var wumiiCommentParams = "$queryParams";
    //--></script><script type="text/javascript" src="$server/ext/cw/widget"></script><a href="http://www.wumii.com/widget/comment" style="border:0;"><img src="http://static.wumii.cn/images/pixel.png" alt="无觅评论，优化体验，加强品牌价值" style="border:0;padding:0;margin:0;" /></a>
</p>
WUMII_SCRIPT;
            
            return $script;
        }
    }
}

$wumii_comments = new WumiiCommentTemplate();
echo $wumii_comments->addWumiiContent();
?>
