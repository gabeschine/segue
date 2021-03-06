<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNavBlockSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/NavBlockSiteComponent.abstract.php");

/**
 * The SiteNavBlockSiteComponent
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNavBlockSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */
interface SiteNavBlockSiteComponent 
	extends NavBlockSiteComponent 
{
	
	/*********************************************************
	 * The following methods support working with slots.
	 * Slots are syntactically-meaningful user-specified 
	 * identifiers for sites. Slots are only guarenteed to be
	 * unique within the scope of a given segue installation.
	 *
	 * Only site nodes can have slots.
	 *********************************************************/
	
	/**
	 * Answer the slot for a site id.
	 * 
	 * @return object Slot
	 * @access public
	 * @since 7/25/07
	 */
	function getSlot () ;
	
	/*********************************************************
	 * Themes
	 *********************************************************/
	
	/**
	 * Answer the current theme.
	 * 
	 * @return object Harmoni_Gui2_ThemeInterface
	 * @access public
	 * @since 5/8/08
	 */
	public function getTheme ();
	
	/**
	 * Update the Site to use the theme passed. Any options set on the theme
	 * will be remembered.
	 * 
	 * @param object Harmoni_Gui2_ThemeInterface $theme
	 * @return null
	 * @access public
	 * @since 5/8/08
	 */
	public function updateTheme (Harmoni_Gui2_ThemeInterface $theme);
	
	/**
	 * Update the Site to use the default theme.
	 * 
	 * @return null
	 * @access public
	 * @since 5/8/08
	 */
	public function useDefaultTheme ();
}

?>