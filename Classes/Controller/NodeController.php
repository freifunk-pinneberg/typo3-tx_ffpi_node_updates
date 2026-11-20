<?php

namespace FFPI\FfpiNodeUpdates\Controller;

use Psr\Http\Message\ResponseInterface;
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
    public function __construct(protected NodeRepository $nodeRepository)
    {
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction(): ResponseInterface
    {
        $nodes = $this->nodeRepository->findAll();
        $this->view->assign('nodes', $nodes);
        return $this->htmlResponse();
    }

    /**
     * action show
     *
     * @param Node $node
     * @return void
     */
    public function showAction(Node $node): ResponseInterface
    {
        $this->view->assign('node', $node);
        return $this->htmlResponse();
    }
}
