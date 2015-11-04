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


class JFormFieldBookmarklet extends JFormField {

	public function getInput()	{
		
		$link = "<a ondragstart=\"try{event.dataTransfer.setDragImage(this,$(this).width()/2,$(this).height()/2);}catch(e){}\" 
					href=\"javascript:(function(){var s=document.createElement('script');s.type='text/javascript';s.src='//u.heatmap.it/bookmark.js';(top.document.body || top.document.getElementsByTagName('head')[0]).appendChild(s);})();\" style=\"display:inline-block; padding:0 8px;border-radius:4px;background:#ccc;text-decoration:none;color:#000;font-size:12px;cursor:move;\">
					heatmap
				</a>";
		
		return $link;

	}	
	
}


