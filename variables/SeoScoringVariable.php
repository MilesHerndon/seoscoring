<?php
namespace Craft;

class SeoScoringVariable
{
  public function getSeoTables($entryId, $keyword)
  {
    return craft()->seoScoring->getSeoTables($entryId, $keyword);
  }
}