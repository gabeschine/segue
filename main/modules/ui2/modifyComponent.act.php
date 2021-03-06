<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modifyComponent.act.php,v 1.8 2008/04/09 21:12:03 adamfranco Exp $
 */ 

require_once(MYDIR."/main/modules/ui2/EditModeSiteAction.abstract.php");
require_once(MYDIR."/main/modules/ui2/Rendering/ModifySettingsSiteVisitor.class.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modifyComponent.act.php,v 1.8 2008/04/09 21:12:03 adamfranco Exp $
 */
class modifyComponentAction 
	extends EditModeSiteAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$director = $this->getSiteDirector();
		$component = $director->getSiteComponentById(SiteDispatcher::getCurrentNodeId());
				
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$component->getQualifierId());
	}
	
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function processChanges ( SiteDirector $director ) {		
		$component = $director->getSiteComponentById(SiteDispatcher::getCurrentNodeId());
		$component->acceptVisitor(new ModifySettingsSiteVisitor());
		
		/*********************************************************
		 * Log the event
		 *********************************************************/
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			
			$item = new AgentNodeEntryItem("Component Modified", $component->getComponentClass()." modified.");
			
			$item->addNodeId($component->getQualifierId());
			$site = $component->getDirector()->getRootSiteComponent($component->getId());
			if (!$component->getQualifierId()->isEqual($site->getQualifierId()))
				$item->addNodeId($site->getQualifierId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
	}
}

?>