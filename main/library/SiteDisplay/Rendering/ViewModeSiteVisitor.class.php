<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.30 2007/04/13 19:59:16 adamfranco Exp $
 */ 

require_once(HARMONI."GUIManager/Components/Header.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/SubMenu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");
require_once(HARMONI."GUIManager/Container.class.php");

require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/TableLayout.class.php");

/**
 * The ViewModeVisitor traverses the site hierarchy, rendering each component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.30 2007/04/13 19:59:16 adamfranco Exp $
 */
class ViewModeSiteVisitor {
		
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	function ViewModeSiteVisitor () {
		/*********************************************************
		 * cell placeholders: 
		 *		target_id => [empty] GUI container object.
		 *********************************************************/
		$this->_emptyCellContainers = array();
		$this->_emptyCellPlaceholders = array();
		
		/*********************************************************
		 * Contents of targets which have not yet been traversed-to
		 * 		target_id => GUI component to place in target.
		 *********************************************************/
		$this->_missingTargets = array();
		$this->_missingTargetWidths = array();
		
		$this->_menuNestingLevel = 0;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitBlock ( &$block ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			$false = false;
			return $false;
		}
		
		$guiContainer =& new Container (	new YLayout, BLOCK, 1);
		
		$pluginManager =& Services::getService('PluginManager');
		
		if ($block->showDisplayName()) {
			$guiContainer->add(
				new Heading(
					$pluginManager->getPluginTitleMarkup($block->getAsset(), false), 
					2),
			$block->getWidth(), null, null, TOP);
		}
		
		$guiContainer->add(
			new Block(
				$pluginManager->getPluginText($block->getAsset(), false),
				STANDARD_BLOCK), 
			$block->getWidth(), null, null, TOP);
		
		return $guiContainer;
	}
	
	/**
	 * Visit a block and return the resulting GUI component. (A menu item)
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object MenuItem 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitBlockInMenu ( &$block ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			$false = false;
			return $false;
		}
		
		$pluginManager =& Services::getService('PluginManager');
		// Create and return the component
		ob_start();
		
		if ($block->showDisplayName()) {
			print "<div style='font-weight: bold; font-size: large;' title=\"".$block->getDescription()."\">"
					.$pluginManager->getPluginTitleMarkup($block->getAsset(), false)
					."</div>";
		}
		
		print "<div>".$pluginManager->getPluginText($block->getAsset(), false)."</div>";
		
		$menuItem =& new MenuItem(ob_get_clean(), 1);
		return $menuItem;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return ref array
	 * @access public
	 * @since 4/3/06
	 */
	function &visitNavBlock ( &$navBlock ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");	
		if (!$authZ->isUserAuthorizedBelow(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($navBlock->getId())))
		{
			$false = false;
			return $false;
		}
		
		$menuItems = array();
		
		// Create the menu item
		$menuItems[] =& new MenuItemLinkWithAdditionalHtml(
							$navBlock->getTitleMarkup(),
							$this->getUrlForComponent($navBlock->getId()),
							$navBlock->isActive(),
							1,
							null,
							null,
							$navBlock->getDescription(),
							'');
		
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($navBlock->isActive()) {
			$childOrganizer =& $navBlock->getOrganizer();
			$childGuiComponent =& $childOrganizer->acceptVisitor($this);
			
			if (isset($this->_emptyCellContainers[$navBlock->getTargetId()])) {
				$this->_emptyCellContainers[$navBlock->getTargetId()]->insertAtPlaceholder(
					$this->_emptyCellPlaceholders[$navBlock->getTargetId()],
					$childGuiComponent, $childOrganizer->getWidth(), '100%', null, TOP);
					
				unset($this->_emptyCellContainers[$navBlock->getTargetId()],
					$this->_emptyCellPlaceholders[$navBlock->getTargetId()]);
			} else {
				$this->_missingTargets[$navBlock->getTargetId()] =& $childGuiComponent;
				$this->_missingTargetWidths[$navBlock->getTargetId()] = $childOrganizer->getWidth();
			}
			
			$nestedMenuOrganizer =& $navBlock->getNestedMenuOrganizer();
			if (!is_null($nestedMenuOrganizer)) {
				$this->_menuNestingLevel++;
				$menuItems[] =& $nestedMenuOrganizer->acceptVisitor($this);
			} else {
				$this->_menuNestingLevel = 0;
			}
		}
		
		// return the menu items
		return $menuItems;
	}
	
