<?php
namespace Craft;

class SeoScoringVariable
{
  // public function getSeoTables($entryId, $keyword)
  // {
  //   return craft()->seoScoring->getSeoTables($entryId, $keyword);
  // }

  public function getSeoInfo($entryId)
  {
    return craft()->seoScoring->getSeoInfo($entryId);
  }

  public function compileSeoTables($entryId)
  {
    return craft()->seoScoring->compileSeoTables($entryId);
  }
}