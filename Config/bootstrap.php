<?php
        Croogo::hookHelper('*', 'Tvocabulary.Tvocabulary');
        Croogo::hookComponent('*', 'Tvocabulary.Tvocabulary');

        CroogoNav::add('extensions.children.tvocabulary', array(
            'title' => 'Tvocabulary',
            'url' => '#',
            'children' => array(
                'settings' => array(
                    'title' => __d('tvocabulary', 'Recover'),
                    'url' => array('plugin' => 'tvocabulary', 'controller' => 'tvocabulary', 'action' => 'recover', 'Tvocabulary')
                )
            )
        ));        
?>