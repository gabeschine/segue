<?php
/**
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.3 2007/09/04 15:05:32 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/SiteVisitor.interface.php");

/**
 * Return a bread-crumbs string
 * 
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.3 2007/09/04 15:05:32 adamfranco Exp $
 */
class BreadCrumbsVisitor 
	implements SiteVisitor
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function BreadCrumbsVisitor () {
		$this->_links = array();
		$this->_separator = " &raquo; ";
	}
	
	/**
	 * Add a link for a node
	 * 
	 * @param object SiteComponent $node
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function addLink ( $node ) {
		$harmoni = Harmoni::instance();
		$this->_links[] = "<a href='"
							.$harmoni->request->quickUrl(
								$harmoni->request->getRequestedModule(),
								$harmoni->request->getRequestedAction(),
								array('node' => $node->getId()))
							."'>".$node->getDisplayName()."</a>";
	}
		
	/**
	 * Visit a block 
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitBlock ( BlockSiteComponent $block ) {
		$this->addLink($block);
		
		$parent = $block->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a nav block
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {		
		return $this->visitBlock($navBlock);
	}
	
	/**
	 * Visit a SiteNavBlock
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		$this->addLink($siteNavBlock);
		
		$val = implode(
					$this->_separator,
					array_reverse($this->_links));
		
		return $val;
	}

	/**
	 * Visit a fixed organizer
	 *
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {		
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a fixed organizer 
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a flow organizer
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a menu organizer
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {	
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
}

?>