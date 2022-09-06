<?php
namespace Drupal\mymodule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterFace;
use Drupal\Core\DataBase\DataBase;
use Drupal\Core\Url;
use Drupal\Core\Messenger;
use Drupal\Core\Link;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;

class MymoduleForm extends FormBase{

  public function getFormid(){
    return 'mymodule_form';
  }

  public function buildform(array $form, FormStateInterFace $form_state){
    $conn = Database::getConnection();
    $record = [];
    if(isset($_GET['id'])){
      $query = $conn->select('mymodule','m')->condition('id',$_GET['id'])->fields('m');
      $record = $query->execute()->fetchAssoc();
    }
    $form['title'] = ['#type'=>'textfield','#title'=>t('Title'),'#required'=>TRUE,'#default_value'=>(
      isset($record['title'])&&$_GET['id'])? $record['title']:'',];

    $form['description'] = ['#type'=>'textfield','#title'=>t('Description'),'#required'=>TRUE,'#default_value'=>(
      isset($record['description'])&&$_GET['id'])? $record['description']:'',];

    $form['start_date'] = ['#type'=>'date','#title'=>t('Start Date'),'#required'=>TRUE,'#default_value'=>(
      isset($record['start_date'])&&$_GET['id'])? $record['start_date']:'',];

    $form['end_date'] = ['#type'=>'date','#title'=>t('End Date'),'#required'=>TRUE,'#default_value'=>(
      isset($record['end_date'])&&$_GET['id'])? $record['end_date']:'',];

    $form['category'] = ['#type'=>'textfield','#title'=>t('Category'),'#required'=>TRUE,'#default_value'=>(
      isset($record['category'])&&$_GET['id'])? $record['category']:'',];


    $country_query = $conn->select('country','c');
    $country_query->fields('c',array('country_id','country_name'));
    $country_query->orderBy('country_name','ASC');
    $country_records = $country_query->execute()->fetchAllKeyed();
    $country_options = array();

    foreach($country_records as $key => $country_results){
      $country_options[$key] = $country_results;
    }

    $form['country'] = ['#type'=>'select','#title'=>t('Country'),'#required'=>TRUE,'#options'=>
    $country_options , '#ajax'=>['callback'=>[$this,'getStates'],'event'=>'change','method'=>'html','wrapper'=>
    'states-to-update','progress'=>['type' => 'throbber','message'=> NULL,],],'#default_value'=>(isset($record['country_id'])&& $_GET['id'])?
    $record['country_id']:'',];

    $states_options = [];

    $form['states'] = array('#type'=>'select','#title'=>t('States'),'#required'=>TRUE,'#options'=>
    $states_options ,'#attributes'=>array('id'=>array('states-to-update')),'#validated'=>TRUE,'#default_value'=>(
    isset($record['state_id'])&& $_GET['id'])?$record['state_id']:'',);


    $form['actions'] = ['#type'=>'action',];

    $form['actions']['submit'] = ['#type'=>'submit','#value'=>t('Save'),];

    $form['actions']['reset'] = ['#type'=>'button','#value'=>t('Reset'),'#attributes'=>['onclick'=>'
    this.form.reset; return false;',],];

    $link = Url::fromUserInput('/mymodule/');

    $form['actions']['cancel'] = ['#markup'=>Link::fromTextAndUrl(t('Back to page'),$link,['
    attributes'=>['class'=>'button']])->toString(),];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterFace $form_state){
    $start_date = $form_state->getValue('start_date');
    $end_date = $form_state->getValue('end_date');
    if($start_date > $end_date){
      $form_state->setErrorByName('name',$this->t('The end date must not exceed the start date'));
    }

    parent::validateForm($form,$form_state);
  }

  public function submitForm(array &$form, FormStateInterFace $form_state){
    $field = $form_state->getValues();
    print_r($field);die;

    $title = $field['title'];
    $description = $field['description'];
    $start_date = $field['start_date'];
    $end_date = $field['end_date'];
    $category = $field['category'];
    $country = $field['country'];
    $states = $field['states'];

    if(isset($_GET['id'])){
      $field = ['title' => $title,'description'=>$description,'start_date'=>$start_date,'end_date'=>$end_date,
      'category'=>$category,'country'=>$country,'states'=>$states,];
      $query = \Drupal::database();
      $query->update('mymodule')->fields($field)->condition('id',$_GET['id'])->execute();
      $this->messenger()->addMessage('Successfully Updated records');

    }else{
      $field = ['title' => $title,'description'=>$description,'start_date'=>$start_date,'end_date'=>$end_date,
      'category'=>$category,'country'=>$country,'states'=>$states,];
      $query = \Drupal::database();
      $query->insert('mymodule')->fields($field)->execute();
      $this->messenger()->addMessage('Successfully Saved records');
    }
    $form_state->setRedirect('mymodule.mymodule_controller_listing');
  }

  public function getStates(array &$element, FormStateInterFace $form_state){
    $triggeringElement = $form_state->getTriggeringElement();
    $value = $triggeringElement['#value'];
    $states = $this->getStatesByCountry($value);
    $wrapper_id = $triggeringElement["#ajax"]["wrapper"];
    $renderedField = '';

    foreach($states as $key => $value){
      $renderedField .="<option value='".$key."'>".$value."</option>";
    }

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#'.$wrapper_id,$renderedField));
    return $response;
  }

  public function getStatesByCountry($default_states){
    $states_record = [];
    $conn = Database::getConnection();
    $states_query = $conn->select('states','s');
    $states_query->fields('s',array('state_id','state_name'));
    $states_query->Condition('country_id',$default_states,'=');
    $states_record = $states_query->execute()->fetchAllKeyed();
    $states_options = array();

    foreach($states_record as $key => $states_results){
      $states_options[$key] = $states_results;
    }
    return $states_options;
  }
}
