<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;
use eZ\Publish\Profiler\GaussDistributor;

use eZ\Publish\SPI\Persistence;

class CreateActorHandler extends Handler
{
    protected $handler;

    protected $fieldTypeRegistry;

    public function __construct( Persistence\Handler $handler, CreateActorHandler\FieldTypeRegistry $fieldTypeRegistry )
    {
        $this->handler = $handler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
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
        $languages = array();
        foreach ($actor->type->languageCodes as $languageCode) {
            $languages[$languageCode] = $this->getLanguage($languageCode, "Test ($languageCode)");
        }

        $mainLanguage = reset($languages);
        $type = $this->getContentType( $actor->type, $languages );

        $fields = $this->getFields($actor, $type, $languages);
        $contentHandler = $this->handler->contentHandler();
        $name = md5(microtime());

        $this->handler->beginTransaction();
        $contentCreateSruct = new Persistence\Content\CreateStruct( array(
            'name' => array_fill_keys(array_keys($languages), $name),
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
            'initialLanguageId' => $mainLanguage->id,
            'modified' => time(),
        ) );
        $content = $contentHandler->create($contentCreateSruct);
        $content = $contentHandler->publish(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct( array(
                'publicationDate' => time(),
                'name' => $name,
            ) )
        );
        $content = $this->ageContent($actor, $content);
        $this->handler->commit();

        // Remember created content objects
        $actor->storage->store( $content );

        if ( $actor->subActor !== null )
        {
            $actor->subActor->parentLocationId = $content->versionInfo->contentInfo->mainLocationId;
        }
    }

    /**
     * Age content
     *
     * @param Content $content
     * @return void
     */
    protected function ageContent($actor, $content)
    {
        $contentHandler = $this->handler->contentHandler();
        $versionCount = GaussDistributor::getNumber($actor->type->versionCount) - 1;
        $draft = null;
        for ($i = 0; $i < $versionCount; ++$i) {
            $draft = $contentHandler->createDraftFromVersion(
                $content->versionInfo->contentInfo->id,
                $content->versionInfo->versionNo,
                $content->versionInfo->creatorId
            );
        }

        if ($draft) {
            $content = $contentHandler->publish(
                $draft->versionInfo->contentInfo->id,
                $draft->versionInfo->versionNo,
                new Persistence\Content\MetadataUpdateStruct( array(
                    'publicationDate' => time(),
                    'name' => $content->versionInfo->contentInfo->name,
                ) )
            );
        }

        return $content;
    }

    /**
     * getFields
     *
     * @param mixed $param
     * @return void
     */
    protected function getFields($actor, $type, array $languages)
    {
        $fields = array();
        $mainLanguage = reset($languages);
        foreach ( $type->fieldDefinitions as $fieldDefinition )
        {
            $spiFieldType = $this->getFieldType( $fieldDefinition->fieldType );
            $profilerFieldType = $actor->type->fields[$fieldDefinition->identifier];

            $data = $profilerFieldType->dataProvider->get($mainLanguage->languageCode);
            $value = $spiFieldType->acceptValue( $data );
            $fields[] = new Persistence\Content\Field( array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'languageCode' => $mainLanguage->languageCode,
                'value' => $spiFieldType->toPersistenceValue( $value ),
            ) );

            if ($profilerFieldType->translatable) {
                foreach ($languages as $language) {
                    if ($language == $mainLanguage) {
                        continue;
                    }

                    $data = $profilerFieldType->dataProvider->get($language->languageCode);
                    $value = $spiFieldType->acceptValue( $data );
                    $fields[] = new Persistence\Content\Field( array(
                        'fieldDefinitionId' => $fieldDefinition->id,
                        'type' => $fieldDefinition->fieldType,
                        'languageCode' => $language->languageCode,
                        'value' => $spiFieldType->toPersistenceValue( $value ),
                    ) );
                }
            }
        }

        return $fields;
    }

    /**
     * Get field type
     *
     * @param string $name
     * @return FieldType
     */
    protected function getFieldType( $name )
    {
        return $this->fieldTypeRegistry->getFieldType($name);
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

    protected function getContentTypeGroup(array $languages)
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
                'name' => array_fill_keys(array_keys($languages), 'Profiler Group'),
                'identifier' => $identifier,
                'modified' => time(),
                'modifierId' => 14,
                'created' => time(),
                'creatorId' => 14,
            ) )
        );
    }

    protected function getContentType( $type, array $languages )
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
        $group = $this->getContentTypeGroup($languages);
        foreach ( $type->fields as $name => $field )
        {
            $fields[] = $this->prepareFieldDefinition( $name, $field, $languages, $position++ );
        }

        $mainLanguage = reset($languages);
        return $contentTypeHandler->create(
            new Persistence\Content\Type\CreateStruct( array(
                'name' => array_fill_keys(array_keys($languages), $type->name),
                'status' => Persistence\Content\Type::STATUS_DEFINED,
                'identifier' => $identifier,
                'modified' => time(),
                'modifierId' => 14,
                'created' => time(),
                'creatorId' => 14,
                'remoteId' => md5( microtime() ),
                'isContainer' => true,
                'fieldDefinitions' => $fields,
                'initialLanguageId' => $mainLanguage->id,
                'groupIds' => array( $group->id ),
            ) )
        );
    }

    protected function prepareFieldDefinition( $name, Field $field, array $languages, $position )
    {
        return new Persistence\Content\Type\FieldDefinition( array(
            'name' => array_fill_keys(array_keys($languages), $name),
            'identifier' => $name,
            'fieldType' => $field->getTypeIdentifier(),
            'isTranslatable' => $field->translatable,
            'isSearchable' => $field->searchable,
            'fieldGroup' => 'main',
            'position' => $position,
        ) );
    }
}
