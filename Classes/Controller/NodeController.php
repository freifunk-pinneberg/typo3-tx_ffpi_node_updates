<?php

namespace FFPI\FfpiNodeUpdates\Controller;

use FFPI\FfpiNodeUpdates\Domain\Model\Node;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
class NodeController extends ActionController
{
    /**
     * nodeRepository
     *
     * @var NodeRepository
     *
     */
    protected $nodeRepository;

    /**
     * @param NodeRepository $nodeRepository
     */
    public function injectNodeRepository(NodeRepository $nodeRepository): void
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction(): void
    {
        $nodes = $this->nodeRepository->findAll();
        $this->view->assign('nodes', $nodes);
    }

    /**
     * action show
     *
     * @param Node $node
     * @return void
     */
    public function showAction(Node $node): void
    {
        $this->view->assign('node', $node);
    }
}
