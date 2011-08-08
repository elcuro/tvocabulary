<?php
/**
* Tvocabulary behavior, recover() terms table after each save
*
* @author Juraj Jancuska <jjancuska@gmail.com>
* @copyright (c) 2010 Juraj Jancuska
* @license MIT License - http://www.opensource.org/licenses/mit-license.php
*/
class TvocabularyBehavior extends ModelBehavior {

        /**
         * Setup
         *
         * @param object $model
         * @param array $settings
         * @return array
         */
        public function setup(&$model, $settings) {

        }

        /**
         * After save call back
         *
         * @param object $mode;
         * @param boolean $created
         * @return array
         */
        public function afterSave(&$model, $created = false) {

                $model->Taxonomy->recover();

                return true;
        }


}
?>