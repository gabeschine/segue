<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueAjaxPlugin.abstract.php,v 1.7 2008/02/21 18:53:02 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SeguePluginsTemplate.abstract.php");

/**
 * Abstract class that all AjaxPlugins must extend
 * 
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueAjaxPlugin.abstract.php,v 1.7 2008/02/21 18:53:02 adamfranco Exp $
 */
abstract class SegueAjaxPlugin 
	extends SeguePluginsTemplate
{
 	
/*********************************************************
 * Instance Methods - API
 *
 * These are the methods that plugins can and should use 
 * to interact with their environment. 
 * 		Valid additional APIs outside of the methods below:
 *			- OSID interfaces (accessed through Plugin->getManager($managerName))
 *
 * To preserve portability, plugins should not access 
 * other Harmoni APIs, constants, global variables, or
 * the super-globals $_GET, $_POST, $_REQUEST, $_COOKIE.
 *********************************************************/
	
	/**
	 * Answer a Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	final public function href ( $parameters = array() ) {		
		return "href='#' onclick='".$this->locationSend($parameters)." return false;'";
	}
	
	/**
	 * Answer a Javascript command to send the window to a url with the parameters
	 * passed.
	 *
	 * Use this method, e.g.:
	 *		"onclick='".$this->locationSend(array('item' => 123))."'"
	 * instead of the following:
	 * 		"onclick='window.location=\"".$this->url(array('item' => 123))."\"'"
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	final public function locationSend ( $parameters = array() ) {
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
			
		return "updateAjaxPlugin(\"".$this->getId()."\", \"".$this->_ajaxUrl($parameters)."\");";
	}
	
	/**
	 * Answer a url with the parameters passed, for a form. As well, specify
	 * an optional boolean second parameter, 'isMultipart' if this is a multipart
	 * form with file uploads.
	 *
	 * Use this method, e.g.:
	 *		$this->formTagWithAction(array('item' => 123), false);
	 * instead of the following:
	 * 		"<form action='".$this->url(array('item' => 123))."' method='post>";
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @param string $method post OR get
	 * @param boolean $isMultipart
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	final public function formStartTagWithAction ( $parameters = array(), $method = 'post', 
		$isMultipart = false ) 
	{
		// If this is a multipart form, we must do a normal 'submit'
		// that includes a page refresh.
		if ($isMultipart) {
			return "<form action='".$this->_url($parameters)."' method='post' enctype='multipart/form-data'>";
		} 
		// If the form is not a multipart form with file uploads, then we can
		// override the submit with an AJAX GET submission instead. (if implemented).
		else {
			if (strtolower($method) == 'get')
				$method = 'get';
			else
				$method = 'post';
			return "<form onsubmit='submitAjaxPluginForm(\"".$this->getId()."\", this, \"".$this->_ajaxUrl($parameters)."\");' action='Javascript: var nullVal = null;' method='".$method."'>";
		}
	}


/*********************************************************
 * Class Methods - Other
 *********************************************************/

	/**
	 * Answer the javascript functions for controlling plugins
	 * 
	 * @return string
	 * @access public
	 * @since 1/16/06
	 * @static
	 */
	final public static function getPluginSystemJavascript () {
		ob_start();
		print<<<END
		
		<script type='text/javascript'>
			/* <![CDATA[ */
			
			function submitAjaxPluginForm( pluginId, form, destination ) {
				/*********************************************************
				 * Thanks to 'execute' at http://www.designplanet.biz/tutorials-15.htm
				 * for info on how to do post forms with AJAX.
				 *********************************************************/
				// Ensure that the form is only submitted once
				if (form.wasSubmitted)
					return;
				
				// Ensure that we have a non-escaped url
				destination = destination.replace(/&amp;/gi, '&');
				
				// Force updating of any FCK Editor fields.
				// This will simply cause the FCK Editor html text to be
				// written to the hidden fields that are actually submitted.
				// There shouldn't be a problem with doing this for fields not 
				// being submitted at the moment. We just need to make sure that
				// it happens before we look for the value of a field.
				if ((typeof(window.FCKeditorAPI) != "undefined")) {
					for ( var name in FCKeditorAPI.__Instances ) {
						var oEditor = FCKeditorAPI.__Instances[ name ];
						oEditor.UpdateLinkedField();
					}
				}
				
				// Build a "name1=val1&name2=val2..." string
				var fields = new Array();
				for (var i = 0; i < form.elements.length; i++) {
					
					var value = encodeURIComponent(form.elements[i].value);
										
					// Javascript doesn't escape plus symbols, so manually encode them
					value = value.replace(/\\+/, '%2B');
					
					// Ignore unchecked check-boxes
					if (form.elements[i].nodeName == 'INPUT' && form.elements[i].type == 'checkbox'
						&& (!form.elements[i].checked || form.elements[i].checked == 'false'))
					{
						// ignore
						continue;
					}
					
					
					fields.push(encodeURIComponent(form.elements[i].name) + '=' + value);
				}
				var data = fields.join('&');
				
				
				if (form.method.toUpperCase() == 'POST')
					updateAjaxPlugin(pluginId, destination, 'POST', data);
				else
					updateAjaxPlugin(pluginId, destination + '&' + data, 'GET', null);
			}
			
			function updateAjaxPlugin( pluginId, destination, method, data ) {
				// Keep the width the same to prevent too much re-flowing of the
				// page
				var pluginElement = document.get_element_by_id('plugin:' + pluginId);
				pluginElement.style.width = pluginElement.offsetWidth + 'px';
				pluginElement.style.height = pluginElement.offsetHeight + 'px';
				
				
				if (method == null) {
					method = 'GET';
					data = null;
				}
				
				// branch for native XMLHttpRequest object (Mozilla, Safari, etc)
				if (window.XMLHttpRequest)
					var req = new XMLHttpRequest();
					
				// branch for IE/Windows ActiveX version
				else if (window.ActiveXObject)
					var req = new ActiveXObject("Microsoft.XMLHTTP");
				
				if (req) {
					req.onreadystatechange = function () {
						var pluginElement = document.get_element_by_id('plugin:' + pluginId);
						
						if (req.readyState > 0 && req.readyState < 4) {
							pluginElement.innerHTML = '<div>Loading...<\/div>';
						} else {
							pluginElement.innerHTML = '<div>Loaded<\/div>';
									
							// only if req shows "loaded"
							if (req.readyState == 4) {
								// only if we get a good load should we continue.
								if (req.status == 200) {
									//get the plugin element
// 									alert(req.responseText);
									var pluginResponseElement = req.responseXML.firstChild;
									
									
									// Markup
									var elements = pluginResponseElement.getElementsByTagName("markup");
									var markup = '';
									if (elements.length > 0 && elements[0]) {
										if (elements[0].textContent)
											markup = elements[0].textContent;
										else if (true) {
											for (var i = 0; i < elements[0].childNodes.length; i++) {
												if (elements[0].childNodes[i].nodeType == 4) {
													markup = elements[0].childNodes[i].data;
												}
											}
										}
									} else {
										alert("Error: No valid <markup> was found in\\n\\n" + req.responseText);	
									}
									
									// Place the new values in the page										
									pluginElement.innerHTML = markup.replace(/}}>/g, ']'+']'+'>');
									
									// unset our temporary width
									pluginElement.style.width = '';
									pluginElement.style.height = '';
									
									// Execute any javascript in the element.
									var scripts = pluginElement.getElementsByTagName("script"); 
									for (var i=0; i< scripts.length; i++) {
										eval(scripts[i].text);
									}
								} else {
									alert("There was a problem retrieving the XML data:\\n" +
										req.statusText);
								}
							}
						}
					}
					
					if (method.toUpperCase() == 'POST') {
						req.open("POST", destination, true);
						req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						req.send(data);
					} else {
						req.open("GET", destination, true);
						req.send(null);
					}
				}
			}
			
			/* ]]> */
		</script>

