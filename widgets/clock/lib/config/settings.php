<?php

return array(
    'type' => array(
	    'title' => /*_w*/('Type'),
	    'control_type' => waHtmlControl::SELECT,
	    'options' => array(
		    array(
			    'value'       => 'round',
			    'title'       => /*_w*/('Round'),
		    ),
		    array(
			    'value'       => 'electronic',
			    'title'       => /*_w*/('Electronic'),
		    ),
	    )
    ),

    'format' => array(
        'title' => /*_w*/('Electronic format'),
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array(
                'value'       => '24',
                'title'       => /*_w*/('Electronic 24 hours'),
            ),
            array(
                'value'       => '12',
                'title'       => /*_w*/('Electronic AM/PM'),
            ),
        )
    ),
);