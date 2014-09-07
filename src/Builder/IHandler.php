<?php
namespace Librette\Doctrine\Forms\Builder;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @author David Matejka
 */
interface IHandler
{

	/**
	 * @param string
	 * @param array
	 * @param ClassMetadata
	 * @param Configuration
	 * @return IBuilder|null
	 */
	public function handle($name, array $options, ClassMetadata $classMetadata, Configuration $configuration);
}
