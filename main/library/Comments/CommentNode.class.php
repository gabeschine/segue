<?php
/**
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentNode.class.php,v 1.18 2008/04/11 20:40:34 adamfranco Exp $
 */ 

/**
 * A CommentNode is an asset that may contain comments. The root of a comment-hierarchy
 * is a CommentNode, but not a Comment itself. Comments extend CommentNodes. CommentNode
 * provides access to authorization and settings for Commenting as well as child comments.
 * 
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentNode.class.php,v 1.18 2008/04/11 20:40:34 adamfranco Exp $
 */
class CommentNode {
		
	/**
	 * Constructor
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 6/7/07
	 */
	function CommentNode ( $asset ) {
		$this->_asset = $asset;
		$this->_enableEditForm = false;
	}
	
	/**
	 * Answer the Id.
	 * 
	 * @return object Id
	 * @access public
	 * @since 7/3/07
	 */
	function getId () {
		return $this->_asset->getId();
	}
	
	/**
	 * Answer the id string
	 * 
	 * @return string
	 * @access public
	 * @since 7/5/07
	 */
	function getIdString () {
		$id = $this->getId();
		return $id->getIdString();
	}
	
	/**
	 * Answer the date this comment was posted
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/3/07
	 */
	function getCreationDate () {
		return $this->_asset->getCreationDate();
	}
	
	/**
	 * Answer the date that the comment was modified
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/3/07
	 */
	function getModificationDate () {
		return $this->_asset->getModificationDate();
	}
	
	/**
	 * Answer the subject of the comment.
	 * 
	 * @return string
	 * @access public
	 * @since 7/3/07
	 */
	function getSubject () {
		return HtmlString::getSafeHtml($this->_asset->getDisplayName());
	}
	
	/**
	 * Update the subject
	 * 
	 * @param string $subject
	 * @return void
	 * @access public
	 * @since 7/11/07
	 */
	function updateSubject ( $subject ) {
		// Check Authorizations
		$authZ = Services::getService('AuthZ');
		if (!$this->canModify())
			throw new PermissionDeniedException("You are not authorized to change this comment.");
		
		if ($subject)
			$this->_asset->updateDisplayName(HtmlString::getSafeHtml($subject));
		else
			$this->_asset->updateDisplayName(_("(untitled)"));
		
		CommentManager::logMessage('Comment Subject Updated', CommentManager::getCommentParentAsset($this), array($this->getId()));
	}
	
	/**
	 * Answer the comment body.
	 * 
	 * @param optional boolean $showControls Defaults to true.
	 * @return string
	 * @access public
	 * @since 7/3/07
	 */
	function getBody ($showControls = true) {
		// Only return a body if we are authorized to view the comment
		if ($this->canView()) {
			$pluginManager = Services::getService('PluginManager');
			$plugin = $pluginManager->getPlugin($this->_asset);
			
			// Attach the owning Site component
			$owningAsset = CommentManager::getCommentParentAsset($this);
			$director = SiteDispatcher::getSiteDirector();
			$owningComponent = $director->getSiteComponentFromAsset($owningAsset);
			$plugin->setRelatedSiteComponent($owningComponent);
			
			try {
				$plugin->setUpdateAction('comments', 'update_plugin_ajax');
			} catch (UnimplementedException $e) {
			}
			
			// We've just checked our view permission, so use true
			$plugin->setCanViewFunction(create_function('$plugin', 'return true;'));
			
			if ($this->canModify() && $showControls)
			{
				$plugin->setCanModifyFunction(create_function('$plugin', 'return true;'));
				return $plugin->executeAndGetMarkup(true);
			} else {
				$plugin->setCanModifyFunction('$plugin', 'return false;');
				return $plugin->executeAndGetMarkup(false);
			}
		} else {
			return _("You are not authorized to view this comment.");
		}
	}
	
