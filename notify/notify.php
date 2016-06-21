<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class PlgContentNotify extends JPlugin
{
	public function onContentAfterSave($context, $article, $isNew)
	{
		//Only run this for articles
		if ($context != 'com_content.form' && $context != 'com_content.article')
		{
			return true;
		}
		//Check this is a new article or existing article. Create suffix for language string.
		if ($isNew)
		{
			$newArticle="NEW_ARTICLE"; 
		}
		else
		{
			$newArticle="EXISTING_ARTICLE"; 
		}
		// Check if "usergroup select" is enabled.
		if ($this->params->def('select_usergroup_notify', 1))
		{	
			$ugroup = $this->params->def('email_usergroup_notify');
			$ugroup = implode(',', $ugroup);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('user_id'))
				->from($db->quoteName('#__user_usergroup_map'))
				->where($db->quoteName('group_id') . ' IN ('. $ugroup .')');
			$db->setQuery($query);
			$users = (array) $db->loadColumn();
			//JError::raiseNotice( 100, '->' . $context);
		}
		else 
		{
			//get the user owning (created by) the category. Core "Uncategorised" is excluded as it does not have a "creator" by default.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('created_user_id'))
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('title') . ' <> "Uncategorised"')
				->where($db->quoteName('id') . ' = ' . $article->catid);
			$db->setQuery($query);
			$users = (array) $db->loadColumn();
		}
		if (empty($users))
		{
			return true;
		}
		$user = JFactory::getUser();
		//Messaging for new items
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/models', 'MessagesModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/tables');
		$default_language = JComponentHelper::getParams('com_languages')->get('administrator');
		$debug = JFactory::getConfig()->get('debug_lang');
		foreach ($users as $user_id)
		{
			if ($user_id != $user->id)
			{
				// Load language for messaging
				$receiver = JUser::getInstance($user_id);
				$lang = JLanguage::getInstance($receiver->getParam('admin_language', $default_language), $debug);
				$lang->load('plg_content_notify');
				$message = array(
					'user_id_to' => $user_id,
					'subject' => sprintf($lang->_('PLG_CONTENT_NOTIFY_'.$newArticle), $article->title),
					'message' => sprintf($lang->_('PLG_CONTENT_NOTIFY_ON_'.$newArticle), $user->get('name'), '<a href="'.JURI::root().'administrator/index.php?option=com_content&task=article.edit&id=' . $article->id.'">'.$article->title.'</a>')
				);
				$model_message = JModelLegacy::getInstance('Message', 'MessagesModel');
				$result = $model_message->save($message);
			}
		}
		return $result;
	}
}
