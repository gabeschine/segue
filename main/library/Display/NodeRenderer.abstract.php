<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.4 2006/01/20 20:53:25 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/NavigationNodeRenderer.class.php");
require_once(dirname(__FILE__)."/SiteNodeRenderer.class.php");
require_once(dirname(__FILE__)."/PluginNodeRenderer.class.php");
require_once(dirname(__FILE__)."/GenericNodeRenderer.class.php");
require_once(HARMONI."GUIManager/Components/MenuItem.class.php");


/**
 * The Node Render class takes an Asset and renders its navegational item,
 * as well as its children if selected
 * 
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.4 2006/01/20 20:53:25 adamfranco Exp $
 */
class NodeRenderer {


/*********************************************************
 * Class Methods - instance Creation
 *********************************************************/ 
	/**
	 * Create a NodeRenderer instance for an asset. Use this instead of
	 * '$obj =& new nodeRenderer()'
	 * 
	 * @param object Asset $asset
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/19/06
	 */
	function &forAsset ( &$asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		
		$type =& $asset->getAssetType();
		$siteType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'site');
		$navType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'navigation');
		
		if ($type->isEqual($siteType))
			$renderer =& new SiteNodeRenderer;
		else if ($type->isEqual($navType))
			$renderer =& new NavigationNodeRenderer;
		else if (strtolower($type->getDomain()) == 'plugins')
			$renderer =& new PluginNodeRenderer;
		else
			$renderer =& new GenericNodeRenderer;
		
		$renderer->_setAsset($asset);
		
		$assetId =& $asset->getId();
		if (in_array($assetId->getIdString(), NodeRenderer::getActiveNodes()))
			$renderer->setActive(true);
		
		return $renderer;
	}

/*********************************************************
 * Class Methods - other
 *********************************************************/
	
	/**
	 * Answer an array of active Nodes
	 * 
	 * @return array
	 * @access public
	 * @since 1/19/06
	 */
	function getActiveNodes () {
		if (!isset($GLOBALS['active_nodes'])) {
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
			
			
			$GLOBALS['active_nodes'] = array();
			if (isset($_REQUEST['node']) && $_REQUEST['node']) {
				$GLOBALS['active_nodes'][] = $_REQUEST['node'];
				$asset =& $repository->getAsset($idManager->getId($_REQUEST['node']));
				NodeRenderer::traverseActiveUp($asset);
				NodeRenderer::traverseActiveDown($asset);
			} else {
				$asset =& $repository->getAsset($idManager->getId($_REQUEST['site_id']));
				NodeRenderer::traverseActiveDown($asset);
			}
		}
		
		return $GLOBALS['active_nodes'];
	}
	
	/**
	 * Add parents to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveUp ($asset) {
		$type =& $asset->getAssetType();
		$siteType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'site');
		$id =& $asset->getId();
		$GLOBALS['active_nodes'][] = $id->getIdString();
		
		if ($type->isEqual($siteType))
			return;
		
		$parents =& $asset->getParents();
		while ($parents->hasNext())
			NodeRenderer::traverseActiveUp($parents->next());
	}
	
	/**
	 * Add first children to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveDown ($asset) {
		$type =& $asset->getAssetType();
		$navType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'navigation');
								
		if (!$type->isEqual($navType))
			return;
			
		$id =& $asset->getId();
		$GLOBALS['active_nodes'][] = $id->getIdString();
		
		// Traverse down just the first children
		$children =& $asset->getAssets();
		if ($children->hasNext())
			NodeRenderer::traverseActiveDown($children->next());
	}

/*********************************************************
 * Object properties
 *********************************************************/
	
	/**
	 * @var object Asset $_asset;  
	 * @access private
	 * @since 1/19/06
	 */
	var $_asset;
	
	/**
	 * @var boolean $_active; 
	 * @access private
	 * @since 1/19/06
	 */
	var $_active = false;

/*********************************************************
 * Instance Methods - Public
 *********************************************************/
	
	/**
	 * Set the active-state of the Renderer.
	 * 
	 * @param optional boolean $isActive
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function setActive ( $isActive = true ) {
		$this->_active = $isActive;
	}
	
	/**
	 * Get the active-state of the Renderer.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/19/06
	 */
	function isActive () {
		return $this->_active;
	}
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Answer the GUI component for target area
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderTargetComponent ($level = 1) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Answer the url to this Node
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getMyUrl () {
		$id =& $this->_asset->getId();
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL('site', 'view', 
					array(	'site_id' => RequestContext::value('site_id'),
							'node' => $id->getIdString()));
	}
	
/*********************************************************
 * Instance Methods - Private
 *********************************************************/
	/**
	 * Set the asset of this renderer
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access private
	 * @since 1/19/06
	 */
	function _setAsset ( &$asset ) {
		$this->_asset =& $asset;
	}
}

?>