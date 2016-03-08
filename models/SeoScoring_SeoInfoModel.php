<?php
namespace Craft;

class SeoScoring_SeoInfoModel extends BaseModel
{

  public function defineAttributes()
  {
    return array(
      'id' => AttributeType::Number,
      'entryId' => AttributeType::Number,
      'seoInfo' => array(AttributeType::Mixed, 'default'=>''),
    );
  }
}