<?php
/*
    echo $this->Form->create('Fightermove');
    echo $this->Form->input('direction',array('options' => array('north'=>'north','east'=>'east','south'=>'south','west'=>'west'), 'default' => 'east'));
    echo $this->Form->end('Move');

    echo $this->Form->create('FighterAttack');
    echo $this->Form->input('direction',array('options' => array('north'=>'north','east'=>'east','south'=>'south','west'=>'west'), 'default' => 'east'));
    echo $this->Form->end('Attack');
*/
?>

<?php

	echo $this->Form->create('UploadPicture');
	echo $this->Form->input('file',array('type'=>'file'));
	echo $this->Form->end('Upload');
?>