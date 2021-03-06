<?php

App::uses('AppModel', 'Model');

class Fighter extends AppModel {

    public $displayField = 'name';
    public $uses = array('Surrounding', 'Event');
    public $belongsTo = array(
        'Player' => array(
            'className' => 'Player',
            'foreignKey' => 'player_id',
        ),
        'Guild'
    );

    function add($playerId, $name) {

        if ($this->find('count', array("conditions" => array('Fighter.name' => $name))) == 0) {
            $data = array(
                'name' => $name,
                'player_id' => $playerId,
                'level' => 1,
                'xp' => 0,
                'skill_sight' => 0,
                'skill_strength' => 1,
                'skill_health' => 3,
                'current_health' => 3,
                'next_action_time' => date("Y-m-d H:i:s")
            );

            $pos = $this->InitPosition();

            $data['coordinate_x'] = $pos[0];
            $data['coordinate_y'] = $pos[1];
            // prepare the model for adding a new entry
            $this->create();
            // save the data
            $this->save($data);

            $current = $this->find('first', array('conditions' => array('Fighter.name' => $data['name'], 'player_id' => $data['player_id'])));
            copy($_SERVER['DOCUMENT_ROOT'] . "/WebArena/app/webroot/img/template.jpg", $_SERVER['DOCUMENT_ROOT'] . "/WebArena/app/webroot/img/" . $current['Fighter']['id'] . ".jpg");

            return true;
        } else
            return false;
    }

    function getSeen($id) {
        $user = $this->findById($id);
        $x = $user['Fighter']['coordinate_x'];
        $y = $user['Fighter']['coordinate_y'];

        $data2 = $this->find('all');
        $nb = 0;
        $tab = array();
        foreach ($data2 as $key) {
            if ($key['Fighter']['current_health'] > 0) {
                $sight_x = $key['Fighter']['coordinate_x'] - $x;
                if ($sight_x < 0)
                    $sight_x = $sight_x * (-1);
                $sight_y = $key['Fighter']['coordinate_y'] - $y;
                if ($sight_y < 0)
                    $sight_y = $sight_y * (-1);
                $total = $sight_x + $sight_y;
                if ($total <= $user['Fighter']['skill_sight']) {
                    echo $total . " ";
                    $key['Distance'] = $total;
                    $tab[$nb] = $key;
                    $nb++;
                }
            }
        }

        // $tab[$nb]=$user;
        //array_push($tab,$user);
        return $tab;
    }

    function checkPosition($coordonnee_x, $coordonnee_y, $fighterId) {
        $a = false;
        $tab = $this->query("Select coordinate_x, coordinate_y from fighters where id <> $fighterId and current_health>0");

        foreach ($tab as $key)
            foreach ($key as $value) {
                if ($value['coordinate_y'] == $coordonnee_y && $value['coordinate_x'] == $coordonnee_x)
                    $a = true;
            }


        $tab = $this->query("Select coordinate_x, coordinate_y from surroundings where type='Column'");

        foreach ($tab as $key)
            foreach ($key as $value) {
                pr($value);
                if ($value['coordinate_y'] == $coordonnee_y &&
                        $value['coordinate_x'] == $coordonnee_x)
                    $a = true;
            }


        return $a;
    }

//changement de niveau
    function upgrade($fighterId, $upskill) {
        $datas = $this->findById($fighterId);

        if ($datas['Fighter']['xp'] / ($datas['Fighter']['level'] * 4) >= 1 && $datas['Fighter']['current_health']>0) {
            $datas['Fighter']['level'] ++;
            if ($upskill == 'sight') {

                $datas['Fighter']['skill_sight'] = $datas['Fighter']['skill_sight'] + 1;
            } elseif ($upskill == 'strength') {
                $datas['Fighter']['skill_strength'] = $datas['Fighter']['skill_strength'] + 1;
            } elseif ($upskill == 'health') {
                $datas['Fighter']['skill_health'] = $datas['Fighter']['skill_health'] + 3;
                $datas['Fighter']['current_health'] = $datas['Fighter']['current_health'] + 3;
            }


            return $this->save($datas);
        } else
            return false;
    }

