<?php
/**
 * @since 5/9/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");

/**
 * This class is a user-interface for changing theme options.
 * 
 * @since 5/9/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_optionsAction
	extends SegueClassicWizard
{
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/9/08
	 */
	function getHeadingText () {
		return _("Theme options");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 5/9/08
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("options", $this->getOptionsStep());
		$wizard->addStep("advanced", $this->getAdvancedStep());
		
		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 5/9/08
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if (!$this->saveOptionsStep($properties['options']))
				return FALSE;
			if (!$this->saveAdvancedStep($properties['advanced']))
				return FALSE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer the url to return to
	 * 
	 * @return string
	 * @access public
	 * @since 5/19/08
	 */
	function getReturnUrl () {
		if (isset($this->createdCopy) && $this->createdCopy) {
			$harmoni = Harmoni::instance();
			return SiteDispatcher::quickURL(
				'ui1', 'theme_options',
				array('wizardSkipToStep' => "advanced"));
		} else {
			return parent::getReturnUrl();
		}
	}
	
	/**
	 * Answer the theme step
	 * 
	 * @return object WizardStep
	 * @access protected
	 * @since 5/8/08
	 */
	protected function getOptionsStep () {
		$component = $this->getSiteComponent();
		$step =  new WizardStep();
		$step->setDisplayName(_("Basic Options"));
		ob_start();
		
		print "\n<h2>"._("Basic Options")."</h2>";
		print "\n<p>";
		print _("Here you can set the options for the current theme."); 
		print "\n</p>\n";
				
		$theme = $component->getTheme();
		if (!$theme->supportsOptions()) {
			print "\n<p>"._("This theme does not currently support options")."</p>";
			$step->setContent(ob_get_clean());
			return $step;
		}
		$optionsSession = $theme->getOptionsSession();
		
		foreach ($optionsSession->getOptions() as $option) {
			print "\n<h3>".$option->getDisplayName()."</h3>";
			print "\n<p>".$option->getDescription()."</p>";
			print "[[".$option->getIdString()."]]";
			$property = $step->addComponent($option->getIdString(), new WSelectList());
			$property->setValue($option->getValue());
			
			$values = $option->getValues();
			$labels = $option->getLabels();
			for ($j = 0; $j < count($values); $j++) {
				$property->addOption($values[$j], $labels[$j]);
			}
		}
		
				
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the theme step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access protected
	 * @since 5/8/08
	 */
	protected function saveOptionsStep (array $values) {
		$component = $this->getSiteComponent();
		$theme = $component->getTheme();
		
		if (!$theme->supportsOptions()) {
			return false;
		}
		$optionsSession = $theme->getOptionsSession();
		
		foreach ($optionsSession->getOptions() as $option) {
			if ($values[$option->getIdString()] != $option->getValue())
				$option->setValue($values[$option->getIdString()]);
		}
		
		$component->updateTheme($theme);
		
		return true;
	}
	
	/**
	 * Answer a wizard step for advanced theme editing.
	 * 
	 * @return object WizardStep
	 * @access protected
	 * @since 5/15/08
	 */
	protected function getAdvancedStep () {
		$component = $this->getSiteComponent();
		$step =  new WizardStep();
		$step->setDisplayName(_("Advanced Editing"));
		$harmoni = Harmoni::instance();
		ob_start();
		
		print "\n<h2>"._("Advanced Theme Editing")."</h2>";				
		$theme = $component->getTheme();
		if (!$theme->supportsModification()) {
			print "\n<p>"._("You currently are using a read-only theme. You can make a copy of this theme for just this site that you can then modify.")."</p>";
			
			$about = _("Modifying a theme requires knowledge of %1Cascading Style Sheets (CSS)%2 and %3Hypertext Markup Language (HTML)%4");
			$about = str_replace('%1', "<a href='http://www.w3schools.com/Css/css_intro.asp' target='_blank'>", $about);
			$about = str_replace('%2', "</a>", $about);
			$about = str_replace('%3', "<a href='http://www.w3schools.com/html/html_intro.asp' target='_blank'>", $about);
			$about = str_replace('%4', "</a>", $about);
			print "\n<p>".$about."</p>";
			
			$property = $step->addComponent('create_copy', WSaveButton::withLabel(_("Create Local Theme Copy")));
			
			print "[[create_copy]]";
			$step->setContent(ob_get_clean());
			return $step;
		}
		
		print "\n<div class='theme_edit_step'>";
		
		$modSess = $theme->getModificationSession();
		
		/*********************************************************
		 * Information
		 *********************************************************/
		print "\n<h3>"._("Theme Information")."</h3>";
		print "\n<table class='info_table'><tr><td>";
		$property = $step->addComponent('display_name', new WSafeHtmlTextField);
		$property->setSize(40);
		$property->setValue($theme->getDisplayName());
		$property->setErrorRule(new WECRegex('[a-zA-Z0-9]+'));
		$property->setErrorText(_("You must specify a name."));
		print "\n<h4>"._("Display Name")."</h4>\n[[display_name]]";
		
		$property = $step->addComponent('description', new WSafeHtmlTextArea);
		$property->setRows(10);
		$property->setColumns(40);
		$property->setValue($theme->getDescription());
		print "\n<br/><h4>"._("Description")."</h4>\n[[description]]";
		
		print "\n</td><td>";
		$property = $step->addComponent('thumbnail', new WFileUploadField);
		$property->setAcceptedMimetypes(array('image/png', 'image/jpeg', 'image/gif'));
		print "\n<h4>"._("Thumbnail")."</h4>\n[[thumbnail]]";
		print "<div><br/>"._("Current Thumbnail: ")."<br/>";
		
		try {
			$currentThumbnail = $theme->getThumbnail();
			$property->setStartingDisplay($currentThumbnail->getBasename(), 
				$currentThumbnail->getSize());
			print "\n\t<img src='".$harmoni->request->quickUrl('gui2', 'theme_thumbnail',
				array('theme' => $theme->getIdString(), 'rand' => rand(1,10000)))."' width='200px'/>";
		} catch (UnknownIdException $e) {
			print "<em>"._("none")."</em>";
		}
		print "</div>";
		print "\n</td></tr></table>";
		
		/*********************************************************
		 * Global CSS
		 *********************************************************/
		print "\n<table class='info_table'><tr><td style='width: 350px;'>";
		print "\n<h3>"._("Theme Data")."</h3>";
		print "\n<p>"._("In the text areas below, add the CSS and HTML for your theme.")."</p>";
		print "\n<p>"._("The CSS snippets will be combined together in the order listed into a single file.")."</p>";
		print "\n<p>"._("The HTML snippets will wrap the various components on the screen and must contain <code>&#91;&#91;CONTENT&#93;&#93;</code> placeholder for the content of the component. Any classes you want to refer to in the CSS will need to be added to the HTML snippets.")."</p>";
		
		$property = $step->addComponent('global_css', new WSafeCssTextArea);
		$value = $modSess->getGlobalCss();
		$property->setValue($value);
		$property->setColumns(40);
		$property->setWrap('off');
		$property->setRows(min(40, max(20, substr_count($value, "\n") + 1)));
		
		
		print "\n<h4>"._("Global CSS")."</h4>\n[[global_css]]";
		
		/*********************************************************
		 * Images
		 *********************************************************/
		print "\n</td><td>";
		
		print "\n<h3>"._("Images")."</h3>";
		print "\n<p>"._("You may upload images to your theme. These images must be JPG, PNG, or GIF images. To use them in your HTML or CSS, reference them with relative urls in an 'images' directory such as <code>images/background_image.jpg</code>.")."</p>";
		print "\n[[images]]";
		$collection = $step->addComponent('images', new WRepeatableComponentCollection);
		$collection->setContent('./images/[[path_prefix]]/[[image]] [[orig_path_prefix]]');
		
		$property = $collection->addComponent('path_prefix', new WTextField);
		$property->setSize('10');
		$property->setErrorRule(new WECRegex('^([a-zA-Z0-9_-]+)?(\/[a-zA-Z0-9_-]+)*$'));
		$property->setErrorText(_("Subdirectories can only contain letters, numbers, and underscore characters."));
		
		$property = $collection->addComponent('orig_path_prefix', new WHiddenField);
		
		$property = $collection->addComponent('image', new WFileUploadField);
		$property->setAcceptedMimetypes(array('image/png', 'image/jpeg', 'image/gif'));
		
		foreach ($theme->getImages() as $image) {
			$collection->addValueCollection(array (
				'path_prefix' => dirname($image->getPath()),
				'orig_path_prefix' => dirname($image->getPath()),
				'image' => array(
					'name' => $image->getBasename(), 
					'size' => $image->getSize(),
					'type' => $image->getMimeType(),
					'starting_name' => $image->getBasename(), 
					'starting_size' => $image->getSize())));
		}
		print "\n</td></tr></table>";
		
		
		/*********************************************************
		 * Other CSS and Templates
		 *********************************************************/
		print "\n<table class='theme_advanced_table'>";
		foreach ($modSess->getComponentTypes() as $type) {
// 			print "\n\t<tr>\n\t\t<th colspan='2'>".$type."</th>\n\t</tr>";
			print "\n\t<tr>";
			print "\n\t\t<th>".$type." CSS</th>";
			print "\n\t\t<th>".$type." HTML</th>";
			print "\n\t</tr>";
			
			print "\n\t<tr>";
			print "\n\t\t<td>[[".$type."-css]]</td>";
			print "\n\t\t<td>[[".$type."-html]]</td>";
			print "\n\t</tr>";
			
			$cssProperty = $step->addComponent($type.'-css', new WSafeCssTextArea);
			$value = $modSess->getCssForType($type);
			$cssLines = substr_count($value, "\n");
			$cssProperty->setValue($value);
			$cssProperty->setColumns(40);
			$cssProperty->setWrap('off');
			
			
			$htmlProperty = $step->addComponent($type.'-html', new WSafeHtmlTextArea);
			$value = $modSess->getTemplateForType($type);
			$htmlLines = substr_count($value, "\n");
			$htmlProperty->setValue($value);
			$htmlProperty->setColumns(60);
			$htmlProperty->setWrap('off');
			$htmlProperty->setErrorRule(new WECRegex('(\[\[CONTENT\]\]|^$)'));
			$htmlProperty->setErrorText(_("Template HTML must contain a [[CONTENT]] placeholder or be blank."));
			
			// Extend the text-areas to fit the content if needed.
			$numLines = max(10, $cssLines + 1, $htmlLines + 1);
			$numLines = min($numLines, 50);
			$cssProperty->setRows($numLines);
			$htmlProperty->setRows($numLines);
		}
		print "\n</table>";
		
		
		/*********************************************************
		 * Options
		 *********************************************************/
		$property = $step->addComponent('options', new WTextArea);
		$property->setRows(40);
		$property->setColumns(100);
		$property->setWrap('off');
		$property->setValue($modSess->getOptionsDocument()->saveXMLWithWhiteSpace());
		$property->setErrorRule(new XmlSchemaRule(HARMONI.'/Gui2/theme_options.xsd'));
		print "\n<h3>"._("Theme Options")."</h3>";
		$help = _("In the text area below you can add an XML document that describes any options for this theme. This document must conform to the %1. (View an example %2.)");
		$schema = "<a href='".$harmoni->request->quickURL('gui2', 'view_options_schema')."' target='_blank'>"._("options schema")."</a>";
		$example = "<a href='".$harmoni->request->quickURL('gui2', 'view_options_example')."' target='_blank'>"._("options document")."</a>";
		print "\n<p>".str_replace('%1', $schema, str_replace('%2', $example, $help))."</p>";
		print "\n<p>"._("Each option defines a set of choices for the user. These choices are composed of one or more settings. When a choice is used, all occurrances of the marker in the CSS and HTML above will be replaced with the value of that setting.")."</p>";
		print "\n[[options]]";
		
		
		print "\n</div>";
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the advanced step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access protected
	 * @since 5/15/08
	 */
	protected function saveAdvancedStep (array $values) {
		$component = $this->getSiteComponent();
		$theme = $component->getTheme();
		
		if (isset($values['create_copy']) && $values['create_copy']) {
			// Get the first source that supports admin.
			$guiMgr = Services::getService('GUIManager');
			foreach ($guiMgr->getThemeSources() as $source) {
				if ($source->supportsThemeAdmin()) {
					$adminSession = $source->getThemeAdminSession();
					$newTheme = $adminSession->createCopy($theme);
					
					$component->updateTheme($newTheme);
					
					$this->createdCopy = true;
					return true;
				}
			}
			// Nowhere to copy to.
			print "<p>"._("Error: No available source to copy this theme to.")."</p>";
			return false;
		}
		
		/*********************************************************
		 * Return if we are not editing advanced options
		 *********************************************************/
		if (!$theme->supportsModification())
			return true;
		
		$modSess = $theme->getModificationSession();
		if (!$modSess->canModify())
			return true;
		
		
		/*********************************************************
		 * Info
		 *********************************************************/
		$modSess->updateDisplayName($values['display_name']);
		$modSess->updateDescription($values['description']);
		if (!is_null($values['thumbnail']['tmp_name'])) {
			$file = new Harmoni_Filing_FileSystemFile($values['thumbnail']['tmp_name']);
			$file->setMimeType($values['thumbnail']['type']);
			$modSess->updateThumbnail($file);
		}
		
		
		/*********************************************************
		 * CSS and HTML
		 *********************************************************/
		$modSess->updateGlobalCss($values['global_css']);
		
		foreach ($modSess->getComponentTypes() as $type) {
			$modSess->updateCssForType($type, $values[$type.'-css']);
			$modSess->updateTemplateForType($type, $values[$type.'-html']);
		}
		
		/*********************************************************
		 * Images
		 *********************************************************/
		$missingImages = $theme->getImages();
		foreach ($values['images'] as $imageVal) {
			// Add new images
			if ($imageVal['image']['tmp_name']) {
				$file = new Harmoni_Filing_FileSystemFile($imageVal['image']['tmp_name']);
				$file->setMimeType($imageVal['image']['type']);
				$theme->addImage($file, $imageVal['image']['name'], $imageVal['path_prefix']);
			}
			// Move any images with changed prefixes.
			else if ($imageVal['path_prefix'] != $imageVal['orig_path_prefix']) {
				$image = $modSess->getImage(
					$imageVal['orig_path_prefix'].'/'.$imageVal['image']['name']);
				$image->setPath($imageVal['path_prefix'].'/'.$imageVal['image']['name']);
			}
			// Mark images as existing
			if ($imageVal['path_prefix'])
				$path = $imageVal['path_prefix'].'/'.$imageVal['image']['name'];
			else
				$path = $imageVal['image']['name'];
			foreach ($missingImages as $key => $image) {
				if ($image->getPath() == $path) {
					unset($missingImages[$key]);
				}
			}
		}
		// Remove any images that were removed.
		foreach ($missingImages as $image) {
			$image->delete();
		}
		
		/*********************************************************
		 * Options
		 *********************************************************/
		$optionsString = trim ($values['options']);
		$optionsDoc = new Harmoni_DOMDocument;
		$optionsDoc->preserveWhiteSpace = false;
		if (strlen($optionsString) && $optionsString != '<?xml version="1.0"?>') {
			try {
				$optionsDoc->loadXML($values['options']);
			} catch (DOMException $e) {
				print "<strong>"._("Error in Options Definition:")." </strong>";
				print $e->getMessage();
				return false;
			}
		}
		try {
			$modSess->updateOptionsDocument($optionsDoc);
		} catch (ValidationFailedException $e) {
			print "<strong>"._("Error in Options Definition:")." </strong>";
			print $e->getMessage();
			return false;
		}
		
		return true;
	}
}

?>