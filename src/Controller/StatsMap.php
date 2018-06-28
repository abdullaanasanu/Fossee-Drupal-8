<?php

namespace Drupal\fossee_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\fossee_stats\FossBase;
use Drupal\fossee_stats\StateMap;
use Symfony\Component\HttpFoundation\Response;

class StatsMap extends ControllerBase
{

  public function dropdown(){
    $response = new Response();
    /* Taking JSON formatted data from ajax request */
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);
    $type = $postBody->Type;
    $id = $postBody->ID;
    $result ='';
    /* Checking type of drop down needed */
    if ($type == 'activities') {
      /* Fetching data for activities dropdown */
      $options = FossBase::get_activities_list($id);
      $front = '<div class="form-item form-type-select form-item-foss-activities" onchange="check_status()"><label for="edit-foss-activities">Activities </label><select id="edit-foss-activities" name="foss_activities" class="form-select">';
      $flag = false;
      for ($i=0; $i <= max(array_keys($options)) ; $i++) {
        if ($options[$i] != '' && $options[$i] != 'Spoken Tutorial') {
          $result .= '<option value = "'.$i.'">'.$options[$i].'</option>';
          $flag = true;
        }
      }
      if ($flag) {
        $result = $front.$result;
        $result .= '</select></div>';
      }
    }else if($type == 'status') {
      /* Fetching data for status dropdown */
      $options = FossBase::_ajax_example_get_third_dropdown_options($id);
      $front = '<div class="form-item form-type-select form-item-foss-status"><label for="edit-foss-status">Status </label><select id="edit-foss-status" name="foss_status" class="form-select">';
      $flag = false;
      for ($i=0; $i <= max(array_keys($options)) ; $i++) {
        if ($options[$i] != '') {
          $flag = true;
          $result .= '<option value = "'.$i.'">'.$options[$i].'</option>';
        }
      }
      if ($flag) {
        $result = $front.$result;
        $result .= '</select></div>';
      }
    }
    $response->setContent($result);
    $response->headers->set('Content-Type','text/plain');
     // return $result;
    return $response;
  }

  function state_map_stats() {
    $response = new Response();
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);

    $type = $postBody->type;
    $state = $postBody->state;
    /* Configuration of map */

    if ($postBody->activities && $postBody->status) {
      $result = StateMap::state_map_data($type,$postBody->activities,$postBody->status,$state);
    }elseif ($postBody->activities) {
      $result = StateMap::state_map_data($type,$postBody->activities,0,$state);
    }else {
      $result = StateMap::state_map_data($type,0,0,$state);
    }
    $response->setContent($result);
    $response->headers->set('Content-Type','text/plain');
     // return $result;
    return $response;
  }

  function map_stats() {
    $response = new Response();
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);

    $type = $postBody->type;
    /* Configuration of map */
    if ($postBody->activities && $postBody->status) {
      $result = FossBase::get_map_data($type,$postBody->activities,$postBody->status);
    }elseif ($postBody->activities) {
      $result = FossBase::get_map_data($type,$postBody->activities,0);
    }else {
      $result = FossBase::get_map_data($type,0,0);
    }

    $response->setContent($result);
    $response->headers->set('Content-Type','text/plain');
     // return $result;
    return $response;
  }

  function post_office_data() {
    $response = new Response();
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);

    $type = $postBody->type;
    $pincode = $postBody->pincode;
    $state = $postBody->state;
    $district = $postBody->district;
    /* Configuration of map */
    if ($postBody->activities && $postBody->status) {
      $result = StateMap::post_data($type,$postBody->activities,$postBody->status,$pincode,$state,$district);
    }elseif ($postBody->activities) {
      $result = StateMap::post_data($type,$postBody->activities,0,$pincode,$state,$district);
    }else {
      $result = StateMap::post_data($type,0,0,$pincode,$state,$district);
    }
    $response->setContent($result);
    $response->headers->set('Content-Type','text/plain');
     // return $result;
    return $response;
  }

  function state_details() {
    $response = new Response();
    $postBody = file_get_contents("php://input");
    $postBody = json_decode($postBody);

    $type = $postBody->type;
    $state = $postBody->state;
    $district = $postBody->district;
    /* Configuration of activities and status for generation of data */
    if ($postBody->activities && $postBody->status) {
      $activities = $postBody->activities;
      $status = $postBody->status;
    }elseif ($postBody->activities) {
      $activities = $postBody->activities;
      $status = 0;
    }else {
      $activities = 0;
      $staus = 0;
    }
    /* Setting all type of data default to zero */
    $workshop = 0;
    $conference = 0;
    $lab_migration_completed = 0;
    $lab_migration_pending = 0;
    $self_workshop = 0;
    $pendingbookcount = 0;
    $completedbookcount = 0;
    $dwsim_flowsheet_completed = 0;
    $dwsim_flowsheet_pending = 0;
    $circuit_simulation_completed = 0;
    $circuit_simulation_pending = 0;
    $city = array();
    $connection = \Drupal::database();
    /* Checking foss type */
    if($district){
      if($type == "" || $type == NULL){
        /* When foss type is not set */
        $query = $connection->select('foss_type','u')
          ->fields('u', array(
            'foss_name',
            'tbc',
            'lab_migration',
            'workshop',
            'conference',
            'foss_selfworkshop_no'
          ))
          ->execute();
          /* Taking all foss type's data */
          while($foss = $query->fetchObject()){
            $name = $foss->foss_name;
            // Workshop
            if($foss->workshop){
              $query1 = $connection->select('workshop', 'w')
                ->fields('w', array('pincode'))
                ->condition('type','workshop')
                ->condition('foss_name',$name)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              // Assigning to each states
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a',array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $workshop++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
            }
            // Conference
            if($foss->conference){
              $query1 = $connection->select('workshop', 'w')
                ->fields('w', array('pincode'))
                ->condition('type','conference')
                ->condition('foss_name',$name)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              // Assigning to each states
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a',array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $conference++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
            }
            // Text Book companion
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($foss->tbc){
              if ($name != 'Python') {
                $query2 = db_query("SELECT po.pincode AS pin, po.city AS city FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                $query3 = db_query("SELECT po.pincode AS pin, po.city AS city FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                while($pin = $query2->fetchObject()->pin) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('officename'))
                    ->condition('pincode',$pin)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $pendingbookcount++;
                    if(!in_array($check,$city)){
                      $city[$pin] = $check;
                    }
                  }
                }
                while($pin = $query3->fetchObject()->pin) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('officename'))
                    ->condition('pincode',$pin)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $completedbookcount++;
                    if(!in_array($check,$city)){
                      $city[$pin] = $check;
                    }
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
              else {
                  /*$query5 = $connection->select('tbc_book');
                  $query5->addExpression('count(*)', 'count');
                  $query5->condition('approved', 1);
                  $result5 = $query5->execute();
                  $completedbookcount += $result5->fetchObject()->count;
                  $query6 = $connection->select('tbc_book');
                  $query6->addExpression('count(*)', 'count');
                  $query6->condition('approved', 1, '<>');
                  $result6 = $query6->execute();
                  $pendingbookcount += $result6->fetchObject()->count;*/
              }
            }

            // Lab Migration
            if($foss->lab_migration){
              /* Completed Lab Migration */
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('pincode','city'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('pincode','city'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $lab_migration_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $lab_migration_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            /*
            //  Self Workshop space
            if($foss->foss_selfworkshop_no){
              db_set_active('selfworkshop');
              $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
                ':foss_id' => $foss->foss_selfworkshop_no,
                ':state' => $state
                                    ));

              $self_workshop += $query2->fetchObject()->count;
            }
            */
            // DWSIM Flowsheet
            if($name == 'DWSIM'){
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode','city'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode','city'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            // eSim Circuit Simulation
            }else if($name == 'eSim'){
              \Drupal\Core\Database\Database::setActiveConnection('eSim');
              $connection = \Drupal\Core\Database\Database::getConnection('eSim');
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode','city'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode','city'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            db_set_active();
          }
      }else{
        /* When foss type is set */
        $query = $connection->select('foss_type','u')
          ->fields('u', array(
            'foss_name',
            'tbc',
            'lab_migration',
            'workshop',
            'conference',
            'foss_selfworkshop_no',
            'flow_sheet',
            'circuit_simulation',
            'case_study'
          ))
          ->condition('id',$type)
          ->execute()
          ->fetchObject();
        $name = $query->foss_name;
        /* Converting ID to name of Activities */
        $options = FossBase::get_activities_list($type);
        for ($i=0; $i <= max(array_keys($options)) ; $i++) {
          if ($i == $activities) {
            $act = $options[$i];
          }
        }
        /* When Activities are set */
        if ($activities != 0) {
          /* Converting ID to name of Activities */
          $options = FossBase::_ajax_example_get_third_dropdown_options($activities);
          for ($i=0; $i <= max(array_keys($options)) ; $i++) {
            if ($i == $status) {
              $stat = $options[$i];
            }
          }
          if ($status != 0) {
            /* Book in Progress for TextBook Companion */
            if ($act == 'Textbook Companion' && $stat == 'Books in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
                  $connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query2->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('officename'))
                      ->condition('pincode',$pin)
                      ->condition('districtname',$district)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $pendingbookcount++;
                      if(!in_array($check,$city)){
                        $city[$pin] = $check;
                      }
                    }
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);

                }
                else {
                    /*$query5 = $connection->select('tbc_book');
                    $query5->addExpression('count(*)', 'count');
                    $query5->condition('approved', 1);
                    $result5 = $query5->execute();
                    $completedbookcount += $result5->fetchObject()->count;
                    $query6 = $connection->select('tbc_book');
                    $query6->addExpression('count(*)', 'count');
                    $query6->condition('approved', 1, '<>');
                    $result6 = $query6->execute();
                    $pendingbookcount += $result6->fetchObject()->count;*/
                }
              }
            /* Completed Books for TextBook Companion */
            }elseif ($act == 'Textbook Companion' && $stat == 'Completed Books') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
                  $connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query3->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('officename'))
                      ->condition('pincode',$pin)
                      ->condition('districtname',$district)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $completedbookcount++;
                      if(!in_array($check,$city)){
                        $city[$pin] = $check;
                      }
                    }
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);

                }
                else {
                    /*$query5 = $connection->select('tbc_book');
                    $query5->addExpression('count(*)', 'count');
                    $query5->condition('approved', 1);
                    $result5 = $query5->execute();
                    $completedbookcount += $result5->fetchObject()->count;
                    $query6 = $connection->select('tbc_book');
                    $query6->addExpression('count(*)', 'count');
                    $query6->condition('approved', 1, '<>');
                    $result6 = $query6->execute();
                    $pendingbookcount += $result6->fetchObject()->count;*/
                }
              }
            /* Lab in Progress for Lab Migration */
            }elseif ($act == 'Lab Migration' && $stat == 'Labs in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                $query2 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 1)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                foreach ($query2 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('officename'))
                    ->condition('pincode',$s->pincode)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_pending++;
                    if(!in_array($check,$city)){
                      $city[$s->pincode] = $check;
                    }
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
            /* Completed Labs for Lab Migration */
            }elseif ($act == 'Lab Migration' && $stat == 'Completed Labs') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                $query1 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 3)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('officename'))
                    ->condition('pincode',$s->pincode)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_completed++;
                    if(!in_array($check,$city)){
                      $city[$s->pincode] = $check;
                    }
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
            }elseif ($act == 'Flowsheet' && $stat == 'Flowsheets in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Flowsheet' && $stat == 'Completed Flowsheets') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Circuit Simulation' && $stat == 'Simulations in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Circuit Simulation' && $stat == 'Completed Simulations') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
          }else {
            // Workshop
            if ($act == 'Workshop') {
              if($query->workshop){
                $query1 = $connection->select('workshop', 'w')
                  ->fields('w', array('pincode'))
                  ->condition('type','workshop')
                  ->condition('foss_name',$name)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                // Assigning to each states
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a',array('officename'))
                    ->condition('pincode',$s->pincode)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $workshop++;
                    if(!in_array($check,$city)){
                      $city[$s->pincode] = $check;
                    }
                  }
                }
              }
            // Conference
            }elseif ($act == 'Conference') {
              if($query->conference){
                $query1 = $connection->select('workshop', 'w')
                  ->fields('w', array('pincode'))
                  ->condition('type','conference')
                  ->condition('foss_name',$name)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                // Assigning to each states
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a',array('officename'))
                    ->condition('pincode',$s->pincode)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $conference++;
                    if(!in_array($check,$city)){
                      $city[$s->pincode] = $check;
                    }
                  }
                }
              }
            // Textbook Companion
            }elseif ($act == 'Textbook Companion') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state,
                  ));
                  $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state,
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
                  $connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query2->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('officename'))
                      ->condition('pincode',$pin)
                      ->condition('districtname',$district)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $pendingbookcount++;
                      if(!in_array($check,$city)){
                        $city[$pin] = $check;
                      }
                    }
                  }
                  while($pin = $query3->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('officename'))
                      ->condition('pincode',$pin)
                      ->condition('districtname',$district)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $completedbookcount++;
                      if(!in_array($check,$city)){
                        $city[$pin] = $check;
                      }
                    }
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);
                }
                else {
                    /*$query5 = $connection->select('tbc_book');
                    $query5->addExpression('count(*)', 'count');
                    $query5->condition('approved', 1);
                    $result5 = $query5->execute();
                    $completedbookcount += $result5->fetchObject()->count;
                    $query6 = $connection->select('tbc_book');
                    $query6->addExpression('count(*)', 'count');
                    $query6->condition('approved', 1, '<>');
                    $result6 = $query6->execute();
                    $pendingbookcount += $result6->fetchObject()->count;*/
                }
              }
            // Lab Migration
            }elseif ($act == 'Lab Migration') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                /* Completed Lab Migration */
                $query1 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 3)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                $query2 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 1)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('officename'))
                    ->condition('pincode',$s->pincode)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_completed++;
                    if(!in_array($check,$city)){
                      $city[$s->pincode] = $check;
                    }
                  }
                }
                foreach ($query2 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('officename'))
                    ->condition('pincode',$s->pincode)
                    ->condition('districtname',$district)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_pending++;
                    if(!in_array($check,$city)){
                      $city[$s->pincode] = $check;
                    }
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
            }elseif ($act == "Flowsheet") {
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == "Circuit Simulation") {
              \Drupal\Core\Database\Database::setActiveConnection('eSim');
              $connection = \Drupal\Core\Database\Database::getConnection('eSim');
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_completed++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$s->pincode)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_pending++;
                  if(!in_array($check,$city)){
                    $city[$s->pincode] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
          }
        }else {
          // Workshop
          if($query->workshop){
            $query1 = $connection->select('workshop', 'w')
              ->fields('w', array('pincode'))
              ->condition('type','workshop')
              ->condition('foss_name',$name)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            // Assigning to each states
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a',array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $workshop++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }

          }
          // Conference
          if($query->conference){
            $query1 = $connection->select('workshop', 'w')
              ->fields('w', array('pincode'))
              ->condition('type','conference')
              ->condition('foss_name',$name)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            // Assigning to each states
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a',array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $conference++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
          }
          // Text Book companion
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
          if($query->tbc){
            if ($name != 'Python') {
              $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                  ':state' => $state,
              ));
              $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                  ':state' => $state,
              ));
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              while($pin = $query2->fetchObject()->pin) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$pin)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $pendingbookcount++;
                  if(!in_array($check,$city)){
                    $city[$pin] = $check;
                  }
                }
              }
              while($pin = $query3->fetchObject()->pin) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('officename'))
                  ->condition('pincode',$pin)
                  ->condition('districtname',$district)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $completedbookcount++;
                  if(!in_array($check,$city)){
                    $city[$pin] = $check;
                  }
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            else {
                /*$query5 = $connection->select('tbc_book');
                $query5->addExpression('count(*)', 'count');
                $query5->condition('approved', 1);
                $result5 = $query5->execute();
                $completedbookcount += $result5->fetchObject()->count;
                $query6 = $connection->select('tbc_book');
                $query6->addExpression('count(*)', 'count');
                $query6->condition('approved', 1, '<>');
                $result6 = $query6->execute();
                $pendingbookcount += $result6->fetchObject()->count;*/
            }
          }

          // Lab Migration
          if($query->lab_migration){
            // Completed Lab Migration
            $query1 = $connection->select('lab_migration_proposal','u')
              ->fields('u', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            $query2 = $connection->select('lab_migration_proposal','u')
              ->fields('u', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $lab_migration_completed++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
            foreach ($query2 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $lab_migration_pending++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          /*
          //  Self Workshop space
          if($query->foss_selfworkshop_no){
            db_set_active('selfworkshop');
            $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
              ':foss_id' => $query->foss_selfworkshop_no,
              ':state' => $state
                                  ));

            $self_workshop += $query2->fetchObject()->count;
          }
          */
          // DWSIM Flowsheet
          if($name == 'DWSIM'){
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            $query1 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            $query2 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $dwsim_flowsheet_pending++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
            foreach ($query2 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $dwsim_flowsheet_completed++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          // eSim Circuit Simulation
          }else if($name == 'eSim'){
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            $query2 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $circuit_simulation_completed++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
            foreach ($query2 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('officename'))
                ->condition('pincode',$s->pincode)
                ->condition('districtname',$district)
                ->execute()
                ->fetchField();
              if ($check) {
                $circuit_simulation_pending++;
                if(!in_array($check,$city)){
                  $city[$s->pincode] = $check;
                }
              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          \Drupal\Core\Database\Database::setActiveConnection('default');
          $connection = \Drupal\Core\Database\Database::getConnection('default');
          db_set_active();
        }
      }
    }else {
      if($type == "" || $type == NULL){
        /* When foss type is not set */
        $query = $connection->select('foss_type','u')
          ->fields('u', array(
            'foss_name',
            'tbc',
            'lab_migration',
            'workshop',
            'conference',
            'foss_selfworkshop_no'
          ))
          ->execute();
          /* Taking all foss type's data */
          while($foss = $query->fetchObject()){
            $name = $foss->foss_name;
            // Workshop
            if($foss->workshop){
              $query1 = $connection->select('workshop', 'w')
                ->fields('w', array('pincode'))
                ->condition('type','workshop')
                ->condition('foss_name',$name)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              // Assigning to each states
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a',array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $workshop++;
                }
              }
            }
            // Conference
            if($foss->conference){
              $query1 = $connection->select('workshop', 'w')
                ->fields('w', array('pincode'))
                ->condition('type','conference')
                ->condition('foss_name',$name)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              // Assigning to each states
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a',array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $conference++;
                }
              }
            }
            // Text Book companion
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($foss->tbc){
              if ($name != 'Python') {
                $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                while($pin = $query2->fetchObject()->pin) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$pin)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $pendingbookcount++;
                  }
                }
                while($pin = $query3->fetchObject()->pin) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$pin)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $completedbookcount++;
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
              else {
                  /*$query5 = $connection->select('tbc_book');
                  $query5->addExpression('count(*)', 'count');
                  $query5->condition('approved', 1);
                  $result5 = $query5->execute();
                  $completedbookcount += $result5->fetchObject()->count;
                  $query6 = $connection->select('tbc_book');
                  $query6->addExpression('count(*)', 'count');
                  $query6->condition('approved', 1, '<>');
                  $result6 = $query6->execute();
                  $pendingbookcount += $result6->fetchObject()->count;*/
              }
            }

            // Lab Migration
            if($foss->lab_migration){
              /* Completed Lab Migration */
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $lab_migration_completed++;
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $lab_migration_pending++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            /*
            //  Self Workshop space
            if($foss->foss_selfworkshop_no){
              db_set_active('selfworkshop');
              $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
                ':foss_id' => $foss->foss_selfworkshop_no,
                ':state' => $state
                                    ));

              $self_workshop += $query2->fetchObject()->count;
            }
            */
            // DWSIM Flowsheet
            if($name == 'DWSIM'){
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_pending++;
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_completed++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            // eSim Circuit Simulation
            }else if($name == 'eSim'){
              \Drupal\Core\Database\Database::setActiveConnection('eSim');
              $connection = \Drupal\Core\Database\Database::getConnection('eSim');
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_completed++;
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_pending++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            db_set_active();
          }
      }else{
        /* When foss type is set */
        $query = $connection->select('foss_type','u')
          ->fields('u', array(
            'foss_name',
            'tbc',
            'lab_migration',
            'workshop',
            'conference',
            'foss_selfworkshop_no',
            'flow_sheet',
            'circuit_simulation',
            'case_study'
          ))
          ->condition('id',$type)
          ->execute()
          ->fetchObject();
        $name = $query->foss_name;
        /* Converting ID to name of Activities */
        $options = FossBase::get_activities_list($type);
        for ($i=0; $i <= max(array_keys($options)) ; $i++) {
          if ($i == $activities) {
            $act = $options[$i];
          }
        }
        /* When Activities are set */
        if ($activities != 0) {
          /* Converting ID to name of Activities */
          $options = FossBase::_ajax_example_get_third_dropdown_options($activities);
          for ($i=0; $i <= max(array_keys($options)) ; $i++) {
            if ($i == $status) {
              $stat = $options[$i];
            }
          }
          if ($status != 0) {
            /* Book in Progress for TextBook Companion */
            if ($act == 'Textbook Companion' && $stat == 'Books in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
                  $connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query2->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('districtname'))
                      ->condition('pincode',$pin)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $pendingbookcount++;
                    }
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);

                }
                else {
                    /*$query5 = $connection->select('tbc_book');
                    $query5->addExpression('count(*)', 'count');
                    $query5->condition('approved', 1);
                    $result5 = $query5->execute();
                    $completedbookcount += $result5->fetchObject()->count;
                    $query6 = $connection->select('tbc_book');
                    $query6->addExpression('count(*)', 'count');
                    $query6->condition('approved', 1, '<>');
                    $result6 = $query6->execute();
                    $pendingbookcount += $result6->fetchObject()->count;*/
                }
              }
            /* Completed Books for TextBook Companion */
            }elseif ($act == 'Textbook Companion' && $stat == 'Completed Books') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
                  $connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query3->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('districtname'))
                      ->condition('pincode',$pin)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $completedbookcount++;
                    }
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);

                }
                else {
                    /*$query5 = $connection->select('tbc_book');
                    $query5->addExpression('count(*)', 'count');
                    $query5->condition('approved', 1);
                    $result5 = $query5->execute();
                    $completedbookcount += $result5->fetchObject()->count;
                    $query6 = $connection->select('tbc_book');
                    $query6->addExpression('count(*)', 'count');
                    $query6->condition('approved', 1, '<>');
                    $result6 = $query6->execute();
                    $pendingbookcount += $result6->fetchObject()->count;*/
                }
              }
            /* Lab in Progress for Lab Migration */
            }elseif ($act == 'Lab Migration' && $stat == 'Labs in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                $query2 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 1)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                foreach ($query2 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$s->pincode)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_pending++;
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
            /* Completed Labs for Lab Migration */
            }elseif ($act == 'Lab Migration' && $stat == 'Completed Labs') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                $query1 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 3)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$s->pincode)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_completed++;
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
            }elseif ($act == 'Flowsheet' && $stat == 'Flowsheets in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_pending++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Flowsheet' && $stat == 'Completed Flowsheets') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_completed++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Circuit Simulation' && $stat == 'Simulations in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_pending++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Circuit Simulation' && $stat == 'Completed Simulations') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_completed++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
          }else {
            // Workshop
            if ($act == 'Workshop') {
              if($query->workshop){
                $query1 = $connection->select('workshop', 'w')
                  ->fields('w', array('pincode'))
                  ->condition('type','workshop')
                  ->condition('foss_name',$name)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                // Assigning to each states
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a',array('districtname'))
                    ->condition('pincode',$s->pincode)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $workshop++;
                  }
                }
              }
            // Conference
            }elseif ($act == 'Conference') {
              if($query->conference){
                $query1 = $connection->select('workshop', 'w')
                  ->fields('w', array('pincode'))
                  ->condition('type','conference')
                  ->condition('foss_name',$name)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                // Assigning to each states
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a',array('districtname'))
                    ->condition('pincode',$s->pincode)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $conference++;
                  }
                }
              }
            // Textbook Companion
            }elseif ($act == 'Textbook Companion') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state,
                  ));
                  $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state,
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
                  $connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query2->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('districtname'))
                      ->condition('pincode',$pin)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $pendingbookcount++;
                    }
                  }
                  while($pin = $query3->fetchObject()->pin) {
                    $check = $connection->select('ai_pincode','a')
                      ->fields('a', array('districtname'))
                      ->condition('pincode',$pin)
                      ->execute()
                      ->fetchField();
                    if ($check) {
                      $completedbookcount++;
                    }
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);
                }
                else {
                    /*$query5 = $connection->select('tbc_book');
                    $query5->addExpression('count(*)', 'count');
                    $query5->condition('approved', 1);
                    $result5 = $query5->execute();
                    $completedbookcount += $result5->fetchObject()->count;
                    $query6 = $connection->select('tbc_book');
                    $query6->addExpression('count(*)', 'count');
                    $query6->condition('approved', 1, '<>');
                    $result6 = $query6->execute();
                    $pendingbookcount += $result6->fetchObject()->count;*/
                }
              }
            // Lab Migration
            }elseif ($act == 'Lab Migration') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                /* Completed Lab Migration */
                $query1 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 3)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                $query2 = $connection->select('lab_migration_proposal','u')
                  ->fields('u', array('pincode'))
                  ->condition('approval_status', 1)
                  ->condition('state',$state)
                  ->execute()
                  ->fetchAll();
                \Drupal\Core\Database\Database::setActiveConnection('default');
                $connection = \Drupal\Core\Database\Database::getConnection('default');
                foreach ($query1 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$s->pincode)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_completed++;
                  }
                }
                foreach ($query2 as $s) {
                  $check = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$s->pincode)
                    ->execute()
                    ->fetchField();
                  if ($check) {
                    $lab_migration_pending++;
                  }
                }
                \Drupal\Core\Database\Database::setActiveConnection($name);
                $connection = \Drupal\Core\Database\Database::getConnection($name);
              }
            }elseif ($act == "Flowsheet") {
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->fields('d', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_pending++;
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $dwsim_flowsheet_completed++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == "Circuit Simulation") {
              \Drupal\Core\Database\Database::setActiveConnection('eSim');
              $connection = \Drupal\Core\Database\Database::getConnection('eSim');
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 3)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->fields('e', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_completed++;
                }
              }
              foreach ($query2 as $s) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $circuit_simulation_pending++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
          }
        }else {
          // Workshop
          if($query->workshop){
            $query1 = $connection->select('workshop', 'w')
              ->fields('w', array('pincode'))
              ->condition('type','workshop')
              ->condition('foss_name',$name)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            // Assigning to each states
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a',array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $workshop++;
              }
            }

          }
          // Conference
          if($query->conference){
            $query1 = $connection->select('workshop', 'w')
              ->fields('w', array('pincode'))
              ->condition('type','conference')
              ->condition('foss_name',$name)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            // Assigning to each states
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a',array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $conference++;
              }
            }
          }
          // Text Book companion
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
          if($query->tbc){
            if ($name != 'Python') {
              $query2 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                  ':state' => $state,
              ));
              $query3 = db_query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                  ':state' => $state,
              ));
              \Drupal\Core\Database\Database::setActiveConnection('default');
              $connection = \Drupal\Core\Database\Database::getConnection('default');
              while($pin = $query2->fetchObject()->pin) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$pin)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $pendingbookcount++;
                }
              }
              while($pin = $query3->fetchObject()->pin) {
                $check = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$pin)
                  ->execute()
                  ->fetchField();
                if ($check) {
                  $completedbookcount++;
                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            else {
                /*$query5 = $connection->select('tbc_book');
                $query5->addExpression('count(*)', 'count');
                $query5->condition('approved', 1);
                $result5 = $query5->execute();
                $completedbookcount += $result5->fetchObject()->count;
                $query6 = $connection->select('tbc_book');
                $query6->addExpression('count(*)', 'count');
                $query6->condition('approved', 1, '<>');
                $result6 = $query6->execute();
                $pendingbookcount += $result6->fetchObject()->count;*/
            }
          }

          // Lab Migration
          if($query->lab_migration){
            // Completed Lab Migration
            $query1 = $connection->select('lab_migration_proposal','u')
              ->fields('u', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            $query2 = $connection->select('lab_migration_proposal','u')
              ->fields('u', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $lab_migration_completed++;
              }
            }
            foreach ($query2 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $lab_migration_pending++;
              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          /*
          //  Self Workshop space
          if($query->foss_selfworkshop_no){
            db_set_active('selfworkshop');
            $query2 = db_query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
              ':foss_id' => $query->foss_selfworkshop_no,
              ':state' => $state
                                  ));

            $self_workshop += $query2->fetchObject()->count;
          }
          */
          // DWSIM Flowsheet
          if($name == 'DWSIM'){
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            $query1 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            $query2 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $dwsim_flowsheet_pending++;
              }
            }
            foreach ($query2 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $dwsim_flowsheet_completed++;
              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          // eSim Circuit Simulation
          }else if($name == 'eSim'){
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            $query2 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
            $connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $circuit_simulation_completed++;
              }
            }
            foreach ($query2 as $s) {
              $check = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              if ($check) {
                $circuit_simulation_pending++;
              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          \Drupal\Core\Database\Database::setActiveConnection('default');
          $connection = \Drupal\Core\Database\Database::getConnection('default');
          db_set_active();
        }
      }
    }
    $pincode_out = "[]";
    $city_out = "[]";
    if($district != "false" && $workshop + $conference + $lab_migration_pending + $lab_migration_completed + $pendingbookcount + $completedbookcount + $dwsim_flowsheet_pending + $dwsim_flowsheet_completed + $circuit_simulation_pending + $circuit_simulation_completed != 0){
      $pincode_out = "[";
      $city_out = "[";
      $pincodes = array_keys($city);
      $i =0;
      foreach ($city as $c) {
        $c = str_replace('S.O', '', $c) ;
        $c = str_replace('H.O', '', $c) ;
        $c = str_replace('B.O', '', $c) ;
        $city_out .= '"'.$c.'",';
        $pincode_out .= '"'.$pincodes[$i++].'",';
      }
      $pincode_out = substr($pincode_out, 0, strlen($pincode_out)-1);
      $city_out = substr($city_out, 0, strlen($city_out)-1);
      $pincode_out .= ']';
      $city_out .= ']';
    }
    // Converting generated data to JSON format
    $result = '{"Workshop" : '.$workshop.', "Conference" : '.$conference.', "lab_migration_completed" : '.$lab_migration_completed.', "lab_migration_pending" : '.$lab_migration_pending.', "PendingBookCount" : '.$pendingbookcount.', "CompletedBookCount" : '.$completedbookcount.', "selfworkshop": '.$self_workshop.', "Flowsheet_completed" : '.$dwsim_flowsheet_completed.', "Flowsheet_pending" : '.$dwsim_flowsheet_pending.', "circuit_simulation_completed" : '.$circuit_simulation_completed.', "circuit_simulation_pending" : '.$circuit_simulation_pending.', "Pincodes":'.$pincode_out.',"City":'.$city_out.'}';
    $response->setContent($result);
    $response->headers->set('Content-Type','text/plain');
     // return $result;
    return $response;
  }

}


 ?>
