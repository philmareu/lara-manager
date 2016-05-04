<?php

namespace Philsquare\LaraManager\Repositories;

use Illuminate\Support\Facades\Storage;
use Philsquare\LaraManager\Exceptions\FilenameExistsException;
use Philsquare\LaraManager\Models\Image;

class ImageRepository {

    protected $model;

    public function __construct()
    {
        $this->model = new Image;
    }

    public function create($attributes)
    {
        return $this->model->create($attributes);
    }

    public function getById($id)
    {
        return $this->model->whereId($id)->first();
    }

    public function getPaginated()
    {
        return $this->model->latest()->paginate(100);
    }

    public function update($id, $attributes)
    {
        $image = $this->getById($id);
        $image->update($attributes);
        $this->updateFilenameIfChanged($image->filename, $attributes['filename']);

        return $image;
    }

    public function delete($id)
    {
        return $this->getById($id)->delete();
    }

    private function filenameWasChanged($old, $new)
    {
        return $old != $new;
    }

    private function updateFilenameIfChanged($old, $new)
    {
        if($this->filenameWasChanged($old, $new)) {
            Storage::move('laramanager/images/' . $old, 'laramanager/images/' . $new);
        }
    }
}