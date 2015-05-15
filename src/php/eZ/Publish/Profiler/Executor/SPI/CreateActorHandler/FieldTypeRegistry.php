<?php

namespace eZ\Publish\Profiler\Executor\SPI\CreateActorHandler;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;

class FieldTypeRegistry
{
    protected $fieldTypes = array();

    protected $evaluatedFieldTypes = array();

    public function __construct( FieldTypeCollectionFactory $fieldTypeCollection )
    {
        $this->fieldTypes = $fieldTypeCollection->getFieldTypes();
    }

    /**
     * Get field type
     *
     * @param string $name
     * @return FieldType
     */
    public function getFieldType( $name )
    {
        if (!isset($this->evaluatedFieldTypes[$name])) {
            if (!isset($this->fieldTypes[$name])) {
                throw new \OutOfBoundsException("unknwon field type $name");
            }

            $this->evaluatedFieldTypes[$name] = $this->fieldTypes[$name]();
        }

        return $this->evaluatedFieldTypes[$name];
    }
}