    //Déplacement du fighter

    function doMove($fighterId, $direction) {
        // récupérer la position et fixer l'id de travail
        $datas = $this->read(null, $fighterId);

        if ($datas['Fighter']['current_health'] > 0) {
            if ($direction == 'east') {

                if ($datas['Fighter']['coordinate_x'] + 1 < Configure::read('Largeur_x') && !$this->checkPosition($datas['Fighter']['coordinate_x'] + 1, $datas['Fighter']['coordinate_y'], $fighterId)) {
                    $this->set('coordinate_x', $datas['Fighter']['coordinate_x'] + 1);
                    //$Even->MoveEvent($fighterId,$direction);
                } else
                    return false;
            }
            elseif ($direction == 'west') {
                if ($datas['Fighter']['coordinate_x'] - 1 >= 0 && !$this->checkPosition($datas['Fighter']['coordinate_x'] - 1, $datas['Fighter']['coordinate_y'], $fighterId))
                    $this->set('coordinate_x', $datas['Fighter']['coordinate_x'] - 1);
                else
                    return false;
                //$Even->MoveEvent($fighterId,$direction);
            }
            elseif ($direction == 'north') {

                if ($datas['Fighter']['coordinate_y'] + 1 < Configure::read('Longueur_y') && !$this->checkPosition($datas['Fighter']['coordinate_x'], $datas['Fighter']['coordinate_y'] + 1, $fighterId))
                    $this->set('coordinate_y', $datas['Fighter']['coordinate_y'] + 1);
                else
                    return false;
                //$Even->MoveEvent($fighterId,$direction);
            }
            elseif ($direction == 'south') {

                if ($datas['Fighter']['coordinate_y'] - 1 >= 0 && !$this->checkPosition($datas['Fighter']['coordinate_x'], $datas['Fighter']['coordinate_y'] - 1, $fighterId))
                    $this->set('coordinate_y', $datas['Fighter']['coordinate_y'] - 1);
                //$Even->MoveEvent($fighterId,$direction);
                else
                    return false;
            }


            // sauver la modif
            $this->save();


            return 1;
        }
        return 2;
    }

    //Obtenir l'ID du mec attaqué
    function getIdDef($coordonnee_x, $coordonnee_y, $fighterID) {
        //Obtenir les autres fighter susceptibles d'être attaqué
        $tab = $this->query("Select * from fighters where id<> $fighterID and current_health>0");

        //Vérifier si l'un des fighter est attaqué en fonction de sa position et retourner l'ID du mec attaqué
        foreach ($tab as $key)
            foreach ($key as $value) {
                if ($value['coordinate_y'] == $coordonnee_y &&
                        $value['coordinate_x'] == $coordonnee_x)
                    return $value['id'];
            }

        //Obtenir les infos sur le monstre
        $tab = $this->query("Select * from surroundings where type='Monster'");

        //Renvoyer -1 si le truc attaqué correspond au monstre
        foreach ($tab as $key)
            foreach ($key as $value) {
                if ($value['coordinate_y'] == $coordonnee_y &&
                        $value['coordinate_x'] == $coordonnee_x)
                    return -1;
            }


        return false;
    }

    function createAvatar($fighterId, $file) {
        $datas = $this->read(null, $fighterId);
        move_uploaded_file($file, "/var/www/html/WebArena/app/Avatar/$fighterId.jpg");
    }

    function updateAvatar($fighterId, $file) {
        $data = $this->read(null, $fighterId);
        //unlink("/var/www/html/WebArena/app/Avatar/$fighterId.jpg");
        $output = $_SERVER['DOCUMENT_ROOT'] . "/WebArena/app/webroot/img/" . $fighterId . ".jpg";
        unlink("$output");
        if(move_uploaded_file($file, $output))
                return true;
        else
                return false;
    }

