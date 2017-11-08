<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Fields\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;

/**
 * The Group controller
 *
 * @since  3.7.0
 */
class GroupController extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string

	 * @since   3.7.0
	 */
	protected $text_prefix = 'COM_FIELDS_GROUP';

	/**
	 * The component for which the group applies.
	 *
	 * @var    string
	 * @since   3.7.0
	 */
	private $component = '';

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CmsApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$parts = \FieldsHelper::extract($this->input->getCmd('context'));

		if ($parts)
		{
			$this->component = $parts[0];
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   3.7.0
	 */
	public function batch($model = null)
	{
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Group');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_fields&view=groups');

		return parent::batch($model);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	protected function allowAdd($data = array())
	{
		return \JFactory::getUser()->authorise('core.create', $this->component);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	protected function allowEdit($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = \JFactory::getUser();

		// Check general edit permission first.
		if ($user->authorise('core.edit', $this->component))
		{
			return true;
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->component . '.fieldgroup.' . $recordId))
		{
			return true;
		}

		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->component . '.fieldgroup.' . $recordId) || $user->authorise('core.edit.own', $this->component))
		{
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		}

		return false;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   BaseModel  $model      The data model object.
	 * @param   array      $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function postSaveHook(BaseModel $model, $validData = array())
	{
		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new Registry;
			$registry->loadArray($item->params);
			$item->params = (string) $registry;
		}

		return;
	}
}