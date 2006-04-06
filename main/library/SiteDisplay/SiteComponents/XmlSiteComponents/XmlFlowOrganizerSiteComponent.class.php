<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFlowOrganizerSiteComponent.class.php,v 1.3 2006/04/06 14:19:06 cws-midd Exp $
 */ 

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFlowOrganizerSiteComponent.class.php,v 1.3 2006/04/06 14:19:06 cws-midd Exp $
 */
class XmlFlowOrganizerSiteComponent
	extends XmlOrganizerSiteComponent 
	// implements FlowOrganizerSiteComponent
{
	
	/**
	 * Answer the ordered indices.
	 * 
 	 * Currently Ignoring Direction and assuming left-right/top-bottom
	 * @return array
	 * @access public
	 * @since 4/3/06
	 */
	function getVisibleOrderedIndices () {
		$array = array();
		for ($i = 0; $i < $this->getNumberOfVisibleCells(); $i++) {
			$array[] = $i;
		}
		return $array;
	}
	
	/**
	 * Answer the number of cells in this organizer that are visible (some may
	 * be empty).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getNumberOfVisibleCells () {
		$max = $this->getMaxVisible();
		return (!$max || $this->_element->childCount < $max)
			?$this->_element->childCount:$max;
	}
	
	/**
	 * Answer the maximum number of cells that can be displayed before overflowing
	 * (i.e. to pagination, archiving, hiding, etc).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getMaxVisible () {
		if ($this->_element->hasAttribute("maxVisible"))
			return $this->_element->getAttribute("maxVisible");
		else
			return 0;
	}
	
	/**
	 * Update the maximum number of cells that can be displayed before overflowing
	 * (i.e. to pagination, archiving, hiding, etc).
	 * 
	 * @param integer $newMaxVisible Greater than or equal to 1
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateMaxVisible ( $newMaxVisible ) {
		$this->_element->setAttribute('maxVisible', $newMaxVisible);
	}
	
	/**
	 * Get the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getOverflowStyle () {
		if ($this->_element->hasAttribute("overflowStyle"))
			return $this->_element->getAttribute("overflowStyle");
		return "Paginate";
	}
	
	/**
	 * Update the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @param string $overflowStyle
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateOverflowStyle ( $overflowStyle ) {
		$this->_element->setAttribute("overflowStyle", $overflowStyle);
	}
	
	/**
	 * Add a subcomponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function addSubcomponent ( &$siteComponent ) {
		$cell =& new DOMIT_Element("cell");
		$cell->appendChild($siteComponent->getElement());
		$this->_element->appendNode($cell);
	}
	
	/**
	 * Move the contents of cellOneIndex before cellTwoIndex
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function moveBefore ( $cellOneIndex, $cellTwoIndex ) {
		// child DOMIT_Elements in an array
		$children =& $this->_element->childNodes;

		$temp =& $children[$cellOneIndex];

		$this->_element->removeChild($children[$cellOneIndex]);
		
		// indices change when child is removed in front of cellTwoIndex
		if ($cellTwoIndex > $cellOneIndex)
			$this->_element->insertBefore($temp, $children[$cellTwoIndex - 1]);
		else
			$this->_element->insertBefore($temp, $children[$cellTwoIndex]);
	}
	
	/**
	 * Move the contents of cellIndex to the end of the organizer
	 * 
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function moveToEnd ( $cellIndex ) {
		$temp =& $this->_element->childNodes[$cellIndex];
		$this->_element->removeChild($temp);
		$this->_element->appendChild($temp);
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
		return $visitor->visitFlowOrganizer($this);
	}
}

?>