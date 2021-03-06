<?php
/**
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.8 2008/04/09 21:12:02 adamfranco Exp $
 */ 

// Use a custom version of Archive/Tar if requested.
if (defined('ARCHIVE_TAR_PATH'))
	require_once(ARCHIVE_TAR_PATH);
else
	require_once("Archive/Tar.php");

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(dirname(__FILE__)."/Rendering/DomExportSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * This action will export a site to an xml file
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.8 2008/04/09 21:12:02 adamfranco Exp $
 */
class exportAction
	extends Action
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/08
	 */
	public function isAuthorizedToExecute () {
		// Authorization checks are handled in the DOMExportSiteVisitor, so just
		// return true for the action as a whole.
		return true;
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 1/17/08
	 */
	public function execute () {
		$harmoni = Harmoni::instance();
				
		$component = SiteDispatcher::getCurrentNode();
		$site = SiteDispatcher::getCurrentRootNode();
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotBySiteId($site->getId());
		
		$exportDir = DATAPORT_TMP_DIR."/".$slot->getShortname()."-".str_replace(':', '_', DateAndTime::now()->asString());
		mkdir($exportDir);
		
		try {
			// Do the export
			$visitor = new DomExportSiteVisitor($exportDir);
			$component->acceptVisitor($visitor);
			
			// Validate the result
// 			printpre(htmlentities($visitor->doc->saveXMLWithWhitespace()));
// 			$tmp = new Harmoni_DomDocument;
// 			$tmp->loadXML($visitor->doc->saveXMLWithWhitespace());
// 			$tmp->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
			$visitor->doc->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
			
			// Write out the XML
			$visitor->doc->saveWithWhitespace($exportDir."/site.xml");		
		
			$archive = new Archive_Tar($exportDir.".tar.gz");
			$archive->createModify($exportDir, '', DATAPORT_TMP_DIR);
			
			// Remove the directory
			$this->deleteRecursive($exportDir);
			
			header("Content-Type: application/x-gzip;");
			header('Content-Disposition: attachment; filename="'
								.basename($exportDir.".tar.gz").'"');
			print file_get_contents($exportDir.".tar.gz");
			
			// Clean up the archive
			unlink($exportDir.".tar.gz");
		} catch (PermissionDeniedException $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($exportDir.".tar.gz"))
				unlink($exportDir.".tar.gz");
			
			return new Block(
				_("You are not authorized to export this component."),
				ALERT_BLOCK);
		} catch (Exception $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($exportDir.".tar.gz"))
				unlink($exportDir.".tar.gz");
			
			throw $e;
		}
		
		error_reporting(0);
		exit;
	}
	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	function getNodeId () {
		return SiteDispatcher::getCurrentNodeId();
	}
	
	/**
	 * Recursively delete a directory
	 * 
	 * @param string $path
	 * @return void
	 * @access protected
	 * @since 1/18/08
	 */
	protected function deleteRecursive ($path) {
		if (is_dir($path)) {
			$entries = scandir($path);
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					$this->deleteRecursive($path.DIRECTORY_SEPARATOR.$entry);
				}
			}
			rmdir($path);
		} else {
			unlink($path);
		}
	}
}

?>