	/**
	 * Visit a SiteNavBlock and return the site GUI component that corresponds to
	 *	it.
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitSiteNavBlock ( &$siteNavBlock ) {
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		$childOrganizer =& $siteNavBlock->getOrganizer();
		$childGuiComponent =& $childOrganizer->acceptVisitor($this);
		
		// Check completeness and render any nodes still waiting for targets
		foreach (array_keys($this->_missingTargets) as $targetId) {
			if (!is_object($this->_emptyCellContainers[$targetId]))
				throwError(new Error("Expecting object, found '".$this->_emptyCellContainers[$targetId]."'.", __CLASS__));
			
			if ($this->_missingTargetWidths[$targetId])
				$width = $this->_missingTargetWidths[$targetId];
			else
				$width = null;
			
			$this->_emptyCellContainers[$targetId]->insertAtPlaceholder(
				$this->_emptyCellPlaceholders[$targetId],
				$this->_missingTargets[$targetId], 
				$width, '100%', null, TOP);
				
			unset($this->_emptyCellContainers[$targetId]);
			unset($this->_emptyCellPlaceholders[$targetId]);
			unset($this->_missingTargets[$targetId]);
			unset($this->_missingTargetWidths[$targetId]);
		}
		
		// returning the entire site in GUI component object tree.
// 		printpre($this);
// 		print "<hr/>";
// 		printpre($siteNavBlock->_director->_activeNodes);
		return $childGuiComponent;
	}

	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitFixedOrganizer ( &$organizer ) {
		$guiContainer =& new Container (new TableLayout($organizer->getNumColumns()),
										BLANK,
										1);
		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$childComponent =& $child->acceptVisitor($this);
				if ($childComponent)
					$guiContainer->add($childComponent, $child->getWidth(), null, null, TOP );
			} else {
				$this->_emptyCellContainers[$organizer->getId().'_cell:'.$i] =& $guiContainer;
				$this->_emptyCellPlaceholders[$organizer->getId().'_cell:'.$i] = $guiContainer->addPlaceholder();
			}
		}
		
		return $guiContainer;
	}
	
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitNavOrganizer ( &$organizer ) {
		return $this->visitFixedOrganizer($organizer);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitFlowOrganizer( &$organizer ) {
		$numCells = $organizer->getTotalNumberOfCells();
		
		if ($organizer->getNumRows() == 0)
			$cellsPerPage = $numCells;
		// If we are limiting to a number of rows, we are paginating.
		else
			$cellsPerPage = $organizer->getNumColumns() * $organizer->getNumRows();
		
		$childGuiComponents = array();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			$childGuiComponent =& $child->acceptVisitor($this);
			// Filter out false entries returned due to lack of authorization
			if ($childGuiComponent)
				$childGuiComponents[] =& $childGuiComponent;
		}
		
		$resultPrinter =& new ArrayResultPrinter($childGuiComponents,
									$organizer->getNumColumns(), $cellsPerPage);
		$resultPrinter->setRenderDirection($organizer->getDirection());
		$resultPrinter->setNamespace('pages_'.$organizer->getId());
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		
		return $resultPrinter->getLayout();
	}
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitMenuOrganizer ( &$organizer ) {	
		// Choose layout direction based on number of rows
		if ($this->_menuNestingLevel) {
			$layout =& new YLayout();
		} else if ($organizer->getDirection() == "Left-Right/Top-Bottom") {
			$layout =& new XLayout();
		} else if ($organizer->getDirection() == "Right-Left/Top-Bottom") {
			$layout =& new XLayout();
			$layout->setRenderDirection("Right-Left/Top-Bottom");
		} else {
			$layout =& new YLayout();
		}
		
		if ($this->_menuNestingLevel)
			$guiContainer =& new SubMenu ( $layout, $this->_menuNestingLevel);
		else
			$guiContainer =& new Menu ( $layout, 1);
		
		$hasChildComponents = false;
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			$childGuiComponents =& $child->acceptVisitor($this, true);
			if ($childGuiComponents === false || (is_array($childGuiComponents) && !count($childGuiComponents))) {
				// do nothing
			} else if (is_array($childGuiComponents)) {
				$hasChildComponents = true;
				foreach (array_keys($childGuiComponents) as $key)
					$guiContainer->add($childGuiComponents[$key]);
			} else {
				$hasChildComponents = true;
				$guiContainer->add($childGuiComponents);
			}
		}
// 		if ($hasChildComponents)
			return $guiContainer;
		
		$false = false;
		return $false;
	}
	
	/**
	 * Answer the Url for this component id.
	 *
	 * Note: this is clunky that this object has to know about harmoni and 
	 * what action to target. Maybe rewrite...
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 4/4/06
	 */
	function getUrlForComponent ( $id ) {
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL('ui2', "view", array("node" => $id));
	}
}

?>