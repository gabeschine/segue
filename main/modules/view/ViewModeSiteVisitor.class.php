<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.68 2008/04/11 19:48:27 achapin Exp $
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

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsHeaderFooterSiteVisitor.class.php");

require_once(MYDIR."/main/modules/view/AttributionPrinter.class.php");
require_once(POLYPHONY."/main/modules/tags/TagAction.abstract.php");

/**
 * The ViewModeVisitor traverses the site hierarchy, rendering each component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.68 2008/04/11 19:48:27 achapin Exp $
 */
class ViewModeSiteVisitor 
	implements SiteVisitor
{
	
	/**
	 * The headerId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no header element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $headerId = null;
	
	/**
	 * The footerId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no footer element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $footerId = null;
	
	/**
	 * The headerCellId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no header element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $headerCellId = null;
	
	/**
	 * The footerCellId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no footer element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $footerCellId = null;
	
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	function __construct () {
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
	public function visitBlock ( BlockSiteComponent $block ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			$false = false;
			return $false;
		}
				
		$guiContainer = new Container (	new YLayout, BLANK, 1);
	
		
		if ($this->showBlockTitle($block)) {
			switch ($block->getHeadingDisplayType()) {
				case 'Heading_1':
					$headingIndex = 1;
					break;
				case 'Heading_2':
					$headingIndex = 2;
					break;
				case 'Heading_3':
					$headingIndex = 3;
					break;
				case 'Heading_4':
				case 'Heading_Sidebar':
					$headingIndex = 4;
					break;
				default:
					throw new OperationFailedException("Unknown Heading Type, '".$block->getHeadingDisplayType()."'.");
			}
			$guiContainer->add(
				new Heading(
					$this->getBlockTitle($block),
					$headingIndex),
			$block->getWidth(), null, null, TOP);
		}
		
		if ($this->showTags($block)) {
			$tagHtml = $this->getTags($block);
		} else {
			$tagHtml = '';
		}
		
		// Gui-component display type and index
		switch ($block->getDisplayType()) {
				case 'Block_Standard':
				case 'Block_Emphasized':
					$class = 'Block';
					$index = STANDARD_BLOCK;
					break;
				case 'Block_Sidebar':
					$class = 'Block';
					$index = SIDEBAR_BLOCK;
					break;
				case 'Block_Alert':
					$class = 'Block';
					$index = ALERT_BLOCK;
					break;
				case 'Header':
					$class = 'Header';
					$index = 1;
					break;
				case 'Footer':
					$class = 'Footer';
					$index = 1;
					break;
				default:
					throw new OperationFailedException("Unknown Display Type, '".$block->getDisplayType()."'.");
			}
		
		// Plugin content		
		$guiContainer->add(
			new $class(
				$this->getPluginContent($block)."\n\n".$tagHtml,
				$index), 
			$block->getWidth(), null, null, TOP);
			
// 		printpre("width:".$block->getWidth());
// 		exit;
		
		return $guiContainer;
	}
	
	/**
	 * Answer true if the block title should be shown.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function showBlockTitle ( $block ) {
		return $block->showDisplayName();
	}
	
	/**
	 * Answer the plugin content for a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/23/07
	 */
	function getPluginContent ( $block ) {
		ob_start();
		$harmoni = Harmoni::instance();
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($block->getAsset());
		
		print "\n<div class='plugin_content'>";
		print $plugin->executeAndGetMarkup($this->showPluginControls());
		print "\n</div>";
		
		if ($block->showComments()) {
			$cm = CommentManager::instance();
			print "\n<div class='comments'>";
			print "\n\t<a href='".$this->getDetailUrl($block->getId())."#";
			$harmoni->request->startNamespace("comments");
			print RequestContext::name('top')."'>";
			$harmoni->request->endNamespace();
			print str_replace("%1", $cm->getNumComments($block->getAsset()), _("Discuss (%1 posts) &raquo;"));
			print "</a>";
			print "\n</div>";
		}

		// print out attribution based on block settings
		$attribution = new AttributionPrinter($block);
		$attributionDisplay = $attribution->getAttributionMarkUp();
		if (!is_null($attributionDisplay) && strlen($attributionDisplay)) {			
			print $attributionDisplay;
		}

		
		if ($plugin->hasExtendedMarkup()) {	
			print "\n<div style='text-align: right;'>";
			print "\n\t<a href='".$this->getDetailUrl($block->getId())."'>";
			print $plugin->getExtendedLinkLabel();
			print "</a>";
			print "\n</div>";
		}
		
		print $this->getHistoryLink($block, $plugin);
		
		print "\n<div class='no_float_spacer'></div>";
		return ob_get_clean();
	}
	
	/**
	 * Answer true if plugin controls should be shown.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function showPluginControls () {
		return false;
	}
	
	/**
	 * Answer the title of a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getBlockTitle ( $block ) {
		return "<a href='".$this->getDetailUrl($block->getId())."'>".$block->getDisplayName()."</a>";
	}

	/**
	 * Answer true if the block tags should be shown.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 4/3/08
	 */
	function showTags ( $block ) {
		$harmoni = Harmoni::instance();		
		$item = TaggedItem::forId($block->getQualifierId(), 'segue');
		if (TagAction::getTagCloud($item->getTags())) {
			return true;
		} else {
			return false;	
		}		
	}

	
	/**
	 * Answer the tags for a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 4/3/08
	 */
	function getTags ( $block ) {
		$harmoni = Harmoni::instance();
		ob_start();	
			
		// Tags
		print "\n\t<div class='tagging_tags_display'>";
		print "Tags: ";
 		SiteDispatcher::passthroughContext();
		$item = TaggedItem::forId($block->getQualifierId(), 'segue');
		print TagAction::getTagCloud($item->getTags(), 'sitetag',
				array(	'font-size: 90%;',
						'font-size: 100%;',
				));
		SiteDispatcher::forgetContext();
		print "\n\t</div>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the detail url of a block
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getDetailUrl ($id) {
		$harmoni = Harmoni::instance();
		return SiteDispatcher::quickURL(
				$harmoni->request->getRequestedModule(),
				$harmoni->request->getRequestedAction(),
				array("node" => $id));
	}
	
	/**
	 * Answer a history link for a block
	 * 
	 * 
	 * @param object BlockSiteComponent $block
	 * @return void
	 * @access public
	 * @since 1/10/08
	 */
	public function getHistoryLink (BlockSiteComponent $block, SeguePluginsAPI $plugin) {
		ob_start();
		if ($plugin->supportsVersioning() && $block->showHistory() && !$this->isHeaderOrFooter($block)) {	
			print "\n<div class='history'>";
			print "\n\t<a href='".$this->getHistoryUrl($block->getId())."'>";
			print _("History");
			print "</a>";
			print "\n</div>";
		}
		return ob_get_clean();
	}
	
	/**
	 * Answer the history url of a block
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getHistoryUrl ($id) {
		$harmoni = Harmoni::instance();
		$harmoni->history->markReturnURL('view_history_'.$id);
		return SiteDispatcher::quickURL('versioning', 'view_history',
				array("node" => $id, 
					'returnModule' => $harmoni->request->getRequestedModule(),
					'returnAction' => $harmoni->request->getRequestedAction()));
	}
	
	/**
	 * Visit a block and return the resulting GUI component. (A menu item)
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object MenuItem 
	 * @access public
	 * @since 4/3/06
	 */
	public function visitBlockInMenu ( BlockSiteComponent $block ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			$false = false;
			return $false;
		}
		
		$pluginManager = Services::getService('PluginManager');
		// Create and return the component
		ob_start();
		
		if ($this->showBlockTitle($block)) {
			$desc = strip_tags($block->getDescription());
			print "<div class='heading' title=\"".$desc."\">"
					.$this->getBlockTitle($block)
					."</div>";
		}
		
		print "<div>".$this->getPluginContent($block)."</div>";
		
		$menuItem = new MenuItem(ob_get_clean(), 1);
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
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		// Since view AZs cascade up, just check at the node.
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($navBlock->getId())))
		{
			return false;
		}
		
		$menuItems = array();
		
		// Create the menu item
		$title = $navBlock->getTitleMarkup();
		if (!$title)
			$title = _("Untitled");
		
		$menuItems[] = new MenuItemLinkWithAdditionalHtml(
							$title,
							$this->getUrlForComponent($navBlock->getId()),
							$navBlock->isActive(),
							1,
							null,
							null,
							$navBlock->getDescription(),
							$this->getAdditionalNavHTML($navBlock));
		
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($navBlock->isActive()) {
			$childOrganizer = $navBlock->getOrganizer();
			$childGuiComponent = $childOrganizer->acceptVisitor($this);
			
			if (isset($this->_emptyCellContainers[$navBlock->getTargetId()])) {
				$this->_emptyCellContainers[$navBlock->getTargetId()]->insertAtPlaceholder(
					$this->_emptyCellPlaceholders[$navBlock->getTargetId()],
					$childGuiComponent, $childOrganizer->getWidth(), '100%', null, TOP);
					
				unset($this->_emptyCellContainers[$navBlock->getTargetId()],
					$this->_emptyCellPlaceholders[$navBlock->getTargetId()]);
			} else {
				$this->_missingTargets[$navBlock->getTargetId()] = $childGuiComponent;
				$this->_missingTargetWidths[$navBlock->getTargetId()] = $childOrganizer->getWidth();
			}
			
			$nestedMenuOrganizer = $navBlock->getNestedMenuOrganizer();
			if (!is_null($nestedMenuOrganizer)) {
				$this->_menuNestingLevel++;
				$menuItems[] = $nestedMenuOrganizer->acceptVisitor($this);
			} else {
				$this->_menuNestingLevel = 0;
			}
		}
		
		// return the menu items
		return $menuItems;
	}
	
	/**
	 * Answer additional HTML to go after the nav title.
	 * 
	 * @param object  NavBlockSiteComponent $navBlock
	 * @return string
	 * @access public
	 * @since 9/21/07
	 */
	public function getAdditionalNavHTML (NavBlockSiteComponent $navBlock) {
		return '';
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
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
				
		$childOrganizer = $siteNavBlock->getOrganizer();
		$childGuiComponent = $childOrganizer->acceptVisitor($this);
				
		// Check completeness and render any nodes still waiting for targets
		try {
			foreach (array_keys($this->_missingTargets) as $targetId) {
				if (!isset($this->_emptyCellContainers[$targetId])) {
					throw new OperationFailedException("Target id '$targetId' was not found or is not empty.", 390134);
				}
				if (!is_object($this->_emptyCellContainers[$targetId])) {
					ob_start();
					var_dump($this->_emptyCellContainers[$targetId]);
					throwError(new Error("Expecting object, found '".ob_get_clean()."'.", __CLASS__));
				}
				
				if (isset($this->_missingTargetWidths[$targetId]) && $this->_missingTargetWidths[$targetId])
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
		} catch (OperationFailedException $e) {
			/*********************************************************
			 * Try to allow recovery if we have just one
			 * missing empty cell and one menu missing a target.
			 *********************************************************/
			if ($e->getCode() == 390134 && count($this->_missingTargets) == 1 && count($this->_emptyCellContainers) == 1) {
				print "<div class='error'>";
				printpre("Inconsistant state. Looking for empty cell: ");
				printpre(array_keys($this->_missingTargets));
				printpre("Found empty cell: ");
				printpre(array_keys($this->_emptyCellContainers));
				print("Using arrange mode, drag the Layout container for your section/page to fix this state.");
				print "Please report what you did to cause this state at the <a href='https://sourceforge.net/tracker/index.php?func=detail&amp;aid=2046491&amp;group_id=82171&amp;atid=565234'>bug tracker</a> or to Adam Franco (afranco@middlebury.edu).";
				print "</div>";
				$emptyCellId = key($this->_emptyCellContainers);
				$targetId = key($this->_missingTargets);
				
				if (!is_object($this->_emptyCellContainers[$emptyCellId])) {
					ob_start();
					var_dump($this->_emptyCellContainers[$emptyCellId]);
					throwError(new Error("Expecting object, found '".ob_get_clean()."'.", __CLASS__));
				}
				
				if (isset($this->_missingTargetWidths[$targetId]) && $this->_missingTargetWidths[$targetId])
					$width = $this->_missingTargetWidths[$targetId];
				else
					$width = null;
				
				$this->_emptyCellContainers[$emptyCellId]->insertAtPlaceholder(
					$this->_emptyCellPlaceholders[$emptyCellId],
					$this->_missingTargets[$targetId], 
					$width, '100%', null, TOP);
					
					
				unset($this->_emptyCellContainers[$emptyCellId]);
				unset($this->_emptyCellPlaceholders[$emptyCellId]);
				unset($this->_missingTargets[$targetId]);
				unset($this->_missingTargetWidths[$targetId]);
				
			} else {
				throw $e;
			}
		}
		
		// returning the entire site in GUI component object tree.
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
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		$guiContainer = new Container (new TableLayout($organizer->getNumColumns()),
										BLANK,
										1);
		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$childComponent = $child->acceptVisitor($this);
				if ($childComponent)
					$guiContainer->add($childComponent, $child->getWidth(), null, null, TOP );
				else
					$guiContainer->add(new UnstyledBlock("<div style='height: 0px;'> &nbsp; </div>"), null, "0px", null, TOP );
			} else {
				$this->_emptyCellContainers[$organizer->getId().'_cell:'.$i] = $guiContainer;
				$this->_emptyCellPlaceholders[$organizer->getId().'_cell:'.$i] = $guiContainer->addPlaceholder(new UnstyledBlock("<div style='height: 0px;'> &nbsp; </div>"));
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
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
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
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		$numCells = $organizer->getTotalNumberOfCells();
		
		if ($organizer->getNumRows() == 0)
			$cellsPerPage = $numCells;
		// If we are limiting to a number of rows, we are paginating.
		else
			$cellsPerPage = $organizer->getNumColumns() * $organizer->getNumRows();
		
		$childGuiComponents = array();
		foreach ($organizer->getSortedSubcomponents() as $child) {
			if ($child) {
				$childGuiComponent = $child->acceptVisitor($this);
				// Filter out false entries returned due to lack of authorization
				if ($childGuiComponent)
					$childGuiComponents[] = $childGuiComponent;
			}
		}
		if (count($childGuiComponents)) {
			$resultPrinter = new ArrayResultPrinter($childGuiComponents,
										$organizer->getNumColumns(), $cellsPerPage);
			$resultPrinter->setRenderDirection($organizer->getDirection());
			$resultPrinter->setNamespace('pages_'.$organizer->getId());
			$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
			
			return $resultPrinter->getLayout();
		} else {
			return null;
		}
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
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {	
		// Choose layout direction based on number of rows
		if ($this->_menuNestingLevel) {
			$layout = new YLayout();
		} else if ($organizer->getDirection() == "Left-Right/Top-Bottom") {
			$layout = new XLayout();
		} else if ($organizer->getDirection() == "Right-Left/Top-Bottom") {
			$layout = new XLayout();
			$layout->setRenderDirection("Right-Left/Top-Bottom");
		} else {
			$layout = new YLayout();
		}
		
		if ($this->_menuNestingLevel)
			$guiContainer = new SubMenu ( $layout, $this->_menuNestingLevel);
		else {
			switch ($organizer->getDisplayType()) {
				case 'Menu_Left':
					$menuIndex = MENU_LEFT;
					break;
				case 'Menu_Right':
					$menuIndex = MENU_RIGHT;
					break;
				case 'Menu_Top':
					$menuIndex = MENU_TOP;
					break;
				case 'Menu_Bottom':
					$menuIndex = MENU_BOTTOM;
					break;
				default:
					throw new OperationFailedException("Unknown Menu DisplayType, '".$organizer->getDisplayType()."'.");
			}
			$guiContainer = new Menu ( $layout, $menuIndex);
		}
		
		$hasChildComponents = false;
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if ($child) {
				$childGuiComponents = $child->acceptVisitor($this, true);
				if ($childGuiComponents === false || (is_array($childGuiComponents) && !count($childGuiComponents))) {
					// do nothing
				} else if (is_array($childGuiComponents)) {
					$hasChildComponents = true;
					// wrap the menu item if needed
					$this->addFlowChildWrapper($organizer, $i, $childGuiComponents[0]);
					
					// Add each of the the menuItems/submenus
					foreach (array_keys($childGuiComponents) as $key)
						$guiContainer->add($childGuiComponents[$key]);
				} else {
					$hasChildComponents = true;
					$guiContainer->add($this->addFlowChildWrapper($organizer, $i, $childGuiComponents));
				}
			}
		}
		
		// Add a placeholder if no content exists so that the screen doesn't stretch
		if (!$hasChildComponents) {	
			$noticeComponent = $this->getMenuTargetPlaceholder($organizer);
			if (isset($this->_emptyCellContainers[$organizer->getTargetId()])) {
				$this->_emptyCellContainers[$organizer->getTargetId()]->insertAtPlaceholder(
					$this->_emptyCellPlaceholders[$organizer->getTargetId()],
					$noticeComponent, null, null, null, TOP);
					
				unset($this->_emptyCellContainers[$organizer->getTargetId()],
					$this->_emptyCellPlaceholders[$organizer->getTargetId()]);
			} else {
				$this->_missingTargets[$organizer->getTargetId()] = $noticeComponent;
				$this->_missingTargetWidths[$organizer->getTargetId()] = null;
			}
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer a placeholder for a menu target
	 * 
	 * @param object MenuOrganizerSiteComponent $organizer
	 * @return Component
	 * @access protected
	 * @since 12/18/07
	 */
	protected function getMenuTargetPlaceholder (MenuOrganizerSiteComponent $organizer) {
		return new UnstyledBlock(' &nbsp; ', 1);
	}
	
	/**
	 * Add any needed markup to a gui component that is the child of a flow organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $organizer
	 * @param integer $cellIndex
	 * @param object Component $guiComponent
	 * @access protected
	 * @since 12/18/07
	 */
	protected function addFlowChildWrapper (FlowOrganizerSiteComponent $organizer, $cellIndex, Component $guiComponent) {
		return $guiComponent;
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
		$harmoni = Harmoni::instance();
		return SiteDispatcher::quickURL(
			$harmoni->request->getRequestedModule(), 
			$harmoni->request->getRequestedAction(),
			array("node" => $id));
	}
	
	/**
	 * Answer true if this block is in the header or footer
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return boolean
	 * @access public
	 * @since 1/10/08
	 */
	public function isHeaderOrFooter (SiteComponent $siteComponent) {
		if (!isset($this->isHeaderFooterVisitor))
			$this->isHeaderFooterVisitor = new IsHeaderFooterSiteVisitor;
			
		return $siteComponent->acceptVisitor($this->isHeaderFooterVisitor);
	}
	
	/**
	 * Answer the root of the site
	 * 
	 * @param SiteComponent $siteComponent
	 * @return SiteNavBlockSiteComponent
	 * @access public
	 * @since 9/24/07
	 */
	public function getSiteNavBlock (SiteComponent $siteComponent) {
		$parent = $siteComponent->getParentComponent();
		
		if (is_null($parent))
			return $siteComponent;
		else
			return $this->getSiteNavBlock($parent);
	}
}

?>
