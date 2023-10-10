<?php

namespace Chuva\Tests\Unit\WebScrapping\WebScrapping\Entity;

use Chuva\Php\WebScrapping\Entity\Paper;
use Chuva\Php\WebScrapping\Entity\Person;
use Chuva\Php\WebScrapping\Scrapper;
use PHPUnit\Framework\TestCase;

/**
 * Tests requirements for Scrapper.
 */
class ScrapperTest extends TestCase {

    public function testMaxAuthorN() {
        $papers = [
            new Paper(137468, 'Paper title 1', 'Oral presentation', [
                new Person('Amanda de Souza', 'Lycée Louis-le-Grand'),
                new Person('Rodrigo Verly', 'Lycée Louis-le-Grand'),
            ]),
            new Paper(137473, 'Paper title 2', 'Oral presentation', [
                new Person('Marília Vilela Salvador', 'Lycée Louis-le-Grand'),
                new Person('Tiago  Venâncio', 'Lycée Louis-le-Grand'),
                new Person('Francisco Paulo dos Santos', 'Lycée Louis-le-Grand'),
            ]),
            new Paper(137459, 'Paper title 3', 'Oral presentation', [
                new Person('Tiago   Bueno de Moraes', 'Lycée Louis-le-Grand'),
            ]),
        ];  
        
        $this->assertEquals((new Scrapper)->maxAuthorN($papers), 3);
    }
}