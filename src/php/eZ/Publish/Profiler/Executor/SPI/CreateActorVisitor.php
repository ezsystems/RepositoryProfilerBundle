<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;

use eZ\Publish\SPI\Persistence;
use eZ\Publish\API\Repository\FieldTypeService;

class CreateActorVisitor
{
    protected $handler;

    protected $fieldTypeService;

    protected $types = array();

    public function __construct( Persistence\Handler $handler, FieldTypeService $fieldTypeService )
    {
        $this->handler = $handler;
        $this->fieldTypeService = $fieldTypeService;
    }

    public function visit( Actor\Create $actor )
    {
        $type = $this->checkType( $actor->type );

        $fields = array();
        foreach ( $type->fieldDefinitions as $fieldDefinition )
        {
            $fieldType = $this->fieldTypeService->buildFieldType( $fieldDefinition->fieldType );

            $data = $actor->type->fields[$fieldDefinition->identifier]->dataProvider->get();
            $value = $fieldType->acceptValue( $data );
            $fields[] = new Persistence\Content\Field( array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'languageCode' => 'eng-US',
                'value' => $fieldType->toPersistenceValue( $value ),
            ) );
        }

        $contentHandler = $this->handler->contentHandler();
        $content = $contentHandler->create(
            new Persistence\Content\CreateStruct( array(
                'name' => array(
                    'eng-US' => $name = md5( microtime() ),
                ),
                'typeId' => $type->id,
                'sectionId' => 1,
                'ownerId' => 14,
                'locations' => array(
                    new Persistence\Content\Location\CreateStruct( array(
                        'remoteId' => 23, // Is currently 'ignored' and broken in the schema
                        'parentId' => $actor->parentLocationId,

                    ) ),
                ),
                'fields' => $fields,
                'remoteId' => md5( microtime() ),
                'initialLanguageId' => 2,
                'modified' => time(),
            ) )
        );

        $content = $contentHandler->publish(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct( array(
                'publicationDate' => time(),
                'name' => $name,
            ) )
        );

        // Remember created content objects
        $actor->storage->store( $content );

        if ( $actor->subActor !== null )
        {
            $actor->subActor->parentLocationId = $content->versionInfo->contentInfo->mainLocationId;
        }
    }

    protected function checkType( $type )
    {
        // Check if already loaded in current run
        if ( isset( $this->types[$type->name] ) )
        {
            return $this->types[$type->name];
        }

        // Try to load, if not yet loaded
        $contentTypeHandler = $this->handler->contentTypeHandler();
        $identifier = 'profiler-' . $type->name;
        try {
            return $contentTypeHandler->loadByIdentifier( $identifier );
        }
        catch ( \eZ\Publish\Core\Persistence\Legacy\Exception\TypeNotFound $e )
        {
            // Just continue creating the type
        }

        $fields = array();
        $position = 1;
        foreach ( $type->fields as $name => $field )
        {
            switch ( true )
            {
                case $field instanceof Field\TextLine:
                    $fields[] = $this->prepareFieldDefinition( $name, 'ezstring', $position++ );
                    break;

                case $field instanceof Field\XmlText:
                    $fields[] = $this->prepareFieldDefinition( $name, 'ezxmltext', $position++ );
                    break;

                case $field instanceof Field\Author:
                    $fields[] = $this->prepareFieldDefinition( $name, 'ezauthor', $position++, false );
                    break;

                case $field instanceof Field\TextBlock:
                    $fields[] = $this->prepareFieldDefinition( $name, 'eztext', $position++ );
                    break;

                default:
                    throw new \RuntimeException(
                        "No field handler available for: " . get_class( $field )
                    );
            }
        }

        return $this->types[$type->name] = $contentTypeHandler->create(
            new Persistence\Content\Type\CreateStruct( array(
                'name' => array(
                    'eng-US' => $type->name,
                ),
                'status' => Persistence\Content\Type::STATUS_DEFINED,
                'identifier' => $identifier,
                'modified' => time(),
                'modifierId' => 14,
                'created' => time(),
                'creatorId' => 14,
                'remoteId' => md5( microtime() ),
                'isContainer' => true,
                'fieldDefinitions' => $fields,
                'initialLanguageId' => 'eng-US',
            ) )
        );
    }

    protected function prepareFieldDefinition( $name, $type, $position, $translatable = true )
    {
        return new Persistence\Content\Type\FieldDefinition( array(
            'name' => array(
                'eng' => $name,
            ),
            'identifier' => $name,
            'fieldType' => $type,
            'isTranslatable' => $translatable,
            'fieldGroup' => 'main',
            'position' => $position,
        ) );
    }
}

