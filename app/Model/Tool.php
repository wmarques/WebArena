<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppModel', 'Model');

class Tool extends AppModel {

     public $displayField = 'name';

    public $belongsTo = array(

        'Fighter' => array(

            'className' => 'Fighter',

            'foreignKey' => 'fighter_id',

        ),

    );
    
    //Vérifier si un fighter a déjà un équipement du type
    function pickTool($data, $toolId){
        $data2 = $this->findById($toolId);
     
        $data2['Tool']['fighter_id']=$data['Fighter']['id'];
        $this->save($data2);
        if($data2['Tool']['type']=='Armure'){
             $data['Fighter']['skill_health']= $data['Fighter']['skill_health']+$data2['Tool']['bonus'];
             $data['Fighter']['current_health']=$data['Fighter']['current_health']+$data2['Tool']['bonus'];
        }
        if($data2['Tool']['type']=='Epee')
            $data['Fighter']['skill_strenght']= $data['Fighter']['skill_strength']+$data2['Tool']['bonus'];
        if($data2['Tool']['type']=='Lunette')
            $data['Fighter']['skill_sight']= $data['Fighter']['skill_sight']+$data2['Tool']['bonus'];
        $this->Fighter->save($data);
    }
    
    function getTool($idTool){
        return $this->findById($idTool);
    }
      

    function initPosition($data2){
        $this->query("Delete from tools");
        $array = array();
       
       for ($i=0; $i<10; $i++){
           for ($j=0; $j<15;$j++){
               $array[$i][$j] = true;
           }
       }
       
       //On marque indispo les cases occupées par l'es colonnes'environnement
        foreach($data2 as $key)
               $array[$key['Surrounding']['coordinate_y']][$key['Surrounding']['coordinate_x']]= false;
        
            
        //20 objets
        for ($i=0; $i<25; $i++){
           do{
               $fin = false;
               $y = rand(0 , 9 );
               $x = rand(0,14);
               
               if($array[$y][$x]==true)
                   $fin=true;
               
           }while(!$fin);
           
           
           //On sauvegarde 
           $data=$this->create();
           $data['Tool']['coordinate_x'] = $x;
           $data['Tool']['coordinate_y'] = $y;
        
           $a = rand(0,3);
           switch($a){
               case 0: $data['Tool']['type'] = 'Armure'; break;
               case 1: $data['Tool']['type'] = 'Epee'; break;
               case 2: $data['Tool']['type'] = 'Lunettes'; break;
               case 3 : $data['Tool']['type'] = 'Armure'; break;
           }
           $data['Tool']['bonus'] = rand(1,3);
           
           
           $this->save($data);
           
           $array[$y][$x] = false;
       }  
        
        
    }
    
    function getFreeTool(){
        return $this->find('all', array('conditions' => array('fighter_id'=>NULL)));
    }
    
    
}