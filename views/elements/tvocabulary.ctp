<div id="vocabulary-<?php echo $vocabulary['Vocabulary']['id']; ?>" class="vocabulary">
<?php
    echo $tvocabulary->nestedTerms($vocabulary['threaded'], $options);
?>
</div>