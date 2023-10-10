<?php

namespace Chuva\Php\WebScrapping;

use Chuva\Php\WebScrapping\Entity\Paper;
use Chuva\Php\WebScrapping\Entity\Person;
use DOMXPath;
use LengthException;

/**
 * Does the scrapping of a webpage.
 */
class Scrapper {
  /**
   * The Papers from the doc.
   * 
   * @var \Chuva\Php\WebScrapping\Entity\Paper[]
   */
  private $papers;

  /**
   * Loads paper information from the HTML and returns the array with the data.
   */
  public function scrap(\DOMDocument $dom): array {
    $xpath = new \DOMXPath($dom);
    $cards = $xpath->query("//*[@class='paper-card p-lg bd-gradient-left']");
    $papers = [];

    if ($cards === NULL) {
      return [];
    }

    foreach ($cards as $card) {
      $persons = [];

      if (($card->childNodes === NULL) || ($card->childNodes->length !== 3)) {
        throw new \LengthException("Each card must have 3 children");
      }

      // Nodes
      $title = $card->firstChild;
      $authorsGroup = $title->nextSibling;
      $footer = $authorsGroup->nextSibling; // Get card footer

      $type = $footer->firstChild;
      $id = ($type->nextSibling)->lastChild;

      // Node list
      $authors = $authorsGroup->childNodes;

      // Extract author's name and institution
      foreach ($authors as $author) {

        // Skips whitespace
        if (ctype_space($author->nodeValue)) {
          continue;
        }

        $institutions = $author->attributes->getNamedItem('title');

        $persons[] = new Person($author->nodeValue, $institutions->nodeValue);
      }

      // Builds object
      $papers[] = new Paper(
        $id->nodeValue,
        $title->nodeValue,
        $type->nodeValue,
        $persons
      );
    }

    $this->papers = $papers;
    return $papers;

    // return [
    //   new Paper(
    //     123,
    //     'The Nobel Prize in Physiology or Medicine 2023',
    //     'Nobel Prize',
    //     [
    //       new Person('Katalin Karikó', 'Szeged University'),
    //       new Person('Drew Weissman', 'University of Pennsylvania'),
    //     ]
    //   ),
    // ];
  }

  /**
   * Builder.
   */
  public function __construct()
  {
    $this->papers = [];    
  }

}
