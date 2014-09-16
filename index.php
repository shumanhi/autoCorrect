<?php

/**
 * This class autoCorrect is based on Peter Norvig's alogrithm for spell checking 
 * http://norvig.com/spell-correct.html
 */
class autoCorrect {

    private $NWORDS = array();

    const ALPHABETS = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * 
     * @param string $file_name
     */
    function __construct($file_name) {
	$file_resource = fopen($file_name, 'r') or die("File {$file_name} not found!");
	while (!feof($file_resource)) {
	    $this->train($this->words(strtolower(fgets($file_resource))));
	}
    }

    /**
     * 
     * @param string $string
     * @return array
     */
    private function words($string) {
	return str_word_count($string, 1);
    }

    /**
     *
     * @param array $training
     */
    private function train($training = array()) {
	foreach ($training as $word) {
	    $this->NWORDS[$word] += 1;
	}
    }

    /**
     * 
     * @param string $word
     * @return array()
     */
    private function edits1($word) {
	$alphabets_array = str_split(self::ALPHABETS);
	$word_length = strlen($word);
	$edits1 = array();
	for ($i = 0; $i < ($word_length + 1); $i++) {
	    //DELETE
	    if ($i < $word_length) {
		$edits1[] = substr_replace($word, '', $i, 1);
	    }
	    //TRANSPOSE
	    if ($i < $word_length - 1) {
		$edits1[] = substr_replace((substr_replace($word, $word[$i + 1], $i, 1)), $word[$i], ($i + 1), 1);
	    }
	    //REPLACE AND INSERTS
	    foreach ($alphabets_array as $char) {
		$edits1[] = substr_replace($word, $char, $i, 0);
		if ($i < $word_length) {
		    $edits1[] = substr_replace($word, $char, $i, 1);
		}
	    }
	}
	return $edits1;
    }

    /**
     * 
     * @param array $words
     * @return string
     */
    private function known($words = array()) {
	$known = false;
	$max = 0;
	foreach ($words as $word) {
	    if (array_key_exists($word, $this->NWORDS)) {
		if ($max < $this->NWORDS[$word]) {
		    $max = $this->NWORDS[$word];
		    $known = $word;
		}
	    }
	}
	return $known;
    }

    /**
     * 
     * @param string $word
     * @return string
     */
    private function known_edits2($word) {
	$known_edits2_filtered = false;
	$max = 0;
	foreach ($this->edits1($word) as $possible_word) {
	    foreach ($this->edits1($possible_word) as $extended_possible_word) {
		if (array_key_exists($extended_possible_word, $this->NWORDS)) {
		    if ($max < $this->NWORDS[$extended_possible_word]) {
			$max = $this->NWORDS[$extended_possible_word];
			$known_edits2_filtered = $extended_possible_word;
		    }
		}
	    }
	}
	return $known_edits2_filtered;
    }

    /**
     * 
     * @param string $word
     * @return mixed
     */
    public function correct($word) {
	$word = strtolower(trim($word));
	if (empty($word))
	    return false;
	if ($this->known(array($word))) {
	    return $word;
	}
	if ($candidate = $this->known($this->edits1($word))) {
	    return $candidate;
	}
	if ($candidate = $this->known_edits2($word)) {
	    return $candidate;
	}
	return $word;
    }

}

?>