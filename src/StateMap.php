<?php

namespace Drupal\fossee_stats;

class StateMap {

  function state_map_data($type,$activities,$status,$state){
    $connection = \Drupal::database();
    $state_id = $connection->select('states','s')
      ->fields('s', array('id'))
      ->condition('name',$state)
      ->execute()
      ->fetchField();
    $district_list = $connection->select('districts','d')
      ->fields('d', array('name'))
      ->condition('state_id',$state_id)
      ->execute();
    $districts = array();

    while($each = $district_list->fetchObject()){
      $districts[$each->name] = 0;
    }
    /* For separation of state name from key of $states array */
    $key = array_keys($districts);
    /* Checking the foss type */
    if($type == "" || $type == NULL){
      $query = $connection->select('foss_type','u')
        ->fields('u', array(
          'tbc',
          'foss_name',
          'lab_migration',
          'workshop',
          'conference',
          '$foss_selfworkshop_no'
        ))
        ->execute();
        /* For fetching data from all foss type */
        while($foss = $query->fetchObject()){
          $name = $foss->foss_name;
          //echo '<h1>'.$name.'</h1>';
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
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
          }
          //echo '<br>Workshop<br>';
          //print_r($districts);
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
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
          }
          //echo '<br>Conference<br>';
          //print_r($districts);

          // Text Book companion
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
          if($foss->tbc){
            if ($name != 'Python') {
                $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
                while($pin = $query2->fetchObject()->pin) {
                  $district = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$pin)
                    ->execute()
                    ->fetchField();
                  $districts[$district]++;
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
          //echo '<br>TBC<br>';
          //print_r($districts);

          // Lab Migration
          if($foss->lab_migration){
            /* Completed Lab Migration */
            $query1 = $connection->select('lab_migration_proposal','u')
              ->fields('u', array('pincode'))
              ->condition('approval_status', 2,'!=')
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          //echo '<br>LM<br>';
          //print_r($districts);
          /*
          //  Self Workshop space
          if($foss->foss_selfworkshop_no){
            db_set_active('selfworkshop');
            foreach ($key as $k) {
              $query2 = $connection->query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
                ':foss_id' => $foss->foss_selfworkshop_no,
                ':state'=>$k
              ));
              $states[$k] += $query2->fetchObject()->count;
            }

          }
          */
          /* DWSIM Flowsheet */
          if($name == 'DWSIM'){
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            $query1 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 2,'!=')
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          /* eSim Circuit Simulation */
          }else if($name == 'eSim'){
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 2,'!=')
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          //echo '<br>Final<br>';
          //print_r($districts);
          \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
          db_set_active();
        }
    }else{
      $query = $connection->select('foss_type','u')
        ->fields('u', array(
          'tbc',
          'foss_name',
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
        /* When Status are set */
        if ($status != 0) {
          /* Book in Progress for Textbook Companion */
          if ($act == 'Textbook Companion' && $stat == 'Books in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->tbc){
              if ($name != 'Python') {
                  /* Pending Textbook Companion */
                  $query3 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query3->fetchObject()->pin) {
                    $district = $connection->select('ai_pincode','a')
                      ->fields('a', array('districtname'))
                      ->condition('pincode',$pin)
                      ->execute()
                      ->fetchField();
                    $districts[$district]++;
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
          /* Completed Books for Textbook Companion */
          }elseif ($act == 'Textbook Companion' && $stat == 'Completed Books') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->tbc){
              if ($name != 'Python') {
                  /* Completed Textbook Companion */
                  $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state,
                  ));
                  \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
                  while($pin = $query2->fetchObject()->pin) {
                    $district = $connection->select('ai_pincode','a')
                      ->fields('a', array('districtname'))
                      ->condition('pincode',$pin)
                      ->execute()
                      ->fetchField();
                    $districts[$district]++;
                  }
                  \Drupal\Core\Database\Database::setActiveConnection($name);
                  $connection = \Drupal\Core\Database\Database::getConnection($name);
              }else {
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
          /* Labs in Progress for Lab Migration */
          }elseif ($act == 'Lab Migration' && $stat == 'Labs in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->lab_migration){
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('pincode'))
                ->condition('approval_status', 1)
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $district = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                $districts[$district]++;
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
                $district = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                $districts[$district]++;
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
          }elseif ($act == 'Flowsheet' && $stat == 'Flowsheets in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            $query1 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }elseif ($act == 'Flowsheet' && $stat == 'Completed Flowsheets') {
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            $query1 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }elseif ($act == 'Circuit Simulation' && $stat == 'Simulations in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 1)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }elseif ($act == 'Circuit Simulation' && $stat == 'Completed Simulations') {
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 3)
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
        /* When status not set */
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
                $district = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                $districts[$district]++;
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
                $district = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                $districts[$district]++;
              }
            }
          // Textbook  Companion
          }elseif ($act == 'Textbook Companion') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->tbc){

              if ($name != 'Python') {
                $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
                while($pin = $query2->fetchObject()->pin) {
                  $district = $connection->select('ai_pincode','a')
                    ->fields('a', array('districtname'))
                    ->condition('pincode',$pin)
                    ->execute()
                    ->fetchField();
                  $districts[$district]++;
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
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('pincode'))
                ->condition('approval_status', 2,'!=')
                ->condition('state',$state)
                ->execute()
                ->fetchAll();
              \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
              foreach ($query1 as $s) {
                $district = $connection->select('ai_pincode','a')
                  ->fields('a', array('districtname'))
                  ->condition('pincode',$s->pincode)
                  ->execute()
                  ->fetchField();
                $districts[$district]++;
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
          }elseif ($act == "Flowsheet") {
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            $query1 = $connection->select('dwsim_flowsheet_proposal','d')
              ->fields('d', array('pincode'))
              ->condition('approval_status', 2,'!=')
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }elseif ($act == "Circuit Simulation") {
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->fields('e', array('pincode'))
              ->condition('approval_status', 2,'!=')
              ->condition('state',$state)
              ->execute()
              ->fetchAll();
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            foreach ($query1 as $s) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$s->pincode)
                ->execute()
                ->fetchField();
              $districts[$district]++;
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
            $district = $connection->select('ai_pincode','a')
              ->fields('a', array('districtname'))
              ->condition('pincode',$s->pincode)
              ->execute()
              ->fetchField();
            $districts[$district]++;
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
            $district = $connection->select('ai_pincode','a')
              ->fields('a', array('districtname'))
              ->condition('pincode',$s->pincode)
              ->execute()
              ->fetchField();
            $districts[$district]++;
          }
        }
        // Text Book companion
        \Drupal\Core\Database\Database::setActiveConnection($name);
        $connection = \Drupal\Core\Database\Database::getConnection($name);
        if($query->tbc){

          if ($name != 'Python') {
            $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE pe.approval_status =1 AND po.state LIKE :state", array(
                ':state' => $state,
            ));
            \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
            while($pin = $query2->fetchObject()->pin) {
              $district = $connection->select('ai_pincode','a')
                ->fields('a', array('districtname'))
                ->condition('pincode',$pin)
                ->execute()
                ->fetchField();
              $districts[$district]++;
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
          $query1 = $connection->select('lab_migration_proposal','u')
            ->fields('u', array('pincode'))
            ->condition('approval_status', 2,'!=')
            ->condition('state',$state)
            ->execute()
            ->fetchAll();
          \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
          foreach ($query1 as $s) {
            $district = $connection->select('ai_pincode','a')
              ->fields('a', array('districtname'))
              ->condition('pincode',$s->pincode)
              ->execute()
              ->fetchField();
            $districts[$district]++;
          }
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
        }
        /*
        //  Self Workshop space
        if($query->foss_selfworkshop_no){
          db_set_active('selfworkshop');
          foreach ($key as $k) {
            $query2 = $connection->query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
              ':foss_id' => $query->foss_selfworkshop_no,
              ':state'=>$k
            ));
            $states[$k] += $query2->fetchObject()->count;
          }

        }
        */
        /* DWSIM Flowsheet */
        if($name == 'DWSIM'){
          \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
          $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
          $query1 = $connection->select('dwsim_flowsheet_proposal','d')
            ->fields('d', array('pincode'))
            ->condition('approval_status', 2,'!=')
            ->condition('state',$state)
            ->execute()
            ->fetchAll();
          \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
          foreach ($query1 as $s) {
            $district = $connection->select('ai_pincode','a')
              ->fields('a', array('districtname'))
              ->condition('pincode',$s->pincode)
              ->execute()
              ->fetchField();
            $districts[$district]++;
          }
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
        /*  eSim Circuit Simulation */
        }else if($name == 'eSim'){
          \Drupal\Core\Database\Database::setActiveConnection('eSim');
          $connection = \Drupal\Core\Database\Database::getConnection('eSim');
          $query1 = $connection->select('esim_circuit_simulation_proposal','e')
            ->fields('e', array('pincode'))
            ->condition('approval_status', 2,'!=')
            ->condition('state',$state)
            ->execute()
            ->fetchAll();
          \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
          foreach ($query1 as $s) {
            $district = $connection->select('ai_pincode','a')
              ->fields('a', array('districtname'))
              ->condition('pincode',$s->pincode)
              ->execute()
              ->fetchField();
            $districts[$district]++;
          }
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);

        }

        \Drupal\Core\Database\Database::setActiveConnection('default');
