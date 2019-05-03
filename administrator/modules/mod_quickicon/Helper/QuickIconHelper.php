<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Quickicon\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Categories\Administrator\Model\CategoriesModel;
use Joomla\Component\Checkin\Administrator\Model\CheckinModel;
use Joomla\Component\Content\Administrator\Model\ArticlesModel;
use Joomla\Component\Content\Administrator\Model\ModulesModel;
use Joomla\Component\Installer\Administrator\Model\ManageModel;
use Joomla\Component\Menus\Administrator\Model\ItemsModel;
use Joomla\Component\Plugins\Administrator\Model\PluginsModel;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Helper for mod_quickicon
 *
 * @since  1.6
 */
abstract class QuickIconHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @var     array[]
	 * @since   1.6
	 */
	protected static $buttons = array();
	
	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param   Registry        $params       The module parameters
	 * @param   CMSApplication  $application  The application
	 *
	 * @return  array  An array of buttons
	 *
	 * @since   1.6
	 */
	public static function &getButtons(Registry $params, CMSApplication $application = null)
	{
		if ($application == null)
		{
			$application = Factory::getApplication();
		}

		$key = (string) $params;

		if (!isset(self::$buttons[$key]))
		{
			// Load mod_quickicon language file in case this method is called before rendering the module
			$application->getLanguage()->load('mod_quickicon');

			// Include buttons defined by published quickicon plugins
			PluginHelper::importPlugin('quickicon');

			$arrays = (array) $application->triggerEvent(
				'onGetIcons',
				new QuickIconsEvent('onGetIcons', [$params->get('show_extensionupdate', '1')])
			);

			foreach ($arrays as $response)
			{
				foreach ($response as $icon)
				{
					$default = array(
						'link'   => null,
						'image'  => null,
						'text'   => null,
						'access' => true,
						'class' => true,
						'group'  => 'MOD_QUICKICON_EXTENSIONS',
					);
					$icon = array_merge($default, $icon);

					if (!is_null($icon['link']) && !is_null($icon['text']))
					{
						self::$buttons[$key][] = $icon;
					}
				}
			}

			if ($params->get('show_checkin', '1'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fa fa-unlock',
					'amount' => self::countCheckin(),
//'ajaxurl' => 'index.php?option=com_checkin&amp;task=getMenuBadgeData&amp;format=json',
					'link'   => Route::_('index.php?option=com_checkin'),
					'text'   => Text::_('MOD_QUICKICON_CHECKIN'),
					'access' => array('core.admin', 'com_checkin'),
					'link2'   => Route::_('index.php?option=com_checkin'),
					'image2'  => 'fa fa-cog',
					'text2'   => Text::_('MOD_QUICKICON_CHECKIN_ALL'),
					'access2' => array('core.admin', 'com_checkin')
				];
			}
			
			if ($params->get('show_cache', '1'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fa fa-cloud',
					'link'   => Route::_('index.php?option=com_cache'),
					'text'   => Text::_('MOD_QUICKICON_CACHE'),
					'access' => array('core.admin', 'com_cache'),
					'class' => 'cache',
					'link2'   => Route::_('index.php?option=com_ache&task=cache.empty'),
					'image2'  => 'fa fa-remove',
					'text2'   => Text::_('MOD_QUICKICON_CACHE_CLEAR'),
					'access2' => array('core.admin', 'com_cache')
				];
			}
				
			if ($params->get('show_global', '1'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fa fa-cog',
					'link'   => Route::_('index.php?option=com_config'),
					'text'   => Text::_('MOD_QUICKICON_GLOBAL'),
					'access' => array('core.admin', 'com_config'),
					'class' => 'configuration-color',
				];
			}
				
			if ($params->get('show_extensions', '1'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fa fa-puzzle-piece',
					'amount' => self::countExtensions(),
//'ajaxurl' => 'index.php?option=com_checkin&amp;task=getMenuBadgeData&amp;format=json',
					'link'   => Route::_('index.php?option=com_installer&view=manage'),
					'text'   => Text::_('MOD_QUICKICON_EXTENSIONS'),
					'access' => array('core.admin', 'com_installer'),
					'link2'   => Route::_('index.php?option=com_installer&view=install'),
					'image2'  => 'fa fa-cog',
					'text2'   => Text::_('MOD_QUICKICON_EXTENSIONS_INSTALL'),
					'access2' => array('core.admin', 'com_checkin')
				];
			}

			if ($params->get('show_users', '1'))
			{
				$amount = self::countUsers();

				self::$buttons[$key][] = [
					'image'  => 'fa fa-users',
					'amount' => self::countUsers(),
					'link'   => Route::_('index.php?option=com_users'),
					'text'   => Text::plural('MOD_QUICKICON_USERS_MANAGER', $amount),
					'access' => array('core.manage', 'com_users'),
					'image2'  => 'fa fa-plus',
					'link2'   => Route::_('index.php?option=com_users&task=user.add'),
					'text2' => Text::_('MOD_QUICKICON_USERS_ADD'),
					'access2' => array('core.create', 'com_users')
				];
			}
				
			if ($params->get('show_menuItems', '1'))
			{
				$amount = self::countMenuItems();

				self::$buttons[$key][] = [
					'image'  => 'fa fa-list',
					'amount' => self::countMenuItems(),
					'link'   => Route::_('index.php?option=com_menus'),
					'access' => array('core.manage', 'com_menus'),
					'image2'  => 'fa fa-plus',
					'text'   => Text::plural('MOD_QUICKICON_MENUITEMS_MANAGER', $amount),
					'link2'   => Route::_('index.php?option=com_menus&task=item.add'),
					'text2' => Text::_('MOD_QUICKICON_MENUITEMS_ADD'),
					'access2' => array('core.create', 'com_menus')
				];
			}

			if ($params->get('show_articles', '1'))
			{
				$amount = self::countArticles();

				self::$buttons[$key][] = [
					'image'  => 'fa fa-file-o',
					'amount' => self::countArticles(),
					'link'   => Route::_('index.php?option=com_content&view=articles'),
					'text'   => Text::plural('MOD_QUICKICON_ARTICLE_MANAGER', $amount),
					'access' => array('core.manage', 'com_content'),
					'image2'  => 'fa fa-plus',
					'link2'   => Route::_('index.php?option=com_content&task=article.add'),
					'text2' => Text::_('MOD_QUICKICON_ARTICLE_ADD'),
					'access2' => array('core.create', 'com_article')
				];
			}
		
			if ($params->get('show_categories', '1'))
			{
				$amount = self::countArticleCategories();

				self::$buttons[$key][] = [
					'image'  => 'fa fa-folder',
					'amount' => self::countArticleCategories(),
					'link'   => Route::_('index.php?option=com_categories'),
					'text'   => Text::plural('MOD_QUICKICON_CATEGORY_MANAGER', $amount),
					'access' => array('core.manage', 'com_categories'),
					'link2'   => Route::_('index.php?option=com_categories&task=category.add'),
					'text2' => Text::_('MOD_QUICKICON_CATEGORY_ADD'),
					'access2' => array('core.create', 'com_categories')
				];
			}

			if ($params->get('show_media', '1'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fa fa-image',
					'link'   => Route::_('index.php?option=com_media'),
					'text'   => Text::_('MOD_QUICKICON_MEDIA_MANAGER'),
					'access' => array('core.manage', 'com_media')
				];
			}

			if ($params->get('show_modules', '1'))
			{
				$amount = self::countModules();

				self::$buttons[$key][] = [
					'image'  => 'fa fa-th',
					'amount' => self::countModules(),
					'link'   => Route::_('index.php?option=com_modules'),
					'text'   => Text::plural('MOD_QUICKICON_MODULE_MANAGER', $amount),
					'access' => array('core.manage', 'com_modules')
				];
			}

			if ($params->get('show_plugins', '1'))
			{
				$amount = self::countPlugins();

				self::$buttons[$key][] = [
					'image'  => 'fa fa-plug',
					'amount' => self::countPlugins(),
					'link'   => Route::_('index.php?option=com_plugins'),
					'text'   => Text::plural('MOD_QUICKICON_PLUGIN_MANAGER', $amount),
					'access' => array('core.manage', 'com_plugins')
				];
			}

			if ($params->get('show_templates', '1'))
			{
				self::$buttons[$key][] = [
					'image'  => 'fa fa-pencil',
					'amount' => self::countTemplates(),
					'link'   => Route::_('index.php?option=com_templates&client_id=0'),
					'text'   => Text::_('MOD_QUICKICON_TEMPLATES'),
					'access' => array('core.admin', 'com_templates'),
					// ToDo get the active default template and overrides (,self::getTplOverride)
					'link2'   => Route::_('index.php?option=com_templates&amp;task=style.edit'),
					'text2' => Text::_('MOD_QUICKICON_TEMPLATES_OVERRIDES'),
					'access2' => array('core.edit', 'com_templates')
				];
			}


			return self::$buttons[$key];
		}
	}
	
	/**
	 * Method to get the number of installed and published extensions (no plugins).
	 * 
	 * @return  integer  The amount of published modules in frontend
	 *
	 * @since   4.0
	 */
	private static function countExtensions()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_installer')->getMVCFactory()
			->createModel('Manage', 'Administrator', ['ignore_request' => true]);
		
		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);

		return  count($model->getItems());
	}	
	
	/**
	 * Method to get the number of published modules in frontend.
	 * 
	 * @return  integer  The amount of published modules in frontend
	 *
	 * @since   4.0
	 */
	private static function countModules()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_modules')->getMVCFactory()
			->createModel('Modules', 'Administrator', ['ignore_request' => true]);
		
		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.client_id', 0);

		return  count($model->getItems());
	}
	/**
	 * Method to get the number of published articles.
	 * 
	 * @return  integer  The amount of published articles
	 *
	 * @since   4.0
	 */
	private static function countArticles()
	{
		$app = Factory::getApplication();
	
		// Get an instance of the generic articles model (administrator)
		$model = $app->bootComponent('com_content')->getMVCFactory()
			->createModel('Articles', 'Administrator', ['ignore_request' => true]);

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);

		return count($model->getItems());
	}
	
	/**
	 * Method to get the number of published menu tems.
	 * 
	 * @return  integer  The amount of active menu Items
	 *
	 * @since   4.0
	 */
	private static function countMenuItems()
	{
		$app = Factory::getApplication();
		
		// Get an instance of the menuitems model (administrator)
		$model = $app->bootComponent('com_menus')->getMVCFactory()->createModel('Items', 'Administrator', ['ignore_request' => true]);

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.client_id', 0);

		return count($model->getItems());
	}
	
	/**
	 * Method to get the number of users
	 * 
	 * @return  integer  The amount of active users
	 *
	 * @since   4.0
	 */
	private static function countUsers()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_users')->getMVCFactory()->createModel('Users', 'Administrator', ['ignore_request' => true]);

		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.state', 0);

		return count($model->getItems());
	}

	/**
	 * Method to get the number of enabled Plugins
	 * 
	 * @return  integer  The amount of enabled plugins
	 *
	 * @since   4.0
	 */
	private static function countPlugins()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_plugins')->getMVCFactory()->createModel('Plugins', 'Administrator', ['ignore_request' => true]);
		
		// Set the Start and Limit to 'all'
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.enabled', 1);

		return count($model->getItems());
	}	
	
	/**
	 * Method to get the number of content categories
	 * 
	 * @return  integer  The amount of published content categories
	 *
	 * @since   4.0
	 */
	private static function countArticleCategories()
	{
		$app = Factory::getApplication();
		
		$model = $app->bootComponent('com_categories')->getMVCFactory()->createModel('Categories', 'Administrator', ['ignore_request' => true]);

		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.extension', 'com_content');

		return count($model->getItems());
	}

	/**
	 * Method to get checkin
	 * 
	 * @return  integer  The amount of checkins
	 *
	 * @since   4.0
	 */
	private static function countCheckin()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_checkin')->getMVCFactory()->createModel('Checkin', 'Administrator', ['ignore_request' => true]);

		return $model->getTotal();
	}

	/**
	 * Method to get Templates
	 * 
	 * @return  integer  The amount of Templates
	 *
	 * @since   4.0
	 */
	private static function countTemplates()
	{
		$app = Factory::getApplication();

		$model = $app->bootComponent('com_templates')->getMVCFactory()->createModel('Templates', 'Administrator', ['ignore_request' => true]);
		
		return count($model->getItems());
	}
}
