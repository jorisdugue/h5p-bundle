<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: "h5p_points")]
class Points
{
    /**
     * @var int|null
     */
    #[ORM\Column(name: "user_id", type: "integer")]
    private ?int $user;
    /**
     * @var Content|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Content::class)]
    #[ORM\JoinColumn(name: "content_main_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?Content $content;
    /**
     * @var int|null
     */
    #[ORM\Column(name: "started", type: "integer")]
    private ?int $started;
    /**
     * @var int
     */
    #[ORM\Column(name: "finished", type: "integer")]
    private int $finished = 0;
    /**
     * @var int|null
     */
    #[ORM\Column(name: "points", type: "integer", nullable: true)]
    private ?int $points;
    /**
     * @var int|null
     */
    #[ORM\Column(name: "max_points", type: "integer", nullable: true)]
    private $maxPoints;
    /**
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @param integer $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    /**
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * @param Content $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    /**
     * @return integer
     */
    public function getStarted()
    {
        return $this->started;
    }
    /**
     * @param integer $started
     */
    public function setStarted($started)
    {
        $this->started = $started;
    }
    /**
     * @return integer
     */
    public function getFinished()
    {
        return $this->finished;
    }
    /**
     * @param integer $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
    }
    /**
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }
    /**
     * @param integer $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }
    /**
     * @return integer
     */
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }
    /**
     * @param integer $maxPoints
     */
    public function setMaxPoints($maxPoints)
    {
        $this->maxPoints = $maxPoints;
    }
}
