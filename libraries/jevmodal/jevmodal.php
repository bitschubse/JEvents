<?php
/**
 *
 * @copyright   Copyright (C) 2015 - JEVENTS_COPYRIGHT GWE Systems Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Utility class for Bootstrap or UIKit Modal Popups especially URL based Modals which bootstrap usually fails on
 *
 */
class JevModal
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Add javascript support for Bootstrap modal
	 *
	 * @param   string $selector   The selector for the modal element.
	 * @param   array  $params     An array of options for the modal element.
	 *                             Options for the tooltip can be:
	 *                             - size     string,  Values can be "max" or "h,w" for height and width values
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function modal($selector = '.jevmodal', $params = array())
	{

		if (!isset(static::$loaded[__METHOD__][$selector]))
		{

			// Include Modal framework
			static::framework();

			$jsonParams = json_encode($params);

			$script = <<< SCRIPT
document.addEventListener('DOMContentLoaded', function() {
	var targets = document.querySelectorAll('$selector');
	targets.forEach(function(target) {
		target.addEventListener('click', function(evt){
			evt.preventDefault();
			jevModalSelector(target, $jsonParams, evt);
			return false;
		}, target);
	});
});
SCRIPT;
			Factory::getDocument()->addScriptDeclaration($script);

			// Set static array
			static::$loaded[__METHOD__][$selector] = true;
		}

		return;
	}


	/**
	 * Method to load the  JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of Bootstrap is included for easier debugging.
	 *
	 * @param   mixed $debug Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function framework($debug = null, $forceBoostrap = false, $forceUIkit = false)
	{

		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		$jevparams = ComponentHelper::getParams('com_jevents');

		// UIKit or Bootstrap
		$jinput = JFactory::getApplication()->input;
		$task = $jinput->getString("task", $jinput->getString("jevtask", ""));
		if (!$forceBoostrap && ($task == "icalevent.edit" || $task == "icalrepeat.edit")
			&& (Factory::getApplication()->isClient('administrator') || $jevparams->get("newfrontendediting", 1))
		)
		{
			HTMLHelper::script('com_jevents/lib_jevmodal/jevmodal_gsl.js', array('framework' => false, 'relative' => true, 'pathOnly' => false, 'detectBrowser' => false, 'detectDebug' => true));
		}
		else if ($forceUIkit)
        {
            HTMLHelper::script('com_jevents/lib_jevmodal/jevmodal_gsl.js', array('framework' => false, 'relative' => true, 'pathOnly' => false, 'detectBrowser' => false, 'detectDebug' => true));
        }
		else if (strpos($jevparams->get('framework', 'bootstrap'), 'uikit') === 0)
		{
			HTMLHelper::script('com_jevents/lib_jevmodal/jevmodal_uikit.js', array('framework' => false, 'relative' => true, 'pathOnly' => false, 'detectBrowser' => false, 'detectDebug' => true));

			static::$loaded[__METHOD__] = true;
			return;
		}
		else {
			// Load jQuery
			HTMLHelper::_('jquery.framework');

			HTMLHelper::stylesheet('com_jevents/lib_jevmodal/jevmodal.css', array('relative' => true));
			HTMLHelper::script('com_jevents/lib_jevmodal/jevmodal.js', array('framework' => false, 'relative' => true, 'pathOnly' => false, 'detectBrowser' => false, 'detectDebug' => true));
		}
		static::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Add javascript support for popovers
	 *
	 * Use element's Title as popover content
	 *
	 * @param   string $selector                        Selector for the popover
	 * @param   array  $params                          An array of options for the popover.
	 *                                                  Options for the popover can be:
	 *                                                  animation  boolean          apply a css fade transition to the popover
	 *                                                  html       boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
	 *                                                  content into the dom.
	 *                                                  placement  string|function  how to position the popover - top | bottom | left | right
	 *                                                  selector   string           If a selector is provided, popover objects will be delegated to the specified targets.
	 *                                                  trigger    string           how popover is triggered - hover | focus | manual
	 *                                                  title      string|function  default title value if `title` tag isn't present
	 *                                                  content    string|function  default content value if `data-content` attribute isn't present
	 *                                                  delay      number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                                  If a number is supplied, delay is applied to both hide/show
	 *                                                  Object structure is: delay: { show: 500, hide: 100 }
	 *                                                  container  string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function popover($selector = '.hasPopover', $params = array())
	{
		// unset($params['delay']);

		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		static::$loaded[__METHOD__][$selector] = true;

		$jevparams = ComponentHelper::getParams('com_jevents');
		$toolTipType = $jevparams->get('tooltiptype', 'bootstrap');

		if(strpos($jevparams->get('framework', 'bootstrap'), 'bootstrap') !== 0)
		{
			$toolTipType = 'uikit';
		}

		if (version_compare(JVERSION, '4', 'ge') && $toolTipType !== 'uikit')
		{
			// For Joomla 4 we need to change the data attribute
			HTMLHelper::_('bootstrap.popover', $selector, $params);
			return;
		}

		//$params['delay'] = [ 'show' => 50, 'hide' => 20000 ];

		if ($toolTipType !== 'uikit')
		{
			JHtml::_('jquery.framework');
			JHtml::_('bootstrap.framework');
			JevHtmlBootstrap::loadCss();
		}

		$opt['animation'] = isset($params['animation']) ? $params['animation'] : false;
		$opt['html']      = isset($params['html']) ? $params['html'] : true;
		$opt['placement'] = isset($params['placement']) ? $params['placement'] : false;
		$opt['selector']  = isset($params['selector']) ? $params['selector'] : false;
		$opt['title']     = isset($params['title']) ? $params['title'] : '';
		$opt['trigger']   = isset($params['trigger']) ? $params['trigger'] : 'hover focus';
		$opt['content']   = isset($params['content']) ? $params['content'] : '';
		$opt['delay']     = isset($params['delay']) ? $params['delay'] : false;
		$opt['container'] = isset($params['container']) ? $params['container'] : 'body';
		//$opt['template'] = isset($params['template']) ? $params['template'] : '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>';

		// Custom option to control display on touch devices
		$opt['mouseonly'] = isset($params['mouseonly']) ? $params['mouseonly'] : false;

		$options = json_encode($opt); //HTMLHelper::getJSObject($opt);

		$uikitopt = array();
		$uikitopt['title'] = isset($params['title']) ? $params['title'] : '';
		$uikitopt['pos']   = isset($params['placement']) ? $params['placement'] : 'top';
		$uikitopt['delay'] = isset($params['delay']['show']) ? $params['delay']['show'] : 0;
		$uikitopt['delayHide'] = 20000;
		$uikitopt['offset'] = 20;
		$uikitopt['animation'] = 'uk-animation-fade';
		$uikitopt['duration'] = 100;
		$uikitopt['cls'] = 'uk-active uk-card uk-card-default uk-padding-remove  uk-background-default';
		$uikitopt['clsPos'] = isset($params['clsPos']) ? $params['clsPos']: 'uk-tooltip';
		$uikitopt['mode'] = isset($params['trigger']) ? str_replace(" ", ",", $params['trigger']) : 'hover';
		$uikitopt['container'] = isset($params['container']) ? $params['container'] : 'body';
		$uikitoptions = json_encode($uikitopt);

		Factory::getDocument()->addScriptDeclaration(
			<<< SCRIPT
document.addEventListener('DOMContentLoaded', function()
{
	if ($options.mouseonly && 'ontouchstart' in document.documentElement) {
		return;
	}
	if (jQuery('$selector').length){
		try {
			ys_setuppopover('$selector', $uikitoptions);
		}
		catch (e) {
			if ('$toolTipType' != "uikit"  || typeof UIkit == 'undefined' ) {
			// Do not use this for YooTheme Pro templates otherwise you get strange behaviour!
				jQuery('$selector').popover($options);
			}
			else 
			{
				// Fall back to native uikit
				var hoveritems = document.querySelectorAll('$selector');
				hoveritems.forEach(function (hoveritem) {
					let title = hoveritem.getAttribute('data-yspoptitle') || hoveritem.getAttribute('data-original-title') || hoveritem.getAttribute('title');
					let body = hoveritem.getAttribute('data-yspopcontent') || hoveritem.getAttribute('data-content') || '';
					let options = hoveritem.getAttribute('data-yspopoptions') || '$uikitoptions';
					options = JSON.parse(options);
					/*
					var phtml = '<div class="uk-card uk-card-default uk-padding-remove uk-background-default" style="width:max-content;border-top-left-radius: 5px;border-top-right-radius: 5px;">' +
					(title != '' ? '<div class="uk-text-emphasis">' + title + '</div>' : '') +
					(body != '' ? '<div class="uk-card-body uk-text-secondary uk-padding-small" style="width:max-content">' + body + '</div>' : '') +
					'</div>';
					*/						
					var phtml = '' +
					(title != '' ? title.replace("jevtt_title", "uk-card-title uk-text-emphasis uk-padding-small").replace(/color:#(.*);/,'color:#$1!important;')  : '') +
					(body != '' ?  body.replace("jevtt_text", "uk-card-body uk-padding-small uk-text-secondary  uk-background-default")  : '') +
					'';
					options.title = phtml;
					
					if (hoveritem.hasAttribute('title')) {
						hoveritem.removeAttribute('title');
					}
			
					UIkit.tooltip(hoveritem, options);
				});
			}	
		}
	}
});
SCRIPT
		);

		static $hide = false;
		if (!$hide)
		{
			$hide = "
(function($) {
	if (typeof $.fn.popover == 'undefined')
	{
		// bootstrap popovers not used or loaded
		return;
	}

    var oldHide = $.fn.popover.Constructor.prototype.hide || false;

    $.fn.popover.Constructor.prototype.hide = function() {
        // Bootstrap 4         
        if (this.config)
        {
            //- This is not needed for recent versions of Bootstrap 4
            /*
	        if (this.config.container == '#jevents_body' && this.config.trigger.indexOf('hover') >=0) {
	            var that = this;
	            // try again after what would have been the delay
	            setTimeout(function() {
	                return that.hide.call(that, arguments);
	            }, that.config.delay.hide);
	            return;
	        }
	        */
        }
        // Earlier Bootstraps 
        else
        {
	        if (this.options.container == '#jevents_body' && this.options.trigger.indexOf('hover') >=0  && this.tip().is(':hover')) {
	            var that = this;
	            // try again after what would have been the delay
	            setTimeout(function() {
	                return that.hide.call(that, arguments);
	            }, that.options.delay.hide);
	            return;
	        }
        }
        if ( oldHide )
        {
            oldHide.call(this, arguments);
        }
    };

})(jQuery);";
			Factory::getDocument()->addScriptDeclaration($hide);
		}

		return;
	}

	/**
	 * Add javascript support for Bootstrap tooltips
	 *
	 * Add a title attribute to any element in the form
	 * title="title::text"
	 *
	 * @param   string $selector                                 The ID selector for the tooltip.
	 * @param   array  $params                                   An array of options for the tooltip.
	 *                                                           Options for the tooltip can be:
	 *                                                           - animation  boolean          Apply a CSS fade transition to the tooltip
	 *                                                           - html       boolean          Insert HTML into the tooltip. If false, jQuery's text method will be used to insert
	 *                                                           content into the dom.
	 *                                                           - placement  string|function  How to position the tooltip - top | bottom | left | right
	 *                                                           - selector   string           If a selector is provided, tooltip objects will be delegated to the specified targets.
	 *                                                           - title      string|function  Default title value if `title` tag isn't present
	 *                                                           - trigger    string           How tooltip is triggered - hover | focus | manual
	 *                                                           - delay      integer          Delay showing and hiding the tooltip (ms) - does not apply to manual trigger type
	 *                                                           If a number is supplied, delay is applied to both hide/show
	 *                                                           Object structure is: delay: { show: 500, hide: 100 }
	 *                                                           - container  string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
		HTMLHelper::_('bootstrap.tooltip',$selector, $params);

		return;
	}
}