    function getBonusTool($id, $type, $bonus){
        $tab = $this->findById($id);
        
        $tab['Fighter'][$type] = $tab['Fighter'][$type]+$bonus;
        if($type == 'skill_health'){
             $tab['Fighter']['current_health'] = $tab['Fighter']['current_health']+$bonus;
        }
        
        $this->save($tab);
    }
    
    
    //Fonction faire l'attaque
    function doAttack($fighterId, $direction) {

        //Lire les info sur le fighter 
        $datas = $this->read(null, $fighterId);

        //Obtenir l'ID du mec qui défend
        if ($direction == 'east')
            $defenderId = $this->getIdDef($datas['Fighter']['coordinate_x'] + 1, $datas['Fighter']['coordinate_y'], $fighterId);
        elseif ($direction == 'west')
            $defenderId = $this->getIdDef($datas['Fighter']['coordinate_x'] - 1, $datas['Fighter']['coordinate_y'], $fighterId);
        elseif ($direction == 'north')
            $defenderId = $this->getIdDef($datas['Fighter']['coordinate_x'], $datas['Fighter']['coordinate_y'] + 1, $fighterId);
        elseif ($direction == 'south')
            $defenderId = $this->getIdDef($datas['Fighter']['coordinate_x'], $datas['Fighter']['coordinate_y'] - 1, $fighterId);

        //Si le mec qui défend est en fait le monstre on supprime le monstre et on augmente l'xp
        if ($defenderId == -1) {
            $this->query("Delete from surrounding where type='Monster'");
            $this->set('xp', $datas['Fighter']['xp']+1);
            //On sauvegarde
            $this->save();
            return array(1, "Monster");
        }

        //Si on a pas détecté de défender, l'attaque est dans le vide 
        if (!$defenderId)
            return array(2, "Nobody");
        else {

            //Lire les info sur le défenseur
            $datas2 = $this->read(null, $defenderId);

            if ($datas['Fighter']['guild_id'] != NULL)
                $attackBonus = $this->getAttackBonus($datas['Fighter']['guild_id'], $datas2, $fighterId);

            echo "Attack $attackBonus";
            //Aléatoire pour l'attaque
            $a = rand(1, 20) + $attackBonus;

            //Si l'attaque réussie
            if ($a > (10 + $datas2['Fighter']['level'] - $datas['Fighter']['level'])) {
                $death = false;
                $xp = 1;
                //On met a jour la senté du mec attaqué, on sauve, on augmente l'xp de l'attaquant, on sauve
                $this->set('current_health', $datas2['Fighter']['current_health'] - $datas['Fighter']['skill_strength']);
                echo "Current_health" . $datas2['Fighter']['current_health'];
                //@todo : Retirer joueur du plateau
                if ($datas2['Fighter']['current_health'] - $datas['Fighter']['skill_strength'] <= 0) {
                    $this->set('current_health', 0);
                    $xp = $xp + $datas2['Fighter']['level'];
                    $death = true;
                }
                $idDef = $datas2['Fighter']['id'];
                //sauver modif
                $this->save();
                $datas = $this->read(null, $fighterId);
                $this->set('xp', $datas['Fighter']['xp'] + $xp);
                $this->save();
                return array(3, $idDef, $death);
            } else {
                return array(3, $defenderId, false);
            }
        }
    }

