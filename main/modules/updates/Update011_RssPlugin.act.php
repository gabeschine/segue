<?php
/**
 * @since 7/8/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update005_RssAndBreadcrumbs.act.php,v 1.1 2008/03/21 19:18:18 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Install the RSS feed display plugin
 * 
 * @since 7/8/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update005_RssAndBreadcrumbs.act.php,v 1.1 2008/03/21 19:18:18 adamfranco Exp $
 */
class Update011_RssPluginAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/8/08
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2008, 7, 8);
		return $date;
	}
		
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/8/08
	 */
	function getTitle () {
		return _("RSS Display Plugin");
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/8/08
	 */
	function getDescription () {
		return _("This update will install and enable the new RSS Display plugin.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/8/08
	 */
	function isInPlace () {
		$pluginMgr = Services::getService("PluginManager");
		
		if (!$pluginMgr->isInstalled(new Type ('SeguePlugins', 'edu.middlebury', 'RssFeed')))
			return false;
		if (!$pluginMgr->isEnabled(new Type ('SeguePlugins', 'edu.middlebury', 'RssFeed')))
			return false;
			
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/8/08
	 */
	function runUpdate () {
		$pluginMgr = Services::getService("PluginManager");
		
		try {
			$pluginMgr->installPlugin(new Type ('SeguePlugins', 'edu.middlebury', 'RssFeed'));
			$pluginMgr->enablePlugin(new Type ('SeguePlugins', 'edu.middlebury', 'RssFeed'));
		} catch (Exception $e) {
			printpre($e->getMessage());
		}
		
		return $this->isInPlace();
	}
	
}

?>