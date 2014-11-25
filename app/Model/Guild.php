<?php

App::uses('AppModel', 'Model');

class Guild extends AppModel {

   //Function to create a new guild
    function CreateGuild($name){
        
        $nb = $this->find('count', array('conditions' => array('name =' => $name)));
        echo $nb;
        if ($nb != 0)
            return false;
        
        $data=$this->create();
        $data['Guild']['name']=$name;
        return $this->save($data);
    }
    
    
    //function to get all the names of the guild
    function getAllGuild(){
        $data = $this->find('all', array('fields' => array('name')));
    
        pr($data);
        return $data;
    }
    
    function getIdGuild($name){
        $data = $this->find('first', array('fields' => array('id'), 'conditions' => array('name'=>$name)));
        echo $data['Guild']['id'];
        return $data['Guild']['id'];
    }
    
    
}