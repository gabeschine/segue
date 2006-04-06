<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.2 2006/04/05 18:03:35 adamfranco Exp $
 */ 

/**
 * The NavBlock component is a hierarchal node that provides a gateway to a 
 * sub-level of the hierarchy.
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.2 2006/04/05 18:03:35 adamfranco Exp $
 */
class XmlNavBlockSiteComponent
	extends XmlBlockSiteComponent
	// implements NavBlockSiteComponent
{
		
	/**
	 * Answers the organizer for this object
	 * 
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function &getOrganizer () {
		$child =& $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'organizer') {
				return $this->_director->getSiteComponent($child->firstChild);
			}
			$child =& $child->nextSibling;
		}
		throwError( new Error("Organizer not found", "XmlSiteComponents"));
	}
	
	/**
	 * Set the organizer for this NavBlock
	 * 
	 * @param object Organizer $organizer
	 * @return voiid
	 * @access public
	 * @since 3/31/06
	 */
	function setOrganizer ( &$organizer ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the target Id
	 * 
	 * @return string Id
	 * @access public
	 * @since 3/31/06
	 */
	function getTargetId () {
		if ($this->_element->hasAttribute('target_id'))
			return $this->_element->getAttribute('target_id');
		else
			throwError( new Error("No target_id available", "XmlSiteComponents"));
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
		return $visitor->visitNavBlock($this);
	}

}

?>