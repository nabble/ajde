<?php

class Ajde_Crud_Cms_Meta_Type_Spatial extends Ajde_Crud_Cms_Meta_Type
{
    private $_uploaddir = UPLOAD_DIR;

    public function getFields()
    {
        $this->required();
        $this->readonly();
        $this->help();
        $this->defaultValue();
        $this->useImage();
        $this->media();

        return parent::getFields();
    }

    public function useImage()
    {
        $field = $this->fieldFactory('spatialtype');
        $field->setLabel('Type');
        $field->setType('enum');
        $field->setLength('Google Maps,Image');
        $field->setIsRequired(true);
        $this->addField($field);
    }

    public function media()
    {
        $field = $this->fieldFactory('media');
        $field->setType('fk');
        $field->setModelName('media');
        $field->addShowOnlyWhen('spatialtype', 'image');
        $field->setUsePopupSelector(true);
        $field->setListRoute('admin/media:view.crud');
        $field->setUseImage(true);
        $field->addTableFileField('thumbnail', $this->_uploaddir);
        $field->setThumbDim(300, 100);
        $field->setIsRequired(false);
        $this->addField($field);
    }

    public function getMetaField(MetaModel $meta)
    {
        $field = $this->decorationFactory($meta);
        $field->setType('spatial');
        if ($meta->getOption('spatialtype') === 'Image') {
            $media = new MediaModel();
            $media->loadByPK($meta->getOption('media'));
            $field->setUseImage(true);
            $field->setLayerImage($this->_uploaddir.$media->get('thumbnail'));
        }

        return $field;
    }
}
