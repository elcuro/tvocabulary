<?php
/**
 * TVocabulary helper
 *
 * Helper class for Tvocabulary plugin.
 *
 * @package  Croogo
 * @author Juraj Jancuska <jjancuska@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class TvocabularyHelper extends AppHelper {

        /**
         * Used helpers
         *
         * @var array
         */
        public $helpers = array(
            'Html',
            'Layout'
        );

        /**
         * Get vocabulary terms
         *
         * @param string $vocabulary_alias
         * @return array
         */
        public function nestedTerms($vocabulary, $options = array()) {

                $_options = array(
                    'linkSelected' => true,
                    'showNodes' => 'true',
                    'nodeController' => 'nodes',
                    'nodeAction' => 'view',
                    'nodePlugin' => false,
                    'nodeTagAttributes' => array(),
                    'nodeTag' => 'ul'
                );
                $options = array_merge($_options, $options);

                $output = '';
                $term_id = false;
                if (is_array($vocabulary)) {
                        $term_id = $this->__getTermId($vocabulary);
                        $output .= $this->__nestedTerms($vocabulary, $options, $term_id);
                }
                return $output;

        }

        /**
         * Return generated list of nested terms
         *
         * @param array $terms
         * @param array $options
         * @param integer $rew_term_id
         * @param integer $depth
         * @return string
         */
        private function __nestedTerms($terms, $options, $req_term_id = false, $depth = 0) {

                $output = '';
                $taxonomy_path = $this->Layout->View->viewVars['taxonomy_path'];

                foreach ($terms as $term) {
                        
                        // term link
                        if ($options['link']) {
                                $term_list_item = $this->Html->link($term['Term']['title'], array(
                                    'plugin' => $options['plugin'],
                                    'controller' => $options['controller'],
                                    'action' => $options['action'],
                                    'type' => $options['type'],
                                    'slug' => $term['Term']['slug']),
                                        array('id' => 'term-' . $term['Term']['id']));
                        } else {
                                $term_list_item = $term['Term']['title'];
                        }
      
                        // recursion
                        if (isset($term['children']) && count($term['children']) > 0) {
                                $term_has_children = true;
                                $term_list_item .= $this->__nestedTerms($term['children'], $options, $req_term_id, $depth + 1);
                        }

                        // is term in taxonomy path
                        if ($this->__isTermInTaxonomyPath($term, $taxonomy_path, $depth)) {
                                // is this term selected
                                if ($term['Taxonomy']['term_id'] == $req_term_id) {
                                        $term_list_item = $this->Html->tag('li', $term_list_item, array('class' => 'selected'));
                                        // show child Nodes
                                        if (!isset($term_has_children) && ($options['showNodes'] == 'true')) {
                                                $term_list_item .= $this->__nodesList($term['Term']['slug'], $options);
                                        }
                                } else {
                                        $term_list_item = $this->Html->tag('li', $term_list_item);
                                }
                        } else {
                                $term_list_item = '';
                        }
                        
                        $output .= $term_list_item;
                }
                
                if ($output != '') {
                        $output = $this->Html->tag($options['tag'], $output, $options['tagAttributes']);
                }
                return $output;
        }

        /**
         * Check if is term in taxonomy path
         *
         * @param array $term
         * @param array $taxonomy_path
         * @param int $depth
         * @return boolean
         */
        private function __isTermInTaxonomyPath($term, $taxonomy_path, $depth) {
                if (isset ($taxonomy_path[$depth-1])) {
                       if ($term['Taxonomy']['parent_id'] == $taxonomy_path[$depth - 1]['Taxonomy']['id']) {
                               return true;
                       }
                }
                if (isset ($taxonomy_path[$depth])) {
                       if ($term['Taxonomy']['parent_id'] == $taxonomy_path[$depth]['Taxonomy']['parent_id']) {
                               return true;
                       }
                }
                return false;
        }

        /**
         * Nodes list
         *
         * @param string $term_slug
         * @return string
         */
        private function __nodesList($term_slug, $options) {
                $output = '';
                $term_nodes = $this->Layout->View->viewVars['term_nodes_for_layout'];
                if (isset($term_nodes[$term_slug])) {
                        foreach ($term_nodes[$term_slug] as $node) {
                                if ($options['link']) {
                                        $node_attr = array(
                                            'id' => 'node-' . $node['Node']['id'],
                                        );
                                        $li_attr = array();
                                        // check if is it selected node
                                        if (isset($this->Layout->View->viewVars['node']) &&
                                                ($this->Layout->View->viewVars['node']['Node']['id'] == $node['Node']['id'])) {
                                                $li_attr['class'] = 'selected-node';
                                        }
                                        $node_output = $this->Html->link($node['Node']['title'], array(
                                            'controller' => $options['nodeController'],
                                            'action' => $options['nodeAction'],
                                            'plugin' => $options['nodePlugin'],
                                            'slug' => $node['Node']['slug'],
                                            'type' => $node['Node']['type']
                                        ), $node_attr);
                                        $node_output = $this->Html->tag('li', $node_output, $li_attr);
                                } else {
                                        $node_output = $node['Node']['title'];
                                }
                                $output .= $node_output;
                        }
                        if (!empty($output)) {
                                $output = $this->Html->tag('ul', $output);
                        }
                }
                return $output;
        }

        /**
         * Get current term_id from view vars
         *
         * @param array $vocabulary
         * @return integer
         */
        private function __getTermId($vocabulary) {
                 $vocabulary_id = Set::classicExtract($vocabulary, '0.Taxonomy.vocabulary_id');

                // term view
                if (isset($this->Layout->View->viewVars['term']['Term']['id'])) {
                        $term_id = $this->Layout->View->viewVars['term']['Term']['id'];
                        //check if is vocabulary for this term
                        $term_vocabulary_id = Set::classicExtract($this->Layout->View->viewVars['term']['Vocabulary'], '0.id');                        
                        if ($vocabulary_id == $term_vocabulary_id) {
                                return $term_id;
                        }
                }

                // node view
                if (isset($this->Layout->View->viewVars['node']['Taxonomy'])) {
                        //check if is vocabulary for this node
                        foreach($this->Layout->View->viewVars['node']['Taxonomy'] as $taxonomy) {
                                if ($vocabulary_id == $taxonomy['vocabulary_id']) {
                                        return $taxonomy['Term']['id'];
                                }
                        }
                }
                return 0;
        }

}
 ?>