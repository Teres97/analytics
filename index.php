<?php

// Загрузка клиентской библиотеки PHP для Google API.
require_once __DIR__ . '/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/client_secrets.json');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
$db = mysqli_connect("localhost", "eduard", "12345", "reporting");

// Если пользователь уже авторизовал это приложение, предоставьте токен доступа.
// В противном случае перенаправьте пользователя на страницу авторизации доступа в Google Analytics.
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  // Установка токена доступа на клиентском компьютере.
  $client->setAccessToken($_SESSION['access_token']);

  // Создание авторизованного объекта службы аналитики.
  $analytics = new Google_Service_AnalyticsReporting($client);

  // Вызов the Analytics Reporting API V4.
  $response = getReport($analytics);

  // Вывод ответа.
  printResults($response,$db);

} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}


function getReport($analytics) {

  // Замена на свой идентификатор представления, напр. XXXX.
  $VIEW_ID = "151536973";

  // Создание объекта DateRange.
  $dateRange = new Google_Service_AnalyticsReporting_DateRange();
  $dateRange->setStartDate("30daysAgo");
  $dateRange->setEndDate("today");

  // Создание объекта Metrics.
  $sessions = new Google_Service_AnalyticsReporting_Metric();
  $sessions->setExpression("ga:totalEvents");
  $unique = new Google_Service_AnalyticsReporting_Metric();
  $unique->setExpression("ga:uniqueEvents");

  $hostname = new Google_Service_AnalyticsReporting_Dimension();
  $hostname->setName("ga:hostname");
  $eventCategory = new Google_Service_AnalyticsReporting_Dimension();
  $eventCategory->setName("ga:eventCategory");
  $eventAction = new Google_Service_AnalyticsReporting_Dimension();
  $eventAction->setName("ga:eventAction");
  $eventLabel = new Google_Service_AnalyticsReporting_Dimension();
  $eventLabel->setName("ga:eventLabel");
  $pagePath = new Google_Service_AnalyticsReporting_Dimension();
  $pagePath->setName("ga:pagePath");
  $date = new Google_Service_AnalyticsReporting_Dimension();
  $date->setName("ga:date");

  // Создание объекта ReportRequest.
  $request = new Google_Service_AnalyticsReporting_ReportRequest();
  $request->setViewId($VIEW_ID);
  $request->setDateRanges($dateRange);
  $request->setMetrics(array($sessions,$unique));
  $request->setDimensions(array($hostname,$eventCategory,$eventAction,$eventLabel,$pagePath,$date));
  $request->setIncludeEmptyRows(true);
  $request->setPageSize(30000);
  $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
  $body->setReportRequests( array( $request) );
  return $analytics->reports->batchGet( $body );
}

function printResults($reports,$db) {
  for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
    $report = $reports[ $reportIndex ];
    $header = $report->getColumnHeader();
    $dimensionHeaders = $header->getDimensions();
    $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
    $rows = $report->getData()->getRows();
    
    
    for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
      $dim = array();
      $row = $rows[ $rowIndex ];
      $dimensions = $row->getDimensions();
      $metrics = $row->getMetrics();
      for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
        //print($dimensionHeaders[$i] . ":" . $dimensions[$i] . "\n");
        
        array_push($dim, $dimensions[$i]);
      }

      for ($j = 0; $j < count( $metricHeaders ) && $j < count( $metrics ); $j++) {
        
        $values = $metrics[$j];
        for ( $valueIndex = 0; $valueIndex < count( $values->getValues() ); $valueIndex++ ) {
          $entry = $metricHeaders[$valueIndex];
          $value = $values->getValues()[ $valueIndex ];
          //print($entry->getName() . ": " . $value . "\n");
          array_push($dim, $value);
        }   
        $sql = "INSERT INTO `report` VALUES ('$dim[0]','$dim[1]','$dim[2]','$dim[3]','$dim[4]','$dim[5]','$dim[6]','$dim[7]')";
        $query = mysqli_query($db,$sql);
        $dim=0;

      }
    }
  }
}


