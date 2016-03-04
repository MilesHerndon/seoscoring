<?php
namespace Craft;

include_once CRAFT_PLUGINS_PATH .'/seoscoring/resources/vendor/simple_html_dom.php';

class SeoScoringService extends BaseApplicationComponent
{

  public function getSeoTables($entry, $seoKeyword)
  {
    global $html, $keyword, $seoInfo;
    $all_keyword_results = array();

    if (!empty($seoKeyword)) {
      // Define initial variable values
      $keyword = strtolower($seoKeyword);
      $keywords = array_map('trim', explode(',', $keyword));
      $page_url = $entry->url;
      $html = self::_curlPage($page_url);

      foreach ($keywords as $keyword) {
        $seoInfo = array('keyword' => $keyword, 'totals'=>array('totalTally'=>0, 'totalPoints'=>0, 'totalOccurrences'=>0));

        // Defaults
        $body =       array('name' => "Body Text", 'description' => "+1 per usage", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $bold =       array('name' => "- Bold", 'description' => "+1 once", 'key_category'=>"No", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $italic =     array('name' => "- Italics", 'description' => "+1 once", 'key_category'=>"No", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $h1h2 =       array('name' => "H1, H2 Tags", 'description' => "+3 per usage", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $h3h4 =       array('name' => "H3, H4 Tags", 'description' => "+2 per usage", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $title =      array('name' => "Page Title", 'description' => "+5 once", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $url =        array('name' => "Page URL", 'description' => "+5 once", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $meta_desc =  array('name' => "Meta Description", 'description' => "0 bonus points", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);
        $imgs =       array('name' => "Image Alt Text", 'description' => "+5 once", 'key_category'=>"Yes", 'contains' => "No", 'points' => 0, 'occurrences'=> 0);

        // Page Title
        $page_title = $html->find("title",0);
        if (substr_count(strtolower($page_title), $keyword) > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=5;
          $seoInfo['totals']['totalOccurrences']+=substr_count(strtolower($page_title), $keyword);
          $title['contains'] = "Yes!";
          $title['occurrences'] = substr_count(strtolower($page_title), $keyword);
          $title['points'] = 5;
        }

        // Page URL
        $url_keyword = str_replace(' ','-',$keyword);
        if (substr_count(strtolower($page_url), $url_keyword) > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=5;
          $seoInfo['totals']['totalOccurrences']+=substr_count(strtolower($page_url), $url_keyword);
          $url['contains'] = "Yes!";
          $url['occurrences'] = substr_count(strtolower($page_url), $url_keyword);
          $url['points'] = 5;
        }

        // Meta description
        $description = $html->find("meta[name='description']", 0)->content;
        if (substr_count(strtolower($description), $keyword) > 0){
          $seoInfo['totals']['totalTally']++;
          $seoInfo['totals']['totalPoints']+=0;
          $seoInfo['totals']['totalOccurrences']+=substr_count(strtolower($description), $keyword);
          $meta_desc['contains'] = "Yes!";
          $meta_desc['occurrences'] = substr_count(strtolower($description), $keyword);
          $meta_desc['points'] = 0;
        }

        // Images
        foreach($html->find('img') as $element){
          if (substr_count(strtolower($element->alt), $keyword) > 0){
            $seoInfo['totals']['totalTally']++;
            $seoInfo['totals']['totalPoints']+=5;
            $seoInfo['totals']['totalOccurrences']+=substr_count(strtolower($element->alt), $keyword);
            $imgs['contains'] = "Yes!";
            $imgs['occurrences'] = substr_count(strtolower($element->alt), $keyword);
            $imgs['points'] = 5;
            break;
          }
        }

        $seoInfo['categories']['body'] = self::_tallyer('p, h5, h6, blockquote, ul, ol, span, table, pre, cite, code, small, label, nav', $body, 1);
        $seoInfo['categories']['bold'] = self::_toggler('strong, b', $bold, 1, 0);
        $seoInfo['categories']['italic'] = self::_toggler('em, i', $italic, 1, 0);
        $seoInfo['categories']['h1h2'] = self::_tallyer('h1, h2', $h1h2, 3);
        $seoInfo['categories']['h3h4'] = self::_tallyer('h3, h4', $h3h4, 2);
        $seoInfo['categories']['title'] = $title;
        $seoInfo['categories']['url'] = $url;
        $seoInfo['categories']['meta_desc'] = $meta_desc;
        $seoInfo['categories']['images'] = $imgs;

        $seoInfo['initial_rating'] = self::_rating($seoInfo)[0];
        $seoInfo['final_rating'] = self::_rating($seoInfo)[1];

        $all_keyword_results[] = $seoInfo;
      }
    }

    return $all_keyword_results;


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

    $count = self::_getStringCount($query_string, $keyword);

    if($count > 0){
      $seoInfo['totals']['totalTally']++;
      $seoInfo['totals']['totalPoints'] += ($count * $value);
      $seoInfo['totals']['totalOccurrences'] += $count;
      $object['contains'] = "Yes!";
      $object['occurrences'] = $count;
      $object['points'] = ($count * $value);
    }
    return $object;
  }

  private function _toggler($query_string, $object, $value, $tally_add)
  {
    global $keyword, $html, $seoInfo;

    $count = self::_getStringCount($query_string, $keyword);

    if ($count > 0 ){
      $seoInfo['totals']['totalTally']+= $tally_add;
      $seoInfo['totals']['totalPoints']+= $value;
      $seoInfo['totals']['totalOccurrences'] += $count;
      $object['contains'] = "Yes!";
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

}