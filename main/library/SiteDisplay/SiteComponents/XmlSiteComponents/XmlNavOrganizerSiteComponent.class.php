<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavOrganizerSiteComponent.class.php,v 1.1 2006/04/10 19:51:48 adamfranco Exp $
 */ 

/**
 * The Organizer that is the direct child of a NavBlock.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavOrganizerSiteComponent.class.php,v 1.1 2006/04/10 19:51:48 adamfranco Exp $
 */
class XmlNavOrganizerSiteComponent
	extends XmlFixedOrganizerSiteComponent 
	// implements NavOrganizerSiteComponent
{
	
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		$parent =& $this->getParentComponent();
		return $parent->getDisplayName()._(" <em>Organizer</em>");
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
		return $visitor->visitNavOrganizer($this);
	}
}

?>