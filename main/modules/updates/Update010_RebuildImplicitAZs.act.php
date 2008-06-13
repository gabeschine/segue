<?php
/**
 * @since 6/12/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add tables for the SiteThemes
 * 
 * @since 6/12/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update010_RebuildImplicitAZsAction
	extends Update
{
	
	/**
	 * @var boolean $checkSeparate;  Tell the list to check speparately since that takes a while.
	 * @access public
	 * @since 6/12/08
	 */
	public $checkSeparate = true;
	
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 6/12/08
	 */
	function getDateIntroduced () {
		return Date::withYearMonthDay(2008, 6, 12);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 6/12/08
	 */
	function getTitle () {
		return _("Rebuild Implicit AZs");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 6/12/08
	 */
	function getDescription () {
		return _("This update rebuild all of the Implicit Authorizations on nodes where the Administrators group does not have authorization to view. This state was caused by a bug in Segue < beta 23.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/12/08
	 */
	function isInPlace () {
		$hierarchyMgr = Services::getService("HierarchyManager");
		$idMgr = Services::getService("IdManager");	
		$hierarchyId = $idMgr->getId("edu.middlebury.authorization.hierarchy");
		$hierarchy = $hierarchyMgr->getHierarchy($hierarchyId);
		
		$view = $idMgr->getId("edu.middlebury.authorization.view");
		$adminId = $this->getAdminId();
		
		$authZ = Services::getService("AuthZ");
		
		$nodes = $hierarchy->getAllNodes();
		while ($nodes->hasNext()) {
			$node = $nodes->next();
			if (!$authZ->isAuthorized($adminId, $view, $node->getId()))
				return false;
		}
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/12/08
	 */
	function runUpdate () {
		$status = new StatusStars(_("Initializing"));
		$status->initializeStatistics(2);
		
		$hierarchyMgr = Services::getService("HierarchyManager");
		$idMgr = Services::getService("IdManager");	
		$hierarchyId = $idMgr->getId("edu.middlebury.authorization.hierarchy");
		$hierarchy = $hierarchyMgr->getHierarchy($hierarchyId);
		
		$view = $idMgr->getId("edu.middlebury.authorization.view");
		$adminId = $this->getAdminId();
		$status->updateStatistics();
		
		$authZ = Services::getService("AuthZ");
		
		$nodes = $hierarchy->getAllNodes();
		$status->updateStatistics();
		
		$status = new StatusStars(_("Checking Nodes"));
		$status->initializeStatistics($nodes->count());
		
		$toDo = array();
		while ($nodes->hasNext()) {
			$node = $nodes->next();
			$status->updateStatistics();
			if (!$authZ->isAuthorized($adminId, $view, $node->getId()))
				$toDo[] = $node;
		}
		
		$status = new StatusStars(str_replace('%1', count($toDo), _("Rebuilding Implicit AZs on %1 nodes.")));
		$status->initializeStatistics(count($toDo));
		$azCache = $authZ->getAuthorizationCache();
		foreach ($toDo as $node) {
			$azCache->createHierarchyImplictAZs($node, $node->getAncestorIds());
			$status->updateStatistics();
		}
		return true;
	}
	
	/**
	 * Answer the id of the administrators group if it exists.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 6/12/08
	 */
	protected function getAdminId () {
		$agentMgr = Services::getService("Agent");
		$groupType = new Type ("System", "edu.middlebury.harmoni", "SystemGroups", "Groups for administrators and others with special privileges.");
		
		$allGroups = $agentMgr->getGroups();
		while($allGroups->hasNext()) {
			$group = $allGroups->next();
			if ($group->getDisplayName() == 'Administrators' && $group->getType()->isEqual($groupType))
				return $group->getId();
		}
		
		throw new OperationFailedException("No Administrators group found.");
	}
}

?>