<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="h5p_content_result")
 */
class ContentResult
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var Content
     *
     * @ORM\ManyToOne(targetEntity="Studit\H5PBundle\Entity\Content")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $content;
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $userId;
    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $score;
    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxScore;
    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $opened;
    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $finished;
    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $time;
    /**
     * ContentResult constructor.
     * @param string $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return Content|null
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * @param Content|null $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * @return int|null
     */
    public function getScore()
    {
        return $this->score;
    }
    /**
     * @param int|null $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }
    /**
     * @return int|null
     */
    public function getMaxScore()
    {
        return $this->maxScore;
    }
    /**
     * @param int|null $maxScore
     */
    public function setMaxScore($maxScore)
    {
        $this->maxScore = $maxScore;
    }
    /**
     * @return int|null
     */
    public function getOpened()
    {
        return $this->opened;
    }
    /**
     * @param int|null $opened
     */
    public function setOpened($opened)
    {
        $this->opened = $opened;
    }
    /**
     * @return int|null
     */
    public function getFinished()
    {
        return $this->finished;
    }
    /**
     * @param int|null $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
    }
    /**
     * @return int|null
     */
    public function getTime()
    {
        return $this->time;
    }
    /**
     * @param int|null $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }
}