	/**
	 * Answer true if the current user can view the comment
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/10/07
	 */
	function canView () {
		if (!isset($this->_canView)) {
			$azManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			$this->_canView = $azManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view_comments"),
				$this->getId());
		}
		return $this->_canView;
	}
	
	/**
	 * Answer true if the current user can modify the comment.
	 * If we are authorized to comment, are the comment author, and there are
	 * no replies yet, allow us to edit the comment.
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/10/07
	 */
	function canModify () {
		if (!isset($this->_canModify)) {
			$azManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			$this->_canModify = $azManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.comment"),
				$this->getId());
				
			if (!$this->isAuthor())
				$this->_canModify = FALSE;
				
			if ($this->numReplies() > 0)
				$this->_canModify = FALSE;
		}
		return $this->_canModify;
	}
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/8/07
	 */
	public function canReply () {
		// Check Authorizations
		$authZ = Services::getService('AuthZ');
		$idManager = Services::getService("Id");
		
		if (CommentManager::getCurrentAgent()->isEqual($idManager->getId('edu.middlebury.agents.anonymous')))
			return false;
		
		if ($authZ->isUserAuthorized(
			$idManager->getId('edu.middlebury.authorization.comment'),
			$this->getId()))
		{
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Answer the number of replies to this comment
	 * 
	 * @return integer
	 * @access public
	 * @since 7/3/07
	 */
	function numReplies () {
		$replies = $this->getReplies();
		return $replies->count();
	}
	
	/**
	 * Answer the replies in ascending or descending time.
	 * 
	 * @param string $order The constant ASC or DESC for ascending time (oldest 
	 *			first) or decending time (recent first).
	 * @return iterator
	 * @access public
	 * @since 7/3/07
	 */
	function getReplies ( $order = ASC ) {
		// Load the replies, their creation times into arrays for caching and 
		// easy sorting.
		if (!isset($this->_replies)) {
			$this->_replyIds = array();
			$this->_replyTimes = array();
			
			$mediaFileType = new Type ('segue', 'edu.middlebury', 'media_file',
				'A file that is uploaded to Segue.');
				
			$children = $this->_asset->getAssets();
			
			while ($children->hasNext()) {
				$child = $children->next();
				if (!$mediaFileType->isEqual($child->getAssetType())) {
					$dateTime = $child->getCreationDate();
					$this->_replyIds[] = $child->getId();
					$this->_replyTimes[] = $dateTime->asString();
				}
			}
		}
		
		// Sort the reply Ids based on time.
		array_multisort($this->_replyIds, $this->_replyTimes, 
			(($order == ASC)?SORT_ASC:SORT_DESC));
		
		$null = null;
		$replies = new HarmoniIterator($null);
		$commentManager = CommentManager::instance();
		foreach ($this->_replyIds as $id) {
			$replies->add($commentManager->getComment($id));
		}
		
		return $replies;
	}
	
	/**
	 * Flag the edit form to be displayed
	 * 
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function enableEditForm () {
		$this->_enableEditForm = true;
	}
	
	/**
	 * Answer the Agent that represents the author of the comment.
	 * 
	 * @return object Agent
	 * @access public
	 * @since 7/5/07
	 */
	function getAuthor () {
		$agentManager = Services::getService('Agent');
		
		if ($this->_asset->getCreator()) {
			return $agentManager->getAgent($this->_asset->getCreator());
		} else {
			$idManager = Services::getService('Id');
			return $agentManager->getAgent($idManager->getId('edu.middlebury.agents.anonymous'));
		}
	}
	
	/**
	 * Answer true if the current user is the author of the comment
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/5/07
	 */
	function isAuthor () {
		$author = $this->getAuthor();
		$authorId = $author->getId();
		
		$idManager = Services::getService('Id');
		$anonId = $idManager->getId('edu.middlebury.agents.anonymous');
		if ($anonId->isEqual($authorId))
			return false;
		
		$authN = Services::getService("AuthN");
		$agentM = Services::getService("Agent");
		$authTypes = $authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType = $authTypes->next();
			if ($authorId->isEqual($authN->getUserId($authType))) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Answer true if the comment has content and can therefor be show and replied-to.
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/13/07
	 */
	function hasContent () {
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($this->_asset);
		return $plugin->hasContent();
	}
	
	/**
	 * Answer the markup for this comment.
	 * 
	 * @param boolean $showThreadedReplies
	 * @return string
	 * @access public
	 * @since 7/5/07
	 */
	function getMarkup ($showThreadedReplies) {
		$harmoni = Harmoni::instance();
		
		ob_start();
		print "\n\t<div class='comment' id='comment_".$this->getIdString()."'>";
		
		if ($this->_enableEditForm) {
			print "<a name='".RequestContext::name('current')."'></a>";
		}
		
		print "\n\t\t<div class='comment_display'>";
		
		print "\n\t\t\t<form class='comment_controls' action='#' method='get'>";
		$controls = array();
		if ($this->canModify()) {
			ob_start();
			print "\n\t\t\t\t<a href='#' onclick=\"this.parentNode.nextSibling.style.display='none'; this.parentNode.nextSibling.nextSibling.style.display='block'; return false;\">"._("edit subject")."</a>";
			$controls[] = ob_get_clean();
			
			ob_start();
			$deleteUrl = SiteDispatcher::mkURL();
			$deleteUrl->setValue('delete_comment', $this->getIdString());
			print "\n\t\t\t\t<a ";
			print "href='".$deleteUrl->write()."#".RequestContext::name('top')."'";
			print " onclick=\"";
			print "if (!confirm('"._("Are you sure that you want to delete this comment?")."')) { ";
			
			print "return false; ";
			print "}";
			print "\">"._("delete")."</a>";
			$controls[] = ob_get_clean();
		}
		if ($this->hasContent() && $this->canReply()) {
			ob_start();
			$replyUrl = SiteDispatcher::mkURL();
			$replyUrl->setValue('reply_parent', $this->getIdString());
			print "\n\t\t\t\t<a href='#' onclick=\"CommentPluginChooser.run(this, '".$replyUrl->write()."#".RequestContext::name('current')."', '".rawurlencode(_('Re: ').$this->getSubject())."'); return false;\">"._("reply")."</a>";
			$controls[] = ob_get_clean();
		}
		
		print implode(" | ", $controls);
		print "\n\t\t\t</form>";
		
		print "<div class='comment_title'";
		if ($this->canModify()) {
			print " onclick=\"this.style.display='none'; this.nextSibling.style.display='block'; this.nextSibling.".RequestContext::name('subject').".focus();\"";
		}
		print ">";
		print $this->getSubject();
		print "\n\t\t\t</div>";
		if ($this->canModify()) {
			print "<form action='"
				.SiteDispatcher::quickURL()."#".RequestContext::name('top')."'"
				." method='post'";
			print " style='display: none;'";
			print " onsubmit=\"";
			print "updateCommentSubject (this, this.previousSibling); ";
			print "this.style.display='none'; ";
			print "this.previousSibling.style.display='block'; ";
			print "return false; \"";
			print ">";
			print "\n\t\t\t\t<input type='text' name='".RequestContext::name('subject')."' value=\"".$this->getSubject()."\"/>";
			print "\n\t\t\t\t<input type='hidden' name='".RequestContext::name('comment_id')."' value=\"".$this->getIdString()."\"/>";
			print "\n\t\t\t\t<input type='submit' name='".RequestContext::name('submit')."' value=\""._("Update Subject")."\"/>";
			print "\n\t\t\t\t<input type='button' name='".RequestContext::name('cancel')."' value=\""._("Cancel")."\" onclick=\"this.parentNode.style.display='none'; this.parentNode.previousSibling.style.display='block'; return false;\"/>";
			print "\n\t\t\t</form>";
		}
		
		print "\n\t\t\t<div class='comment_byline'>";
		$author = $this->getAuthor();
		$date = $this->getCreationDate();
		$dateString = $date->dayOfWeekName()." ".$date->monthName()." ".$date->dayOfMonth().", ".$date->year();
		$time = $date->asTime();
		print str_replace('%1', $author->getDisplayName(),
				str_replace('%2', $dateString,
					str_replace('%3', $time->string12(),
						_("by %1 on %2 at %3"))));
		print "\n\t\t\t</div>";
		
		print "\n\t\t\t<div class='comment_body'>";
		print $this->getBody();
		print "\n\t\t\t</div>";
		print "\n\t\t</div>";
		
				
		if ($showThreadedReplies) {
			print "\n\t\t<div class='comment_replies'>";
			
			$replies = $this->getReplies(ASC);
			while ($replies->hasNext()) {
				$reply = $replies->next();
				// If this is a work in progress that has not had content added yet, 
				// do not display it.
				if ($reply->hasContent() || $reply->isAuthor()) {
					print "\n\t\t\t\t<img src='".MYPATH."/images/reply_indent.png' class='reply_icon' alt='"._('reply')."'/>";
					print "\n\t\t\t<div class='comment_reply'>";
					print $reply->getMarkup(true);
					
					print "\n\t\t\t</div>";
				}
			}
			
			print "\n\t\t</div>";
		}
		
		print "\n\t</div>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the Asset for this comment.
	 * 
	 * @return Asset
	 * @access public
	 * @since 1/17/08
	 */
	public function getAsset () {
		return $this->_asset;
	}
}

?>