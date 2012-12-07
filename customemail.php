<?php
/**
 * @package     Crafty.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2013 Gary A. Mort. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Custom  User Registration email  plugin
 *
 * @package     Crafty.Plugin
 * @subpackage  User.joomla
 */
class plgUserCustomemail extends JPlugin
{


	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param	array		$user		Holds the new user data.
	 * @param	boolean		$isnew		True if a new user is stored.
	 * @param	boolean		$success	True if user was succesfully stored in the database.
	 * @param	string		$msg		Message.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$app	= JFactory::getApplication();
		$config	= JFactory::getConfig();
		$domain = 'domain'; // set the domain from the params here
		$siteDomain = 'domain'; // set the domain from uri here

		if ($app->isAdmin()) {
			return;
		}

		if (($isnew)
			&& !($app->isAdmin())
			&& ($siteDomain == $domain)
			)
		{


					// Load user_joomla plugin language (not done automatically).
					//$lang = JFactory::getLanguage();
					//$lang->load('plg_customemail');

			// Set the link to activate the user account.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;

			// Update the user with the activation code
			// Get the old user
			$realUser = new JUser($user->id);

			// bind the new data
			$realUser->bind($data);

			// save the user
			$realUser->save(true);



					// Compute the mail subject.
					$emailSubject = JText::sprintf(
						'PLG_CUSTOMEMAIL_NEW_USER_EMAIL_SUBJECT',
						$user['name'],
						$config->get('sitename')
					);

			$emailBody = JText::sprintf(
				'PLG_CUSTOMEMAIL_ACTIVATION_BODY_NOPW=',
				$user['name'],
				$config->get('sitename'),
				$base.'index.php?option=com_users&task=registration.activate&token='.$data['activation'] ,
				JUri::root(),
				$user['name']
			);

					// Assemble the email data...the sexy way!
					$mail = JFactory::getMailer()
						->setSender(
							array(
								$config->get('mailfrom'),
								$config->get('fromname')
							)
						)
						->addRecipient($user['email'])
						->setSubject($emailSubject)
						->setBody($emailBody);

					if (!$mail->Send()) {
						JError::raiseWarning(500, JText::_('ERROR_SENDING_EMAIL'));
					}

			// Redirect the app here to send them to a the success page
		}

	}

}