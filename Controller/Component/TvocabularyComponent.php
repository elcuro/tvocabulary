<?php

/**
 * TVcoabulary component
 *
 * @author Juraj Jancuska <jjancuska@gmail.com>
 * @copyright (c) 2010 Juraj Jancuska
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TvocabularyComponent extends Object {

       public function startup(Controller $controller) {              
       }
       
       public function initialize(Controller $controller) {              
       }       
       
       public function shutdown(Controller $controller) {              
       }       
       
       public function beforeRedirect(Controller $controller) {              
       } 
       

       /**
        * beforeRender
        *
        * @param object $controller
        * @return array
        */
       public function beforeRender(Controller $controller) {

              $this->controller = $controller;
              if (!isset($this->controller->params['admin']) && !isset($this->controller->params['requested'])) {
                     $this->taxonomyPath();
                     $this->termNodes();
              }
       }

       /**
        * Set taxonomy path for layout
        *
        * @return void
        */
       public function taxonomyPath() {

              $path = array(0 => array('Taxonomy' => array(
                          'parent_id' => null,
                          'term_id' => null,
                          'id' => null
                  )));

              // term view
              if (isset($this->controller->viewVars['term']['Term']['id'])) {
                     $term_id = $this->controller->viewVars['term']['Term']['id'];
                     $this->controller->Node->Taxonomy->recursive = 0;
                     $taxonomy = $this->controller->Node->Taxonomy->find('first', array(
                         'conditions' => array('Taxonomy.term_id' => $term_id),
                         'fields' => 'Taxonomy.id',
                         'cache' => array(
                             'name' => 'nodes_taxonomy_id_' . $term_id,
                             'config' => 'nodes_term')
                             ));
                     $path = $this->controller->Node->Taxonomy->getPath($taxonomy['Taxonomy']['id']);
              }

              // node view
              if (isset($this->controller->viewVars['node']['Taxonomy'][0])) {
                     //$this->controller->Node->Taxonomy->recover();
                     if (isset($this->controller->viewVars['type']['Params']['master_vocabulary_id'])) {
                            // set path to longest from vocabulary in Type->Params->master_vocabulary_id
                            $master_vocabulary_id = $this->controller->viewVars['type']['Params']['master_vocabulary_id'];
                            $master_taxonomies = Set::extract('/Taxonomy[vocabulary_id=' . $master_vocabulary_id . ']', 
                                    $this->controller->viewVars['node']);

                            foreach ($master_taxonomies as $taxonomy) {
                                   // get longest path
                                   $tmp_path = $this->controller->Node->Taxonomy->getPath(
                                           $taxonomy['Taxonomy']['id']);
                                   if (count($tmp_path) >= count($path))
                                          $path = $tmp_path;
                            }
                     } else {
                            // set path to first finded taxonomy
                            $path = $this->controller->Node->Taxonomy->getPath(
                                    $this->controller->viewVars['node']['Taxonomy'][0]['id']);
                     }
              }
              $this->controller->set('taxonomy_path', $path);
       }

       /**
        * Set children Nodes of for layout
        *
        * @return void
        */
       public function termNodes() {

              $term_nodes = array();

              // term view
              if (isset($this->controller->viewVars['term']['Term']['id'])) {
                     $term_nodes[$this->controller->viewVars['term']['Term']['slug']] = $this->controller->viewVars['nodes'];
              }

              // node view
              if (isset($this->controller->viewVars['node']['Taxonomy'])) {
                     $term_slugs = array();
                     $params = array();
                     $params['order'] = array('Node.created DESC');
                     $params['limit'] = 20;


                     $term_slugs = Set::extract('/Term/slug', $this->controller->viewVars['node']['Taxonomy']);
                     if (count($term_slugs > 0)) {
                            foreach ($term_slugs as $slug) {
                                   $params['cache'] = array(
                                       'prefix' => 'tvocabulary_plugin_child_nodes_' . $slug,
                                       'config' => 'croogo_nodes',
                                   );
                                   $params['conditions'] = array(
                                       'Node.status' => 1,
                                       'Node.terms LIKE' => '%"' . $slug . '"%',
                                       array('OR' => array(
                                               'Node.visibility_roles' => '',
                                               'Node.visibility_roles LIKE' => '%"' . $this->controller->Croogo->roleId . '"%'
                                       ))
                                   );
                                   $params['order'] = array('Node.created DESC');
                                   $term_nodes[$slug] = $this->controller->Node->find('all', $params);
                            }
                     }
              }
              $this->controller->set('term_nodes_for_layout', $term_nodes);
       }

}
