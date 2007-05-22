<?php
/**
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailViewModeSiteVisitor.class.php,v 1.1 2007/05/18 20:00:58 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/ViewModeSiteVisitor.class.php");

/**
 * Render the 'detail' view of a node and its discusions.
 * 
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailViewModeSiteVisitor.class.php,v 1.1 2007/05/18 20:00:58 adamfranco Exp $
 */
class DetailViewModeSiteVisitor
	extends ViewModeSiteVisitor
{
		
	/**
	 * Constructor.
	 * 
	 * @param object BlockSiteComponent $node
	 * @return void
	 * @access public
	 * @since 5/18/07
	 */
	function DetailViewModeSiteVisitor ( &$node ) {
		$this->ViewModeSiteVisitor();
		
		$this->_node =& $node;
		$this->_flowOrg =& $node->getParentComponent();
		$this->_flowOrgId = $this->_flowOrg->getId();
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitTargetBlock () {
		$block =& $this->_node;
		
		$guiContainer =& parent::visitBlock($block);
		
		if ($guiContainer) {
			$guiContainer->add(
					new Heading(
						_("Discussions:"),
						3),
				$block->getWidth(), null, null, TOP);
			
// 			$guiContainer->add(
// 				$this->getDiscussions($block),
// 				$block->getWidth(), null, null, TOP);
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer the title of a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getBlockTitle ( &$block ) {
		if ($block->getId() == $this->_node->getId())
			return $block->getDisplayName()." &raquo; "._("Detail");
		else
			return parent::getBlockTitle($block);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 5/18/07
	 */
	function &visitFlowOrganizer( &$organizer ) {
		if ($organizer->getId() == $this->_flowOrgId) {
			return $this->visitTargetBlock();
		} else {
			return parent::visitFlowOrganizer($organizer);
		}
	}
}

?>