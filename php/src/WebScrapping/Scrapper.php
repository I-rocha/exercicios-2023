<?php

namespace Chuva\Php\WebScrapping;

use Chuva\Php\WebScrapping\Entity\Paper;
use Chuva\Php\WebScrapping\Entity\Person;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;
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
   * Look at the last scrapping done and writes to a xml file
   * (fname) Name of the file to write
   * (fpath) Path to save
   */
  public function writeToXml($fname = 'output.xlsx', $fpath = '/'): void {
    if($this->papers === null)
      return;

    // Create xlsx object and opens
    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToFile(__DIR__ . $fpath . $fname);
    
    // Header
    $header = ['ID', 'Title', 'Type'];
    
    // Adds author's header to header
    for($i = 1; $i < 10; $i++){
      $header[] = "Author {$i}";
      $header[] = "Author {$i} Institution";
    }

    // Styling
    $defaultStyle = (new StyleBuilder())
    ->setFontName('Arial')
    ->setFontSize(11)
    ->build();

    $headerStyle = (new StyleBuilder())
    ->setFontBold()
    ->build();

    // Default style
    $writer->setDefaultRowStyle($defaultStyle);

    // Adding to file
    $rowHeader = WriterEntityFactory::createRowFromArray($header, $defaultStyle);
    $writer->addRow($rowHeader);

    // Write each row
    foreach($this->papers as $paper){
      $rowArr = [
        $paper->id,
        $paper->title,
        $paper->type,
      ];

      // Obtain author's infos
      foreach($paper->authors as $author){
        $rowArr[] = $author->name;
        $rowArr[] = $author->institution;
      }

      // Write
      $row = WriterEntityFactory::createRowFromArray($rowArr);
      $writer->addRow($row);
    }

    $writer->close();

  }

  /**
   * Builder.
   */
  public function __construct()
  {
    $this->papers = [];
  }

}
