<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteComponent.class.php,v 1.5 2007/01/17 21:21:59 adamfranco Exp $
 */ 

/**
 * The site component is the root abstract class that all site components inherit
 * from.
 * 
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteComponent.class.php,v 1.5 2007/01/17 21:21:59 adamfranco Exp $
 */
class AssetSiteComponent 
	// implements SiteComponent
{

	/**
	 * Constructor
	 * 
	 * @param object XmlSiteDirector $director
	 * @param object Domit_Node $element
	 * @return object XmlSiteNavBlockSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function AssetSiteComponent ( &$director, &$asset, &$element) {
		ArgumentValidator::validate($director, ExtendsValidatorRule::getRule('AssetSiteDirector'));
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule('Asset'));
		ArgumentValidator::validate($element, OptionalRule::getRule(
			ExtendsValidatorRule::getRule('DOMIT_Node')));
		
		$this->_director =& $director;
		$this->_asset =& $asset;
		$this->_element =& $element;
	}
	
	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		
	}
	
	/**
	 * Delete any stored data needed as part of the delete process
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function deleteAndCleanUpData () {
		
	}
	
	/**
	 * Answer the asset that corresponds to this block
	 * 
	 * @return object Asset
	 * @access public
	 * @since 1/12/07
	 */
	function &getAsset () {
		return $this->_asset;
	}
		
	/**
	 * Answer the Id
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getId () {
		if ($this->_element->hasAttribute('id')) {
			$assetId =& $this->_asset->getId();
			return $assetId->getIdString()."----".$this->_element->getAttribute('id');
		} else
			throwError( new Error("No id available", "XmlSiteComponents"));
	}
	
	/**
	 * Answer this component's director
	 * 
	 * @return ref object SiteDirector
	 * @access public
	 * @since 4/18/06
	 */
	function &getDirector () {
		return $this->_director;
	}
	
	/**
	 * Answer the DOMIT_Element associated with this SiteComponent
	 * 
	 * @return object DOMIT_Element
	 * @access public
	 * @since 4/5/06
	 */
	function &getElement () {
		return $this->_element;
	}
	
	/**
	 * Answer the parent component
	 * 
	 * @return object SiteComponent
	 * @access public
	 * @since 4/10/06
	 */
	function &getParentComponent () {
		$parentElement =& $this->_director->_getParentWithId($this->getElement());
		if ($parentElement)
			return $this->_director->getSiteComponentFromXml($this->_asset, $parentElement);
		else if ($this->_asset)
			return $this->_director->getSiteComponentFromAsset($this->_asset);
		else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Answer true if this component is active
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/31/06
	 */
	function isActive () {
		return $this->_director->isActive($this->getId());
	}
	
	/**
	 * Answer the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 1/17/07
	 */
	function showDisplayNames () {
		$element =& $this->getElement();
		
		if (!$element->hasAttribute('showDisplayNames'))
			return 'default';
		
		if ($element->getAttribute('showDisplayNames') == 'true')
			return true;
		else if ($element->getAttribute('showDisplayNames') == 'false')
			return false;
		else
			return 'default';
	}
	
	/**
	 * change the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $showDisplayNames true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	function updateShowDisplayNames ( $showDisplayNames ) {
		$element =& $this->getElement();
		
		if ($showDisplayNames === true || $showDisplayNames === 'true')
			$element->setAttribute('showDisplayNames', 'true');
		else if ($showDisplayNames === false || $showDisplayNames === 'false')
			$element->setAttribute('showDisplayNames', 'false');
		else
			$element->setAttribute('showDisplayNames', 'default');
		
		$this->_saveXml();
	}
	
	/**
	 * Answer true if the display name should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	function showDisplayName () {
		if ($this->showDisplayNames() === 'default') {
			$parent =& $this->getParentComponent();
			
			if ($parent)
				return $parent->showDisplayName();
			// Base case if none is specified anywhere in the hierarchy
			else
				return true;
		} else {
			return $this->showDisplayNames();
		}
	}
	
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay"));
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleDestinationsForPossibleAddition () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay"));
	}
	
/*********************************************************
 * Private methods
 *********************************************************/
	
	/**
	 * Store changes to our asset's XML document
	 * 
	 * @return void
	 * @access private
	 * @since 10/5/06
	 */
	function _saveXml () {
		printpre("<hr/><h2>Saving AssetXML for ".get_class($this)." ".$this->getId().": </h2>");
		print("<h3>Previous XML</h3>");
		$oldContent =& $this->_asset->getContent();
		printpre(htmlentities($oldContent->asString()));
		print("<h3>New XML</h3>");
		$element =& $this->getElement();
		printpre($element->ownerDocument->toNormalizedString(true));
// 		exit;
		
		$this->_asset->updateContent(
			Blob::fromString(
				$element->ownerDocument->toNormalizedString()));
	}
}

?>