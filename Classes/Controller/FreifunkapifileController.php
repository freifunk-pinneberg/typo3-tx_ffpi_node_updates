<?php

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

namespace FFPI\FfpiNodeUpdates\Controller;

use phpDocumentor\Reflection\Types\ClassString;
use Psr\Http\Message\ResponseInterface;
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

    /**
     * The default view class to use. Keep this 'null' for default fluid
     * view, or set to 'JsonView::class' or some inheriting class.
     *
     * @var class-string|null
     */
    protected ?string $defaultViewObjectName = JsonView::class;

    public function __construct(protected FreifunkApiFileRepository $freifunkApiFileRepository, protected NodeRepository $nodeRepository)
    {
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    public function showAction(): ResponseInterface
    {
        /** @var FreifunkApiFile $apiFile */
        $apiFile = $this->freifunkApiFileRepository->findAll()->getFirst();
        if (!($apiFile instanceof FreifunkApiFile)) {
            trigger_error('No API-File found', E_USER_ERROR);
        }
        $activeNodeCount = count($this->getActiveNodes());
        $apiFile->setActiveNodes($activeNodeCount);
        $json['value'] = $apiFile->getJson();
        $this->view->assignMultiple($json);
        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Cache-Control', 'public, max-age=172800, stale-while-revalidate=345600')
            ->withBody($this->streamFactory->createStream($this->view->render()));

    }

    /**
     * Active Nodes
     * A node is active if it was online in the last 2 Weeks.
     *
     * @return array<mixed>
     * @throws \Exception
     */
    protected function getActiveNodes(): array
    {
        /** @var Node[] $allNodes */
        $allNodes = $this->nodeRepository->findAll();
        $activeNodes = [];
        $now = new \DateTime();
        foreach ($allNodes as $allNode) {
            if ($allNode->isOnline()) {
                // Node is Online. Add it to the list, and got to the next.
                $activeNodes[] = $allNode;
                continue;
            }
            // Node is Offline, check if it was online in the last 2 Weeks.
            $lastChangeTime = $allNode->getLastChange();
            if($lastChangeTime instanceof \DateTime) {
                $diff = $lastChangeTime->diff($now);
                if ($diff->days <= 14) {
                    $activeNodes[] = $allNode;
                }
            }
        }
        return $activeNodes;
    }
}
