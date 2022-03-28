<?php

require_once __ROOT__ . '/lib/Articles.class.php';

class Router
{

  /**
   * ENDPOINTS
   */

  // list endpoint
  public function route_list()
  {
    $articles = new Articles;

    $category = $this->getCategory();
    if ($category) {
      $articles->filterByCategory($category);
    }

    $this->json($articles->retrieve($this->getPage(), PAGE_SIZE));
  }

  // search endpoint
  public function route_search()
  {
    $articles = new Articles;

    $keywords = $this->getKeywords();
    if (!$keywords) $this->httpError(400);

    $articles->filterByKeywords($keywords);

    $this->json($articles->retrieve($this->getPage(), PAGE_SIZE));
  }

  /**
   * UTILS
   */

  // page in url parameters
  private function getPage(): int
  {
    $page = (int) filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
    return max(1, $page);
  }

  // category in url parameters
  private function getCategory(): string|null
  {
    $category = filter_input(INPUT_GET, 'category');
    if ($category && strlen($category)) return $category;
    return null;
  }

  // keywords in url parameters
  private function getKeywords(): array|null
  {
    $iptKeywords = filter_input(INPUT_GET, 'keywords');
    if (!$iptKeywords || !strlen($iptKeywords)) return null;
    // split with whitespaces
    $keywords = preg_split('/\s/', $iptKeywords);
    if (!$keywords || !count($keywords)) return null;
    return $keywords;
  }

  // print json
  private function json(mixed $data): void
  {
    header('Content-type: application/json');
    echo json_encode($data);
  }

  // print http response
  private function httpError(int $code): void
  {
    http_response_code($code);
    exit();
  }

  // start router
  public function start()
  {
    $endpoint = 'route_' . filter_input(INPUT_GET, 'endpoint');
    if (!method_exists($this, $endpoint)) {
      $this->httpError(404);
    }
    $this->{$endpoint}();
  }
}
