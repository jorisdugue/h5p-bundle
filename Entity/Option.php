<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="OptionRepository")
 * @ORM\Table(name="h5p_option")
 */
class Option
{
    private const INTEGER = "integer";
    private const BOOLEAN = "boolean";
    private const STRING = "string";

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->type === self::INTEGER) {
            return intval($this->value);
        } elseif ($this->type === self::BOOLEAN) {
            return boolval($this->value);
        } else {
            return $this->value;
        }
    }

    /**
     * @param string|int|bool $value
     * @throws InvalidArgumentException
     */
    public function setValue($value): void
    {
        if (is_int($value)) {
            $this->type = self::INTEGER;
        } elseif (is_bool($value)) {
            $this->type = self::BOOLEAN;
        } elseif (is_string($value)) {
            $this->type = self::STRING;
        } else {
            throw new InvalidArgumentException("The value type is not supported.");
        }
        $this->value = strval($value);
    }
}
