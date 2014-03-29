<?php
abstract class AbstractJob
{
	protected $arguments = [];

	public function __construct(array $arguments)
	{
		$this->setArguments($arguments);
	}

	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function getName()
	{
		$name = get_called_class();
		$name = preg_replace('/Job$/', '', $name);
		return TextHelper::convertCase($name,
			TextHelper::UPPER_CAMEL_CASE,
			TextHelper::SPINAL_CASE);
	}

	public abstract function execute(UserEntity $user);
}