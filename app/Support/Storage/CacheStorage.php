<?php
	namespace Cart\Support\Storage;
	
	class CacheStorage
	{
		const S_CACHE_ALLOW = 1;
		const S_CACHE_DENNY = 0;

		public function set($name = "cachefile", $param = "", $content = "")
		{
	        $name = 'cache/production/'. $param .'_'. bin2hex("encode_string_".$param) . date('dmy') .'.php';
	        $cachetime = 18000;
	        if (file_exists($name) && time() - $cachetime < filemtime($name)) {

		        if (!preg_match("/sql_/", $name)) {
		        	    include($cachefile);
            			exit;
		        }
	        }
	        ob_start();

	        if (!preg_match("/sql_/", $name)) {
	        	echo $content;
	        }

	        $fp = fopen($name, 'w');
	        fwrite($fp, $content);
	        fclose($fp);
	        ob_end_flush();
		}

		public function get() {
	        Cache::set("cachefile", "sql", $category);
	        Cache::set("cachefile", "_sql", "[{$last}]");

	        if(isset($_COOKIE['logged'])) {
	            $cachefile = 'cache/production/container_'. bin2hex("cache_shop_logged") . date('dmy') .'.php';
	            $cachetime = 18000;
	        } else {
	            $cachefile = 'cache/production/container_'. bin2hex("cache_shop/") . date('dmy') .'.php';
	            $cachetime = 18000;
	        }

	        if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
	            include($cachefile);
	            exit;
	        }
	        ob_start();

	        echo $template;

	        $saveContent = ob_get_contents();
	        $fp = fopen($cachefile, 'w');
	        fwrite($fp, $saveContent);
	        fclose($fp);
	        ob_end_flush();
		}
	}