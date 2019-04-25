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

		$position = str_replace('.', '-', $extension);

		// Generate a title for the view cPanel
		$parts = explode('.', $extension);

		$component= 'com_' . str_replace('com_', '', $parts[0]);
		$section = !empty($parts[1]) ? $parts[1] : '';

		// Need to load the language file 
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// Search for a component title
		if ($lang->hasKey($component_title_key = strtoupper($component . ($section ? "_$section" : '_DASHBOARD_TITLE'))))
		{
			$title = Text::_($component_title_key);
		}
		elseif ($lang->hasKey($component_section_key = strtoupper($component . ($section ? "_$section" : ''))))
		// Else if the component section string exits, let's use it
		{
			$title = Text::sprintf('COM_CPANEL_DASHBOARD_TITLE', $this->escape(Text::_($component_section_key)));
		}
		else
		// Else use the base title
		{
			$title = Text::_('COM_CPANEL_DASHBOARD_BASE_TITLE');
		}

		// Set toolbar items for the page
		ToolbarHelper::title(Text::_($title, 'home-2 cpanel'));
		ToolbarHelper::help('screen.cpanel');

		// Display the cpanel modules
		$this->position = $position ? 'cpanel-' . $position : 'cpanel';
		$this->modules = ModuleHelper::getModules($this->position);

		$quickicons = $position ? 'icon-' . $position : 'icon';
		$this->quickicons = ModuleHelper::getModules($quickicons);

		parent::display($tpl);
	}
}
