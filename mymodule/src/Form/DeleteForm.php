<?php
namespace Drupal\mymodule\Form;

use Drupal\Core\Form\FormStateInterFace;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Messenger;

class DeleteForm extends ConfirmFormBase{
  public function getFormid(){
    return 'delete_form';
  }

  public $cid;

  public function getQuestion(){
    return t('Delete Record ?');
  }

  public function getCancelUrl(){
    return new Url('mymodule.mymodule_controller_listing');
  }

  public function getDescription(){
    return t('Are you sure to delete Record ?');
  }

  public function getConfirmText(){
    return t('Delete it ?');
  }

  public function getCancelText(){
    return t('Cancel');
  }

  public function buildform(array $form, FormStateInterFace $form_state, $cid = NULL){
    $this->id = $cid;
    return parent::buildform($form,$form_state);
  }

  public function validateForm(array &$form, FormStateInterFace $form_state){
    parent::validateForm($form,$form_state);
  }

  public function submitForm(array &$form, FormStateInterFace $form_state){
    $query = \Drupal::database();
    $query->delete('mymodule')->condition('id',$this->id)->execute();
    $this->messenger()->addMessage('Successfully deleted record');

    $form_state->setRedirect('mymodule.mymodule_controller_listing');
  }
}
