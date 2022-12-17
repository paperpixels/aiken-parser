<?php namespace Fisdap\Aiken\Parser;

use Fisdap\Aiken\Parser\Contracts\Arrayable;

/**
 * Class TestItem
 *
 * Class representing a single test item
 *
 * @package Fisdap\Aiken\Parser
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class TestItem implements Arrayable
{
    const STEM = 'stem';
	const DISTRACTORS = 'distractors';
	const CORRECT_ANSWER = 'correctAnswer';
	const CORRECT_ANSWER_ID = 'correctAnswerId';

    const CORRECT_ANSWER_LINE_DETECTOR_SLUG = 'ANSWER: ';

    /**
     * Correct answer detector slugs
     *
     * @var array
     */
    public static $correctAnswerDetectorSlugs = [
        self::CORRECT_ANSWER_LINE_DETECTOR_SLUG,
    ];

    /**
     * Keys used to define distractors from the answer
     * By default this is initialized to A, B, C, D, E, F, G, H, I, J...
     *
     * @var array
     */
    public static $distractorDetectorSlugs = [];

	/**
	 * @return array
	 */
	public static function getDistractorDetectorSlugs(){
		if( empty( self::$distractorDetectorSlugs ) ){
			self::$distractorDetectorSlugs = range('A', 'Z');
		}

		return self::$distractorDetectorSlugs;
	}

	/**
     * Test item stem
     *
     * @var string
     */
    protected $stem;

    /**
     * Test item distractor collection
     *
     * @var DistractorCollection
     */
    private $distractors;

    /**
     * Test item correct answer
     *
     * @var string
     */
    protected $correctAnswer;

	/**
	 * Test item correct answer key
	 *
	 * @var int
	 */
	protected $correctAnswerId;

	/**
     * Get collection of distractors object
     *
     * @return DistractorCollection
     */
    protected function getDistractorCollection()
    {
        if (!$this->distractors) {
            $this->distractors = new DistractorCollection();
        }
        return $this->distractors;
    }

    /**
     * Append a distractor to the array
     *
     * @param Distractor $distractor
     * @return $this
     */
    public function appendDistractor(Distractor $distractor)
    {
        $this->getDistractorCollection()->append($distractor);
        return $this;
    }

    /**
     * Set the test item stem
     *
     * @param $stem
     * @return $this
     */
    public function setStem($stem)
    {
        $this->stem = $stem;
        return $this;
    }

    /**
     * Set the correct answer
     *
     * @param $answerKey
     * @return $this
     * @throws \Exception
     */
    public function setCorrectAnswer($answerKey)
    {
	    $this->correctAnswer   = $this->getDistractorCollection()->getCorrectAnswerValue( $answerKey );
	    $this->correctAnswerId = array_search( $answerKey, array_values( self::getDistractorDetectorSlugs() ) );

        return $this;
    }

    /**
     * Validate the test item has everything it needs
     *
     * @throws \Exception
     */
    public function validate()
    {
        if (count($this->getDistractorCollection()->toArray()) < 2) {
            throw new \Exception('An issue was encountered with the following text: ' . $this->stem . '.  Please check this file for leading and trailing spaces. No items were imported.');
        }

        if (empty($this->stem)) {
            throw new \Exception('Your Items were not imported.  A question is missing a stem. Please review the format of your Aiken file and upload again.');
        }

        if (empty($this->correctAnswer)) {
            throw new \Exception('Your Items were not imported.  This question does not have a correct answer.  Check to make sure the distractors do not have any extra space before or after the beginning letter. Look at the item with STEM: ' . $this->stem);
        }
    }

    /**
     * Validate that a test item does not have too many distractors
     *
     * @throws \Exception
     */
    public function validateDoesNotHaveTooManyDistractors()
    {
		// uncomment if you want to limit the distractors
	    /*
        if (count($this->getDistractorCollection()->toArray()) > 4) {
            throw new \Exception('An issue was encountered with the following text: ' . $this->stem . '.  This stem has too many distractors. Check that an ANSWER is not missing from previous test item.');
        }
	    */
    }

    /**
     * Return object as array
     *
     * @return array
     * @throws \Exception
     */
    public function toArray()
    {
        $this->validate();

        return [
	        self::STEM              => $this->stem,
	        self::DISTRACTORS       => $this->getDistractorCollection()->toArray(),
	        self::CORRECT_ANSWER    => $this->correctAnswer,
	        self::CORRECT_ANSWER_ID => $this->correctAnswerId,
        ];
    }
}
