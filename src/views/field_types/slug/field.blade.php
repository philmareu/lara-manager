@include('laramanager::partials.elements.form.slug', [
    'field' => [
        'name' => $field->slug,
        'id' => 'slug',
        'value' => isset($entry) ? $entry->{$field->slug} : null
    ]
])