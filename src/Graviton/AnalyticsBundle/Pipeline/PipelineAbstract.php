<?php
/**
 * abstract class for analytic pipelines
 */
namespace Graviton\AnalyticsBundle\Pipeline;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class PipelineAbstract {

	protected $params = [];
	protected const EMPTY_STRING = '__EMPTY__';
	protected $cleanCount = 4;

	public function setParams(array $params) {
		$this->params = $params;
	}

	public function hasParam($paramName) {
		return isset($this->params[$paramName]);
	}

	public function getParam($paramName) {
		if ($this->hasParam($paramName)) {
			return $this->params[$paramName];
		}
		return null;
	}

	public function get() {
		$cleaned = $this->cleanElements($this->getPipeline());
		for ($i = 0; $i < ($this->cleanCount+1); $i++) {
			$cleaned = $this->cleanElements($cleaned);
		}
		return array_values($cleaned);
	}

	private function cleanElements($pipeline) {
		$cleaned = [];
		foreach ($pipeline as $key => $value) {
			if (is_array($value)) {
				$value = $this->cleanElements($value);
			}

			if ((!is_array($value) && $value != self::EMPTY_STRING) || (is_array($value) && !empty($value))) {
				$cleaned[$key] = $value;
			}
		}
		return $cleaned;
	}

	abstract protected function getPipeline();

}
