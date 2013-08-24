=== WP flash img show ===
  Contributors: xwjie
  Donate link: http://xwjie.com/post/wp-flash-img-show.html
  Tags: image,Slide,Flash,Slideshow,images,photo,CMS,WP flash img show,WP-flash-img-show,WP_flash_img_show,图片,幻灯片,轮换
  Requires at least: 2.7
  Tested up to: 3.4.2
  Stable tag: trunk
  
  
 == Description ==
  
= English =
 wp flash img show is a FLASH Image Slide plugin for WordPress.You can show your articles , photo,goods,product and other ad. or introduction . 

 The Options include:Round Corner,Auto Play Time,Is Height Quality,Window Open,Button Margin,Button Distance,Description Bg Color,Description Bg Alpha,Description Text Color,Description Text Font,Description Move Duration,Button Alpha,Button Text Color,Button Default Color,Button Hover Color,Button Focus Color,Chang Image Mode,Is Show button,Is Show Description,Scale Mode,Transform Mode.There are 4 scale mode(No Border/Show All/Exact Filte/No Scale) and 8 transform mode (alpha/blur/left/right/top/bottom/breathe/breathe+Blur). Just enjoy it.

 ! It is unnecessary to update this plugin to the latest version if it work well for you now.
 
 Demo: http://xwjie.com/demo/wp-flash-img-show-demo.php 

= Chinese =
 这是一个flash图片幻灯片轮换wordpress插件，你可以利用它展示热门日志、艺术图片、商品、产品。通过改变用户设置，还可以用来做图片广告、宣传标语等等。请发挥创意。
 你可以自由设置：图片切换时间、是否采用高质量、链接打开方式、按钮的位置、按钮的距离、描述的背景颜色、描述的背景透明、描述的文字颜色、描述的字体(名)、描述背景动画的时间、按钮的透明度、按钮文字的颜色、按钮的默认颜色、按钮的掠过颜色、按钮当前颜色、图片切换模式、是否显示关于信息、是否显示描述、图片放缩模式、图片切换模式。图片放缩模式包括No Border/Show All/Exact Filte/No Scale四种，图片切换模式包括alpha/blur/left/right/top/bottom/breathe/breathe+Blur八种。
 
 ! 如果旧版本已经运行良好，满足你的需求的话，无需尝试更新到最新版。
 
 演示: http://xwjie.com/demo/wp-flash-img-show-demo.php
  
 == Installation ==

  English
 
step 1: Extract wp-flash-img-show.zip 

step 2: Upload  `wp-flash-img-show`  older to the   `/wp-content/plugins/`  directory 

step 3: Activate 'wp flash img show' in your Admin Panel ('Plugins' menu ) 

step 4 Method 1: Copy the HTML code at the bottom of [wp flash img show Admin Panel] like:`<div id="wp_flash_img_show_here_default">wp_flash_img_show will display here (config: default)</div>` . Put HTML code in your Template / Post / Widgets(text-widgets).

step 4 Method 2: Copy the PHP code at the bottom of [wp flash img show Admin Panel] like:`<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show('default');} ?>`. Put the PHP code in your template.

step 5: Change the Setting by navigating to 'Settings'--'wp flash img show' and complete the form 

Notice: Make sure have `wp_head()` just before the closing `</head>` tag of your theme AND  have `wp_footer()` just before the closing `</body>` tag of your theme, or you will break many plugins.(You can edit and add those function to your theme file.)

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


简体中文：

步骤1. 解压 wp-flash-img-show.zip

步骤2. 上传  `wp-flash-img-show`  文件夹到你的网站  `/wp-content/plugins/`  目录内

步骤3. 用管理员身份登录，在'插件'选项内激活 'WP flash img show' 插件

步骤4 方法1： 在设置页面底部复制HTML代码复制到你的主题文件/文章/边栏小工具（文本小工具）。HTML代码是这样子的：`<div id="wp_flash_img_show_here_default">wp_flash_img_show will display here (config: default)</div>`

步骤4 方法2： 在设置页面底部复制PHP代码复制到你的主题文件中。PHP代码是这样子的：`<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show('default');} ?> `

步骤5. 在 '设置' -- 'WP flash img show' 设置图片地址和显示参数

注意：确保你的主题模板文件中有一个 `wp_head()` 在`</head>` 之前， 而切尔有一个 `wp_footer()` 在 `</body>` 标签之前, 否则很多插件无法使用，包括本插件（请手动添加到主题模板文件中）.
	 
 == Frequently Asked Questions ==

= Submit bug =
Just Click :<a href="http://xwjie.com">Xwjie 微风的等待</a>  and leave a message. 

= Can not Display ? =
 1. Make sure have wp_head() just before the closing </head> tag of your theme AND have wp_footer() just before the closing </body> tag of your theme, or you will break many plugins.(You can edit and add those function to your theme file.)
 2. image URL Image URL is not allow cross-domain. e.g. Blog URL is "http://blog.xwjie.com" ,Image URL should begin with "http://blog.xwjie.com"
 
= How to display 2 or more Flash =
 JUST Creat a new config.

 
 == Translations ==

= English =
The plugin comes with various translations, You can submit your translations here: [http://xwjie.com/post/wp-flash-img-show.html](http://xwjie.com/post/wp-flash-img-show.html)  or Email to xwjwind{AT}gmail.com

How To? please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the sitemap.pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).

= Chinese =
此插件支持多语言，含有语言包模版。欢迎您来翻译。你可以在此提交你的翻译 [http://xwjie.com/post/wp-flash-img-show.html](http://xwjie.com/post/wp-flash-img-show.html) 或者发送Email到gxxywj#gmail.com

 = Translators =
 
 zh_CN(Chinese):Xwjie (gd***wj@gmail.com)
 
 tr_TR(Turkish):Weeebdesign (i**o@weeebhosting.com)
 
 vi_VN(Vietnamese):Minh Lâm (minhl***r@gmail.com)

 sk_SK(Slovak):Branco [WebHostingGeeks.com](http://webhostinggeeks.com/blog/)

 == Changelog ==

> v1.4 build 2011-09-08
       - New setting:Description Location, Description Top Position, Description Text FontSize, Description Text Align, Button Width, Button Height, Button FontSize. 
       - Get images from page automatic. 
       - Upload/Select images from media library.

> v1.3 build 2010-11-27 Get images from Recent post automatic // Add color picker // Background Transparent // Get code shortcut .

> v1.2 build 2010-10-17 support edit and display more flash slideshow(only one before).rebuild the code,all option save to a array.

> v1.11 build 2010-09-27 Fixed the BUG in multisite network.

> v1.1  build 2010-08-14 Add Preview to the Admin Panel.Add another way to inset the Flash Show.

> v1.0  build 2010-08-06 The first Ver.


 == Screenshots ==

1. Setting (English). do as your wishes.
2. Setting (Chinese): http://xwjie.com/post/wp-flash-img-show.html

 == License ==

1. This Plugin Released under GPLv2. For More:license.txt
2. The Flash Player(.swf) is base on 'bcastr flash image player',Author: zhangruochi , http://code.google.com/p/bcastr/ , Released under Apache License 2.0 ( http://www.apache.org/licenses/LICENSE-2.0 ).

== Upgrade Notice ==
none