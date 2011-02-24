<div id="vocabulary-<?php echo $vocabulary['Vocabulary']['id']; ?>" class="vocabulary">
<?php
    echo $this->tvocabulary->nestedTerms($vocabulary['threaded'], $options);
?>
</div>
