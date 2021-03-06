<?php

namespace App\Content; 

use App\Field;
use App\Space;
use App\Theme;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FieldTypeOptionsSelect {

    use FieldTypeTrait;

    private $template_add = 'admin.space.content.field_options_select_add';
    private $template_edit = 'admin.space.content.field_options_select_edit';

    const OPTIONS_SELECT_DEFAULT = '__isvr_options-select-default';    

    /**
     * Create a new field instance.
     *
     * @return void
     */
    public function __construct() {
    }


    /**
     * Prepare template.
     *
     * @param int $space_id
     * @param String $field_key
     * @param Array $field_properties
     * @param Array $all_fields 
     *
     * @return Array
     */
    public function prepare($space_id, $field_key, $field_properties, $all_fields) {

        $field = [];
        $field = $field_properties;
        $field['#template'] = $this->template_add;

        if (isset($field['#required']) && $field['#required'] == false) {
            /* union */
            $field['#options'] = array(FieldTypeOptionsSelect::OPTIONS_SELECT_DEFAULT => trans('fieldtype_options_select.select_option')) + $field['#options']; 
        }

        if (!isset($field_properties['#default_value'])) {
            $field['#default_value'] = null;
        }

        return $field;
    }


    /**
     * Load content.
     *
     * @param int $space_id
     * @param int $content_id
     * @param String $field_key
     * @param Array $properties
     * @param Array $all_fields
     *
     * @return Array
     */
    public function load($space_id, $content_id, $field_key, $properties, $all_fields) {

        $field_arr = [];

        $field_arr = $this->prepare($space_id, $field_key, $properties, $all_fields);
        $field_arr['#template'] = $this->template_edit;

        if (isset($field_arr['#required']) && $field_arr['#required'] == false) {
            /* union */
            $field_arr['#options'] = array(FieldTypeOptionsSelect::OPTIONS_SELECT_DEFAULT => trans('fieldtype_options_select.select_option')) + $field_arr['#options']; 
        }

        try {
            $field = Field::where('content_id', $content_id)->where('key', $field_key)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $field_arr['#content'] = array('#value' => FieldTypeOptionsSelect::OPTIONS_SELECT_DEFAULT);
            return $field_arr;
        }

        $field_arr['#content'] = array('#value' => $field->data);

        return $field_arr;
    }


    /**
     * Get validation rules and messages.
     *
     * @param Request $request
     * @param Array $validation_rules_messages
     * @param String $field_key
     * @param Array $properties
     *
     * @return Array
     */
    public function get_validation_rules_messages($request, $validation_rules_messages, $field_key, $properties) {

        return $validation_rules_messages;
    }


    /**
     * Save entry.
     *
     * @param int $space_id
     * @param int $content_id
     * @param String $field_key
     * @param String $type
     * @param Array $request_all
     *
     * @return True
     */
    public function save($space_id, $content_id, $field_key, $type, $request_all) {

        if ($request_all[$field_key] != FieldTypeOptionsSelect::OPTIONS_SELECT_DEFAULT) {

            try {
                /* there is only one field key per content (id) */
                $field = Field::where('content_id', $content_id)->where('key', $field_key)->firstOrFail();
                $field->data = $request_all[$field_key];
                $field->save();

            } catch (ModelNotFoundException $e) {

                $field = new Field;
                $field->content_id = $content_id;
                $field->key = $field_key;
                $field->type = $type;
                $field->data = $request_all[$field_key];
                $field->save();
            }

        } else {

            try {
                $field = Field::where('content_id', $content_id)->where('key', $field_key)->firstOrFail();
                $field->delete();
            } catch (ModelNotFoundException $e) {
            }
        }

        return true;
    }


    /**
     * Delete content.
     *
     * @param integer $content_id
     * @param String $field_key
     * @param Array $properties
     *
     * @return Array
     */
    public function delete($content_id, $field_key, $properties) {

        try {
            $field = Field::where('content_id', $content_id)->where('key', $field_key)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return;
        }
        $field->delete();
    }


    /**
     * Validate theme config field.
     *
     * @param Array $field
     *
     * @return True if valid, false otherwise.
     */
    public function validateThemeFieldType($field) {

        $mandatoryKeys = [
            '#label' => 'string',
            '#help' => 'string',
            '#options' => 'array',
            '#required' => 'boolean'];
      
        return $this->validateFieldType($mandatoryKeys, $field);
    }


    /**
     * Load content for theme.
     *
     * @param Field $field
     *
     * @return Array
     */
    public function loadContent($field) {

        $content_arr = [];

        $content_arr['#id'] = $field->id;
        $content_arr['#content-id'] = $field->content_id;
        $content_arr['#type'] = $field->type;
        $content_arr['#value'] = $field->data;

        return $content_arr;
    }


}
