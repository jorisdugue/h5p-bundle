<?php

namespace Studit\H5PBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Entity\ContentResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ResultService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ResultService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param $userId
     * @return ContentResult
     */
    public function handleRequestFinished(Request $request, $userId): ContentResult
    {
        $contentId = $request->get('contentId', false);
        if (!$contentId) {
            \H5PCore::ajaxError('Invalid content');
        }
        // TODO: Fire 'h5p_alter_user_result' event here.
        $contentRepo = $this->em->getRepository('Studit\H5PBundle\Entity\Content');
        $contentResultRepo = $this->em->getRepository('Studit\H5PBundle\Entity\ContentResult');
        $result = $contentResultRepo->findOneBy(['userId' => $userId, 'content' => $contentId]);
        if (!$result) {
            $result = new ContentResult($userId);
            $result->setContent($contentRepo->find($contentId));
        }
        $result->setMaxScore($request->get('maxScore') ?? $result->getMaxScore());
        $result->setFinished($request->get('finished') ?? $result->getFinished());
        $result->setOpened($request->get('opened') ?? $result->getOpened());
        $result->setScore($request->get('score') ?? $result->getScore());
        $result->setTime($request->get('time') ?? $result->getTime());
        return $result;
    }

    /**
     * remove data in content User Data.
     * @param integer $contentId
     * @param string $dataType
     * @param UserInterface $user Current user
     * @param integer $subContentId
     */
    public function removeData(int $contentId, string $dataType, $user, int $subContentId): void
    {
        $ContentUserData = $this->em->getRepository('Studit\H5PBundle\Entity\ContentUserData')->findBy(
            [
                'subContentId' => $subContentId,
                'mainContent' => $contentId,
                'dataId' => $dataType,
                'user' => $user->getUserIdentifier()
            ]
        );
        if (count($ContentUserData) > 0) {
            foreach ($ContentUserData as $content) {
                $this->em->remove($content);
            }
            $this->em->flush();
        }
    }
}
