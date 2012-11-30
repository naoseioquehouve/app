<?
abstract class MarketingToolboxModuleService extends WikiaService {
	const CLASS_NAME_PREFIX = 'MarketingToolboxModule';
	const CLASS_NAME_SUFFIX = 'Service';

	abstract protected function getValidationRules();

	static public function getModuleByName($name) {
		$moduleClassName = self::CLASS_NAME_PREFIX . $name . self::CLASS_NAME_SUFFIX;
		return new $moduleClassName();
	}

	public function renderEditor($data) {
		return $this->getView('editor', $data);
	}

	public function validate($data) {
		$rules = $this->getValidationRules();
		// TODO validation here
		return true;
	}

	public function filterData($data) {
		return array_intersect_key($data, $this->getValidationRules());
	}

	protected function getView($viewName, $data, $viewType = WikiaResponse::FORMAT_HTML) {
		return $this->app->getView(get_class($this), $viewName, array('data'=> $data));
	}

}
?>