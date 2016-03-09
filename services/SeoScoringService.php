<?php
namespace Craft;

include_once CRAFT_PLUGINS_PATH .'/seoscoring/resources/vendor/simple_html_dom.php';

class SeoScoringService extends BaseApplicationComponent
{

  public function compileSeoTables($entry)
  {
    global $html, $keyword, $seoInfo;
    $all_keyword_results = array();

    $seoKeyword = $this->_getKeyword($entry);

    if (!empty($seoKeyword)) {
      // Define initial variable values
      $keyword = strtolower($seoKeyword);
      $keywords = array_map('trim', explode(',', $keyword));
      $page_url = $entry->url;

      if ($this->_isInFuture($entry)) {
        $page_url = $this->_generateToken($entry);
      }

      $html = $this->_curlPage($page_url);

      foreach ($keywords as $keyword) {
        $seoInfo = array('keyword' => $keyword, 'totals'=>array('totalTally'=>0, 'totalPoints'=>0, 'totalOccurrences'=>0));

        // Defaults
        $body =       (array) new seoArray('Body Text', "+1 per usage", true);
        $bold =       (array) new seoArray('- Bold', "+1 once", false);
        $italic =     (array) new seoArray('- Italics', "+1 once", false);
        $h1h2 =       (array) new seoArray('H1, H2 Tags', "+3 per usage", true);
        $h3h4 =       (array) new seoArray('H3, H4 Tags', "+2 per usage", true);
        $title =      (array) new seoArray('Page Title', "+5 once", true);
        $url =        (array) new seoArray('Page URL', "+5 once", true);
        $meta_desc =  (array) new seoArray('Meta Description', "0 bonus points", true);
        $imgs =       (array) new seoArray('Image Alt Text', "+5 once", true);

        // Page Title
        $page_title = !empty($html->find("title",0)) ? strtolower($html->find("title",0)) : "";
        $count = preg_match_all('/\b'.$keyword.'\b/', $page_title);
        if ($count > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=5;
          $seoInfo['totals']['totalOccurrences']+=$count;
          $title['contains'] = true;
          $title['occurrences'] = $count;
          $title['points'] = 5;
        }

        // Page URL
        $url_string = str_replace(array('-', '/', '.'),' ',$page_url);
        $count = preg_match_all('/\b'.$keyword.'\b/', strtolower($url_string));
        if ($count > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=5;
          $seoInfo['totals']['totalOccurrences']+=$count;
          $url['contains'] = true;
          $url['occurrences'] = $count;
          $url['points'] = 5;
        }

        // Meta description
        $description = !empty($html->find("meta[name='description']", 0)) ? strtolower($html->find("meta[name='description']", 0)->content) : "";
        $count = preg_match_all('/\b'.$keyword.'\b/', $description);
        if ($count > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=0;
          $seoInfo['totals']['totalOccurrences']+=$count;
          $meta_desc['contains'] = true;
          $meta_desc['occurrences'] = $count;
          $meta_desc['points'] = 0;
        }

        // Images
        $tally_array = $html->find('img');
        $tally_string = '';
        foreach ($tally_array as $tally_index){
          $tally_string .= ' ' .$tally_index->alt;
        }
        $tally_string = strtolower(strip_tags($tally_string));
        $count = preg_match_all('/\b'.$keyword.'\b/', $tally_string);
        if ($count > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=5;
          $seoInfo['totals']['totalOccurrences']+=$count;
          $imgs['contains'] = true;
          $imgs['occurrences'] = $count;
          $imgs['points'] = 5;
        }

        $seoInfo['categories']['body'] = $this->_tallyer('p, h5, h6, blockquote, ul, ol, span, table, pre, cite, code, small, label, nav', $body, 1);
        $seoInfo['categories']['bold'] = $this->_toggler('strong, b', $bold, 1, 0);
        $seoInfo['categories']['italic'] = $this->_toggler('em, i', $italic, 1, 0);
        $seoInfo['categories']['h1h2'] = $this->_tallyer('h1, h2', $h1h2, 3);
        $seoInfo['categories']['h3h4'] = $this->_tallyer('h3, h4', $h3h4, 2);
        $seoInfo['categories']['title'] = $title;
        $seoInfo['categories']['url'] = $url;
        $seoInfo['categories']['meta_desc'] = $meta_desc;
        $seoInfo['categories']['images'] = $imgs;

        $seoInfo['initial_rating'] = $this->_rating($seoInfo)[0];
        $seoInfo['final_rating'] = $this->_rating($seoInfo)[1];

        $all_keyword_results[] = $seoInfo;
      }
    }

    $this->saveSeoInfo($all_keyword_results, $entry->id);

  }

  public function getSeoInfo($entryId)
  {
    // create new model
    $seoInfoModel = new SeoScoring_SeoInfoModel();

    // get record from DB
    $seoInfoRecord = SeoScoring_SeoInfoRecord::model()->findByAttributes(array('entryId' => $entryId));

    if ($seoInfoRecord) {
      $seoInfoModel = SeoScoring_SeoInfoModel::populateModel($seoInfoRecord);
    }


    return $seoInfoModel->attributes['seoInfo'];
  }

