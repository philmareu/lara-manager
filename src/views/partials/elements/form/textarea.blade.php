@extends('laramanager::partials.wrappers.form')

@section('field')

    <textarea name="{{ $field['name'] }}"
              id="{{ $field['id'] or '' }}"
              class="{{ $field['class'] or 'uk-width-1-1' }} {{ $errors->has($field['name']) ? 'uk-form-danger' : '' }} uk-textarea"
              rows="4">{{ old($field['name']) ?: (isset($field['value']) ? $field['value'] : '') }}</textarea>

@overwrite