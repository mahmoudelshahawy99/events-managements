<?php
namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DataBase\DataBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Messenger;

class MymoduleController extends ControllerBase{
  public function listing(){
    $header_table = ['title'=>t('Title'),'description'=>t('Description'),'start_date'=>t('Start Date'),'end_date'=>t('End Date'),
    'category'=>t('Category'),'country'=>t('Country'),'states'=>t('State'),'opt'=>t('Operation'),'opt1'=>t('Operation'),];

    $row = [];
    $conn = Database::getConnection();
    $query = $conn->select('mymodule','m');
    $query->fields('m',['id','title','description','start_date','end_date','category','country','states']);
    $query->orderBy('id','DESC')->range(0, 5);
    $result = $query->execute()->fetchAll();

    foreach($result as $value){
      $delete = Url::fromUserInput('/mymodule/form/delete/'.$value->id);
      $edit = Url::fromUserInput('/mymodule/form/data?id='.$value->id);

      $country_query = $conn->select('country','c');
      $country_query->fields('c',array('country_name'));
      $country_query->Condition('country_id',$value->country,'=');
      $country_record = $country_query->execute()->fetchAssoc();

      $states_query = $conn->select('states','s');
      $states_query->fields('s',array('state_name'));
      $states_query->Condition('state_id',$value->states,'=');
      $states_record = $states_query->execute()->fetchAssoc();

      $row[] = ['title' => $value->title, 'description' => $value->description,'start_date' => $value->start_date,'end_date' => $value->end_date,
       'category' => $value->category,'country' => $country_record['country_name'],'states' => $states_record['state_name'],
       'opt'=>Link::fromTextAndUrl('Edit',$edit)->toString(),'opt1'=>Link::fromTextAndUrl('Delete',$delete)->toString(),];

      $add = Url::fromUserInput('/mymodule/form/data');
      $text = 'Add Event';
      $data['table'] = ['#type'=>'table','#header'=>$header_table,'#rows'=>$row,'#empty'=>t('
      No Record found'),'#caption'=>Link::fromTextAndUrl($text,$add)->toString(),];

      $this->messenger()->addMessage('record listed');

    }
    return $data;
  }
}
