<?php

class Articles
{

  // for reset
  private mixed $allArticles = [];
  // working articles
  private mixed $articles = [];

  public function __construct()
  {
    // get all data from api
    $data = json_decode(file_get_contents(__ROOT__ . '/api.json'));
    $articles = [];
    // retrieve all articles from api data
    foreach ($data?->content as $tab) {
      foreach ($tab?->blocks as $block) {
        if ($block?->type == 'list' && count($block?->articles)) {
          $articles = array_merge($articles, $block->articles);
        }
      }
    }
    $this->allArticles = $articles;
    $this->articles = $articles;
  }

  public function filterByCategory(string|Stringable $category): void
  {
    $this->articles = array_filter($this->articles, function ($article) use ($category) {
      // if there is a main category in article
      if (!isset($article->mainCategory)) return false;
      // and main category is the required category
      return $article->mainCategory == $category;
    });
  }

  public function filterByKeywords(array $keywords): void
  {
    $this->articles = array_filter($this->articles, function (mixed $article) use ($keywords) {
      // to know if all keywords are found
      $keywordsCount = count($keywords);
      foreach ($keywords as $keyword) {
        $keywordPattern = "/{$keyword}/i";
        // if keyword found in main category
        if (isset($article->mainCategory) && preg_match($keywordPattern, $article->mainCategory)) {
          $keywordsCount--;
          continue;
        }
        // if keyword found in title
        if (isset($article->title) && preg_match($keywordPattern, $article->title)) {
          $keywordsCount--;
          continue;
        }
      }
      return $keywordsCount == 0;
    });
  }

  public function retrieve(int $page, int $pageSize): mixed
  {
    $articles = $this->articles;
    // reset articles param
    $this->articles = $this->allArticles;
    // sort articles by date (update or publish if not updated) desc
    usort($articles, function ($a, $b) {
      $dateA = isset($a->updatedAt) ? $a->updatedAt : $a->publishedAt;
      $dateB = isset($b->updatedAt) ? $b->updatedAt : $b->publishedAt;
      if ($dateA == $dateB) return 0;
      return $dateA > $dateB ? -1 : 1;
    });
    // get $pageSize articles for $page page
    return array_slice(
      $articles,
      ($page - 1) * $pageSize,
      $pageSize
    );
  }
}