$connection = \Drupal\Core\Database\Database::getConnection('default');
        db_set_active();
      }

    }
    $i =0;
    //print_r($districts);
    // Findiing Maximum and Total Value from states
    $max = max($districts);
    $total = array_sum($districts);
    /* Dynamic representation of data and Assigning color to each state according to it's value */
    if ($max > 100) {
      $max = $max-($max%100);
      foreach ($districts as $s) {
        if($s > $max){
          $districts[$key[$i]]='#ea5507';
        }else if ($s<=$max && $s>$max*(3/4)) {
          $districts[$key[$i]]='#e56b2b';
        }else if ($s<=$max*(3/4) && $s>$max*(2/4)) {
          $districts[$key[$i]]='#e08859';
        }else if ($s<=$max*(2/4) && $s>$max*(1/4)) {
          $districts[$key[$i]]='#e29d78';
        }else if ($s<=$max*(1/4) && $s>0) {
          $districts[$key[$i]]='#e0ab8f';
        }else{
          $districts[$key[$i]]='#ddc6ba';
        }
        $i++;
      }
    }elseif ($max > 10) {
      $max = $max-($max%10);
      foreach ($districts as $s) {
        if($s > $max){
          $districts[$key[$i]]='#ea5507';
        }else if ($s<=$max && $s>$max/2) {
          $districts[$key[$i]]='#e08859';
        }else if ($s<=$max/2 && $s>0) {
          $districts[$key[$i]]='#e0ab8f';
        }else{
          $districts[$key[$i]]='#ddc6ba';
        }
        $i++;
      }
    }else{
      foreach ($districts as $s) {
        if($s > $max/2){
          $districts[$key[$i]]='#ea5507';
        }else if ($s<=$max/2 && $s>0) {
          $districts[$key[$i]]='#e08859';
        }else{
          $districts[$key[$i]]='#ddc6ba';
        }
        $i++;
      }
    }
    // Setting the data set to generate the map with corresponding colors
    require_once('states/generator.inc');
    $out = generator($districts,$max,$total,$state);
    return $out;

  }

  function post_data($type,$activities,$status,$pincode,$state,$district){
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
      $connection = \Drupal::database();
    if(!preg_match("/[a-z]/i", $pincode)){
      if($type == "" || $type == 0){
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
                ->condition('type','workshop')
                ->condition('foss_name',$name)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $workshop += $query1;
            }
            // Conference
            if($foss->conference){
              $query1 = $connection->select('workshop', 'w')
                ->condition('type','conference')
                ->condition('foss_name',$name)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $conference += $query1;
            }
            // Text Book companion
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($foss->tbc){
              if ($name != 'Python') {
                $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                    ':pincode' => $pincode,
                ));
                $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                    ':pincode' => $pincode,
                ));
                $pendingbookcount += $query2->fetchObject()->book_count;
                $completedbookcount += $query3->fetchObject()->book_count;
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
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $query2 = $connection->select('lab_migration_proposal','u')
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $lab_migration_completed += $query1;
              $lab_migration_pending += $query2;
            }
            /*
            //  Self Workshop space
            if($foss->foss_selfworkshop_no){
              db_set_active('selfworkshop');
              $query2 = $connection->query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
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
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $dwsim_flowsheet_pending += $query1;
              $dwsim_flowsheet_completed += $query2;
            // eSim Circuit Simulation
            }else if($name == 'eSim'){
              \Drupal\Core\Database\Database::setActiveConnection('eSim');
              $connection = \Drupal\Core\Database\Database::getConnection('eSim');
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $circuit_simulation_pending += $query2;
              $circuit_simulation_completed += $query1;
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
                  $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                      ':pincode' => $pincode,
                  ));
                  $pendingbookcount += $query2->fetchObject()->book_count;

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
                  $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                      ':pincode' => $pincode,
                  ));
                  $completedbookcount += $query3->fetchObject()->book_count;

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
                  ->condition('approval_status', 1)
                  ->condition('pincode',$pincode)
                  ->countQuery()
                  ->execute()
                  ->fetchField();
                $lab_migration_pending += $query2;
              }
            /* Completed Labs for Lab Migration */
            }elseif ($act == 'Lab Migration' && $stat == 'Completed Labs') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->lab_migration){
                $query1 = $connection->select('lab_migration_proposal','u')
                  ->condition('approval_status', 3)
                  ->condition('pincode',$pincode)
                  ->countQuery()
                  ->execute()
                  ->fetchField();
                $lab_migration_completed += $query1;
              }
            }elseif ($act == 'Flowsheet' && $stat == 'Flowsheets in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $dwsim_flowsheet_pending += $query1;
            }elseif ($act == 'Flowsheet' && $stat == 'Completed Flowsheets') {
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $dwsim_flowsheet_completed += $query2;
            }elseif ($act == 'Circuit Simulation' && $stat == 'Simulations in Progress') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $circuit_simulation_pending += $query2;
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }elseif ($act == 'Circuit Simulation' && $stat == 'Completed Simulations') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $circuit_simulation_completed += $query1;
            }
          }else {
            // Workshop
            if ($act == 'Workshop') {
              if($query->workshop){
                $query1 = $connection->select('workshop', 'w')
                  ->condition('type','workshop')
                  ->condition('foss_name',$name)
                  ->condition('pincode',$pincode)
                  ->countQuery()
                  ->execute()
                  ->fetchField();
                $workshop += $query1;
              }
            // Conference
            }elseif ($act == 'Conference') {
              if($query->conference){
                $query1 = $connection->select('workshop', 'w')
                  ->condition('type','conference')
                  ->condition('foss_name',$name)
                  ->condition('pincode',$pincode)
                  ->countQuery()
                  ->execute()
                  ->fetchField();
                $conference += $query1;
              }
            // Textbook Companion
            }elseif ($act == 'Textbook Companion') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                      ':pincode' => $pincode,
                  ));
                  $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                      ':pincode' => $pincode,
                  ));
                  $pendingbookcount += $query2->fetchObject()->book_count;
                  $completedbookcount += $query3->fetchObject()->book_count;
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
                  ->condition('approval_status', 3)
                  ->condition('pincode',$pincode)
                  ->countQuery()
                  ->execute()
                  ->fetchField();
                $query2 = $connection->select('lab_migration_proposal','u')
                  ->condition('approval_status', 1)
                  ->condition('pincode',$pincode)
                  ->countQuery()
                  ->execute()
                  ->fetchField();
                $lab_migration_completed += $query1;
                $lab_migration_pending += $query2;
              }
            }elseif ($act == "Flowsheet") {
              \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
              $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
              $query1 = $connection->select('dwsim_flowsheet_proposal','d')
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $query2 = $connection->select('dwsim_flowsheet_proposal','d')
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $dwsim_flowsheet_pending += $query1;
              $dwsim_flowsheet_completed += $query2;
            }elseif ($act == "Circuit Simulation") {
              \Drupal\Core\Database\Database::setActiveConnection('eSim');
              $connection = \Drupal\Core\Database\Database::getConnection('eSim');
              $query1 = $connection->select('esim_circuit_simulation_proposal','e')
                ->condition('approval_status', 3)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $query2 = $connection->select('esim_circuit_simulation_proposal','e')
                ->condition('approval_status', 1)
                ->condition('pincode',$pincode)
                ->countQuery()
                ->execute()
                ->fetchField();
              $circuit_simulation_pending += $query2;
              $circuit_simulation_completed += $query1;
            }
          }
        }else {
          // Workshop
          if($query->workshop){
            $query1 = $connection->select('workshop', 'w')
              ->condition('type','workshop')
              ->condition('foss_name',$name)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $workshop += $query1;

          }
          // Conference
          if($query->conference){
            $query1 = $connection->select('workshop', 'w')
              ->condition('type','conference')
              ->condition('foss_name',$name)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $conference += $query1;
          }
          // Text Book companion
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
          if($query->tbc){
            if ($name != 'Python') {
              $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                  ':pincode' => $pincode,
              ));
              $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.pincode LIKE :pincode", array(
                  ':pincode' => $pincode,
              ));
              $pendingbookcount += $query2->fetchObject()->book_count;
              $completedbookcount += $query3->fetchObject()->book_count;
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
              ->condition('approval_status', 3)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $query2 = $connection->select('lab_migration_proposal','u')
              ->condition('approval_status', 1)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $lab_migration_completed += $query1;
            $lab_migration_pending += $query2;
          }
          /*
          //  Self Workshop space
          if($query->foss_selfworkshop_no){
            db_set_active('selfworkshop');
            $query2 = $connection->query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
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
              ->condition('approval_status', 1)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $query2 = $connection->select('dwsim_flowsheet_proposal','d')
              ->condition('approval_status', 3)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $dwsim_flowsheet_pending += $query1;
            $dwsim_flowsheet_completed += $query2;
          // eSim Circuit Simulation
          }else if($name == 'eSim'){
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            $query1 = $connection->select('esim_circuit_simulation_proposal','e')
              ->condition('approval_status', 3)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $query2 = $connection->select('esim_circuit_simulation_proposal','e')
              ->condition('approval_status', 1)
              ->condition('pincode',$pincode)
              ->countQuery()
              ->execute()
              ->fetchField();
            $circuit_simulation_pending += $query2;
            $circuit_simulation_completed += $query1;
          }
          \Drupal\Core\Database\Database::setActiveConnection('default');
          $connection = \Drupal\Core\Database\Database::getConnection('default');
          db_set_active();
        }
      }
    }else {
      if($type == "" || $type == 0){
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

                }
              }
            }
            // Text Book companion
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($foss->tbc){
              if ($name != 'Python') {
                $query2 = $connection->query("SELECT po.pincode AS pin, po.city AS city FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                    ':state' => $state,
                ));
                $query3 = $connection->query("SELECT po.pincode AS pin, po.city AS city FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
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

                }
              }
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
            }
            /*
            //  Self Workshop space
            if($foss->foss_selfworkshop_no){
              db_set_active('selfworkshop');
              $query2 = $connection->query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
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
                  $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
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
                  $query3 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
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

                  }
                }
              }
            // Textbook Companion
            }elseif ($act == 'Textbook Companion') {
              \Drupal\Core\Database\Database::setActiveConnection($name);
              $connection = \Drupal\Core\Database\Database::getConnection($name);
              if($query->tbc){
                if ($name != 'Python') {
                  $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $state,
                  ));
                  $query3 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
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

              }
            }
          }
          // Text Book companion
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
          if($query->tbc){
            if ($name != 'Python') {
              $query2 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                  ':state' => $state,
              ));
              $query3 = $connection->query("SELECT po.pincode AS pin FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state", array(
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

              }
            }
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
          }
          /*
          //  Self Workshop space
          if($query->foss_selfworkshop_no){
            db_set_active('selfworkshop');
            $query2 = $connection->query("SELECT count(t.id) as count FROM {events_training} t, {events_academiccenter} ac, {events_city} c, {events_state} s WHERE t.academic_id=ac.id and ac.city_id=c.id and c.state_id=s.id and (t.status = 4 AND t.foss_id = :foss_id ) and s.name LIKE :state ", array(
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
    echo '{"Workshop" : '.$workshop.', "Conference" : '.$conference.', "lab_migration_completed" : '.$lab_migration_completed.', "lab_migration_pending" : '.$lab_migration_pending.', "PendingBookCount" : '.$pendingbookcount.', "CompletedBookCount" : '.$completedbookcount.', "selfworkshop": '.$self_workshop.', "Flowsheet_completed" : '.$dwsim_flowsheet_completed.', "Flowsheet_pending" : '.$dwsim_flowsheet_pending.', "circuit_simulation_completed" : '.$circuit_simulation_completed.', "circuit_simulation_pending" : '.$circuit_simulation_pending.'}';
  }

}

 ?>
