<div id="vocabulary-<?php echo $vocabulary['Vocabulary']['id']; ?>" class="vocabulary">
<?php
    echo $this->Tvocabulary->nestedTerms($vocabulary['threaded'], $options);
?>
</div>
