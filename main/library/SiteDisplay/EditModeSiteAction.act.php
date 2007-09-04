<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteAction.act.php,v 1.9 2007/09/04 15:05:32 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");


/**
 * This is an abstract class that makes it easy to add new editing actions
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteAction.act.php,v 1.9 2007/09/04 15:05:32 adamfranco Exp $
 */
abstract class EditModeSiteAction 
	extends MainWindowAction
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
		
		return true;
// 		return $authZ->isUserAuthorized(
// 			$idManager->getId("edu.middlebury.authorization.modify"),
// 			$idManager->getId(RequestContext::value('parent_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Node</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$director = $this->getSiteDirector();
		
		$this->processChanges($director);
		
		$this->writeDataAndReturn();
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
	abstract function processChanges ( SiteDirector $director );
	
	/**
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access public
	 * @since 4/14/06
	 */
	function getSiteDirector () {
		/*********************************************************
		 * XML Version
		 *********************************************************/
// 		$this->filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
// 		
// 		$this->document = new DOMIT_Document();
// 		$this->document->setNamespaceAwareness(true);
// 		$success = $this->document->loadXML($this->filename);
// 
// 		if ($success !== true) {
// 			throwError(new Error("DOMIT error: ".$this->document->getErrorCode().
// 				"<br/>\t meaning: ".$this->document->getErrorString()."<br/>", "SiteDisplay"));
// 		}
// 
// 		$director = new XmlSiteDirector($this->document);
		
		
		/*********************************************************
		 * Asset version
		 *********************************************************/
		$repositoryManager = Services::getService('Repository');
		$idManager = Services::getService('Id');
		
		$director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));
		
		
		return $director;
	}
	
	/**
	 * Write to our data source and return to our previous action
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function writeDataAndReturn () {
		// 		printpre($this->document->toNormalizedString(true));
// 		$this->filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
// 		
// 		// Let's make sure the file exists and is writable first.
// 		if (is_writable($this->filename)) {
// 		
// 			// In our example we're opening $filename in append mode.
// 			// The file pointer is at the bottom of the file hence
// 			// that's where $somecontent will go when we fwrite() it.
// 			if (!$handle = fopen($this->filename, 'w')) {
// 				echo "Cannot open file (".$this->filename.")";
// 				exit;
// 			}
// 			
// 			// Write $somecontent to our opened file.
// 			if (fwrite($handle, $this->document->toNormalizedString()) === FALSE) {
// 				echo "Cannot write to file (".$this->filename.")";
// 				exit;
// 			}
// 			
// 			fclose($handle);
			
			$this->returnToCallerPage();
// 			
// 		} else {
// 			echo "The file ".$this->filename." is not writable.<hr/>";
// 			printpre($this->document->toNormalizedString(true));
// 		}

	}
	
	/**
	 * Return the browser to the page from whence they came
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function returnToCallerPage () {
		$harmoni = Harmoni::instance();
		if (!($returnAction = RequestContext::value('returnAction')))
			$returnAction = 'editview';
			
		RequestContext::locationHeader($harmoni->request->quickURL(
			$harmoni->request->getRequestedModule(), $returnAction,
			array("node" => RequestContext::value('returnNode'))));	
	}
}

?>