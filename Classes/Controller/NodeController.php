<?php
namespace FFPI\FfpiNodeUpdates\Controller;

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2016 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

/**
 * NodeController
 */
class NodeController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * nodeRepository
     *
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository
     * @inject
     */
    protected $nodeRepository = null;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $nodes = $this->nodeRepository->findAll();
        $this->view->assign('nodes', $nodes);
    }

    /**
     * action show
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Node $node
     * @return void
     */
    public function showAction(\FFPI\FfpiNodeUpdates\Domain\Model\Node $node)
    {
        $this->view->assign('node', $node);
    }
}
