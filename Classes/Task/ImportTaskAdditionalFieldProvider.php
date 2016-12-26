<?php
namespace FFPI\FfpiNodeUpdates\Task;

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

    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject)
    {
        if (!empty($submittedData['FfpiNodeUpdates_pid']) AND is_numeric($submittedData['FfpiNodeUpdates_pid'])) {
            $parentObject->addMessage('Page Id must be integer', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
            return true;
        } else {
            return false;
        }
    }

    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        $task->pid = intval($submittedData['FfpiNodeUpdates_pid']);
    }
}