<?php

namespace Chuva\Php\WebScrapping;

/**
 * Runner for the Webscrapping exercice.
 */
class Main {

  /**
   * Main runner, instantiates a Scrapper and runs.
   */
  public static function run(): void {
    // Suppressing warnings.
    libxml_use_internal_errors(true);

    $dom = new \DOMDocument('1.0', 'utf-8');
    $dom->loadHTMLFile(__DIR__ . '/../../assets/origin.html');

    $sc = new Scrapper();
    $data = $sc->scrap($dom);

    $sc->writeToXml();

    // Write your logic to save the output file bellow.
    print_r($data);
  }

}
