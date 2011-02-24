<?php
/**
* TVcoabulary components
*
* @author Juraj Jancuska <jjancuska@gmail.com>
* @copyright (c) 2010 Juraj Jancuska
* @license MIT License - http://www.opensource.org/licenses/mit-license.php
*/
class TvocabularyComponent extends Object {

        /**
         * startup
         * called before controller action
         *
         * @param object $controller
         * @return void
         */
        function startup(&$controller) {

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

                $params = array();
                $params['order'] = array('Node.slug');
                $params['limit'] = 20;
                $params['cache'] = array(
                    'prefix' => 'croogo_nodes_tvocabulary_plugin_',
                    'config' => 'croogo_nodes',
                );

                $term_slugs = array();
                // term view
                if ($this->controller->params['action'] == 'term') {
                        $term_slugs = array($this->controller->params['named']['slug']);
                }
                // node view
                if (($this->controller->params['action'] == 'view')) {
                        $node_params = array_merge($params, array(
                            'conditions' => array('Node.slug' => $this->controller->params['named']['slug']),
                            'contain' => array(
                                'Taxonomy' => array('Term')
                                )
                            ));
                        $node = $this->controller->Node->find('first', $node_params);
                        $term_slugs = Set::extract('/Term/slug', $node['Taxonomy']);
                }

                if (count($term_slugs > 0)) {
                        $term_nodes = array();
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

                        $this->controller->set('term_nodes_for_layout', $term_nodes);
                }

        }

        
}
