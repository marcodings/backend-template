<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Array of cpanel modules
	 *
	 * @var  array
	 */
	protected $modules = null;

	/**
	 * Array of cpanel modules
	 *
	 * @var  array
	 */
	protected $quickicons = null;

	/**
	 * Moduleposition to load
	 *
	 * @var  string
	 */
	protected $position = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$extension = $app->input->getCmd('dashboard');
		$title = ''; 

		// Generate a title for the view cPanel
		$sections = explode('.', $extension);

		if (!empty($extension))
		{
			// Title should include the name of the component.  
			$component= 'com_' . str_replace('com_', '', $sections[0]);
			
			// Try to find a language string for the component in the respective language file
			$lang = Factory::getLanguage();
			$lang->load($component, JPATH_BASE, null, false, true)
			|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

			$key = strtoupper($component);
			$title = $lang->hasKey($key) ? Text::_($key) : '';
			
			// A section can follow the component name, i.e. com_content.workflow
			if (!empty($section[1]))
			{
				// Language key then supposed to be COM_CONTENT_WORKFLOW_DASHBOARD_TITLE
				$key = strtoupper($component) . '_' . strtoupper($sections[1]) . '_DASHBOARD_TITLE';
				
				$title = $lang->hasKey($key) ? Text::_($key) : '';
			}
		}

		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_CPANEL') . ' ' . $title, 'home-2 cpanel');
		ToolbarHelper::help('screen.cpanel');

		// Display the cpanel modules
		$this->position = $sections[0] ? 'cpanel-' . $sections[0] : 'cpanel';
		$this->modules = ModuleHelper::getModules($this->position);

		$quickicons = $sections[0] ? 'icon-' . $sections[0] : 'icon';
		$this->quickicons = ModuleHelper::getModules($quickicons);

		parent::display($tpl);
	}
}
