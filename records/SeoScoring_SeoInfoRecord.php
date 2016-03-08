<?php
namespace Craft;

class SeoScoring_SeoInfoRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'seoscoring';
  }

  public function defineAttributes()
  {
    return array(
      'seoInfo' => array(AttributeType::Mixed, 'default'=>array('keyword'=>""))
    );
  }

  public function defineRelations()
  {
    return array(
      'entry' => array(static::BELONGS_TO, 'EntryRecord', 'required' => false, 'onDelete' => static::CASCADE)
    );
  }

}