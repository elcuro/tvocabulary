<?php

/**
 * Tvocabulary Controller
 *
 *
 */
class TvocabularyController extends TvocabularyAppController {

	/**
	 * Controller name
	 *
	 * @var string
	 * @access public
	 */
	public $name = 'Tvocabulary';

	/**
	 * Recover taxonomy tree (tree behavior)
	 *
	 * @return void
	 **/
	public function admin_recover() {
	
		$this->Node->Taxonomy->recover();
		Cache::clear();
		$this->Session->setFlash(__d('tvocabulary', 'Taxonomy tree successfully recovered, and cache cleared'), 'default', array('class' => 'success'));
		$this->redirect(array(
			'plugin' => false,
			'controller' => 'nodes',
			'action' => 'index')
		);
	}

}
