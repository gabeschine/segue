<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetFlowOrganizerSiteComponent.class.php,v 1.19 2008/03/19 19:33:47 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/FlowOrganizerSiteComponent.abstract.php");

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetFlowOrganizerSiteComponent.class.php,v 1.19 2008/03/19 19:33:47 adamfranco Exp $
 */
class AssetFlowOrganizerSiteComponent
	extends AssetOrganizerSiteComponent 
	implements FlowOrganizerSiteComponent
{

	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		parent::populateWithDefaults();
		$this->updateNumRows(0);
		$this->updateNumColumns(1);
		$this->updateOverflowStyle('Paginate');
	}
	
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("<em>Content Container</em>");
	}
	
	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 11/09/07
	 */
	function getComponentClass () {
		return "FlowOrganizer";
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
		$this->_saveXml();
	}
	
	/**
	 * Add a subcomponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function addSubcomponent ( BlockSiteComponent $siteComponent ) {
		$cell = $this->_element->ownerDocument->createElement('cell');
		$snippet = $this->_element->ownerDocument->createElement($siteComponent->getComponentClass());
		$snippet->setAttribute('id', $siteComponent->getId());
		$cell->appendChild($snippet);
		$this->_element->appendChild($cell);
		// this is only for single page load deletes (testing)
		$this->_getChildComponents(true);
		
		$this->_saveXml();
		
		if (!$this->isOrganizer($siteComponent->_asset->getAssetType()))
			$this->_asset->addAsset($siteComponent->_asset->getId());
	}
	
	/**
	 * Add a subcomponent to a given cell and push the later elements towards the
	 * end.
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return string The Id of the original cell
	 * @access public
	 * @since 3/31/06
	 */
	public function putSubcomponentInCell ( SiteComponent $siteComponent, $cellIndex ) {
		ArgumentValidator::validate($siteComponent, ExtendsValidatorRule::getRule('BlockSiteComponent'));
		
		$currentIndex = $this->getCellForSubcomponent($siteComponent);
		if ($currentIndex === FALSE) {			
			// A cell will have no old parent if it is newly created.
			if ($oldParent = $siteComponent->getParentComponent()) {
				$oldCellId = $oldParent->getId()."_cell:".$oldParent->getCellForSubcomponent($siteComponent);
				$oldParent->detatchSubcomponent($siteComponent);
			} else {
				$oldCellId = null;
			}
			
			$this->addSubcomponent($siteComponent);
			$currentIndex = $this->getCellForSubcomponent($siteComponent);
		} else {
			$oldCellId = $this->getId()."_cell:".$currentIndex;
		}
		
		if ($currentIndex == $cellIndex) {
			// do nothing
		} else if ($currentIndex > $cellIndex)
			$this->moveBefore($currentIndex, $cellIndex);
		else
			$this->moveBefore($currentIndex, $cellIndex + 1);
		
		return $oldCellId;
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
		$children = $this->_element->childNodes;

		$temp1 = $children->item($cellOneIndex);
		if (is_object($children->item($cellTwoIndex)))
			$temp2 = $children->item($cellTwoIndex);
		else
			$temp2 = null;

		$this->_element->removeChild($temp1);
		
		$this->_element->insertBefore($temp1, $temp2);
		
		$this->_saveXml();
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
		$temp = $this->_element->childNodes->item($cellIndex);
		$this->_element->removeChild($temp);
		$this->_element->appendChild($temp);
		
		$this->_saveXml();
	}

	/**
	 * Remove a subcomponent, but don't delete it from the director completely.
	 * 
	 * @param object SiteComponent $subcomponent
	 * @return void
	 * @access public
	 * @since 4/12/06
	 */
	function detatchSubcomponent ( $subcomponent ) {
		$cellIndex = $this->getCellForSubcomponent($subcomponent);
		
		$cell = $this->_element->firstChild;
		while ($cellIndex) {
			$cell = $cell->nextSibling;
			$cellIndex--;
		}
		$this->_element->removeChild($cell);
		
		$this->_saveXml();
		
		if (isset($siteComponent->_asset) && !$this->isOrganizer($siteComponent->_asset->getAssetType()))
			$this->_asset->removeAsset($subcomponent->_asset->getId(), true);
	}
	
	/**
	 * Delete the subcomponent located in organizer cell $i
	 * 
	 * @param integer $i
	 * @return void
	 * @access public
	 * @since 4/3/06
	 */
	function deleteSubcomponentInCell ( $i ) {
		$cell = $this->_element->firstChild;
		while ($i) {
			$cell = $cell->nextSibling;
			$i--;
		}
		$this->_element->removeChild($cell);
		$this->_director->deleteSiteComponent($this->getSubcomponentForCell($i));
		unset($this->_childComponents);
		
		$this->_saveXml();
	}	
	
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) {
		return $visitor->visitFlowOrganizer($this);
	}
	
	/**
	 * Answer true if there is a level of menus below the current one.
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/22/06
	 */
	function subMenuExists () {
		// Flow organizers can't contain menus.
		return false;
	}
	
	/**
	 * Answer true if the type passed corresponds to an organizer
	 * 
	 * @param object Type $type
	 * @return boolean
	 * @access public
	 * @since 1/12/07
	 */
	function isOrganizer ( $type ) {
		foreach ($this->_director->organizerTypes as $orgType) {
			if ($type->isEqual($orgType))
				return true;	
		}
		
		return false;
	}
	
	/**
	 * Answer a sorted array of sub-components. The order will be based on the
	 * organizer's sortMethod setting.
	 *
	 * @return array of Components
	 * @access public
	 * @since 1/11/08
	 */
	public function getSortedSubcomponents () {
		$subcomponents = array();
		$sortKeys = array();
		$numCells = $this->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$subcomponents[] = $this->getSubcomponentForCell($i);
		}
		
		switch ($this->sortMethod()) {
			case 'custom':
				return $subcomponents;
			case 'title_asc':
				foreach ($subcomponents as $component)
					$sortKeys[] = strtolower($component->getDisplayName());
				$direction = SORT_ASC;
				break;
			case 'title_desc':
				foreach ($subcomponents as $component)
					$sortKeys[] = strtolower($component->getDisplayName());
				$direction = SORT_DESC;
				break;
			case 'create_date_asc':
				foreach ($subcomponents as $component)
					$sortKeys[] = $component->getCreationDate()->asString();
				$direction = SORT_ASC;
				break;
			case 'create_date_desc':
				foreach ($subcomponents as $component)
					$sortKeys[] = $component->getCreationDate()->asString();
				$direction = SORT_DESC;
				break;
			case 'mod_date_asc':
				foreach ($subcomponents as $component)
					$sortKeys[] = $component->getModificationDate()->asString();
				$direction = SORT_ASC;
				break;
			case 'mod_date_desc':
				foreach ($subcomponents as $component)
					$sortKeys[] = $component->getModificationDate()->asString();
				$direction = SORT_DESC;
				break;
			default:
				$methods = array('default', 'custom', 'title_asc', 'title_desc', 'create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc');
				throw new Exception("Invalid sort method, '$sortMethod', not one of '".implode("', ", $methods)."'.");
		}

		// if the sort keys match, then sorting will be attempted on the subcomponents,
		// resulting in an error. To prevent this, generate an array of unique integers
		// to do secondary sorting on if sort-keys match.
		$secondaryKeys = array_keys($subcomponents);
		array_multisort(
			$sortKeys, $direction, SORT_STRING, 
			$secondaryKeys, SORT_ASC, SORT_NUMERIC, 
			$subcomponents); 
		return $subcomponents;
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 *
	 * For flow organizers the possible destinations are cells in any
	 * FixedOrganizer or NavOrganizer
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function getVisibleDestinationsForPossibleAddition () {
		$results = array();
		
		// If not authorized to remove this item, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		
		$visibleComponents = $this->_director->getVisibleComponents();
		foreach (array_keys($visibleComponents) as $id) {
			if (preg_match('/^.*FixedOrganizerSiteComponent$/i', get_class($visibleComponents[$id]))
				|| preg_match('/^.*NavOrganizerSiteComponent$/i', get_class($visibleComponents[$id])))
			{
					$results[$id] = $visibleComponents[$id];
			}
		}
		
		return $results;
	}
	
}

?>