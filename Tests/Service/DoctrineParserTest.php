<?php

namespace Studit\H5PBundle\Tests\Service;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Studit\H5PBundle\Service\DoctrineParser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Studit\H5PBundle\Utils\VersionORM;

class DoctrineParserTest extends TestCase
{
    /**
     * Test that buildParams returns an ArrayCollection for Doctrine ORM v3.
     * @throws Exception
     */
    public function testBuildParamsForDoctrineV3()
    {
        // Mock VersionProvider to simulate Doctrine v2 version
        $mockVersionORM = $this->createMock(VersionORM::class);
        $mockVersionORM->method('getDoctrineVersion')
            ->willReturn('3.1.0'); // Simulate Doctrine v2 version
        // Inject the mocked version provider into DoctrineParser
        $doctrineParser = new DoctrineParser($mockVersionORM);

        // Define test parameters
        $params = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        // Call the method under test
        $result = $doctrineParser->buildParams($params);

        // Assert that the result is an instance of ArrayCollection
        $this->assertInstanceOf(ArrayCollection::class, $result);

        // Assert that the ArrayCollection contains Parameter objects
        foreach ($result as $param) {
            $this->assertInstanceOf(Parameter::class, $param);
        }

        // Assert that the parameters inside the Parameter objects match the input parameters
        $this->assertEquals('value1', $result[0]->getValue());
        $this->assertEquals('value2', $result[1]->getValue());
    }

    /**
     * Test that buildParams returns the original parameters as an array for Doctrine ORM v2.
     * @throws Exception
     */
    public function testBuildParamsForDoctrineV2()
    {
        // Mock VersionProvider to simulate Doctrine v2 version
        $mockVersionORM = $this->createMock(VersionORM::class);
        $mockVersionORM->method('getDoctrineVersion')
            ->willReturn('2.9.3'); // Simulate Doctrine v2 version

        // Inject the mocked version provider into DoctrineParser
        $doctrineParser = new DoctrineParser($mockVersionORM);

        // Define test parameters
        $params = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        // Call the method under test
        $result = $doctrineParser->buildParams($params);

        // Assert that the result is an array (not an ArrayCollection)
        $this->assertIsArray($result);

        // Assert that the returned array contains the same values as the input parameters
        $this->assertSame($params, $result);
    }
}