    function getAttackBonus($guild_id, $defender, $idAttack) {
        $data = $this->find('all', array('conditions' => array('guild_id' => $guild_id, 'Fighter.id !=' => $idAttack)));
        $bonus = 0;
        foreach ($data as $key) {
            if (($key['Fighter']['coordinate_x'] == $defender['Fighter']['coordinate_x'] + 1 && $key['Fighter']['coordinate_y'] == $defender['Fighter']['coordinate_y']) || ($key['Fighter']['coordinate_x'] == $defender['Fighter']['coordinate_x'] - 1 && $key['Fighter']['coordinate_y'] == $defender['Fighter']['coordinate_y']) || ($key['Fighter']['coordinate_x'] == $defender['Fighter']['coordinate_x'] && $key['Fighter']['coordinate_y'] + 1 == $defender['Fighter']['coordinate_y']) || ($key['Fighter']['coordinate_x'] == $defender['Fighter']['coordinate_x'] && $key['Fighter']['coordinate_y'] - 1 == $defender['Fighter']['coordinate_y']))
                $bonus++;
        }
        return $bonus;
    }

    function InitPosition() {

        $array = array();

        //Tableau pour éviter qu'un mec soit bloqué par tous les poteaux autours de lui
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 15; $j++) {
                $array[$i][$j] = true;
            }
        }

        $tab = $this->query("Select coordinate_x, coordinate_y from surroundings");

        //On marque indispo les cases occupées par les colonnes
        foreach ($tab as $key)
            foreach ($key as $value) {
                $array[$value['coordinate_y']][$value['coordinate_x']] = false;
            }

        $data = $this->find('all');



        foreach ($data as $key)
        // foreach($key as $value){
            if ($key['Fighter']['current_health'] > 0)
                $array[$key['Fighter']['coordinate_y']][$key['Fighter']['coordinate_x']] = false;
        // }

        $fin = false;
        $pos = array();
        do {
            $pos[0] = rand(0, Configure::read('Largeur_x') - 1);
            $pos[1] = rand(0, Configure::read('Longueur_y') - 1);

            if ($array[$pos[1]][$pos[0]] == true)
                $fin = true;
        }while (!$fin);

        return $pos;
    }

    //Function to join a guild by its name
    function joinGuild($idFighter, $idGuild) {
        $data = $this->findById($idFighter);
        $data['Fighter']['guild_id'] = $idGuild;
        return $this->save($data);
    }

    //Function revive for a dead figther
    function reviveFighter($idFighter) {
        $data = $this->findById($idFighter);

        if ($data['Fighter']['current_health'] == 0) {
            $data['Fighter']['level'] = 1;
            $data['Fighter']['xp'] = 0;
            $data['Fighter']['skill_sight'] = 0;
            $data['Fighter']['skill_strenght'] = 1;
            $data['Fighter']['skill_health'] = 3;
            $data['Fighter']['current_health'] = 3;
            $tab = $this->InitPosition();
            $data['Fighter']['coordinate_x'] = $tab[0];
            $data['Fighter']['coordinate_y'] = $tab[1];
            $data['Fighter']['next_action_time'] = "0000-00-00 00:00:00";
            return $this->save($data);
        } else
            return false;
    }

    function getFighterview($idFighter) {
        return $this->findById($idFighter);
    }

    function getFighterId($name, $player) {
        $data = $this->find('first', array('conditions' => array('Fighter.name' => $name, 'player_id' => $player)));
        if(!empty($data))
            return $data['Fighter']['id'];
        else
            return false;
    }

    function getAllFighterviewPlayer($idPlayer) {
        return $this->find('all', array('conditions' => array('player_id like' => $idPlayer)));
    }

    function getAllFighterview() {
        return $this->find('all');
    }

    function deathFromSurrounding($idFighter, $bool) {
        if ($bool == true) {
            $data = $this->findById($idFighter);
            $data['Fighter']['current_health'] = 0;
            $this->save($data);
            return true;
        }
        else
            return false;
    }

    function getFighterUser($idUser) {
        return $this->find('all', array('conditions' => array('player_id' => $idUser)));
    }

    function Action($nb, $fighterId) {
        $data = $this->findById($fighterId);
        
        
        echo "tttt ".strtotime(date("Y-m-d H:i:s"))." ". (strtotime($data['Fighter']['next_action_time']) + 1  * Configure::read('Delai'));
        if (strtotime(date("Y-m-d H:i:s")) <= strtotime($data['Fighter']['next_action_time']))
            return -1;
        else {


            if (strtotime(date("Y-m-d H:i:s")) > (strtotime($data['Fighter']['next_action_time']) + 3 * Configure::read('Delai'))){
                 $nb--;
                 echo "++++++";
            }
               
            if (strtotime(date("Y-m-d H:i:s")) > (strtotime($data['Fighter']['next_action_time']) + 2 * Configure::read('Delai')))                
            {
                echo "-------";
                $nb--;
            }
            if (strtotime(date("Y-m-d H:i:s")) > (strtotime($data['Fighter']['next_action_time']) + 1  * Configure::read('Delai')))                
            {
                echo "ggg ".strtotime(date("Y-m-d H:i:s"))." ". (strtotime($data['Fighter']['next_action_time']) + 1  * Configure::read('Delai'));
                $nb--;
                $data['Fighter']['next_action_time'] = date("Y-m-d H:i:s");
            }
            
            if ($nb < 0)
                $nb = 0;

            $nb++;
            if ($nb == Configure::read('nbAction')) {
                $date = (strtotime(date("Y-m-d H:i:s"))+Configure::read('Delai'));
                echo "data data data " . $date;
                $data['Fighter']['next_action_time'] = date("Y-m-d H:i:s", $date);
                $nb = Configure::read('nbAction')-1;
            }
            $this->save($data);
            return $nb;
        }
    }

    function getFighterSight($data) {
        $x = $data['Fighter']['coordinate_x'];
        $y = $data['Fighter']['coordinate_y'];

        $data2 = $this->find('all');
        $nb = 0;
        $tab = array();
        foreach ($data2 as $key) {
            $sight_x = $key['Fighter']['coordinate_x'] - $x;

            if ($sight_x < 0)
                $sight_x = $sight_x * (-1);
            $sight_y = $key['Fighter']['coordinate_y'] - $y;
            if ($sight_y < 0)
                $sight_y = $sight_y * (-1);
            echo " sighty " . $sight_y . " sightx " . $sight_x;
            $total = $sight_x + $sight_y;
            echo " total " . $total;
            if ($total <= $data['Fighter']['skill_sight'] && $key['Fighter']['current_health'] > 0 && $data['Fighter']['id'] != $key['Fighter']['id']) {
                $key['Distance'] = $total;
                $tab[$nb] = $key;
                $nb++;
            }
        }

        return $tab;
    }

    function getNbFighterFromPlayer($idPlayer) {
        $nb = $this->find('count', array('conditions' => array('player_id' => $idPlayer)));
        return $nb;
    }

    function getRankFighter() {
        return $this->find('all', array('order' => array('level' => 'desc')));
    }

    function getNbGuild($idGuild) {
        echo $idGuild;
        return $this->find('count', array('conditions' => array('guild_id' => $idGuild)));
    }

    function getIdGuild($idFighter) {
        $data = $this->findById($idFighter);
        return $data['Fighter']['guild_id'];
    }

    function getFighterForMessage($idFighter) {
        return $this->find('all', array('conditions' => array('Fighter.id !=' => $idFighter), 'fields' => array('name')));
    }

    function getFighterByName($name) {
        return $this->find('first', array('conditions' => array('Fighter.name' => $name)));
    }
    
    function getFreePosition(){
        $tab=array();
        for($i=0; $i<Configure::read('Largeur_x'); $i++){
            for ($j=0; $j<Configure::read('Longueur_y'); $j++){
                $tab[$i][$j]=true;
            }
        }
        
        $data = $this->find('all', array('conditions'=>array('current_health <>' => 0)));
        
        foreach($data as $key){
            $tab[$key['Fighter']['coordinate_x']][$key['Fighter']['coordinate_y']] = false;
        }
        
        return $tab;
        
    }

}
