<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;

use eZ\Publish\SPI\Persistence;
use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;

class CreateActorHandler extends Handler
{
    protected $handler;

    protected $fieldTypeCollection;

    public function __construct( Persistence\Handler $handler, FieldTypeCollectionFactory $fieldTypeCollection )
    {
        $this->handler = $handler;
        $this->fieldTypeCollection = $fieldTypeCollection;
    }

    /**
     * Can handle
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\Create;
    }

    /**
     * Handle
     *
     * @param Actor $actor
     * @return void
     */
    public function handle(Actor $actor)
    {
        $language = $this->getLanguage( 'eng-US', 'English (US)' );
        $type = $this->getContentType( $actor->type, $language );

        $fields = array();
        foreach ( $type->fieldDefinitions as $fieldDefinition )
        {
            $fieldType = $this->getFieldType( $fieldDefinition->fieldType );

            $data = $actor->type->fields[$fieldDefinition->identifier]->dataProvider->get();
            $value = $fieldType->acceptValue( $data );
            $fields[] = new Persistence\Content\Field( array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'languageCode' => $language->languageCode,
                'value' => $fieldType->toPersistenceValue( $value ),
            ) );
        }

        $contentHandler = $this->handler->contentHandler();
        $content = $contentHandler->create(
            new Persistence\Content\CreateStruct( array(
                'name' => array(
                    $language->languageCode => $name = md5( microtime() ),
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
                'initialLanguageId' => $language->id,
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

    /**
     * Get field type
     *
     * @param string $name
     * @return FieldType
     */
    protected function getFieldType( $name )
    {
        $fieldTypes = $this->fieldTypeCollection->getFieldTypes();

        if (isset($fieldTypes[$name])) {
            return $fieldTypes[$name]();
        }

        throw new \OutOfBoundsException("unknwon field type $name");
    }

    /**
     * Get language
     *
     * @param string $languageCode
     * @return void
     */
    protected function getLanguage($languageCode, $name)
    {
        $languageHandler = $this->handler->contentLanguageHandler();

        try {
            return $languageHandler->loadByLanguageCode( $languageCode );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // Just continue creating the type
        }

        return $languageHandler->create(
            new Persistence\Content\Language\CreateStruct( array(
                'languageCode' => $languageCode,
                'name' => $name,
                'isEnabled' => true,
            ) )
        );
    }

    protected function getContentTypeGroup($language)
    {
        $contentTypeHandler = $this->handler->contentTypeHandler();
        $identifier = 'profiler-content-type-group';
        try {
            return $contentTypeHandler->loadGroupByIdentifier( $identifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // Just continue creating the type
        }

        return $contentTypeHandler->createGroup(
            new Persistence\Content\Type\Group\CreateStruct( array(
                'name' => array(
                    $language->languageCode => 'Profiler Group'
                ),
                'identifier' => $identifier,
                'modified' => time(),
                'modifierId' => 14,
                'created' => time(),
                'creatorId' => 14,
            ) )
        );
    }

    protected function getContentType( $type, $language )
    {
        $contentTypeHandler = $this->handler->contentTypeHandler();
        $identifier = 'profiler-' . $type->name;
        try {
            return $contentTypeHandler->loadByIdentifier( $identifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // Just continue creating the type
        }

        $fields = array();
        $position = 1;
        $group = $this->getContentTypeGroup($language);
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

        return $contentTypeHandler->create(
            new Persistence\Content\Type\CreateStruct( array(
                'name' => array(
                    $language->languageCode => $type->name,
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
                'initialLanguageId' => $language->id,
                'groupIds' => array( $group->id ),
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

