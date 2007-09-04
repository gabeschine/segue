<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsPlugin.abstract.php,v 1.35 2007/09/04 17:38:42 adamfranco Exp $
 */ 

require_once (HARMONI."/Primitives/Collections-Text/HtmlString.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(MYDIR."/main/modules/media/MediaAsset.class.php");

/**
 * Abstract class that all Plugins must extend
 * 
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsPlugin.abstract.php,v 1.35 2007/09/04 17:38:42 adamfranco Exp $
 */
class SeguePluginsPlugin {
 	
/*********************************************************
 * Instance Methods/Variables - API
 *
 * These are the methods that plugins can and should use 
 * to interact with their environment. 
 * 		Valid additional APIs outside of the methods below:
 *			- OSID interfaces (accessed through Plugin->getManager($managerName))
 *
 * To preserve portability, plugins should not access 
 * other Harmoni APIs, constants, global variables, or
 * the super-globals $_GET, $_POST, $_REQUEST, $_COOKIE.
 *********************************************************/
/*********************************************************
 * Object Variables - API
 *********************************************************/
	/**
	 * @var array $data; 4-dimensional array holding plugin data 
	 * @access private
	 * @since 3/1/06
	 */
	var $data;

/*********************************************************
 * Instance Methods - API - Override in Children
 *
 * Override these methods to implement the functionality of
 * a plugin.
 *********************************************************/
 	
 	/**
 	 * Answer a description of the the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	function getPluginDescription () {
 		return _("Override this method in your plugin.");
 	}
 	
 	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.  This is where you would make more complex data that your 
 	 * plugin needs.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function initialize () {
		// Override as needed.
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function update ( $request ) {
 		// Override as needed.
 	}
 	
 	/**
 	 * Return the markup that represents the plugin.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function getMarkup () {
 		return _("<p>Override this method to display your pluggin.</p>");
 	}
 	
 	/**
 	 * Return the markup that represents the plugin in and expanded form.
 	 * This method will be called when looking at a "detail view" of the plugin
 	 * where the representation of the plugin will be the focus of the page
 	 * rather than just one of many elements.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	function getExtendedMarkup () {
 		return $this->getMarkup();
 	}
 	
 	/**
 	 * Answer the label to use when linking to the plugin's extented markup.
 	 * For a text-based plugin this may be the default, 'read more >>', for
 	 * an image plugin it might be something like "Large View", etc.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	function getExtendedLinkLabel () {
 		return _("read more &raquo;");
 	}
 	
 	/**
 	 * Generate a plain-text or HTML description string for the plugin instance.
 	 * This may simply be a stored 'raw description' string, it could be generated
 	 * from other content in the plugin instance, or some combination there-of.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/22/07
 	 */
 	function generateDescription () {
 		return $this->getRawDescription();
 	}
 	
 	/**
 	 * Answer true if this instance of a plugin 'has content'. This method is called
 	 * to determine if the plugin instance is ready to be 'published' or is a newly-created
 	 * placeholder awaiting content addition. If the plugin has no appreciable 
 	 * difference between have content or not, this method should return true. For
 	 * example: an interactive calendar plugin should probably be 'published' 
 	 * whether or not events have been added to it.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 7/13/07
 	 */
 	function hasContent () {
 		// Override as needed
 		return true;
 	}
 	
 	/*********************************************************
 	 * The following three methods allow plugins to work within
 	 * the "Segue Classic" user interface.
 	 *
 	 * If plugins do not support the wizard directly, then their
 	 * markup with 'show controls' enabled will be put directly 
 	 * in the wizard.
 	 *********************************************************/
 	/**
 	 * Answer true if this plugin natively supports editing via wizard components.
 	 * Override to return true if you implement the getWizardComponent(), 
 	 * and updateFromWizard() methods.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 5/9/07
 	 */
 	function supportsWizard () {
 		return false;
 	}
 	/**
 	 * Return the a {@link WizardComponent} to allow editing of your
 	 * plugin in the Wizard.
 	 * 
 	 * @return object WizardComponent
 	 * @access public
 	 * @since 5/8/07
 	 */
 	function getWizardComponent () {
 		print "<p>Override ".__CLASS__."::".__FUNCTION__."() to enable editing of your pluggin in Segue Classic Mode.</p>";
 	}
 	
 	/**
 	 * Update the component from an array of values
 	 * 
 	 * @param array $values
 	 * @return void
 	 * @access public
 	 * @since 5/8/07
 	 */
 	function updateFromWizard ( $values ) {
 		print "<p>Override ".__CLASS__."::".__FUNCTION__."() to enable editing of your pluggin in Segue Classic Mode.</p>";
 	}
 	
/*********************************************************
 * Instance Methods - API
 *
 * Use these methods in your plugin as needed, but do not 
 * override them.
 *********************************************************/
	
	/**
	 * Answer an href tag string with the array values added as parameters to 
	 * an internal url.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	function href ( $parameters = array() ) {
		return "href='".$this->url($parameters)."'";
	}
	/**
	 * Answer an internal Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function url ( $parameters = array() ) {		
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
		
		$url = $this->_baseUrl->deepCopy();
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		return $url->write();
	}
	
	/**
	 * Answer a Javascript command to send the window to an internal url with the 
	 * parameters passed.
	 *
	 * Use this method, e.g.:
	 *		"onclick=".$this->locationSend(array('item' => 123))
	 * instead of the following:
	 * 		"onclick='window.location=\"".$this->url(array('item' => 123))."\"'"
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	function locationSend ( $parameters = array() ) {		
		return "'window.location=\"".$this->url($parameters)."\"'";
	}
	
	/**
	 * Answer a url with the parameters passed, for a form. As well, specify
	 * an optional boolean second parameter, 'isMultipart' if this is a multipart
	 * form with file uploads.
	 *
	 * Use this method, e.g.:
	 *		$this->formTagWithAction(array('item' => 123), false);
	 * instead of the following:
	 * 		"<form action='".$this->url(array('item' => 123))."' method='post>";
	 *
	 * Usage of this method instead of manually writing the form start tag
	 * is optional, but will allow the plugin to more easily be ported to being
	 * an 'AjaxPlugin' later on as the AjaxPlugin redefines the behavior of
	 * this method.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @param string $method post OR get
	 * @param boolean $isMultipart
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	function formStartTagWithAction ( $parameters = array(), $method = 'post', 
		$isMultipart = false ) 
	{
		// If this is a multipart form, we must do a normal 'submit'
		// that includes a page refresh.
		if ($isMultipart) {
			return "<form action='".$this->url($parameters)."' method='post' enctype='multipart/form-data'>";
		} 
		// If the form is not a multipart form with file uploads, then we
		// don't ned the enctype parameter.
		else {
			if (strtolower($method) == 'get')
				$method = 'get';
			else
				$method = 'post';
			return "<form action='".$this->url($parameters)."' method='".$method."'>";
		}
	}
	
	/**
	 * Answer the name-spaced field-name for a given name.
	 * This method MUST be used in form inputs for their values to be
	 * accessible to the plugin.
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getFieldName ( $name ) {
		return RequestContext::name($name);
	}
	
	/**
	 * Answer the value of a submitted/requested field (i.e. GET, POST, REQUEST)
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getFieldValue ( $name ) {
		return RequestContext::value($name);
	}
	
	/**
	 * Answer the persisted 'title' value of this plugin.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getTitle () {
		return $this->_asset->getDisplayName();
	}
	
	/**
	 * Answer the persisted raw description value of this plugin. 
	 * It is up to the plugin writer what data to store in the raw description 
	 * field. The 'raw description' will only be used internally in this plugin.
	 * External access to the plugin's description will all go through 
	 * the getDescription() method, which may include additional operations.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getRawDescription () {
		$idManager = Services::getService("Id");
		$parts = $this->_asset->getPartsByPartStructure(
			$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"));
		
		if ($parts->hasNext()) {
			$part = $parts->next();
			$value = $part->getValue();
			return $value->asString();
		} else {
			return "";
		}
	}
	
	/**
	 * Set the persisted 'raw description' value of this plugin.
	 * It is up to the plugin writer what data to store in the raw description 
	 * field. The 'raw description' will only be used internally in this plugin.
	 * External access to the plugin's description will all go through 
	 * the getDescription() method, which may include additional operations.
	 * 
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function setRawDescription ( $description ) {
		$idManager = Services::getService("Id");
		$parts = $this->_asset->getPartsByPartStructure(
			$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"));
		if ($parts->hasNext()) {
			$part = $parts->next();
			$part->updateValue(String::fromString($description));
		} else {
			$records = $this->_asset->getRecordsByRecordStructure(
				$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs"));
			if ($records->hasNext()) {
				$record = $records->next();
			} else {
				$record = $this->_asset->createRecord($idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs"));
			}
			
			$part = $record->createPart($idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"), String::fromString($description));
		}
	}
	
	/**
	 * Answer the description markup for the plugin instance. This description
	 * will have been generated via the plugin's generateDescription() method.
	 * 
	 * This method MAY be overridden in your plugin implementation and 
	 * WILL BE used to display a description of the plugin instance in external 
	 * contexts (such as a site-map).
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getDescription () {
		return $this->_asset->getDescription();
	}
	
	/**
	 * Answer the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getContent () {
		$content = $this->_asset->getContent();
		return $content->asString();
	}
	
	/**
	 * Set the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @param string $content
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function setContent ( $content ) {
		$string = Blob::withValue($content);
		$this->_asset->updateContent($string);
	}
	
	/**
	 * Answer the persisted data of this plugin. Changes to this data can be
	 * persisted via updateDataRecords()
	 * 
	 * The data array returned from this function is a 4-dimensional array of 
	 * the following organization:
	 * level 1: associative array using the record name as the 'key' and an 
	 * array of instances of the record as the 'value'
	 * level 2: array of instances of the record, each index (0,1,...) maps to 
	 * an associative array of the fields in the record
	 * level 3: associative array using the field name as the 'key' and an array
	 * of instances of the fields as the 'value'
	 * level 4: array of instances of the field, each index (0,1,...) maps to
	 * the actual value for its instance of this field in this record
	 * Example: to get a value you will need to access the data array with 4
	 * indices; $myDataArray['recordName'][0]['fieldName'][3] would return the 
	 * fourth instance of 'fieldName' in the first instance of 'recordName'
	 * NOTE: Files are accessible through $myDataArray['FILE'][?] where the
	 * field names are: FILE_NAME DIMENSIONS FILE_SIZE MIME_TYPE 
	 * NOTE: you can also just access this data array through $this->data
	 *
	 * @return array this is the data array for your plugin
	 * @access public
	 * @since 1/13/06
	 */
	function getDataRecords () {
		return $this->data;
	}

	/**
	 * Automagically updates any changed data in the data array
	 *
	 * @return void
	 * @access public
	 * @since 1/18/06
	 */
	function updateDataRecords () {
		$this->_storeData();
	}
	
	/**
	 * Answer a valid XHTML with any tag or special-character errors fixed.
	 * 
	 * @param string $htmlString
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function cleanHTML ($htmlString) {
		$htmlStringObj = HtmlString::withValue($htmlString);
 		$htmlStringObj->clean();
 		return $htmlStringObj->asString();
	}
	
	/**
	 * Answer a valid XHTML string trimmed to the specified word length. Cleans
	 * the syntax as well.
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function trimHTML ($htmlString, $maxWords, $addElipses = true) {
		$htmlStringObj = HtmlString::withValue($htmlString);
 		$htmlStringObj->trim($maxWords, $addElipses);
 		return $htmlStringObj->asString();
	}
	
	/**
	 * Answer a string trimmed to the specified word length. Strips html tags
	 * as well
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function stripTagsAndTrim ($htmlString, $maxWords, $addElipses = true) {
		$htmlStringObj = HtmlString::withValue($htmlString);
 		$htmlStringObj->stripTagsAndTrim($maxWords, $addElipses);
 		return $htmlStringObj->asString();
	}

	/**
	 * Answer TRUE if the current user is authorized to modify this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	function canModify () {
		if (isset($this->_canModifyFunction)) {
			$function = $this->_canModifyFunction;
			return $function($this);
		} else {
			$azManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			return $azManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"),
					$this->_asset->getId());
		}
	}
	
	/**
	 * Answer TRUE if the current user is authorized to view this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	function canView () {
		if (isset($this->_canViewFunction)) {
			$function = $this->_canViewFunction;
			return $function($this);
		} else {
			$azManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			return $azManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"),
					$this->_asset->getId());
		}
	}
	
	/**
	 * Answer TRUE if modification controls should be displayed, assuming that
	 * authorization is had as well. This method allows the plugin to operate
	 * in two modes, hiding editing controls when they are not needed.
	 *  
	 * @return boolean
	 * @access public
	 * @since 2/22/06
	 */
	function shouldShowControls () {
		if ($this->_showControls && $this->canModify())
			return true;
		else
			return false;
	}
	
	/**
	 * Answer the string Id of this plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	function getId () {
		if (!isset($this->_id)) {
			$id = $this->_asset->getId();
			$this->_id = $id->getIdString();
		}
		return $this->_id;			
	}

	/**
	 * Answer the filesystem filepath for the plugin
	 * 
	 * @return string the filesystem path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	function getPluginDir () {
		$dir = $this->_configuration->getProperty('plugin_dir')."/";
		$type = $this->_asset->getAssetType();
		$dir .= $type->getDomain()."/";
		$dir .= $type->getAuthority()."/";
		$dir .= $type->getKeyword()."/";

		return $dir;
	}

	/**
	 * Answer the url filepath for the plugin?
	 * 
	 * @return string the url path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	function getPluginPath () {
		$path = $this->_configuration->getProperty('plugin_path')."/";
		$type = $this->_asset->getAssetType();
		$path .= $type->getDomain()."/";
		$path .= $type->getAuthority()."/";
		$path .= $type->getKeyword()."/";

		return $path;
	}

/*********************************************************
 * Files
 *
 * There are two ways that files can be stored and accessed
 * by plugins. 
 * 		1. 	Reference files from the media library
 *		2. 	Store and access file records in the plugin's 
 *			data array.
 *
 * 
 *********************************************************/
 
 /*********************************************************
  * Files referenced from the media library.
  * -----------------------------------------
  *
  * Segue has a built-in "media library" that allows users
  * to upload and manage files across their site and from
  * remote sources.
  *
  * MediaFiles are identified by a string id. To access a MediaFile
  * object use the MediaFile::withIdString() static accessor method:
  *		$mediaFile = MediaFile::withIdString('repositoryId=123&assetId=456&recordId=789');
  *
  * MediaFiles provide access to Dublin Core metadata as as urls and file 
  * properties.
  *
  * To load the media library create a button or link that
  * calls the media library's static initializer method, run(),
  * and pass it the plugin's id and a DOM Element for reference.
  * To the referenced DOM element you must attach an "onUse" method
  * which will be given a MediaFile javascript object when the
  * user chooses a file in the media library.
  *
  * Example (Note that the line-returns in the Javascript must be removed for actual usage):
  *		<input type="button" 
  *			onclick="this.onUse = function (mediaFile) {
  *
  *						alert(mediaFile.getTitles()[0]
  *							+ '\n' + mediaFile.getUrl()
  *							+ '\n' + mediaFile.getThumbnailUrl());
  *
  *					 }
  *
  *					 MediaLibrary.run('12345', this);"
  *			value="Select File"/>
  *
  * 
  * This is the preferred method for handling files.
  *
  * The methods availible in the MediaFile class are as follows:
  *
  * 	Instance Creation (static):
  *			withIdString($idString)
  *
  *		Access:
  *			getIdString()
  *			getSize()
  *			getFilename()
  *			getMimeType()
  *			getFileContents()
  *			getUrl()
  *			getThumbnailUrl()
  *			getModificationDate()
  *			
  *		Access - Dublin Core:
  *			getTitle()
  *			getTitles()
  *			getCreator()
  *			getCreators()
  *			getDescription()
  *			getDescriptions()
  *			getSource()
  *			getSources()
  *			getPublisher()
  *			getPublishers()
  *			getDate()
  *			getDates()
  *			getContributor()
  *			getContributors()
  *			getRight()
  *			getRights()
  *			getRelation()
  *			getRelations()
  *			getLanguage()
  *			getLanguages()
  *			
  *********************************************************/
 
 
 /*********************************************************
  * Files in the Plugin's data array.
  * -----------------------------------------
  *
  * Your plugin can store files in its data array if needed.
  * This may be useful if the plugin needs to handle file
  * uploads that should not be directly accessible.
  *********************************************************/
 
	/**
	 * Answer the URL for the file 
	 * 
	 * @param string $idString of the 'associated file'
	 * @param string $fname the 'FILE_NAME'
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function getThumbnailURL ($idString, $fname) {
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$repositoryId = $idManager->getId(
			"edu.middlebury.segue.sites_repository");
		$assetId = $this->_asset->getId();

		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewthumbnail", 
			array(
			"repository_id" => $repositoryId->getIdString(),
			"asset_id" => $assetId->getIdString(),
			"record_id" => $idString,
			"thumbnail_name" => $fname));
		$harmoni->request->endNamespace();
		
		return $url;
	}

	/**
	 * Returns the HTML string for viewing files associated with your plugin
	 *
	 * Choose which information to print by passing two arrays, one that is the
	 * file record array from your plugin data, and the other that is an array
	 * of the data part keys (chosen from {"FILE_NAME", "FILE_SIZE",
	 * "DIMENSIONS", "MIME_TYPE", "FILE_DATA"} having "FILE_DATA" in the array 
	 * will print the thumbnail for the file record) that you want printed.
	 *
	 * @param array $fileData array referencing the file record
	 * @param array $parts array listing parts to print 
	 * @return string 
	 * @access public
	 * @since 1/31/06
	 */
	function printFileRecord($fileData, $parts) {
		$idManager = Services::getService("Id");
		$moduleManager = Services::getService("InOutModules");
		
		$repositoryId = $idManager->getId(
			"edu.middlebury.segue.sites_repository");
		$assetId = $this->_asset->getId();
		$rid = $idManager->getId($fileData['assoc_file_id'][0]);
		$record = $this->_asset->getRecord($rid);
		$rs = $record->getRecordStructure();
		
		$setArray = array("FILE_NAME", "FILE_SIZE", "DIMENSIONS", 
			"MIME_TYPE", "FILE_DATA");
		$newArray = array_intersect($setArray, $parts);
		$partStructureArray = array();
		
		foreach ($newArray as $part) {
				$partStructureArray[] = $rs->getPartStructure(
					$idManager->getId($part));
		}

		return $moduleManager->generateDisplayForPartStructures(
			$repositoryId, $assetId, $record, $partStructureArray);
	}

	/**
	 * Answer the URL for the file 
	 * 
	 * @param string $idString of the 'associated file'
	 * @param string $fname the 'FILE_NAME'
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function getFileURL ($idString, $fname) {
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$repositoryId = $idManager->getId(
			"edu.middlebury.segue.sites_repository");
		$assetId = $this->_asset->getId();

		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewfile", 
			array(
			"repository_id" => $repositoryId->getIdString(),
			"asset_id" => $assetId->getIdString(),
			"record_id" => $idString,
			"file_name" => $fname));
		$harmoni->request->endNamespace();
		
		return $url;		
	}

	/**
	 * Answer the file data for the file
	 * 
	 * @param string $idString of the 'associated file'
	 * @return blob
	 * @access public
	 * @since 1/26/06
	 */
	function getFileData ($idString) {
		$idManager = Services::getService("Id");
		$id = $idManager->getId($idString);
		$fileRS = $this->_asset->getRecord($id);
		$data_id = $idManager->getId("FILE_DATA");
		$data = $fileRS->getPartsByPartStructure($data_id);
		if ($data->hasNext())
			$datum = $data->next();
			
		return $datum;
	}
	
/*********************************************************
 * Logging
 *********************************************************/
	
	/**
	 * Log an event. Plugins should log events that involve data modification
	 * with type 'Event_Notice' and events that involve errors with type 'Error'
	 * 
	 * @param string $category
	 * @param string $description
	 * @param optional string $type
	 * @return void
	 * @access public
	 * @since 3/6/06
	 */
	function logEvent ($category, $description, $type = 'Event_Notice') {
		ArgumentValidator::validate($category, StringValidatorRule::getRule());
		ArgumentValidator::validate($description, StringValidatorRule::getRule());
		ArgumentValidator::validate($type, ChoiceValidatorRule::getRule('Event_Notice', 'Error'));
		
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", $type,
							"Normal events.");
			
			$item = new AgentNodeEntryItem($category, $description);
			$item->addNodeId($this->_asset->getId());
			
			// Get the site Id (Note: this creates a circular dependancy between
			// the plugins package and the SiteDisplay Package.
			$idManager = Services::getService("Id");
			$nodeId = $this->_asset->getId();
			$director = new AssetSiteDirector($this->_asset->getRepository());	
			$rootSiteComponent = $director->getRootSiteComponent($nodeId->getIdString());
			
			$item->addNodeId($idManager->getId($rootSiteComponent->getId()));
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
	}

/*********************************************************
 *********************************************************
 *********************************************************
 *********************************************************
 * Non-API vars/methods
 * 
 * The variables and methods listed below are not part of the
 * plugin API and should never be called by plugins. Their
 * functionality is used by the plugin system internally
 *
 *********************************************************/

/*********************************************************
 * Class Methods - Instance Creation
 *********************************************************/
	/**
	 * Instantiate a new plugin for an Asset
	 * 
	 * @param object Asset $asset
	 * @param object ConfigurationProperties $configuration
	 * @return object Plugin OR string (error string) on error.
	 * @access public
	 * @since 1/12/06
	 */
	function newInstance ( $asset, $configuration ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		ArgumentValidator::validate($configuration, ExtendsValidatorRule::getRule("Properties"));
		$false = false;
		


		$type = $asset->getAssetType();
		$pluginManager = Services::getService("PluginManager");
		$pluginDir = $pluginManager->getPluginDir($type);
		$pluginClass = $pluginManager->getPluginClass($type);
		$pluginFile = $pluginDir.$pluginClass.".class.php";
		
		
		// Check for the file
		if (!file_exists($pluginFile))	
			return _("Error: Plugin not found at '$pluginFile'.");
		
		require_once($pluginFile);
		
		// Check for the class
		if (!class_exists($pluginClass)) 
			return _("Error: Plugin class, '$pluginClass', not found.");
		
		// Ensure that the plugin writer didn't override the constructor
		if (in_array($pluginClass, get_class_methods($pluginClass)))
			return _("Error: Plugin class should not have a constructor method, '$pluginClass'.");
		
		// Instantiate the plugin
		$plugin = new $pluginClass;
		
		$plugin->setConfiguration($configuration);
		$plugin->setAsset($asset);
		
		// Execute the decendent's initialization
		$plugin->initialize();
		
		return $plugin;
	}
	
/*********************************************************
 * Object Properties - Non-API
 *********************************************************/
	/**
	 * @var string $_id; The string Id of the Plugin/Asset 
	 * @access private
	 * @since 1/17/06
	 */
	var $_id;
	
	/**
	 * If true, editing controls will be displayed (assuming authorization)
	 * @var boolean $_showControls;  
	 * @access private
	 * @since 2/22/06
	 */
	var $_showControls = false;
	
	/**
	 * @var object URLWriter $_baseURL; URL for the plugin w/o mods
	 * @access private
	 * @since 3/1/06
	 */
	var $_baseURL;
	
	/**
	 * @var object HarmoniAsset $_asset; the asset that contains the plugin data 
	 * @access private
	 * @since 3/1/06
	 */
	var $_asset;
	
	/**
	 * @var object HarmoniConfiguration $_configuration; holds config data 
	 * @access private
	 * @since 3/1/06
	 */
	var $_configuration;
	
	/**
	 * @var string $_pluginDir; filesystem path to appropriate plugin class 
	 * @access private
	 * @since 3/1/06
	 */
	var $_pluginDir;
	
	/**
	 * @var aray $_data_ids; parallel structure to 'data' w/ part ids
	 * @access private
	 * @since 3/1/06
	 */
	var $_data_ids;
	
	/**
	 * @var array $_loadedData; the data that persisted since the last change
	 * @access private
	 * @since 3/1/06
	 */
	var $_loadedData;
	
	/**
	 * @var array $_structures; array mapping 'data' indices to structure ids 
	 * @access private
	 * @since 3/1/06
	 */
	var $_structures;
	
/*********************************************************
 * Instance Methods - Non-API
 *********************************************************/
	
	/**
	 * Set the plugin's environmental configuration
	 * 
	 * @param object ConfigurationProperties $configuration
	 * @return void
	 * @access public
	 * @since 1/12/06
	 */
	function setConfiguration ( $configuration ) {
		if (isset($this->_configuration))
			throwError(new Error("Configuration already set.", "Plugin.abstract", true));
			
		$this->_configuration = $configuration;
	}
	
	/**
	 * Inialize ourselves with our data-source asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/12/06
	 */
	function setAsset ( $asset ) {
		if (isset($this->_asset))
			throwError(new Error("Asset already set.", "Plugin.abstract", true));
		
		$this->_asset = $asset;

		$type = $this->_asset->getAssetType();

		$this->_pluginDir = $this->_configuration->getProperty("plugin_dir")."/".$type->getDomain()."/".
						$type->getAuthority()."/".$type->getKeyword()."/";
		
		$this->_loadData();
	}
	
	/**
	 * Set the status of showControls.
	 * 
	 * @param boolean $showControls
	 * @return void
	 * @access public
	 * @since 2/22/06
	 */
	function setShowControls ($showControls) {
		ArgumentValidator::validate($showControls, BooleanValidatorRule::getRule());
		$this->_showControls = $showControls;
	}
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended	If true, return the extended version. Default: false.
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function executeAndGetMarkup ( $showControls = false, $extended = false ) {
		$this->setShowControls($showControls);
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(
			get_class($this).':'.$this->getId());
		$this->_baseUrl = $harmoni->request->mkURL();
		
		$this->update($this->_getRequestData());
		
		if ($extended)
			$markup = $this->getExtendedMarkup();
		else
			$markup = $this->getMarkup();
		
		// update the description if needed
		$this->setShowControls(false);
		$desc = $this->generateDescription();
		$this->setShowControls($showControls);
		if ($desc != $this->_asset->getDescription()) {
			$this->_asset->updateDescription($desc);
		}
		
		$this->_storeData();
		
		
		$harmoni->request->endNamespace();
		
		return $markup;
	}
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended
	 * @return string
	 * @access public
	 * @since 5/23/07
	 */
	function executeAndGetExtendedMarkup ( $showControls = false) {
		return $this->executeAndGetMarkup($showControls, true);
	}
	
	/**
	 * Answer true if this plugin instance has extended content that should
	 * be linked to.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/23/07
	 */
	function hasExtendedMarkup () {
		$showControls = $this->_showControls;
		$this->_showControls = false;
		
		if ($this->getMarkup() == $this->getExtendedMarkup())
			$hasExtended = false;
		else
			$hasExtended = true;
		
		$this->_showControls = $showControls;
		return $hasExtended;
	}
	
	/**
	 * Answer the REQUEST data for this plugin instance.
	 * 
	 * @return array
	 * @access private
	 * @since 1/13/06
	 */
	function _getRequestData () {
		return array();
	}
	
	/**
	 * Load our data from our Asset
	 * 
	 * NOTE: Part Id's are used to maintain order in the part array
	 * @return void
	 * @access private
	 * @since 1/12/06
	 */
	function _loadData () {
		// one array for the data, a second for the persistence of ids
		if (isset($this->data))
			unset($this->data, $this->_data_ids);
		$this->data = array();
		$this->_data_ids = array();

		foreach (array_keys($this->getStructuresForPlugin()) as $struct) {
			$this->data[$struct] = array();
			$this->_data_ids[$struct] = array();
		}
// @todo rework using getStructures...
// initialize the data array with the structures from getstructures, but do not
// give any instances to the structures.
// get the records from the asset.  load 'em up

		// get all the records for this asset
		$records = $this->_asset->getRecords();
		
		// maintain record order
		$sets = Services::getService("Sets");
		$recordOrder = $sets->getPersistentSet($this->_asset->getId());
		$ordered = array();
		
		while ($records->hasNext()) {
			$record = $records->next();
			$rid = $record->getId();
			if (!$recordOrder->isInSet($rid))
				$recordOrder->addItem($rid);
			$ordered[$recordOrder->getPosition($rid)] = $rid;
		}

// @todo make sure the array exists for each structure, but not an instance yet

		foreach ($ordered as $recid) {
			$record = $this->_asset->getRecord($recid);
			
			// for each new recordstructure add an array for holding instances
			$recordStructure = $record->getRecordStructure();
			$rsId = $recordStructure->getId();
			$rsIdString = $rsId->getIdString();
			if ($rsIdString != "FILE") {
				$rsName = $recordStructure->getDisplayName();
				if (!in_array($rsName, array_keys($this->data))) {
					$this->data[$rsName] = array();
					$this->_data_ids[$rsName] = array();
				}
				
				// each instance itself should be acessible via index (1,2,3...)
				$this->data[$rsName][] = array();
				$this->_data_ids[$rsName][] = array();
				$instance = count($this->data[$rsName]) - 1; // current instance
	
				// each instance populates its parts like the records
				$parts = $record->getParts();
				while ($parts->hasNext()) {
					$part = $parts->next();
	
				// for each new partstructure add an array for holding instances
					$partStructure = $part->getPartStructure();
					$psName = $partStructure->getDisplayName();
					if (!in_array($psName, 	
							array_keys($this->data[$rsName][$instance]))) {
						$this->data[$rsName][$instance][$psName] = array();
						$this->_data_ids[$rsName][$instance][$psName] = array();
					}
					
					// again with the instances
					$partValue = $part->getValue();
					$id = $part->getId();
					$idString = $id->getIdString();
					$idArray = explode("::", $idString);
					$this->data[$rsName][$instance][$psName][$idArray[2]] = 
						$partValue->asString();
					$this->_data_ids[$rsName][$instance][$psName][$idArray[2]]
						= $part->getId();
				}
			}
		}
		// this call does all FILE records information
		$this->_populateFileInfo();
		
		// keep original data for modification check.
		$this->_loadedData = $this->data;
	}
	
	/**
	 * Store and changes to our data set to our Asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function _storeData () {
		if (isset($changes))
			unset($changes);
		// only change things when you must
		if (isset($this->data) && $this->_dataChanged()) {
			$changes = array();	// array for storing a part id and its new value
			
			// go through all recordstructures
			foreach ($this->data as $rs => $instances) {
				
				if (is_array($instances) && ($rs != 'FILE')) {
					// go through each instance of the recordstructure
					foreach ($instances as $instance => $record) {
						if (!isset($this->_data_ids[$rs][$instance])) 
							$this->_createInstance($rs, $instance);
						else if (is_array($record)) {
					// for each array of part values find out which have changed
							foreach ($record as $ps => $values) {
								$differences = array_diff_assoc(
									$values, 
									$this->_loadedData[$rs][$instance][$ps]);
								
								// add each change to the array of changes
								if (count($differences) > 0) {
									foreach ($differences as $key => $value) {
$changes[$this->_data_ids[$rs][$instance][$ps][$key]->getIdString()] = $value;
									}
								}
							}
						}
					}
				}
			}
		}
		if (isset($this->data['FILE']) && (count($this->data['FILE']) > 0) && 
				$this->_fileDataChanged()) {
			// handle all file data changes
			$this->_changeFileInfo();
		}
		
		// make them changes
		if (isset($changes)) {
		$idManager = Services::getService("Id");
			foreach ($changes as $idString => $value) {
				$id = $idManager->getId($idString);
				$part = $this->_asset->getPart($id);
				$part->updateValueFromString($value);
			}
 		$this->_loadedData = $this->data;	// new data persisted
		}
	}
	
	/**
	 * Changes the stored file information
	 *
	 * All changes to files are done here (even deletion) 
	 * @return void
	 * @access private
	 * @since 1/27/06
	 */
	function _changeFileInfo () {
		$idManager = Services::getService("Id");
		$changes = array();
		foreach ($this->data['FILE'] as $instance => $file) {
			$fpids = $this->_data_ids['FILE'][$instance];
			$lfile = $this->_loadedData['FILE'][$instance];
			$frecord = $this->_asset->getRecord(
				$idManager->getId($file['assoc_file_id'][0]));
		
			// delete_file can change
			if ($file['delete_file'][0] != $lfile['delete_file'][0]) {
				$this->_asset->deleteRecord($idManager->getId(
					$file['assoc_file_id'][0]));
				// unset the file in the data arrays
				unset($this->data['FILE'][$instance],
					$this->_loadedData['FILE'][$instance],
					$this->_data_ids['FILE'][$isntance]);
				if (count($this->data['FILE']) == 0)
					unset($this->data['FILE']);
			} else {
				// new_file_path can change
				if ($file['new_file_path'][0] != $lfile['new_file_path'][0]) { 
					$fparts = $this->_asset->getPartsByPartStructure(
						$idManager->getId('FILE_DATA'));
					$fpart = $fparts->next();
					$fpart->updateValue(
						file_get_contents($file['new_file_path'][0]));
				}
				if ($file['FILE_NAME'][0] != $lfile['FILE_NAME'][0]) {
				// FILE_NAME can change
					$fpart = $this->_asset->getPart(
						$idManager->getId(
						$fpids['FILE_NAME'][0]));
					$fpart->updateValueFromString($file['FILE_NAME'][0]);
				}
				
				// FILE_SIZE can't change
				if ($file['FILE_SIZE'][0] != $lfile['FILE_SIZE'][0])
					$file['FILE_SIZE'][0] = $lfile['FILE_SIZE'][0];
				
				// DIMENSIONS can't change
				if ($file['DIMENSIONS'][0] != $lfile['DIMENSIONS'][0])
					$file['DIMENSIONS'][0] = $lfile['DIMENSIONS'][0];
				
				// MIME_TYPE can't change
				if ($file['MIME_TYPE'][0] != $lfile['MIME_TYPE'][0])
					$file['MIME_TYPE'][0] = $lfile['MIME_TYPE'][0];
				
				// assoc_file_id can't change
				if ($file['assoc_file_id'][0] != $lfile['assoc_file_id'][0])
					$file['assoc_file_id'][0] = $lfile['assoc_file_id'][0];
			}			
		}	
		// @todo determine if this is a necessary step	
		$this->_populateFileInfo();
	}

	/**
	 * Populates the data array with usable file information
	 *
	 * Plugins get only minimal access to file information and handling
	 * @return void
	 * @access private
	 * @since 1/27/06
	 */
	function _populateFileInfo () {
		// plugins get specific file information, can request URL or 
		// data via functions defined above
		$idManager = Services::getService("Id");
		$frecords = $this->_asset->getRecordsByRecordStructure(
			$idManager->getId("FILE"));
		$farray = array("FILE_DATA", "THUMBNAIL_DATA", "THUMBNAIL_MIME_TYPE", 
			"THUMBNAIL_DIMENSIONS");

		// always reset the data
		$this->data['FILE'] = array();
		$this->_data_ids['FILE'] = array();

		// maintain record order
		$sets = Services::getService("Sets");
		$recordOrder = $sets->getPersistentSet($this->_asset->getId());
		$fordered = array();

		// populate fordered array with current file records
		while ($frecords->hasNext()) {
			$frecord = $frecords->next();
			$frid = $frecord->getId();
			if (!$recordOrder->isInSet($frid))
				$recordOrder->addItem($frid);
			$fordered[$recordOrder->getPosition($frid)] = $frid;
		}

		// removing outdated id's in the set.		
		$recordOrder->reset();
		while ($recordOrder->hasNext()) {
			$recId = $recordOrder->next();
			
			if (!isset($fordered[$recordOrder->getPosition($recId)]))
				$recordOrder->removeItem($recId);
		}
		
		// populate the data array with the file data
		foreach ($fordered as $frecid) {
			$frecord = $this->_asset->getRecord($frecid);
			
			$this->data['FILE'][] = array();
			$this->_data_ids['FILE'][] = array();
			
			$instance = count($this->data['FILE']) - 1;
			$file = $this->data['FILE'][$instance];
			$file_ids = $this->_data_ids['FILE'][$instance];

			$parts = $frecord->getParts();
			while ($parts->hasNext()) {
				$part = $parts->next();
				$id = $part->getId();
				$ps = $part->getPartStructure();
				$psid = $ps->getId();
				$psidString = $psid->getIdString();
				// plugin safe parts
				if (!in_array($psidString, $farray)) {
					$file[$psidString] = array();
					$file_ids[$psidString] = array();
					$file[$psidString][] = $part->getValue();
					$file_ids[$psidString][] = $id->getIdString();
				}
			}
			$file['assoc_file_id'][] = $frecid->getIdString();
			$file['new_file_path'][] = '';
			$file['delete_file'][] = '';
		}
	}
	
	/**
	 * Answer true if our data has been modified
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/13/06
	 */
	function _dataChanged () {
		// @todo test different implementations of this function
		$new = serialize($this->data);
		$old = serialize($this->_loadedData);
		if ($old == $new)
			return false;
		return true;
	}
	
	/**
	 * Answer true if our data has been modified
	 * 
	 * @return boolean
	 * @access private
	 * @since 1/13/06
	 */
	function _fileDataChanged () {
		// @todo test different implementations of this function
		$new = serialize($this->data['FILE']);
		$old = serialize($this->_loadedData['FILE']);
		if ($old == $new)
			return false;
		return true;
	}

	/**
	 * Initializes the structures of the Asset to allow for record creation
	 * 
	 * @return void
	 * @access public
	 * @since 3/1/06
	 */
	function getStructuresForPlugin () {
		if (!isset($this->_structures)) {
			$db = Services::getService("DBHandler");
			
			$type = $this->_asset->getAssetType();
			
			$query = new SelectQuery();
			$query->addTable("plugin_manager");
			$query->addTable("plugin_type", INNER_JOIN);
			$query->addWhere("plugin_type.type_id = plugin_manager.FK_plugin_type");
			$query->addWhere("plugin_type.type_domain = '".
				addslashes($type->getDomain())."'");
			$query->addWhere("plugin_type.type_authority = '".
				addslashes($type->getAuthority())."'");
			$query->addWhere("plugin_type.type_keyword = '".
				addslashes($type->getKeyword())."'");
			$query->addColumn("*");
			
			$results = $db->query($query, IMPORTER_CONNECTION);
	
			$id = Services::getService("Id");
			$rm = Services::getService("Repository");
			$sites_rep = $rm->getRepository($id->getId(
				"edu.middlebury.segue.sites_repository"));
			
			$structures = array();
			// populate structures array with displayname to id association
			while ($results->hasMoreRows()) {
				$result = $results->next();
				
				$rs = $sites_rep->getRecordStructure($id->getId(
					$result['plugin_manager.FK_schema']));
				
				$structures[$rs->getDisplayName()] =
					$result['plugin_manager.FK_schema'];
			}
			$this->_structures = $structures;
		}

		return $this->_structures;
	}

	/**
	 * Creates a new Record for the instance held in $this->data
	 * 
	 * @param string $dname RecordStructure display name
	 * @param integer $instance index in $this->data for record
	 * @access private
	 * @since 3/1/06
	 */
	function _createInstance ($dname, $instance) {
		// @todo take the data in $this->data[$rs][$instance] and create a 
		// proper record for it in the database.
		
		$rm = Services::getService("Repository");
		$id = Services::getService("Id");
		$pm = Services::getService("Plugs");
		$dtm = Services::getService("DataTypeManager");
		
		$sites_rep = $rm->getRepository($id->getId(
			"edu.middlebury.segue.sites_repository"));

		// need: RecordStructureId, asset, data
		$structures = $this->getStructuresForPlugin();
		$rs = $sites_rep->getRecordStructure($id->getId($structure[$dname]));
		$partstructs = $rs->getPartStructures();
		
		$record = $this->_asset->createRecord($id->getId($structures[$dname]));
		
		while ($partstructs->hasNext()) {
			$partstruct = $partstructs->next();
			$type = $partstruct->getType();
			// this is the class I need for the part object
			$class = $dtm->primitiveClassForType($type->getKeyword());

			foreach 
				($this->data[$dname][$instance][$partstruct->getDisplayName()] 		
					as $inst => $val) {
				eval('$object = '.$class.'::fromString($val);');
				if (!is_object($object)) {
					throwError( new Error("PluginManager", "bad part object: creating instance", true));
					// @todo handle an error here.
				} else {
					$part = $record->createPart($partstruct->getId(), $object);
					$partId = $part->getId();
					$this->_data_ids[$dname][$instance] = array();
					$this->_data_ids[$dname][$instance][$partstruct->getDisplayName()] = array();
					$this->_data_ids[$dname][$instance][$partstruct->getDisplayName()][$inst] = $partId->getIdString();
				}
			}
		}
	}
	
	/**
	 * Set a custom function for checking if the user can modify the plugin.
	 * This function must accept the plugin as its only argument and return
	 * a boolean. Use the create_function() method to create an anonymous function.
	 *
	 * This may be used to allow the plugin to make use of alternate authorization
	 * systems or settings.
	 * 
	 * @param string $function
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function setCanModifyFunction ($function) {
		$this->_canModifyFunction = $function;
	}
	
	/**
	 * Set a custom function for checking if the user can modify the plugin.
	 * This function must accept the plugin as its only argument and return
	 * a boolean. Use the create_function() method to create an anonymous function.
	 *
	 * This may be used to allow the plugin to make use of alternate authorization
	 * systems or settings.
	 * 
	 * @param string $function
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function setCanViewFunction ($function) {
		$this->_canViewFunction = $function;
	}
}
?>