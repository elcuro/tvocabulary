<?php
/**
 * TVocabulary helper
 *
 * Helper class for TVocabulary plugin.
 *
 * @package  Croogo
 * @author Juraj Jancuska <jjancuska@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class TVocabularyHelper extends AppHelper {

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
         * contain term parent id of thread
         *
         * @var array
         */
        private $_thread_path = array();

        /**
         * Constructor
         *
         * @param array $options options
         * @access public
         */
        public function __construct($options = array()) {
                $this->View = & ClassRegistry::getObject('view');
                return parent::__construct($options);
        }

        /**
         * Get vocabulary terms
         *
         * @param string $vocabulary_alias
         * @return array
         */
        public function nestedTerms($vocabulary, $options = array()) {
                $_options = array(
                    'linkSelected' => true
                );
                $options = array_merge($_options, $options);

                $output = '';
                $term_id = false;
                if (is_array($vocabulary)) {
                        if (isset($this->View->viewVars['term']['Term']['id'])) {
                                $term_id = $this->View->viewVars['term']['Term']['id'];
                        }
                        $this->__getPath($term_id, $vocabulary);                        
                        $output .= $this->__nestedTerms($vocabulary, $options, $term_id);
                }               
                return $output;

        }

        /**
         * Return generated list of nested terms
         *
         * @param array $terms
         * @param array $options
         * @param integer $depth
         * @return string
         */
        private function __nestedTerms($terms, $options, $req_term_id = false, $depth = 1) {

                $output = '';
                foreach ($terms as $term) {

                        if ($options['link']) {
                                $term_attr = array(
                                    'id' => 'term-' . $term['Term']['id'],
                                );
                                $term_output = $this->Html->link($term['Term']['title'], array(
                                    'plugin' => $options['plugin'],
                                    'controller' => $options['controller'],
                                    'action' => $options['action'],
                                    'type' => $options['type'],
                                    'slug' => $term['Term']['slug']), $term_attr);
                        } else {
                                $term_output = $term['Term']['title'];
                        }

                        $term_parent = $term['Taxonomy']['parent_id'];
                        $term_id = $term['Term']['id'];
                        $term_title = $term['Term']['title'];

                        // add current term_parent id to _thread_path
                        $this->_thread_path[$depth] = $term_parent;
                        
                        // recursion
                        if (isset($term['children']) && count($term['children']) > 0) {
                                $term_output .= $this->__nestedTerms($term['children'], $options, $req_term_id, $depth + 1);
                        }
                                                
                        // trim thread paths to current depth level
                        $term_path = array();
                        if (isset($this->_term_path) && is_array($this->_term_path)) {
                                $this->_thread_path = array_slice($this->_thread_path, 0, $depth, true);
                                $term_path = array_slice($this->_term_path, 0, $depth, true);
                        }

                        // if path is equal or required node has child terms
                        if (($this->_thread_path === $term_path) || ($term_parent == $req_term_id)) {
                                // if is selected term
                                if ($term_id == $req_term_id) {
                                        $term_output = ($options['linkSelected']) ?
                                                $this->Html->tag('li', $term_output, array('class' => 'selected')) :
                                                $this->Html->tag('li', $term_title, array('class' => 'selected'));
                                } else {
                                        $term_output = $this->Html->tag('li', $term_output);
                                }
                        } else {
                                $term_output = '';
                        }
                        
                        $output .= $term_output;
                }
                
                if ($output != null) {
                        $output = $this->Html->tag('ul', $output);                        
                }
                return $output;

        }

        /**
         * Scan for path of current term
         *
         * @param integer $term_id
         * @param array $terms Threaded terms
         * @depth integer $depth
         * @return array
         */
        private function __getPath($term_id, $terms, $depth = 1) {
                $output = array();
                foreach ($terms as $term) {
                        $term_output = array();
                        $term_output['parent_id'] = $term['Taxonomy']['parent_id'];
                        $term_output['term_id'] = $term['Term']['id'];

                        $this->_thread_path = array_slice($this->_thread_path, 0, $depth, true);
                        $this->_thread_path[$depth] = $term_output['parent_id'];
                        if ($term_output['term_id'] == $term_id) {
                                // save current _thread_path to _term_path
                                $this->_term_path = $this->_thread_path;
                        }

                        if (isset($term['children']) && count($term['children']) > 0) {
                                $term_output['children'] = $this->__getPath($term_id, $term['children'], $depth + 1);
                        }                                                                      

                        $output[] = $term_output;
                }                
                return $output;
        }

        /**
         * Get current term_id from view vars
         *
         * @return void
         */
        private function __getTermId() {

                // term view

                // node view
        }

}
 ?>