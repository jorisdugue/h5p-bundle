<?php

namespace Studit\H5PBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Entity\Counters;
use Studit\H5PBundle\Entity\Event;

class H5PEvents extends \H5PEventBase
{
    const SCRIPTS = 'h5p.scripts';
    const STYLES = 'h5p.styles';
    const SEMANTICS = 'h5p.semantics';

    /**
     * @var int $userid
    */
    private $userid;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;
    /**
     * H5PEvents constructor.
     * @param $type
     * @param null $sub_type
     * @param null $content_id
     * @param null $content_title
     * @param null $library_name
     * @param null $library_version
     * @param int $userId
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, $type, $sub_type = null, $content_id = null, $content_title = null, $library_name = null, $library_version = null, $userId = 0)
    {
        parent::__construct($type, $sub_type, $content_id, $content_title, $library_name, $library_version);
        $this->userid = $userId;
        $this->em = $em;
    }


    /**
     * @inheritDoc
     */
    protected function save()
    {
        /*$data = $this->getDataArray();
        $event = new Event();
        $event->setUser($this->userid);
        $event->setContent($data['content_id']);
        $event->setContentTitle($data['content_title']);
        $event->setLibraryName($data['library_name']);
        $event->setLibraryVersion($data['library_version']);
        $this->em->persist($event);
        $this->em->flush();
        //$this->id = $event->getId();
        return $event->getId();*/
    }


    /**
     * Overrides H5PEventBase::saveStats().
     *
     * Add current event data to statistics counter.
     */
    protected function saveStats()
    {
        $type = $this->type . ' ' . $this->sub_type;
        /*/**
         * @var Counters $current_num
        */
        /*$current_num = $this->em->getRepository("Studit\H5PBundle\Entity\Counters")->findOneBy(['type' => $type, 'libraryName' => $this->library_name, 'libraryVersion' => $this->library_version]);
        if(!$current_num){
            $current_num = new Counters();
            $current_num->setNum(1);
            $current_num->setLibraryVersion($this->library_version);
            $current_num->setLibraryVersion($this->library_name);
            $current_num->setType($type);
            $this->em->persist($current_num);
            $this->em->flush();
        }else{
            $current_num->setNum($current_num->getNum()+1);
            $this->em->persist($current_num);
            $this->em->flush();
        }*/
    }
}
