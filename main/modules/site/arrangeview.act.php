<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: arrangeview.act.php,v 1.1 2007/01/15 17:57:15 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ArrangeModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/view.act.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: arrangeview.act.php,v 1.1 2007/01/15 17:57:15 adamfranco Exp $
 */
class arrangeviewAction
	extends viewAction {
	
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function &getSiteVisitor () {
		$visitor =& new ArrangeModeSiteVisitor();
		return $visitor;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function &getCommandsComponent () {
		$harmoni =& Harmoni::instance();
		
		ob_start();
		print "<a href='";
		print $harmoni->request->quickURL('site', 'view', array(
				'node' => RequestContext::value("node")));
		print "' alt='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print "| <a href='";
		print $harmoni->request->quickURL('site', 'editview', array(
				'node' => RequestContext::value("node")));
		print "' alt='"._("Go to Edit-Mode")."'>";
		print _("edit")."</a>";
		
		print " | "._("arrange");
				
		$ret =& new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>