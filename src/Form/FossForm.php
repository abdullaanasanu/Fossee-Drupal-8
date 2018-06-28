<?php

namespace Drupal\fossee_stats\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

use Drupal\fossee_stats\FossBase;

class FossForm extends FormBase
{

  public function getFormId() {
    return 'fossee_stats_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state){

    /* First Dropdown for Foss type */

    $options_first = $this->_ajax_example_get_first_dropdown_options();
    $form['foss_type'] = array(
        '#type' => 'select',
        '#prefix' => '<div class="content"><div><table ><tr><td id="type" onchange="check_activities()">',
        '#suffix' => '</td><td id="activities"></td><td id="status"></td>',
        '#title' => t('FOSS Type'),
        '#multiple' => FALSE,
        '#options' => $options_first,
        '#validated' => TRUE
    );
    /* Submit button with onclick function for javascript*/
    $form['submit']= array(
        '#type' => 'inline_template',
        '#template' => '<tr><td><input type="button" onclick="map()" value="Submit" class="button js-form-submit form-submit">'
    );
    /* Reset button to clear all form*/
    $form['reset'] = array(
        '#type' => 'submit',
        '#value' => t('Reset'),
        '#prefix' => '',
        '#suffix' => '</td></tr></table></div>'
    );
    /* Space to display the map by default and by Ajax request */
    $form['map'] = array(
        '#type' => 'inline_template',
        '#template' => '<div id="poper"></div><div id="loader"><div id="wait"><img src="'.drupal_get_path('module','fossee_stats').'/assets/images/loader.svg" alt="Loading ..."></div><div id="load_map" >'.FossBase::get_map_data(NULL,0,0).'</div></div></div>'
     );
     $form['#attached']['library'][] = 'fossee_stats/mapdata' ;
    // drupal_add_css(drupal_get_path('module','fossee_stats')."/css/map.css");
     //drupal_add_js(drupal_get_path('module','fossee_stats'). "/js/map.js");
     return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  private function _ajax_example_get_first_dropdown_options() {
    $connection = \Drupal::database();
    $query = $connection->select('foss_type','f');
    $query->fields('f', array(
        'id','foss_name'
    ));
    $result = $query->execute();
    $options = array();
    $options[''] = "--------------";
    while ($foss_detail = $result->fetchObject()) {
        $options[$foss_detail->id] = $foss_detail->foss_name;
    }
    return $options;
  }

}




 ?>