END;
		return ob_get_clean();
	}
	

/*********************************************************
 * Instance Methods - Not in API
 *********************************************************/
 	/**
 	 * @var boolean $_isExtended;  
 	 * @access private
 	 * @since 10/25/07
 	 */
 	private $_isExtended;
 	
 	/**
 	 * @var string $updateModule;  
 	 * @access private
 	 * @since 11/8/07
 	 */
 	private $updateModule = 'plugin_manager';
 	
 	/**
 	 * @var string $updateAction;  
 	 * @access private
 	 * @since 11/8/07
 	 */
 	private $updateAction = 'update_ajax';
 	
	
	/**
	 * Answer the markup for this plugin
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended	If true, return the extended version. Default: false.
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	final public function executeAndGetMarkup ( $showControls = false, $extended = false ) {
		$this->_isExtended = $extended;
		
		$markup = parent::executeAndGetMarkup($showControls, $extended);
		
		$this->writeAjaxLib();
		
		ob_start();
		print "\n<div id='plugin:".$this->getId()."'>\n";
		print $markup;
		print "\n</div>"; 
		return ob_get_clean();
	}
 	
	/**
	 * Answer a Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access private
	 * @since 1/13/06
	 */
	private function _url ( $parameters = array() ) {		
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
		
		$url = $this->_baseUrl->deepCopy();
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		return $url->write();
	}
	
	/**
	 * Answer a url for Ajax updating
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	private function _ajaxUrl ( $parameters = array() ) {
		$harmoni = Harmoni::instance();
		$url = $harmoni->request->mkURL($this->updateModule, $this->updateAction);
	
		$harmoni->request->startNamespace('plugin_manager');
		
		$url->setValue('plugin_id', $this->getId());
		
		// Local modules and actions for generating valid links
		if (isset($this->localModule) && $this->localModule)
			$url->setValue('module', $this->localModule);
		else
			$url->setValue('module', $harmoni->request->getRequestedModule());
		if (isset($this->localAction) && $this->localAction)
			$url->setValue('action', $this->localAction);
		else
			$url->setValue('action', $harmoni->request->getRequestedAction());
		
		if ($this->_isExtended)
			$url->setValue('extended', 'true');
			
		$harmoni->request->endNamespace();
		
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		
		return $url->write();
	}
	
	/**
	 * Write the AJAX library to the document's head
	 * 
	 * @return void
	 * @access public
	 * @since 5/9/07
	 * @static
	 */
	final public static function writeAjaxLib () {
		if (!isset($GLOBALS['ajaxLibWritten']) || !$GLOBALS['ajaxLibWritten']) {
			$harmoni = Harmoni::instance();
			$outputHandler = $harmoni->getOutputHandler();
			$outputHandler->setHead(
				$outputHandler->getHead()
				.self::getPluginSystemJavascript());
			$GLOBALS['ajaxLibWritten'] = true;	
		}
	}
	
	/**
	 * Set the update module and action. This method should not be used by plugins.
	 * it is to be used only by plugin users to direct plugins to alternate updating
	 * actions.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 11/8/07
	 */
	public function setUpdateAction ($module, $action) {
		ArgumentValidator::validate($module, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($action, NonZeroLengthStringValidatorRule::getRule());
		
		$this->updateModule = $module;
		$this->updateAction = $action;
	}
}

?>