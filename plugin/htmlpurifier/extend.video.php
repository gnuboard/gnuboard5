<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//https://stackoverflow.com/questions/4739284/htmlpurifier-iframe-vimeo-and-youtube-video
/**
 * Based on: http://sachachua.com/blog/2011/08/drupal-html-purifier-embedding-iframes-youtube/
 * Iframe filter that does some primitive whitelisting in a somewhat recognizable and tweakable way
 */

if( !class_exists('HTMLPurifier_Filter_Iframevideo') ){
	class HTMLPurifier_Filter_Iframevideo extends HTMLPurifier_Filter
	{
		public $name = 'Iframevideo';

		/**
		 *
		 * @param string $html
		 * @param HTMLPurifier_Config $config
		 * @param HTMLPurifier_Context $context
		 * @return string
		 */
		public function preFilter($html, $config, $context)
		{
			if (strstr($html, '<iframe')) {
				$html = preg_replace_callback('/<iframe.*?src="https?:\/\/www\.youtube\.com\/embed\/([^"]*)[^>]*>(.*?)?\/iframe>/i', array($this, 'trust_url_match'), $html);
				$html = preg_replace_callback('/<iframe.*?src="https?:\/\/player\.vimeo.com\/video\/([^"]*)[^>]*>(.*?)?\/iframe>/i', array($this, 'trust_url_match'), $html);
                $html = preg_replace_callback('/<iframe.*?src="https?:\/\/www\.facebook.com\/plugins\/([^"]*)[^>]*>(.*?)?\/iframe>/i', array($this, 'trust_url_match'), $html);
				//$html = preg_replace('#<iframe#i', '<img class="Iframevideo"', $html);
				//$html = preg_replace('#</iframe>#i', '</img>', $html);
			}
			return $html;
		}

		public function trust_url_match($matches)
		{
			$str = $matches[0];
			if( $matches[1] ){
				$str = preg_replace('#<iframe#i', '<img class="Iframevideo"', $str);
				$str = preg_replace('#</iframe>#i', '</img>', $str);
			}
			return $str;
		}
		/**
		 *
		 * @param string $html
		 * @param HTMLPurifier_Config $config
		 * @param HTMLPurifier_Context $context
		 * @return string
		 */
		public function postFilter($html, $config, $context)
		{
			$post_regex = '#<img class="Iframevideo"([^>]+?)>#';
			return preg_replace_callback($post_regex, array($this, 'postFilterCallback'), $html);
		}

		/**
		 *
		 * @param array $matches
		 * @return string
		 */
		protected function postFilterCallback($matches)
		{
			// Domain Whitelist
			$youTubeMatch = preg_match('#src="https?://www\.youtube(-nocookie)?\.com/#i', $matches[1]);
			$vimeoMatch = preg_match('#src="https?://player\.vimeo\.com/#i', $matches[1]);
            $fackbookMatch = preg_match('#src="https?://www\.facebook\.com/#i', $matches[1]);
			if ($youTubeMatch || $vimeoMatch || $fackbookMatch) {
				$extra = ' frameborder="0"';
				if ($youTubeMatch || $fackbookMatch) {
					$extra .= ' allowfullscreen';
				} elseif ($vimeoMatch) {
					$extra .= ' webkitAllowFullScreen mozallowfullscreen allowFullScreen';
				}
				return '<iframe ' . $matches[1] . $extra . '></iframe>';
			} else {
				return '';
			}
		}
	}
}

if( !class_exists('HTMLPurifierContinueParamFilter') ){
	class HTMLPurifierContinueParamFilter extends HTMLPurifier_URIFilter
	{
		public $name = 'ContinueParamFilter';
        
        public function filter(&$uri, $config, $context)
        {
            // 쿼리 파라미터 검사
            $query = $uri->query;
            $path = $uri->path;
            
            if ($path && preg_match('#[\\\\/]logout#i', $path)) {
                return false;
            }
            
            if ($query) {
                
                parse_str($query, $query_params);
                
                if (isset($query_params['continue']) || isset($query_params['pcurl'])) {
                    return false;
                }
            }

            return true; // 조건 통과 시 허용
        }
	}
}