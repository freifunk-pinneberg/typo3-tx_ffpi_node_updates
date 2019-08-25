<?php

namespace FFPI\FfpiNodeUpdates\Task;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class NotificationTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{

    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds an email field
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        if (empty($taskInfo['FfpiNodeUpdates_pid']) || empty($taskInfo['FfpiNodeUpdates_url'])) {
            if ($parentObject->getCurrentAction() === 'edit') {
                // In case of edit, and editing a test task, set to internal value if not data was submitted already
                $taskInfo['FfpiNodeUpdates_pid'] = $task->pid;
                $taskInfo['FfpiNodeUpdates_url'] = $task->path;
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo['FfpiNodeUpdates_pid'] = '';
                $taskInfo['FfpiNodeUpdates_url'] = '';
            }
        }

        $additionalFields = [];

        $fieldID = 'FfpiNodeUpdates_url';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[FfpiNodeUpdates_url]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['FfpiNodeUpdates_url']) . '" size="30">';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'nodelist.json URL',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
        $fieldID = 'FfpiNodeUpdates_pid';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[FfpiNodeUpdates_pid]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['FfpiNodeUpdates_pid']) . '" size="30">';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'Page ID',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
        return $additionalFields;
    }

    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return TRUE
     *
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject)
    {
        $ret = true;
        if (empty($submittedData['FfpiNodeUpdates_pid']) || !is_numeric($submittedData['FfpiNodeUpdates_pid'])) {
            $this->addMessage(
                'Page Id must be integer, ' . gettype($submittedData['FfpiNodeUpdates_pid']) . ' given',
                FlashMessage::ERROR
            );
            $ret = false;
        }
        if (empty($submittedData['FfpiNodeUpdates_url'])) {
            $this->addMessage(
                'URL must not be empty',
                FlashMessage::ERROR
            );
            $ret = false;
        }
        return $ret;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData Array containing the data submitted by the user
     * @param AbstractTask $task Reference to the current task object
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->pid = intval($submittedData['FfpiNodeUpdates_pid']);
        $task->path = trim($submittedData['FfpiNodeUpdates_url']);
    }
}