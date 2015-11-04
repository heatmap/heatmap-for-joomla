<?php
/*------------------------------------------------------------------------
# plg_system_heatmap - Real-time analytics and event tracking for your Joomla sites
# ------------------------------------------------------------------------
# author    Jeremy Magne
# copyright Copyright 2014 - HeatMap, Inc - https://heatmap.me/. All rights reserved.
# Websites: https://heatmap.me/, https://www.daycounts.com
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-------------------------------------------------------------------------*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.cache.cache');
jimport('joomla.application.helper');
jimport('joomla.filesystem.file');
jimport('joomla.html.parameter.element');

if(!class_exists('HeatmapAccountChecker')) {
	class HeatmapAccountChecker {
		public static function check($domain='',$secure=true) {
			
			$url = ($secure) ? 'https://' : 'http://';
			$url .= 'heatmap.it/api/check/account?u='.$domain.'&callback=joomla';
			
			if(function_exists('curl_exec')) {
				// Use cURL
				$curl_options = array(
					CURLOPT_AUTOREFERER		=> true,
					CURLOPT_FAILONERROR		=> true,
					CURLOPT_HEADER			=> false,
					CURLOPT_RETURNTRANSFER	=> true,
					CURLOPT_CONNECTTIMEOUT	=> 5,
					CURLOPT_MAXREDIRS		=> 20,
					CURLOPT_USERAGENT		=> 'Joomla Plugin Account Checker'
				);
				$ch = curl_init($url);
				foreach($curl_options as $option => $value)	{
					@curl_setopt($ch, $option, $value);
				}
				$data = curl_exec($ch);
			} elseif( ini_get('allow_url_fopen') ) {
				// Use fopen() wrappers
				$options = array( 'http' => array(
					'max_redirects' => 10,          // stop after 10 redirects
					'timeout'       => 20,         // timeout on response
					'user_agent'	=> 'Joomla Plugin Account Checker'
				) );
				$context = stream_context_create( $options );
				$data = @file_get_contents( $url, false, $context );
			} elseif ($secure) {
				//Try calling the non secure url
				$data = self::check($domain,false);
			} else {
				return false;
			}
			
			$data = trim(preg_replace(array('/[\n\r]/', '/^joomla/'), array('', ''), $data), '();');
			$json = @json_decode($data, true);
			$json['lastcheck'] = JFactory::getDate();
			$json = JFilterInput::getInstance()->clean($json,'none');
			return $json;
		}
	}
}

class JFormFieldAccountCheck extends JFormField {

	public function getInput()	{
		
		$app = JFactory::getApplication();
		$heatmapforcecheck = JRequest::getInt('heatmapforcecheck',0);
		if ($app->isAdmin() && $heatmapforcecheck) {
			$cache = JFactory::getCache();
			$result = $cache->clean('plg_system_heatmap:accountcheck');

			$refresh_uri = JUri::getInstance();
			$refresh_uri->delVar('heatmapforcecheck');
			$refresh_uri = $refresh_uri->toString();
			$app->redirect($refresh_uri);
		}

		$cache = JFactory::getCache('plg_system_heatmap:accountcheck');
		$cache->setCaching(true);	
		$cache->setLifeTime(86400); //24h	

		$domain = urlencode(JUri::root(false));
		//$accountCheck = HeatmapAccountChecker::check($domain);
		$accountCheck = $cache->call( array( 'HeatmapAccountChecker', 'check' ) , $domain);
		
		if ($accountCheck['valid']) {
			$msg .= '<span class="text-success">'.JText::_('PLG_HEATMAP_CHECK_ACCOUNT_VALID').'</span>';
		} else {
			$msg .= '<span class="text-error">'.JText::_('PLG_HEATMAP_CHECK_ACCOUNT_NOT_FOUND').'</span>';
		}
		
		$refresh_uri = JUri::getInstance();
		$refresh_uri->setVar('heatmapforcecheck',1);
		$refresh_uri = $refresh_uri->toString();
		$msg .= '<br><a class="btn" href="'.$refresh_uri.'">'.JText::_('PLG_HEATMAP_CHECK_ACCOUNT').'</a>';
		$lastcheck = (isset($accountCheck['lastcheck'])) ? $accountCheck['lastcheck'] : JText::_('PLG_HEATMAP_NEVER');
		$msg .= '<br>'.JText::_('PLG_HEATMAP_LAST_CHECK').' '.$lastcheck;
		if (isset($accountCheck['error']) && $accountCheck['error']) {
			$msg .= '<br>'.JText::_('PLG_HEATMAP_ERROR_MSG').' <em>'.$accountCheck['error'].'</em>';
		}

		return $msg;

	}	
	
}


