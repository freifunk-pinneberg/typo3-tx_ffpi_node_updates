<?php
namespace FFPI\FfpiNodeUpdates\Task;

use TYPO3\CMS\Scheduler\Task\AbstractTask;

class ImportTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{

    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds an email field
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject)
    {
        if (empty($taskInfo['FfpiNodeUpdates_pid'])) {
            if ($parentObject->CMD === 'edit') {
                // In case of edit, and editing a test task, set to internal value if not data was submitted already
                $taskInfo['FfpiNodeUpdates_pid'] = $task->pid;
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo['FfpiNodeUpdates_pid'] = '';
            }
        }

        $fieldID = 'FfpiNodeUpdates_pid';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[FfpiNodeUpdates_pid]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['FfpiNodeUpdates_pid']) . '" size="30">';
        $additionalFields = [];
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
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject)
    {
        if (!empty($submittedData['FfpiNodeUpdates_pid']) AND is_numeric($submittedData['FfpiNodeUpdates_pid'])) {
            return true;
        } else {
            $parentObject->addMessage(
                'Page Id must be integer, ' . gettype($submittedData['FfpiNodeUpdates_pid']) . ' given',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            return false;
        }
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData Array containing the data submitted by the user
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the current task object
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        $task->pid = intval($submittedData['FfpiNodeUpdates_pid']);
    }
}