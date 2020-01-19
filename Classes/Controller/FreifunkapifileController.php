<?php

namespace FFPI\FfpiNodeUpdates\Controller;

use FFPI\FfpiNodeUpdates\Domain\Model\FreifunkApiFile;
use FFPI\FfpiNodeUpdates\Domain\Model\Node;
use FFPI\FfpiNodeUpdates\Domain\Repository\FreifunkApiFileRepository;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;

/**
 * Class FreifunkApiFileController
 *
 * @package FFPI\FfpiNodeUpdates\Controller
 */
class FreifunkapifileController extends ActionController
{
    /** @var JsonView */
    protected $view;

    /** @var string */
    protected $defaultViewObjectName = JsonView::class;

    /** @var FreifunkApiFileRepository */
    protected $freifunkApiFileRepository;

    /** @var NodeRepository */
    protected $nodeRepository;

    /**
     * @param FreifunkApiFileRepository $freifunkApiFileRepository
     */
    public function injectFreifunkApiFileRepository(FreifunkApiFileRepository $freifunkApiFileRepository)
    {
        $this->freifunkApiFileRepository = $freifunkApiFileRepository;
    }

    /**
     * @param NodeRepository $nodeRepository
     */
    public function injectNodeRepository(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    public function showAction()
    {
        /** @var FreifunkApiFile $apiFile */
        $apiFile = $this->freifunkApiFileRepository->findAll()->getFirst();
        if (!($apiFile instanceof FreifunkApiFile)) {
            return false;
        }
        $activeNodeCount = count($this->getActiveNodes());
        $apiFile->setActiveNodes($activeNodeCount);
        $json['value'] = $apiFile->getJson();
        $this->view->assignMultiple($json);
    }

    /**
     * Active Nodes
     * A node is active if it was online in the last 2 Weeks.
     *
     * @return array
     * @throws \Exception
     */
    protected function getActiveNodes(): array
    {
        /** @var Node[] $allNodes */
        $allNodes = $this->nodeRepository->findAll();
        $activeNodes = [];
        foreach ($allNodes as $node) {
            if ($node->isOnline()) {
                // Node is Online. Add it to the list, an got to the next.
                $activeNodes[] = $node;
                continue;
            }
            // Node is Offline, check if it was online in the last 2 Weeks.
            $lastChangeTime = $node->getLastChange();
            $now = new \DateTime();
            $diff = $lastChangeTime->diff($now);
            if ($diff->d <= 14) {
                $activeNodes[] = $node;
            }
        }
        return $activeNodes;
    }
}