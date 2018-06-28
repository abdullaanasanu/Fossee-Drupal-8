<?php

namespace Drupal\fossee_stats;

//use Drupal\Core\Controller\ControllerBase;

class FossBase
{

  public static function get_map_data($type,$activities,$status){
    /* Initializing all states in an array with their corresponding values */
    $states = array(
      'Andhra Pradesh' => 0,
      'Arunachal Pradesh' => 0,
      'Assam' => 0,
      'Bihar' => 0,
      'Chhattisgarh' => 0,
      'Goa' => 0,
      'Gujarat' => 0,
      'Haryana' => 0,
      'Himachal Pradesh' => 0,
      'Jammu and Kashmir' => 0,
      'Jharkhand' => 0,
      'Karnataka' => 0,
      'Kerala' => 0,
      'Madhya Pradesh' => 0,
      'Maharashtra' => 0,
      'Manipur' => 0,
      'Meghalaya' => 0,
      'Mizoram' => 0,
      'Nagaland' => 0,
      'Odisha(Orissa)' => 0,
      'Punjab' => 0,
      'Rajasthan' => 0,
      'Sikkim' => 0,
      'Tamil Nadu' => 0,
      'Telangana' => 0,
      'Tripura' => 0,
      'Uttarakhand' => 0,
      'Uttar Pradesh' => 0,
      'West Bengal' => 0,
      'Andaman and Nicobar Islands' => 0,
      'Chandigarh' => 0,
      'Dadra and Nagar Haveli' => 0,
      'Daman and Diu' => 0,
      'National Capital Territory of Delhi' => 0,
      'Puducherry (Pondicherry)' => 0
    );
    /* For separation of state name from key of $states array */
    $key = array_keys($states);
    $connection = \Drupal::database();

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
          // Workshop
          if($foss->workshop){
            $query1 = $connection->select('workshop', 'w')
              ->fields('w', array('state'))
              ->condition('type','workshop')
              ->condition('foss_name',$name)
              ->execute()
              ->fetchAll();
            // Assigning to each states
            foreach ($query1 as $s) {
              if($s->state){
                $states[$s->state]++;
              }
            }
          }
          // Conference
          if($foss->conference){
            $query1 = $connection->select('workshop', 'w')
              ->fields('w',array('state'))
              ->condition('type','conference')
              ->condition('foss_name',$name)
              ->execute()
              ->fetchAll();
            foreach ($query1 as $s) {
              if($s->state){
                $states[$s->state]++;
              }
            }
          }

          // Text Book companion
          \Drupal\Core\Database\Database::setActiveConnection($name);
          $connection = \Drupal\Core\Database\Database::getConnection($name);
          if($foss->tbc){
            if ($name != 'Python') {
              $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( date("Y-m-d"))));
              if ($name != 'eSim' && $name != 'OpenModelica' && $name != 'OpenFOAM' && $name != 'OR-Tools') {
                  if ($name != 'DWSIM') {
                    foreach ($key as $k) {
                      /* Completed Textbook Companion */
                      $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state", array(
                          ':state' => $k,
                      ));
                      /* Pending Textbook Companion */
                      $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                          ':state' => $k,
                          ':enddate' => $pending_enddate
                      ));
                      $states[$k] += $query2->fetchObject()->book_count;
                      $states[$k] += $query3->fetchObject()->book_count;
                    }
                  }
                  else {
                    foreach ($key as $k) {
                      /* Completed Textbook Companion */
                      $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state", array(
                          ':state' => $k,
                      ));
                      /* Pending Textbook Companion */
                      $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                          ':state' => $k,
                          ':enddate' => $pending_enddate
                      ));
                      $states[$k] += $query2->fetchObject()->book_count;
                      $states[$k] += $query3->fetchObject()->book_count;
                    }
                  }
              }
              else {
                foreach ($key as $k) {
                  /* Completed Textbook Companion */
                  $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                      ':state' => $k,
                  ));
                  /* Pending Textbook Companion */
                  $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                      ':state' => $k,
                      ':enddate' => $pending_enddate
                  ));
                  $states[$k] += $query2->fetchObject()->book_count;
                  $states[$k] += $query3->fetchObject()->book_count;
                }
              }
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
              ->fields('u', array('state'))
              ->condition('approval_status', 3)
              ->execute()
              ->fetchAll();
            foreach ($query1 as $s) {
              if($s->state){
                $states[$s->state]++;
              }
            }
            /* Lab in Progress of Lab Migration */
            $query1 = $connection->select('lab_migration_proposal','u')
              ->fields('u', array('state'))
              ->condition('approval_status', 1)
              ->execute()
              ->fetchAll();
            foreach ($query1 as $s) {
              if($s->state){
                $states[$s->state]++;
              }
            }
          }
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
            foreach ($key as $k) {
              $query1 = $connection->select('dwsim_flowsheet_proposal')
                ->condition('approval_status', 3)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
              $query1 = $connection->select('dwsim_flowsheet_proposal')
                ->condition('approval_status', 1)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          /* eSim Circuit Simulation */
          }else if($name == 'eSim'){
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            foreach ($key as $k) {
              $query1 = $connection->select('esim_circuit_simulation_proposal')
                ->condition('approval_status', 3)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
              $query1 = $connection->select('esim_circuit_simulation_proposal')
                ->condition('approval_status', 1)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }

          }
          $connection = \Drupal::database();

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
                    $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( date("Y-m-d"))));

                      if ($name != 'eSim' && $name != 'OpenModelica' && $name != 'OpenFOAM' && $name != 'OR-Tools') {
                          if ($name != 'DWSIM') {
                            foreach ($key as $k) {
                              /* Pending Textbook Companion */
                              $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':state' => $k,
                                  ':enddate' => $pending_enddate
                              ));
                              $states[$k] += $query3->fetchObject()->book_count;
                            }
                          }
                          else {
                            foreach ($key as $k) {
                              /* Pending Textbook Companion */
                              $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status != 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':state' => $k,
                                  ':enddate' => $pending_enddate
                              ));
                              $states[$k] += $query3->fetchObject()->book_count;
                            }
                          }
                      }
                      else {
                        foreach ($key as $k) {
                          /* Pending Textbook Companion */
                          $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':state' => $k,
                              ':enddate' => $pending_enddate
                          ));
                          $states[$k] += $query3->fetchObject()->book_count;
                        }
                      }

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
                    $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( date("Y-m-d"))));

                      if ($name != 'eSim' && $name != 'OpenModelica' && $name != 'OpenFOAM' && $name != 'OR-Tools') {
                          if ($name != 'DWSIM') {
                            foreach ($key as $k) {
                              /* Completed Textbook Companion */
                              $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':state' => $k,
                                  ':enddate' => $pending_enddate
                              ));
                              $states[$k] += $query2->fetchObject()->book_count;
                            }
                          }
                          else {
                            foreach ($key as $k) {
                              /* Completed Textbook Companion */
                              $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':state' => $k,
                                  ':enddate' => $pending_enddate
                              ));
                              $states[$k] += $query2->fetchObject()->book_count;
                            }
                          }
                      }
                      else {
                        foreach ($key as $k) {
                          /* Completed Textbook Companion */
                          $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status = 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':state' => $k,
                              ':enddate' => $pending_enddate
                          ));
                          $states[$k] += $query2->fetchObject()->book_count;
                        }
                      }

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
          /* Labs in Progress for Lab Migration */
          }elseif ($act == 'Lab Migration' && $stat == 'Labs in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->lab_migration){
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('state'))
                ->condition('approval_status', 1)
                ->execute()
                ->fetchAll();
              foreach ($query1 as $s) {
                if($s->state){
                  $states[$s->state]++;
                }
              }
            }
          /* Completed Labs for Lab Migration */
          }elseif ($act == 'Lab Migration' && $stat == 'Completed Labs') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->lab_migration){
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('state'))
                ->condition('approval_status', 3)
                ->execute()
                ->fetchAll();
              foreach ($query1 as $s) {
                if($s->state){
                  $states[$s->state]++;
                }
              }
            }
          }elseif ($act == 'Flowsheet' && $stat == 'Flowsheets in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            foreach ($key as $k) {
              $query1 = $connection->select('dwsim_flowsheet_proposal')
                ->condition('approval_status', 1)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          }elseif ($act == 'Flowsheet' && $stat == 'Completed Flowsheets') {
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            foreach ($key as $k) {
              $query1 = $connection->select('dwsim_flowsheet_proposal')
                ->condition('approval_status', 3)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          }elseif ($act == 'Circuit Simulation' && $stat == 'Simulations in Progress') {
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            foreach ($key as $k) {
              $query1 = $connection->select('esim_circuit_simulation_proposal')
                ->condition('approval_status', 1)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          }elseif ($act == 'Circuit Simulation' && $stat == 'Completed Simulations') {
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            foreach ($key as $k) {
              $query1 = $connection->select('esim_circuit_simulation_proposal')
                ->condition('approval_status', 3)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          }
        /* When status not set */
        }else {
          // Workshop
          if ($act == 'Workshop') {
            if($query->workshop){
              $query1 = $connection->select('workshop', 'w')
                ->fields('w', array('state'))
                ->condition('type','workshop')
                ->condition('foss_name',$name)
                ->execute()
                ->fetchAll();
              foreach ($query1 as $s) {
                if($s->state){
                  $states[$s->state]++;
                }
              }
            }
          // Conference
          }elseif ($act == 'Conference') {
            if($query->conference){
              $query1 = $connection->select('workshop', 'w')
                ->fields('w',array('state'))
                ->condition('type','conference')
                ->condition('foss_name',$name)
                ->execute()
                ->fetchAll();
              foreach ($query1 as $s) {
                if($s->state){
                  $states[$s->state]++;
                }
              }
            }
          // Textbook  Companion
          }elseif ($act == 'Textbook Companion') {
            \Drupal\Core\Database\Database::setActiveConnection($name);
            $connection = \Drupal\Core\Database\Database::getConnection($name);
            if($query->tbc){

              if ($name != 'Python') {
                    $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( date("Y-m-d"))));

                      if ($name != 'eSim' && $name != 'OpenModelica' && $name != 'OpenFOAM' && $name != 'OR-Tools') {
                          if ($name != 'DWSIM') {
                            foreach ($key as $k) {
                              /* Completed Textbook */
                              $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state", array(
                                  ':state' => $k,
                              ));
                                /* Pending Textbook */
                              $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':state' => $k,
                                  ':enddate' => $pending_enddate
                              ));
                              $states[$k] += $query2->fetchObject()->book_count;
                              $states[$k] += $query3->fetchObject()->book_count;
                            }
                          }
                          else {
                            foreach ($key as $k) {
                              /* Completed Textbook */
                              $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state", array(
                                  ':state' => $k,
                              ));
                                /* Pending Textbook */
                              $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status != 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                                  ':state' => $k,
                                  ':enddate' => $pending_enddate
                              ));
                              $states[$k] += $query2->fetchObject()->book_count;
                              $states[$k] += $query3->fetchObject()->book_count;
                            }
                          }
                      }
                      else {
                        foreach ($key as $k) {
                          /* Completed Textbook */
                          $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                              ':state' => $k,
                          ));
                            /* Pending Textbook */
                          $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':state' => $k,
                              ':enddate' => $pending_enddate
                          ));
                          $states[$k] += $query2->fetchObject()->book_count;
                          $states[$k] += $query3->fetchObject()->book_count;
                        }
                      }

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
                ->fields('u', array('state'))
                ->condition('approval_status', 3)
                ->execute()
                ->fetchAll();
              foreach ($query1 as $s) {
                if($s->state){
                  $states[$s->state]++;
                }
              }
              /* Lab in Progress of Lab Migration */
              $query1 = $connection->select('lab_migration_proposal','u')
                ->fields('u', array('state'))
                ->condition('approval_status', 1)
                ->execute()
                ->fetchAll();
              foreach ($query1 as $s) {
                if($s->state){
                  $states[$s->state]++;
                }
              }
            }
          }elseif ($act == "Flowsheet") {
            \Drupal\Core\Database\Database::setActiveConnection('DWSIM');
            $connection = \Drupal\Core\Database\Database::getConnection('DWSIM');
            foreach ($key as $k) {
              $query1 = $connection->select('dwsim_flowsheet_proposal')
                ->condition('approval_status', 3)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
              $query1 = $connection->select('dwsim_flowsheet_proposal')
                ->condition('approval_status', 1)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          }elseif ($act == "Circuit Simulation") {
            \Drupal\Core\Database\Database::setActiveConnection('eSim');
            $connection = \Drupal\Core\Database\Database::getConnection('eSim');
            foreach ($key as $k) {
              $query1 = $connection->select('esim_circuit_simulation_proposal')
                ->condition('approval_status', 3)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
              $query1 = $connection->select('esim_circuit_simulation_proposal')
                ->condition('approval_status', 1)
                ->condition('state',$k)
                ->countQuery()
                ->execute()
                ->fetchField();
              $states[$k] += $query1;
            }
          }
        }
      }else {
        // Workshop
        if($query->workshop){
          $query1 = $connection->select('workshop', 'w')
            ->fields('w', array('state'))
            ->condition('type','workshop')
            ->condition('foss_name',$name)
            ->execute()
            ->fetchAll();
          foreach ($query1 as $s) {
            if($s->state){
              $states[$s->state]++;
            }
          }
        }
        // Conference
        if($query->conference){
          $query1 = $connection->select('workshop', 'w')
            ->fields('w',array('state'))
            ->condition('type','conference')
            ->condition('foss_name',$name)
            ->execute()
            ->fetchAll();
          foreach ($query1 as $s) {
            if($s->state){
              $states[$s->state]++;
            }
          }
        }
        // Text Book companion
        \Drupal\Core\Database\Database::setActiveConnection($name);
        $connection = \Drupal\Core\Database\Database::getConnection($name);
        if($query->tbc){

          if ($name != 'Python') {
                $pending_enddate = date('Y-m-d', strtotime("+5 months", strtotime( date("Y-m-d"))));

                  if ($name != 'eSim' && $name != 'OpenModelica' && $name != 'OpenFOAM' && $name != 'OR-Tools') {
                      if ($name != 'DWSIM') {
                        foreach ($key as $k) {
                          /* Completed Textbook */
                          $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state", array(
                              ':state' => $k,
                          ));
                            /* Pending Textbook */
                          $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':state' => $k,
                              ':enddate' => $pending_enddate
                          ));
                          $states[$k] += $query2->fetchObject()->book_count;
                          $states[$k] += $query3->fetchObject()->book_count;
                        }
                      }
                      else {
                        foreach ($key as $k) {
                          /* Completed Textbook */
                          $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND pe.category>0 AND po.state LIKE :state", array(
                              ':state' => $k,
                          ));
                            /* Pending Textbook */
                          $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status != 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                              ':state' => $k,
                              ':enddate' => $pending_enddate
                          ));
                          $states[$k] += $query2->fetchObject()->book_count;
                          $states[$k] += $query3->fetchObject()->book_count;
                        }
                      }
                  }
                  else {
                    foreach ($key as $k) {
                      /* Completed Textbook */
                      $query2 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status =3 AND pe.approval_status =1 AND po.state LIKE :state", array(
                          ':state' => $k,
                      ));
                        /* Pending Textbook */
                      $query3 = $connection->query("SELECT COUNT( pe.book ) AS book_count FROM {textbook_companion_preference} pe LEFT JOIN {textbook_companion_proposal} po ON pe.proposal_id = po.id WHERE po.proposal_status <> 3 AND pe.approval_status =1 AND po.state LIKE :state AND FROM_UNIXTIME(po.completion_date) <= :enddate", array(
                          ':state' => $k,
                          ':enddate' => $pending_enddate
                      ));
                      $states[$k] += $query2->fetchObject()->book_count;
                      $states[$k] += $query3->fetchObject()->book_count;
                    }
                  }

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
          /* completed Lab Migration */
          $query1 = $connection->select('lab_migration_proposal','u')
            ->fields('u', array('state'))
            ->condition('approval_status', 3)
            ->execute()
            ->fetchAll();
          foreach ($query1 as $s) {
            if($s->state){
              $states[$s->state]++;
            }
          }
          /* Lab in Progress of Lab Migration */
          $query1 = $connection->select('lab_migration_proposal','u')
            ->fields('u', array('state'))
            ->condition('approval_status', 1)
            ->execute()
            ->fetchAll();
          foreach ($query1 as $s) {
            if($s->state){
              $states[$s->state]++;
            }
          }
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
          foreach ($key as $k) {
            $query1 = $connection->select('dwsim_flowsheet_proposal')
              ->condition('approval_status', 3)
              ->condition('state',$k)
              ->countQuery()
              ->execute()
              ->fetchField();
            $states[$k] += $query1;
            $query1 = $connection->select('dwsim_flowsheet_proposal')
              ->condition('approval_status', 1)
              ->condition('state',$k)
              ->countQuery()
              ->execute()
              ->fetchField();
            $states[$k] += $query1;
          }
        /*  eSim Circuit Simulation */
        }else if($name == 'eSim'){
          \Drupal\Core\Database\Database::setActiveConnection('eSim');
          $connection = \Drupal\Core\Database\Database::getConnection('eSim');
          foreach ($key as $k) {
            $query1 = $connection->select('esim_circuit_simulation_proposal')
              ->condition('approval_status', 3)
              ->condition('state',$k)
              ->countQuery()
              ->execute()
              ->fetchField();
            $states[$k] += $query1;
            $query1 = $connection->select('esim_circuit_simulation_proposal')
              ->condition('approval_status', 1)
              ->condition('state',$k)
              ->countQuery()
              ->execute()
              ->fetchField();
            $states[$k] += $query1;
          }

        }

        $connection = \Drupal::database();

      }

    }
    $i =0;
    // Findiing Maximum and Total Value from states
    $max = max($states);
    $total = array_sum($states);
    /* Dynamic representation of data and Assigning color to each state according to it's value */
    if ($max > 100) {
      $max = $max-($max%100);
      foreach ($states as $s) {
        if($s > $max){
          $states[$key[$i]]='#ea5507';
        }else if ($s<=$max && $s>$max*(3/4)) {
          $states[$key[$i]]='#e56b2b';
        }else if ($s<=$max*(3/4) && $s>$max*(2/4)) {
          $states[$key[$i]]='#e08859';
        }else if ($s<=$max*(2/4) && $s>$max*(1/4)) {
          $states[$key[$i]]='#e29d78';
        }else if ($s<=$max*(1/4) && $s>0) {
          $states[$key[$i]]='#e0ab8f';
        }else{
          $states[$key[$i]]='#ddc6ba';
        }
        $i++;
      }
    }elseif ($max > 10) {
      $max = $max-($max%10);
      foreach ($states as $s) {
        if($s > $max){
          $states[$key[$i]]='#ea5507';
        }else if ($s<=$max && $s>$max/2) {
          $states[$key[$i]]='#e08859';
        }else if ($s<=$max/2 && $s>0) {
          $states[$key[$i]]='#e0ab8f';
        }else{
          $states[$key[$i]]='#ddc6ba';
        }
        $i++;
      }
    }else{
      foreach ($states as $s) {
        if($s > $max/2){
          $states[$key[$i]]='#ea5507';
        }else if ($s<=$max/2 && $s>0) {
          $states[$key[$i]]='#e08859';
        }else{
          $states[$key[$i]]='#ddc6ba';
        }
        $i++;
      }
    }
    // Setting the data set to generate the map with corresponding colors
    require_once('fossee_stats_map.inc');
    $out = stats_map($states,$max,$total);
    return $out;

  }

  function get_activities_list($foss_project) {
    $connection = \Drupal::database();
    if ($foss_project != NULL) {
        $query = $connection->select('foss_type');
        $query->fields('foss_type', array(
            'tbc',
            'lab_migration',
            'workshop',
            'conference'
        ));
        $query->fields('foss_type', array(
            'spoken_tutorial',
            'postal_campaigns',
            'flow_sheet',
            'circuit_simulation',
            'case_study'
        ));
        $query->condition('id', $foss_project);
        $result = $query->execute();
        $subproject_detail = $result->fetchObject();
        $optiondata = array(
            "tbc",
            "lab_migration",
            "workshop",
            "conference",
            "spoken_tutorial",
            "postal_campaigns",
            "flow_sheet",
            "circuit_simulation",
            "case_study"
        );
        $optionvalue = array(
            " ",
            "Textbook Companion",
            "Lab Migration",
            "Workshop",
            "Conference",
            "Spoken Tutorial",
            "Postal Campaigns",
            "Flowsheet",
            "Circuit Simulation",
            "Case Study"
        );
        $options = array();
        $options[0] = "--------------";
        $i = 0;
        foreach ($optiondata as $value) {
            $i++;
            if (($subproject_detail->$value) != 0) {
                $options[$i] = $optionvalue[$i];
            }
        }
        return $options;
    }
    else {
        $options[0] = "--------------";
        return $options;
    }

  }

  function _ajax_example_get_third_dropdown_options($foss_sub_project) {
    $options = array();
    if ($foss_sub_project != 0) {
        if ($foss_sub_project == 1) {
            $options[0] = "--------------";
            $options[1] = "Books in Progress";
            $options[2] = "Completed Books";
        }
        elseif ($foss_sub_project == 2) {
            $options[0] = "--------------";
            $options[1] = "Labs in Progress";
            $options[2] = "Completed Labs";
        }elseif ($foss_sub_project == 7) {
            $options[0] = "--------------";
            $options[1] = "Flowsheets in Progress";
            $options[2] = "Completed Flowsheets";
        }
        elseif ($foss_sub_project == 8) {
            $options[0] = "--------------";
            $options[1] = "Simulations in Progress";
            $options[2] = "Completed Simulations";
        }
    }

    return $options;
  }

}

 ?>
