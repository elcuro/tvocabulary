<?php
/**
* TVcoabulary component
*
* @author Juraj Jancuska <jjancuska@gmail.com>
* @copyright (c) 2010 Juraj Jancuska
* @license MIT License - http://www.opensource.org/licenses/mit-license.php
*/
class TvocabularyComponent extends Object {

        /**
         * beforeRender
         *
         * @param object $controller
         * @return array
         */
        public function beforeRender(&$controller) {

                $this->controller =& $controller;
                if (!isset($this->controller->params['admin']) && !isset($this->controller->params['requested'])) {
                        $this->termNodes();
                }

        }

        /**
         * Set term nodes for layout
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
                        $params['cache'] = array(
                            'prefix' => 'croogo_nodes_tvocabulary_plugin_',
                            'config' => 'croogo_nodes',
                        );

                        $term_slugs = Set::extract('/Term/slug', $this->controller->viewVars['node']['Taxonomy']);
                        if (count($term_slugs > 0)) {
                                foreach ($term_slugs as $slug) {
                                        $params['conditions'] = array(
                                            'Node.status' => 1,
                                            'Node.terms LIKE' => '%"' . $slug . '"%',
                                            array('OR' => array(
                                                'Node.visibility_roles' => '',
                                                'Node.visibility_roles LIKE' => '%"' . $this->controller->Croogo->roleId . '"%'
                                            ))
                                        );
                                        $term_nodes[$slug] = $this->controller->Node->find('all', $params);
                                }

                        }
                }
                $this->controller->set('term_nodes_for_layout', $term_nodes);

        }

        
}
