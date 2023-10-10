<?php

namespace Chuva\Php\WebScrapping;

use Chuva\Php\WebScrapping\Entity\Paper;
use Chuva\Php\WebScrapping\Entity\Person;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\XLSX\Entity\SheetView;

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

      // Nodes.
      $title = $card->firstChild;
      $authorsGroup = $title->nextSibling;

      // Get card footer.
      $footer = $authorsGroup->nextSibling;

      $type = $footer->firstChild;
      $id = ($type->nextSibling)->lastChild;

      // Node list.
      $authors = $authorsGroup->childNodes;

      // Extract author's name and institution.
      foreach ($authors as $author) {

        // Skips whitespace.
        if (ctype_space($author->nodeValue)) {
          continue;
        }

        $institutions = $author->attributes->getNamedItem('title');

        // Remove ';' at the end of string.
        $authorStr = trim($author->nodeValue, ';');
        $persons[] = new Person($authorStr, $institutions->nodeValue);
      }

      // Builds object.
      $papers[] = new Paper(
        $id->nodeValue,
        $title->nodeValue,
        $type->nodeValue,
        $persons
      );
    }
    $this->papers = $papers;
    return $papers;
  }

  /**
   * Look at the last scrapping done and writes to a xml file.
   *
   * @param string $fname
   *   Name of the file to write.
   * @param string $fpath
   *   Path to save.
   */
  public function writeToXml($fname = 'output.xlsx', $fpath = ''): void {
    if ($this->papers === NULL) {
      return;
    }

    // Create xlsx object and opens.
    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToFile(__DIR__ . '/' . $fpath . $fname);

    // Header.
    $header = ['ID', 'Title', 'Type'];

    // Adds author's header to header.
    for ($i = 1; $i <= $this->maxAuthorN($this->papers); $i++) {
      $header[] = "Author {$i}";
      $header[] = "Author {$i} Institution";
    }

    // Sheet view.
    $sheetView = new SheetView();
    $sheetView->setFreezeRow(2);

    // Apply default view.
    $writer->getCurrentSheet()->setSheetView($sheetView);

    // Styling.
    $defaultStyle = (new StyleBuilder())
      ->setFontName('Arial')
      ->setShouldWrapText(TRUE)
      ->setFontSize(11)
      ->build();

    $headerStyle = (new StyleBuilder())
      ->setFontBold()
      ->build();

    // Default style.
    $writer->setDefaultRowStyle($defaultStyle);

    // Adding to file.
    $rowHeader = WriterEntityFactory::createRowFromArray($header, $headerStyle);
    $writer->addRow($rowHeader);

    // Write each row.
    foreach ($this->papers as $paper) {
      $rowArr = [
        (int) $paper->id,
        $paper->title,
        $paper->type,
      ];

      // Obtain author's infos.
      foreach ($paper->authors as $author) {
        $rowArr[] = $author->name;
        $rowArr[] = $author->institution;
      }

      // Write.
      $row = WriterEntityFactory::createRowFromArray($rowArr);
      $writer->addRow($row);
    }

    $writer->close();
  }

  /**
   * Calculate the maximun number of author in a single paper.
   *
   * @return int
   *   max number of author per page.
   */
  public function maxAuthorN($papers): int {
    $max = 0;
    $nAuthor = 0;

    if ($papers == NULL) {
      return 0;
    }

    // Check for all papers.
    foreach ($papers as $paper) {

      // Get number of authors in this paper.
      $nAuthor = count($paper->authors);

      // Update max number.
      if ($max < $nAuthor) {
        $max = $nAuthor;
      }
    }

    return $max;
  }

  /**
   * Builder.
   */
  public function __construct() {
    $this->papers = [];
  }

}
