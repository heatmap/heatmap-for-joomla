<?php
/*------------------------------------------------------------------------
# plg_system_heatmap - Real-time analytics and event tracking for your Joomla sites
# ------------------------------------------------------------------------
# author    Jeremy Magne
# copyright Copyright 2014 - HeatMap, Inc - https://heatmap.me/. All rights reserved.
# Websites: https://heatmap.me/, https://www.daycounts.com
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-------------------------------------------------------------------------*/



// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.cache.cache');
jimport( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * One Page Checkout Module for VirtueMart plugin
 */
class plgSystemHeatmap extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onBeforeRender() {
		
		//Do not display tag in administrator section
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return false;
		}

		// Only render for HTML output
		if ('html' !== JFactory::getDocument()->getType()) {
			return;
		}

		$this->front_write_script();

	}
	
	/**
	 * Writing the script on the front-end pages
	 */
	private function front_write_script() {

		$document = JFactory::getDocument();
		$js = '';
		if ($this->params->get('ext_use',0)) {
			$js .= $this->params->get('ext_code','');
		}

		$isadmin = JFactory::getUser()->authorise('core.admin'); 
		if ($isadmin) {
			//Do not record admin user heatmap
			$js .= 'window.heatmap_ext=window.heatmap_ext||{};window.heatmap_ext.recordDisabled=true;';
		}
		$js .= "
			(function() {
			var hm = document.createElement('script'); hm.type ='text/javascript'; hm.async = true;
			hm.src = ('++u-heatmap-it+log-js').replace(/[+]/g,'/').replace(/-/g,'.');
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(hm, s);
			})();
		";
		$document->addScriptDeclaration($js );
	}
}