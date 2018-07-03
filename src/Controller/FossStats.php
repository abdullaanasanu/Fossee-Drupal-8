<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class FossStats extends ControllerBase
{

  public function get_list( $foss_type, $sub_type, $status, $startdate, $enddate, $countryname, $statename, $cityname) {

      if ($cityname == "" || $cityname == "null") {
          $cityname = "%";
      }
      else {
          $cityname = $cityname;
      }
      if ($statename == "" || $statename == "null") {
          $statename = "%";
      }
      else {
          $statename = $statename;
      }
      if ($countryname == "" || $countryname == "null") {
          $countryname = "%";
      }
      else {
          $countryname = $countryname;
      }

      $flag = 1;
      $page_content = "";
      \Drupal\Core\Database\Database::setActiveConnection($foss_type);
      $connection = \Drupal\Core\Database\Database::getConnection($foss_type);
      $query2 = "";
      if ($sub_type == "TBC") {

          if ($foss_type != NULL) {
              if ($foss_type != 'Python') {
                  //For TBC
                  if ($foss_type != 'eSim' && $foss_type != 'OpenModelica'  && $foss_type != 'OpenFOAM' && $foss_type != 'OR-Tools') {
                      if ($foss_type != 'DWSIM') {
                          if ($status == "completed") {
                              $query2 = $connection->query("SELECT pe.book as book,pe.author as author,pe.publisher as publisher,pe.year as year,pe.id as id FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':city' => $cityname,
                                  ':state' => $statename,
                                  ':country' => $countryname,
                                  ':startdate' => $startdate,
                                  ':enddate' => $enddate
                              ));

                          }
                          if ($status == "pending") {

                  /* For setting completion date for pending TBC and LM more */
                          $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( $enddate)));

                              $query2 = $connection->query("SELECT pe.book,pe.author,pe.publisher,pe.year,pe.id  FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':city' => $cityname,
                                  ':state' => $statename,
                                  ':country' => $countryname,
                                  ':startdate' => $startdate,
                                  ':enddate' => $pending_enddate
                              ));
                              $flag = 0;
                          }
                      }
                      else {
                          if ($status == "completed") {
                              $query2 = $connection->query("SELECT pe.book,pe.author,pe.publisher,pe.year,pe.id  FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate ", array(
                                  ':city' => $cityname,
                                  ':state' => $statename,
                                  ':country' => $countryname,
                                  ':startdate' => $startdate,
                                  ':enddate' => $enddate
                              ));
                          }
                          if ($status == "pending") {

                  /* For setting completion date for pending TBC and LM more */
                          $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( $enddate)));

                              $query2 = $connection->query("SELECT pe.book,pe.author,pe.publisher,pe.year,pe.id FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <>3 AND pe.approval_status =1 AND pe.category>0 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':city' => $cityname,
                                  ':state' => $statename,
                                  ':country' => $countryname,
                                  ':startdate' => $startdate,
                                  ':enddate' => $pending_enddate
                              ));
                              $flag = 0;
                          }
                      }
                  }
                  else {
                      if ($status == "completed") {
                          $query2 = $connection->query("SELECT pe.book,pe.author,pe.publisher,pe.year,pe.id FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND po.city LIKE :city  AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':city' => $cityname,
                              ':state' => $statename,
                              ':country' => $countryname,
                              ':startdate' => $startdate,
                              ':enddate' => $enddate
                          ));
                      }
                      if ($status == "pending") {

                 /* For setting completion date for pending TBC and LM more */
                          $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( $enddate)));

                          $query2 = $connection->query("SELECT pe.book,pe.author,pe.publisher,pe.year,pe.id FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.city LIKE :city AND po.state LIKE :state AND po.country LIKE :country AND FROM_UNIXTIME(po.completion_date) >= :startdate AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':city' => $cityname,
                              ':state' => $statename,
                              ':country' => $countryname,
                              ':startdate' => $startdate,
                              ':enddate' => $pending_enddate
                          ));
                          $flag = 0;
                      }
                  }

              }
              else {
                  if ($status == "completed") {
                      $query2 = $connection->select('tbc_book', 'tbc');
                      $query2->fields('tbc', array(
                          'title',
                          'author',
                          'id'
                      ));
                      $query2->condition('approved', 1);

                  }
                  if ($status == "pending") {
                      $query2 = $connection->select('tbc_book', 'tbc');
                      $query2->fields('tbc', array(
                          'title',
                          'author',
                          'id'
                      ));
                      $query2->condition('approved', 1, '<>');

                  }

              }
          }
          $connection = \Drupal::database();
          if ($flag != 0) {
              $page_content .= "<h3>List of Completed Books</h3><br>";
              $query = $connection->select('foss_type');
              $query->fields('foss_type', array(
                  'id'
              ));
              $query->fields('foss_type', array(
                  'tbc_run'
              ));
              $query->condition('tbc', 1);
              $query->condition('foss_name', $foss_type);
              $result = $query->execute();
              $foss_detail = $result->fetchObject();
              $page_content .= "<ol>";
              $i = 1;
              while ($preference_data = $query2->fetchObject()) {
                  $page_content .= "<li>";
                  $page_content .= "<a href=" . $foss_detail->tbc_run . "/" . $preference_data->id . " target='_blank'>" . $preference_data->book . " by " . $preference_data->author . ", " . $preference_data->publisher . ", " . $preference_data->year . "</a>";


                  $page_content .= "</li>";
              }
              $page_content .= "</ol>";

          }
          else {
              $page_content .= "<h3>List of Books in Progress</h3><br>";
              $page_content .= "<ol>";
              $i = 1;
              while ($preference_data = $query2->fetchObject()) {
                  $page_content .= "<li>";
                  $page_content .= $preference_data->book . " by " . $preference_data->author . ", " . $preference_data->publisher . ", " . $preference_data->year;
                  $page_content .= "</li>";
              }
              $page_content .= "</ol>";
          }

      }
      else {

          if ($foss_type != NULL) {

              if ($status == "completed") {
                  $query2 = $connection->query("SELECT * from {lab_migration_proposal} WHERE approval_status=3 AND city LIKE :city AND  state LIKE :state AND country LIKE :country AND FROM_UNIXTIME(approval_date) >=:startdate AND FROM_UNIXTIME(approval_date) <=:enddate", array(
                      ':city' => $cityname,
                      ':state' => $statename,
                      ':country' => $countryname,
                      ':startdate' => $startdate,
                      ':enddate' => $enddate
                  ));
              }
              if ($status == "pending") {
                  $query2 = $connection->query("SELECT * from {lab_migration_proposal} WHERE approval_status=1 AND city LIKE :city AND state LIKE :state AND country LIKE :country AND FROM_UNIXTIME(approval_date) >=:startdate AND FROM_UNIXTIME(approval_date) <=:enddate", array(
                      ':city' => $cityname,
                      ':state' => $statename,
                      ':country' => $countryname,
                      ':startdate' => $startdate,
                      ':enddate' => $enddate
                  ));
                  $flag = 0;
              }
              $connection = \Drupal::database();
              if ($flag != 0) {
                  $page_content .= "<h3>List of Completed Labs</h3><br>";
                  $query = $connection->select('foss_type');
                  $query->fields('foss_type', array(
                      'id'
                  ));
                  $query->fields('foss_type', array(
                      'lm_run'
                  ));
                  $query->condition('lab_migration', 1);
                  $query->condition('foss_name', $foss_type);
                  $result = $query->execute();
                  $foss_detail = $result->fetchObject();
                  $page_content .= "<ol>";
                  $i = 1;
                  while ($row = $query2->fetchObject()) {
                      $page_content .= "<li>";
                      $approval_date = date("Y", $row->approval_date);
                      $page_content .= "<a href=" . $foss_detail->lm_run . "/" . $row->id . " target='_blank'>" . $row->lab_title . " " . $approval_date . "  (Proposed by " . $row->name . ") </a>";
                      //$page_content .= l($row->lab_title . " " . $approval_date,$foss_detail->lm_run."/". $row->id);
                      $page_content .= "</li>";
                  }
                  $page_content .= "</ol>";
              }
              else {
                  $page_content .= "<h3>List of Labs in Progress</h3><br>";
                  $page_content .= "<ol>";
                  $i = 1;
                  while ($row = $query2->fetchObject()) {
                      $page_content .= "<li>";
                      $approval_date = date("Y", $row->approval_date);
                      $page_content .= $row->lab_title . " " . $approval_date . "  (Proposed by " . $row->name . ")";
                      $page_content .= "</li>";
                  }
                  $page_content .= "</ol>";
              }

          }

      }
      $connection = \Drupal::database();

      return array('#type'=> 'inline_template', '#template' => $page_content);
  }

}
