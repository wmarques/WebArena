<?php

App::uses('AppModel', 'Model');

class Surrounding extends AppModel {

        public $displayField = 'name';
    public $uses = array('Fighter');
   //Initialisation du plateau
   function beginGame($tab){
       //On enlève toute trace du plateau précédent et on crée colonne, piege, monstre
    
       $this->query("Delete from surroundings");
       $tab = $this->createColonne($tab);
       $this->createPiegeMonster($tab); 
   } 
   
   
   //Créer les colonnes
   function createColonne($array){
       
       
       //1 colonne pour 10 cases or 150 cases donc 15 colonnes
       for ($i=0; $i<Configure::read('nbColonne'); $i++){
           do{
               //PLacement aléatoire sur la map
               $fin = false;
               $y = rand(0 , Configure::read('Longueur_y')-1 );
               $x = rand(0, Configure::read('Largeur_x')-1);
               
               //Si l'espace est libre, on sort de la boucle sinon on recommence
               if($array[$x][$y]==true)
                   $fin=true;
               
           }while(!$fin);
  
           //On crée la colonne dans la bdd
           $data=$this->create();
           $data['Surrounding']['coordinate_x'] = $x;
           $data['Surrounding']['coordinate_y'] = $y;
           $data['Surrounding']['type'] = "Column";
           $this->save($data);
           
           //On indique sur le tableau des cases libres, que les cases alentours
           //et celle sélectionnée ne sont plus libres. Pas 2 colonnes à côté
           $array[$x][$y]=false;
           $array[$x-1][$y]=false;
           $array[$x-1][$y-1]=false;
           $array[$x][$y-1]=false;
           $array[$x+1][$y]=false;
           $array[$x+1][$y+1]=false;
           $array[$x][$y+1]=false;
           $array[$x-1][$y+1]=false;
           $array[$x+1][$y-1]=false;
           
       }

       return $array;
       
   }

   //Même principe que les colonnes
   function createPiegeMonster($array){

       
            //15 pièges + un monstre
        for ($i=0; $i<Configure::read('nbPiege'); $i++){
           do{
               $fin = false;
               $y = rand(0 , Configure::read('Longueur_y')-1);
               $x = rand(0,Configure::read('Largeur_x')-1);
               
               if($array[$x][$y]==true)
                   $fin=true;
               
           }while(!$fin);
           
           
           //On sauvegarde 
           $data=$this->create();
           $data['Surrounding']['coordinate_x'] = $x;
           $data['Surrounding']['coordinate_y'] = $y;
           if ($i==15)
               $data['Surrounding']['type'] = "Monster";
           else
               $data['Surrounding']['type'] = "Trap";
           
           $this->save($data);
           
           $array[$x][$y] = false;
       }    
       
   }
   
   function nearFromPiege($data2){
       $data = $this->find('all', array('conditions'=>array('type' => "Trap")));
       $x = $data2['Fighter']['coordinate_x'];
       $y = $data2['Fighter']['coordinate_y'];
       foreach ($data as $key){
           if($key['Surrounding']['coordinate_x']==$x+1 && $key['Surrounding']['coordinate_y']==$y)
               return true;
           if($key['Surrounding']['coordinate_x']==$x-1 && $key['Surrounding']['coordinate_y']==$y)
               return true;
           if($key['Surrounding']['coordinate_x']==$x && $key['Surrounding']['coordinate_y']==$y+1)
               return true;
           if($key['Surrounding']['coordinate_x']==$x && $key['Surrounding']['coordinate_y']==$y-1)
               return true;
       }
       return false;
   }
   
   function fighterOnPiege($data2){
       $data = $this->find('all', array('conditions'=>array('type' => "Trap")));
       $x = $data2['Fighter']['coordinate_x'];
       $y = $data2['Fighter']['coordinate_y'];
       foreach ($data as $key){
           if($key['Surrounding']['coordinate_x']==$x && $key['Surrounding']['coordinate_y']==$y)
               return true;
       }
       return false;
   }
   
   function nearFromMonster($data2){
       $data = $this->find('all', array('conditions'=>array('type' => "Monster")));
       $x = $data2['Fighter']['coordinate_x'];
       $y = $data2['Fighter']['coordinate_y'];
       foreach ($data as $key){
           if($key['Surrounding']['coordinate_x']==$x+1 && $key['Surrounding']['coordinate_y']==$y)
               return true;
           if($key['Surrounding']['coordinate_x']==$x-1 && $key['Surrounding']['coordinate_y']==$y)
               return true;
           if($key['Surrounding']['coordinate_x']==$x && $key['Surrounding']['coordinate_y']==$y+1)
               return true;
           if($key['Surrounding']['coordinate_x']==$x && $key['Surrounding']['coordinate_y']==$y-1)
               return true;
       }
       return false;
   }
   
   function fighterOnMonster($data2){
       $data = $this->find('all', array('conditions'=>array('type' => "Monster")));
       $x = $data2['Fighter']['coordinate_x'];
       $y = $data2['Fighter']['coordinate_y'];
       foreach ($data as $key){
           if($key['Surrounding']['coordinate_x']==$x && $key['Surrounding']['coordinate_y']==$y)
               return true;
       }
       return false;
   }
    
   function getAllSurrounding(){
       return $this->find('all');
   }
   
   function getSurroundingSight($data){
       $x = $data['Fighter']['coordinate_x'];
       $y = $data['Fighter']['coordinate_y'];
       
       $data2 = $this->find('all');
       $nb = 0;
       $tab = array();
       foreach($data2 as $key){
           $sight_x = $key['Surrounding']['coordinate_x']-$x;
           if ($sight_x<0)
               $sight_x = $sight_x*(-1);
           $sight_y = $key['Surrounding']['coordinate_y']-$y;
           if ($sight_y<0)
               $sight_y = $sight_y*(-1);
           $total = $sight_x+$sight_y;
           if ($total<=$data['Fighter']['skill_sight'] && $key['Surrounding']['type']!='Monster' ){
               
               $key['Distance']=$total;
               $tab[$nb]=$key;
               $nb++;
           }
           if ($key['Surrounding']['type']=='Monster' && $total<=1 && $data['Fighter']['skill_sight']>0){
               $tab[$nb]=$key;
               $nb++;
           }
               
               
       }
       
     
       return $tab;
       
   }
    

}