  public function saveSeoInfo($seoArray, $entryId)
  {
    // get record from DB
    $seoInfoRecord = SeoScoring_SeoInfoRecord::model()->findByAttributes(array('entryId' => $entryId));

    if (!$seoInfoRecord)
    {
      $seoInfoRecord = new SeoScoring_SeoInfoRecord;
      $seoInfoRecord->setAttribute('entryId', $entryId);
    }

    $seoInfoRecord->setAttribute('seoInfo', $seoArray);

    // save record in DB
    $seoInfoRecord->save();

  }

  // HELPER FUNCTIONS
  private function _isInFuture($entry)
  {
    return date('U') < $entry->postDate->getTimestamp() ? true : false;
  }

  private function _generateToken($entry)
  {
    $tokenParams = array('action'=>'entries/viewSharedEntry', 'params'=>array('entryId'=>$entry->id, 'locale'=>'en_us'));
    $token = craft()->tokens->createToken($tokenParams);
    $page_url = UrlHelper::getUrlWithToken($entry->url, $token);
    return $page_url;
  }

  private function _getKeyword($entry)
  {
    $fields = $entry->getFieldLayout()->getFields();
    foreach ($fields as $field)
    {
        $type = $field->getField()->type;
        if ($type == 'SeoScoring_Widget') {
          $handle = $field->getField()->handle;
          break;
        }
    }
    return $entry->$handle;
  }

  private function _curlPage($url)
  {
    $curl_connection = curl_init($url);
    curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_connection, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_connection, CURLINFO_HEADER_OUT, true);

    $curl_result = curl_exec($curl_connection);

    curl_close($curl_connection);

    return str_get_html($curl_result);
  }

  private function _tallyer($query_string, $object, $value)
  {
    global $keyword, $html, $seoInfo;

    $count = $this->_getStringCount($query_string, $keyword);

    if($count > 0){
      $seoInfo['totals']['totalTally']++;
      $seoInfo['totals']['totalPoints'] += ($count * $value);
      $seoInfo['totals']['totalOccurrences'] += $count;
      $object['contains'] = true;
      $object['occurrences'] = $count;
      $object['points'] = ($count * $value);
    }
    return $object;
  }

  private function _toggler($query_string, $object, $value, $tally_add)
  {
    global $keyword, $html, $seoInfo;

    $count = $this->_getStringCount($query_string, $keyword);

    if ($count > 0 ){
      $seoInfo['totals']['totalTally']+= $tally_add;
      $seoInfo['totals']['totalPoints']+= $value;
      $seoInfo['totals']['totalOccurrences'] += $count;
      $object['contains'] = true;
      $object['occurrences'] = $count;
      $object['points'] = $value;
    }
    return $object;
  }

  private function _rating($seoInfo_array)
  {
    global $seoInfo;
    $seoInfo = $seoInfo_array;

    if ($seoInfo['totals']['totalPoints'] < 15) {
      $initial_rating = "Red";
    }
    else if ($seoInfo['totals']['totalPoints'] >= 15 && $seoInfo['totals']['totalPoints'] < 25) {
      $initial_rating = "Yellow";
    }
    else if ($seoInfo['totals']['totalPoints'] >= 25) {
      $initial_rating = "Green";
    }

    $final_rating = $initial_rating;
    if ($initial_rating == "Green") {
      if ($seoInfo['totals']['totalTally'] < 3 ){
        $final_rating = "Red";
      }
      elseif ($seoInfo['totals']['totalTally'] < 5){
        $final_rating = "Yellow";
      }
    }
    if ($initial_rating == "Yellow" && $seoInfo['totals']['totalTally'] < 5){
      $final_rating = "Red";
    }

    return array($initial_rating, $final_rating);

  }

  private function _getStringCount($string, $keyword)
  {
    global $html;

    $tally_array = $html->find($string);
    $tally_string = '';
    foreach ($tally_array as $tally_index){
      $tally_string .= ' ' .$tally_index;
    }
    $tally_string = strtolower(strip_tags($tally_string));
    $count = preg_match_all('/\b'.$keyword.'\b/', $tally_string);

    return $count;
  }

  public function getTheTab($entry)
  {
    $tabs_array = array();
    $tabs = $entry->getFieldLayout()->getTabs();
    foreach ($tabs as $fields) {
      $tabs_array[$fields->id] = $fields->sortOrder;
    }

    $the_raw_tab = null;
    $fields = $entry->getFieldLayout()->getFields();
    foreach ($fields as $field) {
      $type = $field->getField()->type;
      if ($type == 'SeoScoring_Widget') {
        $the_raw_tab = $field->tabId;
        break;
      }
    }

    return $tabs_array[$the_raw_tab];
    // SeoScoringPlugin::log(\CVarDumper::dumpAsString($the_tab));
  }

}

class seoArray
{
  function __construct($name, $description, $key_category)
  {
    $this->name = $name;
    $this->description = $description;
    $this->key_category = $key_category;
    $this->contains = false;
    $this->points = 0;
    $this->occurrences = 0;
  }
}